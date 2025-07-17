<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cruise;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Statement;
use Illuminate\Support\Str;

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
        $csv = Reader::createFromPath($fullPath, 'r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);
        $records = Statement::create()->process($csv);

        $skipped = [];

        foreach ($records as $record) {
            $exists = Cruise::where('ship', $record['ship'])
                ->where('line', $record['line'])
                ->where('cruise', $record['cruise'])
                ->whereDate('partenza', $record['partenza'])
                ->whereDate('arrivo', $record['arrivo'])
                ->exists();

            if ($exists) {
                $skipped[] = $record;
                continue;
            }

            Cruise::create($record);
        }

        // Log duplicati
        if ($skipped) {
            $logFile = 'logs/duplicate_cruises_' . now()->format('Ymd_His') . '.csv';
            $handle = fopen(storage_path('app/' . $logFile), 'w');
            fputcsv($handle, array_keys($skipped[0]));

            foreach ($skipped as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
            session()->flash('warning', 'Alcune crociere erano già presenti e sono state salvate in: ' . $logFile);
        }

        return redirect()->back()->with('success', 'Crociere importate correttamente.');
    }
}
