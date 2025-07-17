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
    public function showForm()
    {
        // Controllo admin semplice (opzionale)
        // if (auth()->user() && auth()->user()->id !== 1) {
        //     abort(403, 'Accesso negato');
        // }
        
        return view('admin.import-crociere');
    }

    public function import(Request $request)
    {
        // Validazione base
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
                    // Crea record base
                    $record = [];
                    
                    // Mappa i dati usando gli indici
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
                                $record['partenza'] = $this->parseDate($value);
                                break;
                            case 'arrivo':
                            case 'arrival':
                                $record['arrivo'] = $this->parseDate($value);
                                break;
                            case 'interior':
                            case 'interna':
                                $record['interior'] = $this->parsePrice($value);
                                break;
                            case 'oceanview':
                            case 'vista mare':
                                $record['oceanview'] = $this->parsePrice($value);
                                break;
                            case 'balcony':
                            case 'balcone':
                                $record['balcony'] = $this->parsePrice($value);
                                break;
                            case 'minisuite':
                            case 'mini suite':
                                $record['minisuite'] = $this->parsePrice($value);
                                break;
                            case 'suite':
                                $record['suite'] = $this->parsePrice($value);
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

                    // Validazione base
                    if (empty($record['ship']) || empty($record['cruise']) || empty($record['line'])) {
                        $importStats['errors'][] = [
                            'line' => $lineNumber,
                            'record' => $record,
                            'error' => 'Dati mancanti (ship, cruise, line richiesti)'
                        ];
                        continue;
                    }

                    // Controllo duplicati semplice
                    $exists = Cruise::where('ship', $record['ship'])
                                   ->where('line', $record['line'])
                                   ->where('cruise', $record['cruise'])
                                   ->first();

                    if ($exists) {
                        $importStats['skipped_records'][] = $record;
                        $importStats['total_skipped']++;
                        continue;
                    }

                    // Crea la crociera
                    $cruise = Cruise::create($record);
                    $importStats['imported_ids'][] = $cruise->id;
                    $importStats['total_imported']++;

                } catch (\Exception $e) {
                    $importStats['errors'][] = [
                        'line' => $lineNumber,
                        'record' => $record ?? [],
                        'error' => $e->getMessage()
                    ];
                }
            }

            fclose($handle);
            DB::commit();

            // Salva stats in sessione
            session(['import_stats' => $importStats]);

            // Pulisci file temporaneo
            Storage::delete($path);

            $message = "Importazione completata! Processati: {$importStats['total_processed']}, Importati: {$importStats['total_imported']}, Saltati: {$importStats['total_skipped']}, Errori: " . count($importStats['errors']);

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Errore importazione: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Errore durante l\'importazione: ' . $e->getMessage());
        }
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;

        try {
            // Prova formato Y-m-d
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return $value;
            }
            
            // Prova formato d/m/Y
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }
            
            // Prova con Carbon
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parsePrice($value)
    {
        if (empty($value)) return null;

        // Rimuovi simboli e spazi
        $cleaned = preg_replace('/[€$£,\s]/', '', $value);
        
        return is_numeric($cleaned) ? (float)$cleaned : null;
    }

    public function showResults()
    {
        $importStats = session('import_stats', []);
        
        if (empty($importStats)) {
            return redirect()->route('cruises.import.form')
                           ->with('error', 'Nessun risultato disponibile.');
        }

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

        $callback = function() use ($importStats) {
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