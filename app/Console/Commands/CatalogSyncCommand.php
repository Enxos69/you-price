<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CatalogSyncCommand extends Command
{
    protected $signature = 'catalog:sync
                            {--file= : Percorso file JSON locale (per test senza API)}
                            {--force : Esegui anche se il limite giornaliero (4 download) è stato raggiunto}
                            {--source=cron : Origine del sync (cron|manual)}
                            {--log-id= : ID log preesistente da aggiornare (usato dal trigger manuale)}';

    protected $description = 'Scarica e sincronizza il catalogo CruiseHost nel database locale';

    private const MAX_DAILY_SYNCS = 4;

    public function handle(): int
    {
        if (! $this->checkDailyLimit()) {
            $this->error('Limite giornaliero di ' . self::MAX_DAILY_SYNCS . ' sync raggiunto. Usa --force per ignorarlo.');
            return self::FAILURE;
        }

        $source = $this->option('source') === 'manual' ? 'manual' : 'cron';

        if ($existingLogId = $this->option('log-id')) {
            $logId = (int) $existingLogId;
            DB::table('catalog_sync_log')->where('id', $logId)->update([
                'started_at'   => now(),
                'status'       => 'running',
                'triggered_by' => $source,
            ]);
        } else {
            $logId = DB::table('catalog_sync_log')->insertGetId([
                'started_at'   => now(),
                'status'       => 'running',
                'triggered_by' => $source,
            ]);
        }

        $this->info("Sync avviato (log ID: {$logId})");

        try {
            Log::info('[CatalogSync] Avvio sync', ['log_id' => $logId, 'source' => $source]);
            $this->logProgress($logId, 'Download catalogo da CruiseHost...');
            $catalog = $this->loadCatalog();
            $this->info('Catalogo caricato — avvio importazione...');
            Log::info('[CatalogSync] Catalogo caricato in memoria');

            $stats = ['products_imported' => 0, 'prices_recorded' => 0];

            $this->logProgress($logId, 'Recupero aree da /search...');
            $searchAreas = $this->fetchAreasFromSearch();
            Log::info('[CatalogSync] Aree da /search', ['count' => count($searchAreas)]);

            $syncStart = now();

            $this->logProgress($logId, 'Importazione cruise lines, aree, porti, navi...');
            Log::info('[CatalogSync] Avvio transazione DB');
            DB::transaction(function () use ($catalog, $searchAreas, &$stats, $logId) {
                Log::info('[CatalogSync] Sync cruise lines');
                $this->syncCruiseLines($catalog['cruiselines'] ?? []);
                Log::info('[CatalogSync] Sync aree');
                $areaIds = $this->syncAreas(array_merge($searchAreas, $catalog['areas'] ?? []));
                Log::info('[CatalogSync] Sync porti');
                $this->syncPorts($catalog['ports'] ?? []);
                Log::info('[CatalogSync] Sync navi');
                $this->syncShips($catalog['ships'] ?? []);
                $nProd = count($catalog['products'] ?? []);
                $this->logProgress($logId, "Importazione prodotti e prezzi ({$nProd} prodotti)...");
                Log::info('[CatalogSync] Sync prodotti', ['count' => $nProd]);
                $this->syncProducts($catalog['products'] ?? [], $stats, $areaIds);
                Log::info('[CatalogSync] Sync prodotti completato', $stats);
            });

            // Soft-delete partenze future non presenti nel catalogo appena scaricato
            $stale = \App\Models\Departure::future()
                ->where(fn($q) => $q->whereNull('synced_at')->orWhere('synced_at', '<', $syncStart))
                ->count();

            if ($stale > 0) {
                \App\Models\Departure::future()
                    ->where(fn($q) => $q->whereNull('synced_at')->orWhere('synced_at', '<', $syncStart))
                    ->delete();
                $this->warn("  {$stale} partenze future rimosse dal catalogo → soft-deleted.");
            }

            DB::table('catalog_sync_log')->where('id', $logId)->update([
                'finished_at'       => now(),
                'products_imported' => $stats['products_imported'],
                'prices_recorded'   => $stats['prices_recorded'],
                'status'            => 'completed',
                'notes'             => null,
            ]);

            $this->info("Completato: {$stats['products_imported']} prodotti, {$stats['prices_recorded']} prezzi.");
            return self::SUCCESS;

        } catch (\Throwable $e) {
            DB::table('catalog_sync_log')->where('id', $logId)->update([
                'finished_at' => now(),
                'status'      => 'failed',
                'notes'       => $e->getMessage(),
            ]);

            $this->error('Sync fallito: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    // -------------------------------------------------------------------------
    // Load
    // -------------------------------------------------------------------------

    private function checkDailyLimit(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        $count = DB::table('catalog_sync_log')
            ->where('started_at', '>=', today())
            ->where('status', '!=', 'failed')
            ->count();

        return $count < self::MAX_DAILY_SYNCS;
    }

    private function logProgress(int $logId, string $message): void
    {
        DB::table('catalog_sync_log')->where('id', $logId)->update(['notes' => $message]);
        $this->line('  ' . $message);
    }

    private function loadCatalog(): array
    {
        if ($file = $this->option('file')) {
            $this->line("  Lettura da file: {$file}");
            return str_ends_with(strtolower($file), '.zip')
                ? $this->extractJsonFromZip($file)
                : json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        }

        $url    = rtrim(config('services.cruisehost.base_url'), '/') . '/catalog';
        $apiKey = config('services.cruisehost.api_key');

        $this->line("  Connessione a: {$url}");
        Log::info('[CatalogSync] Inizio download', ['url' => $url]);

        $zipPath = tempnam(sys_get_temp_dir(), 'cruisehost_catalog_') . '.zip';
        Log::info('[CatalogSync] File temporaneo', ['path' => $zipPath]);

        try {
            Log::info('[CatalogSync] Invio richiesta HTTP...');

            $response = Http::withToken($apiKey)
                ->withoutVerifying()
                ->connectTimeout(15)
                ->timeout(300)
                ->sink($zipPath)
                ->get($url);

            Log::info('[CatalogSync] Risposta ricevuta', [
                'status'         => $response->status(),
                'ok'             => $response->successful(),
                'content_type'   => $response->header('Content-Type'),
                'content_length' => $response->header('Content-Length'),
                'file_size_kb'   => file_exists($zipPath) ? round(filesize($zipPath) / 1024) : 'file assente',
            ]);

            if (! $response->successful()) {
                Log::error('[CatalogSync] Download fallito', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 500),
                ]);
                throw new \RuntimeException("Download catalogo fallito: HTTP {$response->status()}");
            }

            $sizeKb = round(filesize($zipPath) / 1024);
            $this->line("  ZIP scaricato ({$sizeKb} KB) — estrazione...");
            Log::info('[CatalogSync] Inizio estrazione ZIP', ['size_kb' => $sizeKb]);

            $data = $this->extractJsonFromZip($zipPath);

            Log::info('[CatalogSync] Estrazione completata', [
                'products' => count($data['products'] ?? []),
                'ships'    => count($data['ships'] ?? []),
                'ports'    => count($data['ports'] ?? []),
                'areas'    => count($data['areas'] ?? []),
            ]);

            return $data;

        } catch (\Throwable $e) {
            Log::error('[CatalogSync] Eccezione durante download/estrazione', [
                'message' => $e->getMessage(),
                'class'   => get_class($e),
                'file'    => $e->getFile() . ':' . $e->getLine(),
            ]);
            throw $e;
        } finally {
            @unlink($zipPath);
        }
    }

    private function extractJsonFromZip(string $zipPath): array
    {
        $zip = new \ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException("Impossibile aprire il file ZIP: {$zipPath}");
        }

        $jsonFile = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with(strtolower($name), '.json')) {
                $jsonFile = $name;
                break;
            }
        }

        if (! $jsonFile) {
            $zip->close();
            throw new \RuntimeException('Nessun file .json trovato nello ZIP.');
        }

        $this->line("  Estrazione: {$jsonFile}");
        $json = $zip->getFromName($jsonFile);
        $zip->close();

        if ($json === false) {
            throw new \RuntimeException("Errore durante la lettura di {$jsonFile} dallo ZIP.");
        }

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Chiama GET /search?package=all per ottenere l'albero completo delle aree.
     * Non conta nel limite giornaliero (è una chiamata leggera).
     * In caso di errore ritorna array vuoto — le aree del catalogo bastano come fallback.
     */
    private function fetchAreasFromSearch(): array
    {
        $this->line('  aree da /search...');

        try {
            $url    = rtrim(config('services.cruisehost.base_url'), '/') . '/search';
            $apiKey = config('services.cruisehost.api_key');

            $response = Http::withToken($apiKey)->timeout(30)->get($url, [
                'package'   => 'all',
                'agency_id' => config('services.cruisehost.agency_id'),
            ]);

            if (! $response->successful()) {
                $this->warn("  /search fallito (HTTP {$response->status()}) — uso solo aree del catalogo.");
                return [];
            }

            $areas = $response->json('area') ?? [];
            $this->line('  ' . count($areas) . ' aree da /search.');
            return $areas;

        } catch (\Throwable $e) {
            $this->warn('  /search non raggiungibile: ' . $e->getMessage());
            return [];
        }
    }

    // -------------------------------------------------------------------------
    // Cruise Lines
    // Struttura JSON: cruiselines[]{id, title, data[]{id, text, selected, data[]}}
    // Il livello esterno (es. "online") è un raggruppamento, non una cruise line.
    // Le cruise lines reali sono in data[n].data con id/text.
    // -------------------------------------------------------------------------

    private function syncCruiseLines(array $groups): void
    {
        $this->line('  cruise_lines...');
        $now = now();

        foreach ($groups as $group) {
            foreach ($group['data'] ?? [] as $cl) {
                // $cl = {id: "CCL", text: "Carnival Cruise Line", selected: true, data: [...ships...]}
                DB::table('cruise_lines')->upsert(
                    [
                        'id'        => $cl['id'],
                        'name'      => $cl['text'],
                        'logo_url'  => null, // non presente nel catalogo
                        'is_online' => (bool) ($cl['selected'] ?? true),
                        'synced_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    ['id'],
                    ['name', 'is_online', 'synced_at', 'updated_at']
                );
            }
        }
    }

    // -------------------------------------------------------------------------
    // Areas
    // Struttura JSON: areas[]{id, text, children[]{id, text}}
    // -------------------------------------------------------------------------

    private function syncAreas(array $areas): array
    {
        $this->line('  areas...');
        $now      = now();
        $areaIds  = [];

        foreach ($areas as $area) {
            $id = (string) $area['id'];
            DB::table('areas')->upsert(
                [
                    'id'         => $id,
                    'parent_id'  => null,
                    'name'       => $area['text'],
                    'slug'       => Str::slug($area['text']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                ['id'],
                ['parent_id', 'name', 'slug', 'updated_at']
            );
            $areaIds[$id] = true;

            foreach ($area['children'] ?? [] as $child) {
                $childId = (string) $child['id'];
                DB::table('areas')->upsert(
                    [
                        'id'         => $childId,
                        'parent_id'  => $id,
                        'name'       => $child['text'],
                        'slug'       => Str::slug($child['text']),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    ['id'],
                    ['parent_id', 'name', 'slug', 'updated_at']
                );
                $areaIds[$childId] = true;
            }
        }

        return $areaIds;
    }

    // -------------------------------------------------------------------------
    // Ports
    // Struttura JSON: ports[]{id, name, lat, long}
    // Nota: "long" (non "lng"), nomi con HTML entities
    // -------------------------------------------------------------------------

    private function syncPorts(array $ports): void
    {
        $this->line('  ports (' . count($ports) . ')...');
        $now = now();

        // Chunk per evitare query troppo grandi (7755 port nel catalogo)
        foreach (array_chunk($ports, 500) as $chunk) {
            $rows = array_map(fn($port) => [
                'id'           => $port['id'],
                'name'         => html_entity_decode($port['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'latitude'     => $port['lat'] ?: null,
                'longitude'    => $port['long'] ?: null,
                'country_code' => null, // non presente nel catalogo
                'created_at'   => $now,
                'updated_at'   => $now,
            ], $chunk);

            DB::table('ports')->upsert(
                $rows,
                ['id'],
                ['name', 'latitude', 'longitude', 'updated_at']
            );
        }
    }

    // -------------------------------------------------------------------------
    // Ships + cabin images + categories
    // Struttura JSON: ships[]{shipCode, cruiselineCode, description, name,
    //   images: {ship: [url], cabin: [{name, path}], gallery: [...], decks: null},
    //   features: [id,...], decks: [...], categories: [{cl_cat, cruisehost_cat, description}]}
    // -------------------------------------------------------------------------

    private function syncShips(array $ships): void
    {
        $this->line('  ships...');
        $now = now();

        foreach ($ships as $ship) {
            $mainImage = $this->normalizeUrl($ship['images']['ship'][0] ?? null);

            DB::table('ships')->upsert(
                [
                    'id'                  => $ship['shipCode'],
                    'cruise_line_id'      => $ship['cruiselineCode'],
                    'name'                => $ship['name'],
                    'description'         => trim($ship['description'] ?? '') ?: null,
                    'image_url'           => $mainImage,
                    'features'            => ! empty($ship['features']) ? json_encode($ship['features']) : null,
                    'decks'               => ! empty($ship['decks']) ? json_encode($ship['decks']) : null,
                    'images_refreshed_at' => null,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                ['id'],
                ['cruise_line_id', 'name', 'description', 'image_url', 'features', 'decks', 'updated_at']
            );

            // Cabin images — immagini per categoria cabina
            // {name: "BK", path: "//images.cruisec.net/..."}
            $cabinImages = $ship['images']['cabin'] ?? [];
            if ($cabinImages) {
                DB::table('ship_cabin_images')->where('ship_id', $ship['shipCode'])->delete();

                DB::table('ship_cabin_images')->insert(
                    array_map(fn($img) => [
                        'ship_id'       => $ship['shipCode'],
                        'category_code' => $img['name'],
                        'image_url'     => $this->normalizeUrl($img['path']),
                        'gallery_name'  => null,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ], $cabinImages)
                );
            }

            // Ship categories: mapping cl_cat <-> cruisehost_cat
            // {cl_cat: "4A", cruisehost_cat: "IS", description: "Category: 4A ..."}
            foreach ($ship['categories'] ?? [] as $cat) {
                DB::table('ship_categories')->upsert(
                    [
                        'ship_id'        => $ship['shipCode'],
                        'cl_cat'         => $cat['cl_cat'],
                        'cruisehost_cat' => $cat['cruisehost_cat'],
                        'description'    => $cat['description'] ?? null,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ],
                    ['ship_id', 'cl_cat'],
                    ['cruisehost_cat', 'description', 'updated_at']
                );
            }
        }
    }

    // -------------------------------------------------------------------------
    // Products + itinerario + partenze + prezzi
    // Struttura JSON:
    //   products[]{productID, isPackage, cruiseline, matchcode, cruiseName, sea,
    //     shipCode, duration, area, fromPort, toPort,
    //     itin: [{day, sort, arrival, departure, port}],
    //     cruises: [{mastercruiseID, depDate, arrDate, categories: [{code, price}]}]}
    //
    // Currency: non è nel catalogo, si ricava dal matchcode (es. CCL-USD-JPN → USD)
    // -------------------------------------------------------------------------

    private function syncProducts(array $products, array &$stats, array $areaIds): void
    {
        $this->line('  products (' . count($products) . ')...');
        $now = now();

        // ── 0. Aree mancanti (placeholder) per evitare FK violation ──────────
        $missingAreaIds = [];
        foreach ($products as $product) {
            $areaId = (string) $product['area'];
            if (! isset($areaIds[$areaId]) && ! isset($missingAreaIds[$areaId])) {
                $missingAreaIds[$areaId] = true;
            }
        }
        if ($missingAreaIds) {
            $this->warn('  ' . count($missingAreaIds) . ' area_id sconosciute — inserisco placeholder.');
            $missingRows = array_map(fn($id) => [
                'id'         => $id,
                'parent_id'  => null,
                'name'       => 'Area ' . $id,
                'slug'       => 'area-' . $id,
                'created_at' => $now,
                'updated_at' => $now,
            ], array_keys($missingAreaIds));
            DB::table('areas')->upsert($missingRows, ['id'], ['updated_at']);
            foreach (array_keys($missingAreaIds) as $id) {
                $areaIds[$id] = true;
            }
        }

        // ── 1. Pre-fetch ultimi prezzi registrati (1 query per tutto il sync) ─
        // Con l'indice (departure_id, category_code, id) MySQL usa un loose
        // index scan per MAX(id) GROUP BY → nessuna scansione full-table.
        $lastPrices = DB::table('price_history')
            ->select('departure_id', 'category_code', 'price')
            ->whereIn('id', function ($q) {
                $q->select(DB::raw('MAX(id)'))
                  ->from('price_history')
                  ->groupBy('departure_id', 'category_code');
            })
            ->get()
            ->keyBy(fn($r) => $r->departure_id . '|' . $r->category_code);

        // ── 2. Raccolta dati: loop puro PHP, zero query ───────────────────────
        $productRows    = [];
        $itinProductIds = [];   // product_id da cui eliminare le vecchie righe
        $itinRows       = [];
        $departureRows  = [];
        $allPriceRows   = [];

        foreach ($products as $product) {
            $currency = $this->currencyFromMatchcode($product['matchcode'] ?? '');

            $productRows[] = [
                'id'             => $product['productID'],
                'cruise_line_id' => $product['cruiseline'],
                'ship_id'        => $product['shipCode'],
                'area_id'        => (string) $product['area'],
                'port_from_id'   => $product['fromPort'],
                'port_to_id'     => $product['toPort'],
                'cruise_name'    => $product['cruiseName'],
                'is_package'     => (bool) $product['isPackage'],
                'matchcode'      => $product['matchcode'] ?? null,
                'sea'            => (bool) $product['sea'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            // Itinerario — deduplica (day, port) in memoria
            if (! empty($product['itin'])) {
                $itinProductIds[] = $product['productID'];
                $itinSeen = [];
                foreach ($product['itin'] as $stop) {
                    $key = $stop['day'] . '|' . $stop['port'];
                    if (isset($itinSeen[$key])) continue;
                    $itinSeen[$key] = true;
                    $itinRows[] = [
                        'product_id'     => $product['productID'],
                        'port_id'        => $stop['port'],
                        'day_number'     => (int) $stop['day'],
                        'arrival_time'   => ($stop['arrival'] !== '' && $stop['arrival'] !== '00:00') ? $stop['arrival'] : null,
                        'departure_time' => ($stop['departure'] !== '' && $stop['departure'] !== '00:00') ? $stop['departure'] : null,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }
            }

            // Partenze e prezzi
            foreach ($product['cruises'] ?? [] as $cruise) {
                $duration = $this->calcDuration($cruise['depDate'], $cruise['arrDate'], (int) $product['duration']);

                $departureRows[] = [
                    'id'         => $cruise['mastercruiseID'],
                    'product_id' => $product['productID'],
                    'dep_date'   => $cruise['depDate'],
                    'arr_date'   => $cruise['arrDate'],
                    'duration'   => $duration,
                    'synced_at'  => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                foreach ($cruise['categories'] ?? [] as $cat) {
                    if (! isset($cat['price'])) continue;
                    $allPriceRows[] = [
                        'departure_id'  => $cruise['mastercruiseID'],
                        'category_code' => $cat['code'],
                        'price'         => $cat['price'],
                        'currency'      => $currency,
                        'recorded_at'   => $now,
                        'source'        => 'catalog',
                    ];
                }
            }
        }

        $stats['products_imported'] = count($productRows);

        // ── 3. Upsert prodotti (bulk, chunk 500) ─────────────────────────────
        foreach (array_chunk($productRows, 500) as $chunk) {
            DB::table('products')->upsert(
                $chunk,
                ['id'],
                ['cruise_line_id', 'ship_id', 'area_id', 'port_from_id', 'port_to_id',
                 'cruise_name', 'is_package', 'matchcode', 'sea', 'updated_at']
            );
        }

        // ── 4. Itinerari: 1 delete bulk + insert bulk ────────────────────────
        if ($itinProductIds) {
            foreach (array_chunk($itinProductIds, 500) as $chunk) {
                DB::table('product_itinerary')->whereIn('product_id', $chunk)->delete();
            }
            foreach (array_chunk($itinRows, 500) as $chunk) {
                DB::table('product_itinerary')->insert($chunk);
            }
        }

        // ── 5. Upsert partenze (bulk, chunk 500) ──────────────────────────────
        foreach (array_chunk($departureRows, 500) as $chunk) {
            DB::table('departures')->upsert(
                $chunk,
                ['id'],
                ['dep_date', 'arr_date', 'duration', 'synced_at', 'updated_at']
            );
        }

        // ── 6. Filtra prezzi cambiati (confronto in memoria, zero query) ──────
        $changedPriceRows = array_values(array_filter($allPriceRows, function ($row) use ($lastPrices) {
            $last = $lastPrices->get($row['departure_id'] . '|' . $row['category_code']);
            return ! $last || (float) $last->price !== (float) $row['price'];
        }));

        // ── 7. Insert prezzi cambiati + aggiorna min_price (1 UPDATE finale) ──
        if ($changedPriceRows) {
            foreach (array_chunk($changedPriceRows, 500) as $chunk) {
                DB::table('price_history')->insert($chunk);
            }
            $stats['prices_recorded'] = count($changedPriceRows);

            $changedDepIds = array_unique(array_column($changedPriceRows, 'departure_id'));
            $placeholders  = implode(',', array_fill(0, count($changedDepIds), '?'));
            DB::statement("
                UPDATE departures d
                INNER JOIN (
                    SELECT departure_id, MIN(price) AS min_p
                    FROM price_history
                    WHERE departure_id IN ({$placeholders})
                    GROUP BY departure_id
                ) ph ON d.id = ph.departure_id
                SET d.min_price = ph.min_p
            ", $changedDepIds);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Normalizza gli URL che arrivano senza schema: //images.cruisec.net/... → https://...
     */
    private function normalizeUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return str_starts_with($url, '//') ? 'https:' . $url : $url;
    }

    /**
     * Estrae la valuta dal matchcode: "CCL-USD-JPN" → "USD"
     */
    private function currencyFromMatchcode(?string $matchcode): string
    {
        if (! $matchcode) {
            return 'EUR';
        }

        $parts = explode('-', $matchcode);

        return (isset($parts[1]) && strlen($parts[1]) === 3) ? $parts[1] : 'EUR';
    }

    /**
     * Calcola la durata in notti tra depDate e arrDate.
     * Usa il valore del product come fallback se le date non tornano.
     */
    private function calcDuration(string $depDate, string $arrDate, int $fallback): int
    {
        try {
            $dep = new \DateTimeImmutable($depDate);
            $arr = new \DateTimeImmutable($arrDate);
            $nights = $dep->diff($arr)->days;
            return $nights > 0 ? $nights : $fallback;
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
