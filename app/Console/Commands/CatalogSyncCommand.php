<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CatalogSyncCommand extends Command
{
    protected $signature = 'catalog:sync
                            {--file= : Percorso file JSON locale (per test senza API)}
                            {--force : Esegui anche se il limite giornaliero (4 download) è stato raggiunto}';

    protected $description = 'Scarica e sincronizza il catalogo CruiseHost nel database locale';

    private const MAX_DAILY_SYNCS = 4;

    public function handle(): int
    {
        if (! $this->checkDailyLimit()) {
            $this->error('Limite giornaliero di ' . self::MAX_DAILY_SYNCS . ' sync raggiunto. Usa --force per ignorarlo.');
            return self::FAILURE;
        }

        $logId = DB::table('catalog_sync_log')->insertGetId([
            'started_at' => now(),
            'status'     => 'running',
        ]);

        $this->info("Sync avviato (log ID: {$logId})");

        try {
            $catalog = $this->loadCatalog();
            $this->info('Catalogo caricato — avvio importazione...');

            $stats = ['products_imported' => 0, 'prices_recorded' => 0];

            $searchAreas = $this->fetchAreasFromSearch();

            DB::transaction(function () use ($catalog, $searchAreas, &$stats) {
                $this->syncCruiseLines($catalog['cruiselines'] ?? []);
                $areaIds = $this->syncAreas(array_merge($searchAreas, $catalog['areas'] ?? []));
                $this->syncPorts($catalog['ports'] ?? []);
                $this->syncShips($catalog['ships'] ?? []);
                $this->syncProducts($catalog['products'] ?? [], $stats, $areaIds);
            });

            DB::table('catalog_sync_log')->where('id', $logId)->update([
                'finished_at'       => now(),
                'products_imported' => $stats['products_imported'],
                'prices_recorded'   => $stats['prices_recorded'],
                'status'            => 'completed',
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

        $response = Http::withToken($apiKey)->timeout(300)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("Download catalogo fallito: HTTP {$response->status()}");
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'cruisehost_catalog_') . '.zip';

        try {
            file_put_contents($zipPath, $response->body());
            return $this->extractJsonFromZip($zipPath);
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

        // Auto-inserisci aree mancanti (placeholder) per evitare FK violation
        $missingAreaIds = [];
        foreach ($products as $product) {
            $areaId = (string) $product['area'];
            if (! isset($areaIds[$areaId]) && ! isset($missingAreaIds[$areaId])) {
                $missingAreaIds[$areaId] = true;
            }
        }
        if ($missingAreaIds) {
            $this->warn('  ' . count($missingAreaIds) . ' area_id sconosciute — inserisco placeholder.');
            foreach (array_keys($missingAreaIds) as $missingId) {
                DB::table('areas')->upsert(
                    [
                        'id'         => $missingId,
                        'parent_id'  => null,
                        'name'       => 'Area ' . $missingId,
                        'slug'       => 'area-' . $missingId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    ['id'],
                    ['updated_at']
                );
                $areaIds[$missingId] = true;
            }
        }

        foreach ($products as $product) {
            $currency = $this->currencyFromMatchcode($product['matchcode'] ?? '');

            DB::table('products')->upsert(
                [
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
                ],
                ['id'],
                ['cruise_line_id', 'ship_id', 'area_id', 'port_from_id', 'port_to_id',
                 'cruise_name', 'is_package', 'matchcode', 'sea', 'updated_at']
            );

            $stats['products_imported']++;

            // Itinerario: itin[]{day, sort, arrival, departure, port}
            // arrival/departure possono essere stringa vuota → null
            if (! empty($product['itin'])) {
                DB::table('product_itinerary')->where('product_id', $product['productID'])->delete();

                // Deduplica: il JSON può avere la stessa (day, port) due volte
                $itinSeen = [];
                $itin = array_filter($product['itin'], function ($stop) use (&$itinSeen) {
                    $key = $stop['day'] . '|' . $stop['port'];
                    if (isset($itinSeen[$key])) return false;
                    $itinSeen[$key] = true;
                    return true;
                });

                DB::table('product_itinerary')->insert(
                    array_map(fn($stop) => [
                        'product_id'     => $product['productID'],
                        'port_id'        => $stop['port'],
                        'day_number'     => (int) $stop['day'],
                        'arrival_time'   => ($stop['arrival'] !== '' && $stop['arrival'] !== '00:00') ? $stop['arrival'] : null,
                        'departure_time' => ($stop['departure'] !== '' && $stop['departure'] !== '00:00') ? $stop['departure'] : null,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ], $itin)
                );
            }

            // Partenze e prezzi
            // cruises[]{mastercruiseID, depDate, arrDate, categories[]{code, price}}
            $priceRows = [];

            foreach ($product['cruises'] ?? [] as $cruise) {
                $duration = $this->calcDuration($cruise['depDate'], $cruise['arrDate'], (int) $product['duration']);

                DB::table('departures')->upsert(
                    [
                        'id'         => $cruise['mastercruiseID'],
                        'product_id' => $product['productID'],
                        'dep_date'   => $cruise['depDate'],
                        'arr_date'   => $cruise['arrDate'],
                        'duration'   => $duration,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    ['id'],
                    ['dep_date', 'arr_date', 'duration', 'updated_at']
                );

                foreach ($cruise['categories'] ?? [] as $cat) {
                    if (! isset($cat['price'])) {
                        continue;
                    }

                    $priceRows[] = [
                        'departure_id'  => $cruise['mastercruiseID'],
                        'category_code' => $cat['code'],
                        'price'         => $cat['price'],
                        'currency'      => $currency,
                        'recorded_at'   => $now,
                        'source'        => 'catalog',
                    ];

                    $stats['prices_recorded']++;
                }
            }

            // Insert prezzi a blocchi per non superare i limiti di MySQL
            foreach (array_chunk($priceRows, 500) as $chunk) {
                DB::table('price_history')->insert($chunk);
            }
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
