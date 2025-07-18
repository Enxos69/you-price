<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cruise;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CruiseImportController extends Controller
{
    // Tassi di cambio - potresti usare un API per valori real-time
    private const USD_TO_EUR_RATE = 0.92;

    public function showForm()
    {
        return view('admin.import-crociere');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        try {
            $file = $request->file('csv_file');
            $path = $file->storeAs('imports', Str::uuid() . '.csv');
            $fullPath = storage_path('app/' . $path);

            $importStats = [
                'total_processed' => 0,
                'total_imported' => 0,
                'total_skipped' => 0,
                'imported_ids' => [],
                'skipped_records' => [],
                'errors' => []
            ];

            // Leggi il file CSV
            $handle = fopen($fullPath, 'r');
            if (!$handle) {
                throw new \Exception('Impossibile aprire il file CSV');
            }

            // Leggi header
            $headers = fgetcsv($handle, 0, ';');
            if (!$headers) {
                throw new \Exception('File CSV vuoto o formato non valido');
            }

            // Pulisci headers
            $headers = array_map('trim', $headers);
            $headers = array_map('strtolower', $headers);

            DB::beginTransaction();

            $lineNumber = 1;
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                $lineNumber++;
                $importStats['total_processed']++;

                try {
                    // Mappa i dati base
                    $rawRecord = $this->mapCsvData($headers, $data);

                    // Parse specifico per compagnia
                    $parsedRecord = $this->parseByCompany($rawRecord);

                    // Validazione base
                    if (empty($parsedRecord['ship']) || empty($parsedRecord['cruise']) || empty($parsedRecord['line'])) {
                        $importStats['errors'][] = [
                            'line' => $lineNumber,
                            'record' => $rawRecord,
                            'error' => 'Dati mancanti (ship, cruise, line richiesti)'
                        ];
                        continue;
                    }

                    // Controllo duplicati e gestione prezzi migliori
                    $existingCruise = Cruise::where('ship', $parsedRecord['ship'])
                        ->where('line', $parsedRecord['line'])
                        ->when(isset($parsedRecord['partenza']), function ($q) use ($parsedRecord) {
                            return $q->whereDate('partenza', $parsedRecord['partenza']);
                        })
                        ->when(isset($parsedRecord['arrivo']), function ($q) use ($parsedRecord) {
                            return $q->whereDate('arrivo', $parsedRecord['arrivo']);
                        })
                        ->first();

                    if ($existingCruise) {
                        // Confronta i prezzi e mantieni quello più basso
                        $updated = $this->updateWithBestPrices($existingCruise, $parsedRecord);

                        if ($updated) {
                            $importStats['total_updated'] = ($importStats['total_updated'] ?? 0) + 1;
                        } else {
                            $importStats['skipped_records'][] = $parsedRecord;
                            $importStats['total_skipped']++;
                        }
                        continue;
                    }

                    // Crea la crociera
                    $cruise = Cruise::create($parsedRecord);
                    $importStats['imported_ids'][] = $cruise->id;
                    $importStats['total_imported']++;
                } catch (\Exception $e) {
                    $importStats['errors'][] = [
                        'line' => $lineNumber,
                        'record' => $rawRecord ?? [],
                        'error' => $e->getMessage()
                    ];
                }
            }

            fclose($handle);
            DB::commit();
            // Assicurati che tutti i campi necessari siano presenti
            $importStats = [
                'total_processed' => $importStats['total_processed'] ?? 0,
                'total_imported' => $importStats['total_imported'] ?? 0,
                'total_updated' => $importStats['total_updated'] ?? 0, // Aggiungi se manca
                'total_skipped' => $importStats['total_skipped'] ?? 0,
                'imported_ids' => $importStats['imported_ids'] ?? [],
                'skipped_records' => $importStats['skipped_records'] ?? [],
                'errors' => $importStats['errors'] ?? [] 
            ];
            // Salva stats in sessione
            session(['import_stats' => $importStats]);

            // Pulisci file temporaneo
            Storage::delete($path);

            $message = "Importazione completata! Processati: {$importStats['total_processed']}, Importati: {$importStats['total_imported']}, Aggiornati: " . ($importStats['total_updated'] ?? 0) . ", Saltati: {$importStats['total_skipped']}, Errori: " . count($importStats['errors']);

            return redirect()->route('cruises.import.results')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Errore importazione: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Errore durante l\'importazione: ' . $e->getMessage());
        }
    }

    private function mapCsvData($headers, $data)
    {
        $record = [];

        foreach ($headers as $index => $header) {
            $value = isset($data[$index]) ? trim($data[$index]) : '';

            // Mappa i nomi delle colonne
            switch ($header) {
                case 'ship':
                case 'nave':
                    $record['ship'] = $value;
                    break;
                case 'cruise':
                case 'crociera':
                    $record['cruise'] = $value;
                    break;
                case 'line':
                case 'linea':
                case 'compagnia':
                    $record['line'] = $value;
                    break;
                case 'duration':
                case 'durata':
                    $record['duration'] = is_numeric($value) ? (int)$value : null;
                    break;
                case 'night':
                case 'nights':
                case 'notti':
                    $record['night'] = is_numeric($value) ? (int)$value : null;
                    break;
                case 'partenza':
                case 'departure':
                case 'departure_date':
                    $record['partenza'] = $value;
                    break;
                case 'arrivo':
                case 'arrival':
                case 'arrival_date':
                    $record['arrivo'] = $value;
                    break;
                case 'interior':
                case 'interna':
                    $record['interior'] = $value;
                    break;
                case 'oceanview':
                case 'vista mare':
                    $record['oceanview'] = $value;
                    break;
                case 'balcony':
                case 'balcone':
                    $record['balcony'] = $value;
                    break;
                case 'minisuite':
                case 'mini suite':
                    $record['minisuite'] = $value;
                    break;
                case 'suite':
                    $record['suite'] = $value;
                    break;
                case 'from':
                case 'da':
                    $record['from'] = $value;
                    break;
                case 'to':
                case 'a':
                    $record['to'] = $value;
                    break;
                case 'details':
                case 'dettagli':
                    $record['details'] = $value;
                    break;
            }
        }

        return $record;
    }

    private function parseByCompany($record)
    {
        $line = strtolower($record['line'] ?? '');

        if (strpos($line, 'msc') !== false) {
            return $this->parseMSCCruise($record);
        }

        // Aggiungi qui altri parser per altre compagnie
        // elseif (strpos($line, 'costa') !== false) {
        //     return $this->parseCostaCruise($record);
        // }

        // Parser generico per altre compagnie
        return $this->parseGenericCruise($record);
    }

    private function parseMSCCruise($record)
    {
        try {
            // 1. Pulisci il nome della nave (rimuovi "MSC" iniziale)
            $ship = $record['ship'] ?? '';
            if (stripos($ship, 'msc') === 0) {
                $ship = trim(substr($ship, 3));
            }

            // 2. Parse della cruise
            $cruise = $record['cruise'] ?? '';
            $cruiseParts = explode(' - ', $cruise);

            $cruiseName = '';
            $duration = null;
            $ports = '';
            $details = '';

            if (count($cruiseParts) >= 2) {
                // Salta il primo elemento se è "MSC Package" o simile
                $startIndex = 0;
                if (stripos(trim($cruiseParts[0]), 'msc package') !== false) {
                    $startIndex = 1;
                }

                if (isset($cruiseParts[$startIndex])) {
                    // Parse del secondo elemento: "Transatlantic 17 days from Civitavecchia - Rome to Rio de Janeiro"
                    $mainPart = trim($cruiseParts[$startIndex]);

                    // Estrai durata (pattern: numero + days/day)
                    if (preg_match('/(\d+)\s*(day|days)/i', $mainPart, $matches)) {
                        $duration = (int)$matches[1];
                        $mainPart = preg_replace('/\d+\s*days?\s*/i', '', $mainPart);
                    }

                    // Estrai informazioni su porti - Pattern più flessibile
                    if (preg_match('/from\s+(.+?)\s+to\s+(.+)$/i', $mainPart, $matches)) {
                        // Pattern: "from Porto1 to Porto2"
                        $fromPort = trim($matches[1]);
                        $toPort = trim($matches[2]);
                        $ports = $fromPort . ' to ' . $toPort;
                        $mainPart = preg_replace('/\s*from\s+.+$/i', '', $mainPart);
                    } elseif (preg_match('/from\/to\s+(.+)$/i', $mainPart, $matches)) {
                        // Pattern: "from/to Porto"
                        $ports = 'round-trip ' . trim($matches[1]);
                        $mainPart = preg_replace('/\s*from\/to\s+.+$/i', '', $mainPart);
                    }

                    $cruiseName = trim($mainPart);
                }

                // L'ultimo elemento sono i dettagli
                if (count($cruiseParts) > 2) {
                    $details = trim($cruiseParts[count($cruiseParts) - 1]);
                }
            }

            // 3. Parse delle porte (from/to)
            $from = '';
            $to = '';

            if ($ports) {
                if (stripos($ports, 'round-trip') !== false) {
                    // Round trip - stesso porto
                    $port = preg_replace('/round-trip\s*/i', '', $ports);
                    $from = $to = trim($port);
                } elseif (preg_match('/(.+?)\s+to\s+(.+)/i', $ports, $matches)) {
                    // Da porto A a porto B
                    $from = trim($matches[1]);
                    $to = trim($matches[2]);
                } else {
                    // Fallback: stesso porto
                    $from = $to = $ports;
                }
            }

            // 4. Costruisci il record finale
            $parsedRecord = [
                'ship' => $ship,
                'cruise' => $cruiseName ?: ($record['cruise'] ?? ''),
                'line' => $record['line'] ?? '',
                'duration' => $duration ?: ($record['duration'] ?? null),
                'night' => $duration ?: ($record['night'] ?? null),
                'from' => $from ?: ($record['from'] ?? ''),
                'to' => $to ?: ($record['to'] ?? ''),
                'details' => $details ?: ($record['details'] ?? ''),
                'partenza' => $this->parseDate($record['partenza'] ?? ''),
                'arrivo' => $this->parseDate($record['arrivo'] ?? ''),
                'interior' => $this->convertUsdToEur($record['interior'] ?? ''),
                'oceanview' => $this->convertUsdToEur($record['oceanview'] ?? ''),
                'balcony' => $this->convertUsdToEur($record['balcony'] ?? ''),
                'minisuite' => $this->convertUsdToEur($record['minisuite'] ?? ''),
                'suite' => $this->convertUsdToEur($record['suite'] ?? ''),
            ];

            return $parsedRecord;
        } catch (\Exception $e) {
            Log::warning("Errore parsing MSC cruise: " . $e->getMessage(), ['record' => $record]);
            return $this->parseGenericCruise($record);
        }
    }

    private function parseGenericCruise($record)
    {
        return [
            'ship' => $record['ship'] ?? '',
            'cruise' => $record['cruise'] ?? '',
            'line' => $record['line'] ?? '',
            'duration' => $record['duration'],
            'night' => $record['night'],
            'from' => $record['from'] ?? '',
            'to' => $record['to'] ?? '',
            'details' => $record['details'] ?? '',
            'partenza' => $this->parseDate($record['partenza'] ?? ''),
            'arrivo' => $this->parseDate($record['arrivo'] ?? ''),
            'interior' => $this->parsePrice($record['interior'] ?? ''),
            'oceanview' => $this->parsePrice($record['oceanview'] ?? ''),
            'balcony' => $this->parsePrice($record['balcony'] ?? ''),
            'minisuite' => $this->parsePrice($record['minisuite'] ?? ''),
            'suite' => $this->parsePrice($record['suite'] ?? ''),
        ];
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;

        try {
            // Prova formato Y-m-d
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return $value;
            }

            // Prova formato d.m.Y (formato tedesco/europeo)
            if (preg_match('/^\d{1,2}\.\d{1,2}\.\d{4}$/', $value)) {
                return Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
            }

            // Prova formato d/m/Y
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }

            // Prova formato m/d/Y (formato americano)
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('m/d/Y', $value)->format('Y-m-d');
            }

            // Prova con Carbon per altri formati
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Impossibile parsare la data: $value", ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function convertUsdToEur($value)
    {
        if (empty($value) || $value === '-' || $value === 'N/A') return null;

        try {
            // Rimuovi simboli di valuta e caratteri non numerici
            $cleaned = preg_replace('/[\$€£,\s]/', '', $value);
            $cleaned = str_replace(',', '.', $cleaned);

            if (!is_numeric($cleaned)) return null;

            $usdAmount = (float) $cleaned;

            // Converti da USD a EUR
            $eurAmount = $usdAmount * self::USD_TO_EUR_RATE;

            return round($eurAmount, 2);
        } catch (\Exception $e) {
            Log::warning("Impossibile convertire il prezzo: $value", ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function updateWithBestPrices($existingCruise, $newRecord)
    {
        $updated = false;
        $priceFields = ['interior', 'oceanview', 'balcony', 'minisuite', 'suite'];
        $priceUpdated = false;

        foreach ($priceFields as $field) {
            $existingPrice = $existingCruise->$field;
            $newPrice = $newRecord[$field] ?? null;

            // Se il nuovo prezzo è migliore (più basso) o se il campo esistente è vuoto
            if ($this->isBetterPrice($existingPrice, $newPrice)) {
                $existingCruise->$field = $newPrice;
                $updated = true;
                $priceUpdated = true;
            }
        }

        // Se abbiamo aggiornato almeno un prezzo, aggiorna anche i details
        if ($priceUpdated && !empty($newRecord['details'])) {
            $existingCruise->details = $newRecord['details'];
        }

        // Aggiorna altri campi solo se sono vuoti (non se aggiorniamo prezzi)
        if (empty($existingCruise->details) && !empty($newRecord['details']) && !$priceUpdated) {
            $existingCruise->details = $newRecord['details'];
            $updated = true;
        }

        if (empty($existingCruise->duration) && !empty($newRecord['duration'])) {
            $existingCruise->duration = $newRecord['duration'];
            $updated = true;
        }

        if (empty($existingCruise->night) && !empty($newRecord['night'])) {
            $existingCruise->night = $newRecord['night'];
            $updated = true;
        }

        if ($updated) {
            $existingCruise->save();
            Log::info("Aggiornata crociera con prezzi migliori", [
                'cruise_id' => $existingCruise->id,
                'ship' => $existingCruise->ship,
                'partenza' => $existingCruise->partenza,
                'price_updated' => $priceUpdated,
                'new_details' => $priceUpdated ? $newRecord['details'] : null
            ]);
        }

        return $updated;
    }

    private function isBetterPrice($existingPrice, $newPrice)
    {
        // Se non c'è un prezzo esistente e c'è un nuovo prezzo
        if (is_null($existingPrice) && !is_null($newPrice) && $newPrice > 0) {
            return true;
        }

        // Se c'è un nuovo prezzo più basso
        if (!is_null($newPrice) && !is_null($existingPrice) && $newPrice > 0 && $newPrice < $existingPrice) {
            return true;
        }

        return false;
    }

    private function parsePrice($value)
    {
        if (empty($value)) return null;

        // Rimuovi simboli e spazi
        $cleaned = preg_replace('/[€$£,\s]/', '', $value);

        return is_numeric($cleaned) ? (float)$cleaned : null;
    }

    // Metodi esistenti (showResults, getImportedCruisesData, downloadSkippedRecords)
    public function showResults()
    {
        $importStats = session('import_stats', []);

        if (empty($importStats)) {
            return redirect()->route('cruises.import.form')
                ->with('error', 'Nessun risultato disponibile.');
        }

        // Assicurati che errors sia sempre un array valido
        $importStats['errors'] = $importStats['errors'] ?? [];

        return view('admin.import-results', ['importStats' => $importStats]);
    }

    public function getImportedCruisesData()
    {
        $importStats = session('import_stats', []);

        if (empty($importStats['imported_ids'])) {
            return response()->json(['data' => []]);
        }

        $cruises = Cruise::whereIn('id', $importStats['imported_ids'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [];
        foreach ($cruises as $cruise) {
            $data[] = [
                'id' => $cruise->id,
                'ship' => $cruise->ship ?? 'N/D',
                'cruise' => $cruise->cruise ?? 'N/D',
                'line' => $cruise->line ?? 'N/D',
                'duration' => $cruise->duration ? $cruise->duration . ' giorni' : 'N/D',
                'partenza' => $cruise->partenza ? date('d/m/Y', strtotime($cruise->partenza)) : 'N/D',
                'arrivo' => $cruise->arrivo ? date('d/m/Y', strtotime($cruise->arrivo)) : 'N/D',
                'interior' => $cruise->interior ? '€' . number_format($cruise->interior, 0, ',', '.') : '-',
                'oceanview' => $cruise->oceanview ? '€' . number_format($cruise->oceanview, 0, ',', '.') : '-',
                'balcony' => $cruise->balcony ? '€' . number_format($cruise->balcony, 0, ',', '.') : '-',
                'minisuite' => $cruise->minisuite ? '€' . number_format($cruise->minisuite, 0, ',', '.') : '-',
                'suite' => $cruise->suite ? '€' . number_format($cruise->suite, 0, ',', '.') : '-',
                'details' => $cruise->details ?? '-',
                'created_at' => date('d/m/Y H:i:s', strtotime($cruise->created_at))
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function downloadSkippedRecords()
    {
        $importStats = session('import_stats', []);

        if (empty($importStats['skipped_records'])) {
            return redirect()->back()->with('error', 'Nessun record saltato.');
        }

        $filename = 'crociere_duplicate_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($importStats) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM UTF-8

            if (!empty($importStats['skipped_records'])) {
                fputcsv($file, array_keys($importStats['skipped_records'][0]), ';');
                foreach ($importStats['skipped_records'] as $row) {
                    fputcsv($file, $row, ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
