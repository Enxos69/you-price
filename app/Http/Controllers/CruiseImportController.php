<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cruise;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Statement;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CruiseImportController extends Controller
{
    public function showForm()
    {
        return view('admin.import-crociere');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $path = $request->file('csv_file')->storeAs('imports', Str::uuid() . '.csv');
        $fullPath = storage_path('app/' . $path);
        
        try {
            $csv = Reader::createFromPath($fullPath, 'r');
            $csv->setDelimiter(';');
            $csv->setHeaderOffset(0);
            $records = Statement::create()->process($csv);

            $importStats = [
                'total_processed' => 0,
                'total_imported' => 0,
                'total_skipped' => 0,
                'imported_ids' => [],
                'skipped_records' => [],
                'errors' => []
            ];

            DB::beginTransaction();

            foreach ($records as $record) {
                $importStats['total_processed']++;

                try {
                    // Verifica se esiste già
                    $exists = Cruise::where('ship', $record['ship'])
                        ->where('line', $record['line'])
                        ->where('cruise', $record['cruise'])
                        ->whereDate('partenza', $record['partenza'])
                        ->whereDate('arrivo', $record['arrivo'])
                        ->first();

                    if ($exists) {
                        $importStats['skipped_records'][] = $record;
                        $importStats['total_skipped']++;
                        continue;
                    }

                    // Crea nuova crociera
                    $cruise = Cruise::create($record);
                    $importStats['imported_ids'][] = $cruise->id;
                    $importStats['total_imported']++;

                } catch (\Exception $e) {
                    $importStats['errors'][] = [
                        'record' => $record,
                        'error' => $e->getMessage()
                    ];
                    Log::error('Errore importazione crociera: ' . $e->getMessage(), ['record' => $record]);
                }
            }

            DB::commit();

            // Salva i risultati in sessione per la pagina dei risultati
            session()->put('import_stats', $importStats);

            // Log dei duplicati se presenti
            if (!empty($importStats['skipped_records'])) {
                $this->logSkippedRecords($importStats['skipped_records']);
            }

            // Pulisci il file temporaneo
            Storage::delete($path);

            return redirect()->route('cruises.import.results');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Errore durante l\'importazione CSV: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Errore durante l\'importazione: ' . $e->getMessage());
        }
    }

    public function showResults()
    {
        $importStats = session()->get('import_stats', []);
        
        if (empty($importStats)) {
            return redirect()->route('cruises.import.form')->with('error', 'Nessun risultato di importazione disponibile.');
        }

        // Recupera le crociere importate
        $importedCruises = collect();
        if (!empty($importStats['imported_ids'])) {
            $importedCruises = Cruise::whereIn('id', $importStats['imported_ids'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin.import-results', [
            'importStats' => $importStats,
            'importedCruises' => $importedCruises
        ]);
    }

    public function getImportedCruisesData()
    {
        $importStats = session()->get('import_stats', []);
        
        if (empty($importStats['imported_ids'])) {
            return response()->json(['data' => []]);
        }

        $cruises = Cruise::whereIn('id', $importStats['imported_ids'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $cruises->map(function ($cruise) {
            return [
                'id' => $cruise->id,
                'ship' => $cruise->ship,
                'cruise' => $cruise->cruise,
                'line' => $cruise->line,
                'duration' => $cruise->duration,
                'partenza' => $cruise->partenza->format('d/m/Y'),
                'arrivo' => $cruise->arrivo->format('d/m/Y'),
                'interior' => $cruise->interior ? '$' . number_format($cruise->interior) : '-',
                'oceanview' => $cruise->oceanview ? '$' . number_format($cruise->oceanview) : '-',
                'balcony' => $cruise->balcony ? '$' . number_format($cruise->balcony) : '-',
                'minisuite' => $cruise->minisuite ? '$' . number_format($cruise->minisuite) : '-',
                'suite' => $cruise->suite ? '$' . number_format($cruise->suite) : '-',
                'created_at' => $cruise->created_at->format('d/m/Y H:i:s')
            ];
        });

        return response()->json(['data' => $data]);
    }

    private function logSkippedRecords($skippedRecords)
    {
        $logFile = 'logs/duplicate_cruises_' . now()->format('Ymd_His') . '.csv';
        $handle = fopen(storage_path('app/' . $logFile), 'w');
        
        if (!empty($skippedRecords)) {
            // Scrivi header
            fputcsv($handle, array_keys($skippedRecords[0]), ';');
            
            // Scrivi dati
            foreach ($skippedRecords as $row) {
                fputcsv($handle, $row, ';');
            }
        }
        
        fclose($handle);
        return $logFile;
    }

    public function downloadSkippedRecords()
    {
        $importStats = session()->get('import_stats', []);
        
        if (empty($importStats['skipped_records'])) {
            return redirect()->back()->with('error', 'Nessun record saltato da scaricare.');
        }

        $filename = 'crociere_duplicate_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($importStats) {
            $file = fopen('php://output', 'w');
            
            if (!empty($importStats['skipped_records'])) {
                // Header
                fputcsv($file, array_keys($importStats['skipped_records'][0]), ';');
                
                // Dati
                foreach ($importStats['skipped_records'] as $row) {
                    fputcsv($file, $row, ';');
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}