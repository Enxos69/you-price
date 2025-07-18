<?php

namespace App\Http\Controllers;

use App\Models\Cruise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class CruiseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Controlla se l'utente è un admin
        if (Auth::user()->role !== '1') {
            return redirect('/home')->with('error', 'Accesso negato');
        }

        return view('admin.cruises.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->role !== '1') {
            return redirect('/home')->with('error', 'Accesso negato');
        }

        return view('admin.cruises.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ship' => 'required|string|max:255',
                'cruise' => 'required|string|max:255',
                'line' => 'required|string|max:255',
                'duration' => 'nullable|integer|min:1|max:365',
                'night' => 'nullable|integer|min:1|max:365',
                'partenza' => 'nullable|date',
                'arrivo' => 'nullable|date|after_or_equal:partenza',
                'from' => 'nullable|string|max:255',
                'to' => 'nullable|string|max:255',
                'details' => 'nullable|string',
                'interior' => 'nullable|numeric|min:0',
                'oceanview' => 'nullable|numeric|min:0',
                'balcony' => 'nullable|numeric|min:0',
                'minisuite' => 'nullable|numeric|min:0',
                'suite' => 'nullable|numeric|min:0',
            ], [
                'ship.required' => 'Il nome della nave è obbligatorio',
                'cruise.required' => 'Il nome della crociera è obbligatorio',
                'line.required' => 'La compagnia è obbligatoria',
                'duration.integer' => 'La durata deve essere un numero intero',
                'night.integer' => 'Il numero di notti deve essere un numero intero',
                'partenza.date' => 'La data di partenza deve essere una data valida',
                'arrivo.date' => 'La data di arrivo deve essere una data valida',
                'arrivo.after_or_equal' => 'La data di arrivo deve essere uguale o successiva alla partenza',
                'interior.numeric' => 'Il prezzo della cabina interna deve essere un numero',
                'oceanview.numeric' => 'Il prezzo della cabina con vista mare deve essere un numero',
                'balcony.numeric' => 'Il prezzo della cabina con balcone deve essere un numero',
                'minisuite.numeric' => 'Il prezzo della mini suite deve essere un numero',
                'suite.numeric' => 'Il prezzo della suite deve essere un numero',
            ]);

            // Controlla duplicati
            $existing = Cruise::where('ship', $validated['ship'])
                ->where('line', $validated['line'])
                ->where('cruise', $validated['cruise'])
                ->when($validated['partenza'] ?? null, function ($query) use ($validated) {
                    return $query->whereDate('partenza', $validated['partenza']);
                })
                ->first();

            if ($existing) {
                return response()->json([
                    'response' => false,
                    'errors' => ['duplicate' => ['Una crociera con questi dati esiste già']]
                ]);
            }

            $cruise = Cruise::create($validated);

            return response()->json([
                'response' => true,
                'message' => 'Crociera creata con successo',
                'cruise_id' => $cruise->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'response' => false,
                'errors' => $e->errors()
            ]);
        } catch (\Exception $e) {
            Log::error('Errore creazione crociera: ' . $e->getMessage());
            return response()->json([
                'response' => false,
                'errors' => ['general' => ['Si è verificato un errore. Riprova più tardi.']]
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cruise $cruise)
    {
        if (Auth::user()->role !== '1') {
            return redirect('/home')->with('error', 'Accesso negato');
        }

        return view('admin.cruises.show', compact('cruise'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cruise $cruise)
    {
        if (Auth::user()->role !== '1') {
            return redirect('/home')->with('error', 'Accesso negato');
        }

        return view('admin.cruises.edit', compact('cruise'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cruise $cruise)
    {
        try {
            $validated = $request->validate([
                'ship' => 'required|string|max:255',
                'cruise' => 'required|string|max:255',
                'line' => 'required|string|max:255',
                'duration' => 'nullable|integer|min:1|max:365',
                'night' => 'nullable|integer|min:1|max:365',
                'partenza' => 'nullable|date',
                'arrivo' => 'nullable|date|after_or_equal:partenza',
                'from' => 'nullable|string|max:255',
                'to' => 'nullable|string|max:255',
                'details' => 'nullable|string',
                'interior' => 'nullable|numeric|min:0',
                'oceanview' => 'nullable|numeric|min:0',
                'balcony' => 'nullable|numeric|min:0',
                'minisuite' => 'nullable|numeric|min:0',
                'suite' => 'nullable|numeric|min:0',
            ], [
                'ship.required' => 'Il nome della nave è obbligatorio',
                'cruise.required' => 'Il nome della crociera è obbligatorio',
                'line.required' => 'La compagnia è obbligatoria',
                'duration.integer' => 'La durata deve essere un numero intero',
                'night.integer' => 'Il numero di notti deve essere un numero intero',
                'partenza.date' => 'La data di partenza deve essere una data valida',
                'arrivo.date' => 'La data di arrivo deve essere una data valida',
                'arrivo.after_or_equal' => 'La data di arrivo deve essere uguale o successiva alla partenza',
                'interior.numeric' => 'Il prezzo della cabina interna deve essere un numero',
                'oceanview.numeric' => 'Il prezzo della cabina con vista mare deve essere un numero',
                'balcony.numeric' => 'Il prezzo della cabina con balcone deve essere un numero',
                'minisuite.numeric' => 'Il prezzo della mini suite deve essere un numero',
                'suite.numeric' => 'Il prezzo della suite deve essere un numero',
            ]);

            // Controlla duplicati (escludendo l'attuale)
            $existing = Cruise::where('ship', $validated['ship'])
                ->where('line', $validated['line'])
                ->where('cruise', $validated['cruise'])
                ->where('id', '!=', $cruise->id)
                ->when($validated['partenza'] ?? null, function ($query) use ($validated) {
                    return $query->whereDate('partenza', $validated['partenza']);
                })
                ->first();

            if ($existing) {
                return response()->json([
                    'response' => false,
                    'errors' => ['duplicate' => ['Una crociera con questi dati esiste già']]
                ]);
            }

            $cruise->update($validated);

            return response()->json([
                'response' => true,
                'message' => 'Crociera aggiornata con successo'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'response' => false,
                'errors' => $e->errors()
            ]);
        } catch (\Exception $e) {
            Log::error('Errore aggiornamento crociera: ' . $e->getMessage());
            return response()->json([
                'response' => false,
                'errors' => ['general' => ['Si è verificato un errore. Riprova più tardi.']]
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cruise $cruise)
    {
        try {
            $cruise->delete();

            return response()->json([
                'response' => true,
                'message' => 'Crociera eliminata con successo'
            ]);

        } catch (\Exception $e) {
            Log::error('Errore eliminazione crociera: ' . $e->getMessage());
            return response()->json([
                'response' => false,
                'message' => 'Si è verificato un errore durante l\'eliminazione'
            ]);
        }
    }

    /**
     * Get data for DataTables
     */
    public function getData()
    {
        $cruises = Cruise::select([
            'id', 'ship', 'cruise', 'line', 'duration', 'night', 
            'partenza', 'arrivo', 'from', 'to', 'interior', 
            'oceanview', 'balcony', 'minisuite', 'suite', 'details',
            'created_at', 'updated_at'
        ]);

        return DataTables::of($cruises)
            ->addColumn('formatted_duration', function (Cruise $cruise) {
                return $cruise->formatted_duration;
            })
            ->addColumn('price_range', function (Cruise $cruise) {
                return $cruise->price_range;
            })
            ->addColumn('itinerary', function (Cruise $cruise) {
                $partenza = $cruise->partenza ? $cruise->partenza->format('d/m/Y') : 'N/D';
                $arrivo = $cruise->arrivo ? $cruise->arrivo->format('d/m/Y') : 'N/D';
                return $partenza . ' → ' . $arrivo;
            })
            ->addColumn('actions', function (Cruise $cruise) {
                $actions = '<div class="action-buttons">';
                
                // Pulsante Visualizza
                $actions .= '<a href="' . route('cruises.show', $cruise->id) . '" 
                    class="btn btn-sm btn-info me-1" 
                    data-bs-toggle="tooltip" 
                    title="Visualizza dettagli">
                    <i class="fas fa-eye"></i>
                </a>';
                
                // Pulsante Modifica
                $actions .= '<a href="' . route('cruises.edit', $cruise->id) . '" 
                    class="btn btn-sm btn-primary me-1" 
                    data-bs-toggle="tooltip" 
                    title="Modifica crociera">
                    <i class="fas fa-edit"></i>
                </a>';
                
                // Pulsante Elimina
                $actions .= '<button 
                    data-id="' . $cruise->id . '" 
                    data-ship="' . htmlspecialchars($cruise->ship) . '"
                    data-cruise="' . htmlspecialchars($cruise->cruise) . '"
                    class="btn btn-sm btn-danger deleteButton" 
                    data-bs-toggle="tooltip" 
                    title="Elimina crociera">
                    <i class="fas fa-trash"></i>
                </button>';
                
                $actions .= '</div>';
                return $actions;
            })
            ->editColumn('line', function (Cruise $cruise) {
                $badgeClass = 'bg-secondary';
                $line = strtolower($cruise->line);
                
                if (strpos($line, 'msc') !== false) $badgeClass = 'bg-info';
                if (strpos($line, 'costa') !== false) $badgeClass = 'bg-success';
                if (strpos($line, 'royal') !== false) $badgeClass = 'bg-warning';
                if (strpos($line, 'norwegian') !== false) $badgeClass = 'bg-primary';
                
                return '<span class="badge ' . $badgeClass . '">' . $cruise->line . '</span>';
            })
            ->editColumn('interior', function (Cruise $cruise) {
                return Cruise::formatPrice($cruise->interior);
            })
            ->editColumn('oceanview', function (Cruise $cruise) {
                return Cruise::formatPrice($cruise->oceanview);
            })
            ->editColumn('balcony', function (Cruise $cruise) {
                return Cruise::formatPrice($cruise->balcony);
            })
            ->editColumn('details', function (Cruise $cruise) {
                if (!$cruise->details) return '-';
                
                $details = $cruise->details;
                if (strlen($details) > 50) {
                    $details = substr($details, 0, 50) . '...';
                }
                
                return '<span title="' . htmlspecialchars($cruise->details) . '">' . 
                       htmlspecialchars($details) . '</span>';
            })
            ->rawColumns(['line', 'actions', 'details'])
            ->make(true);
    }

    /**
     * Get statistics for dashboard
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_cruises' => Cruise::count(),
                'available_cruises' => Cruise::available()->count(),
                'future_cruises' => Cruise::future()->count(),
                'companies' => Cruise::distinct('line')->count('line'),
                'avg_price' => Cruise::whereNotNull('interior')->avg('interior'),
                'min_price' => Cruise::whereNotNull('interior')->min('interior'),
                'max_price' => Cruise::whereNotNull('interior')->max('interior'),
                'recent_additions' => Cruise::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            ];

            // Aggiungi statistiche per compagnia
            $stats['by_company'] = Cruise::selectRaw('line, COUNT(*) as count')
                ->groupBy('line')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            // Aggiungi statistiche mensili per le partenze
            $stats['departures_by_month'] = Cruise::selectRaw('MONTH(partenza) as month, COUNT(*) as count')
                ->whereNotNull('partenza')
                ->where('partenza', '>=', Carbon::now())
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Errore recupero statistiche: ' . $e->getMessage());
            return response()->json(['error' => 'Impossibile recuperare le statistiche'], 500);
        }
    }

    /**
     * Bulk delete cruises
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            
            if (empty($ids)) {
                return response()->json([
                    'response' => false,
                    'message' => 'Nessuna crociera selezionata'
                ]);
            }

            $deleted = Cruise::whereIn('id', $ids)->delete();

            return response()->json([
                'response' => true,
                'message' => "Eliminate $deleted crociere con successo"
            ]);

        } catch (\Exception $e) {
            Log::error('Errore eliminazione multipla: ' . $e->getMessage());
            return response()->json([
                'response' => false,
                'message' => 'Si è verificato un errore durante l\'eliminazione'
            ]);
        }
    }

    /**
     * Export cruises to CSV
     */
    public function export(Request $request)
    {
        try {
            $filename = 'crociere_export_' . date('Ymd_His') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF"); // BOM UTF-8

                // Headers CSV
                fputcsv($file, [
                    'ID', 'Nave', 'Crociera', 'Compagnia', 'Durata (giorni)', 
                    'Notti', 'Data Partenza', 'Data Arrivo', 'Porto Partenza', 
                    'Porto Arrivo', 'Cabina Interna', 'Vista Mare', 'Balcone', 
                    'Mini Suite', 'Suite', 'Dettagli', 'Data Creazione'
                ], ';');

                // Dati
                Cruise::chunk(100, function ($cruises) use ($file) {
                    foreach ($cruises as $cruise) {
                        fputcsv($file, [
                            $cruise->id,
                            $cruise->ship,
                            $cruise->cruise,
                            $cruise->line,
                            $cruise->duration,
                            $cruise->night,
                            $cruise->partenza ? $cruise->partenza->format('d/m/Y') : '',
                            $cruise->arrivo ? $cruise->arrivo->format('d/m/Y') : '',
                            $cruise->from,
                            $cruise->to,
                            $cruise->interior,
                            $cruise->oceanview,
                            $cruise->balcony,
                            $cruise->minisuite,
                            $cruise->suite,
                            $cruise->details,
                            $cruise->created_at->format('d/m/Y H:i:s')
                        ], ';');
                    }
                });

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Errore export: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Errore durante l\'export');
        }
    }
}