<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Richiesta;
use Illuminate\Http\Request;
use App\Models\RichiestaTipo;
use App\Models\RichiestaStato;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RichiestaController extends Controller
{
    public function index()
    {
        // Ottieni tutte le richieste dell'utente corrente
        $richieste = Richiesta::with(['stato', 'tipo'])
            ->where('id_utente', Auth::id())
            ->get();

        return view('richieste.index', compact('richieste'));
    }

    public function create()
    {
        $tipi = RichiestaTipo::all();
        $stati = RichiestaStato::all();
        return view('richieste.create', compact('tipi', 'stati'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_richiesta_tipo' => 'required|exists:richiesta_tipo,id',
            'data_fine_validita' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $minimumDate = Carbon::now()->addDays(10);
                    $inputDate = Carbon::parse($value);
                    if ($inputDate->lt($minimumDate)) {
                        $fail('La data di fine validità deve essere almeno 10 giorni dopo la data di oggi.');
                    }
                }
            ],
            'budget' => 'required|numeric',
            'note' => 'nullable|string',
        ], [
            // Messaggi personalizzati per ciascun campo e regola
            'id_richiesta_tipo.required' => 'Il tipo di richiesta è obbligatorio.',
            'id_richiesta_tipo.exists' => 'Il tipo di richiesta selezionato non è valido.',
            'data_fine_validita.required' => 'La data di fine validità è obbligatoria.',
            'data_fine_validita.date' => 'La data di fine validità non è una data valida.',
            'budget.required' => 'Il budget è obbligatorio.',
            'budget.numeric' => 'Il budget deve essere un numero.',
            'note.string' => 'Le note devono essere un testo.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'response' => false,
                'errors' => $validator->errors()
            ]);
        }
        $requestData = $request->all();
        // Calcolo del rating in base al budget
        $budget = $requestData['budget'];
        $maxRating = 100; // Il massimo rating è 100
        $rating = ($budget / 1000) * $maxRating;

        // Variazione casuale tra -10% e +10%
        $variation = rand(-10, 10); // rand() genera un numero intero casuale tra -10 e 10
        $ratingWithVariation = $rating + ($rating * $variation / 100);

        // Assicurati che il rating sia sempre compreso tra 0 e 100
        $finalRating = max(0, min($ratingWithVariation, 100));

        Richiesta::create([
            'id_utente' => Auth::id(),
            'id_richiesta_tipo' => $request->id_richiesta_tipo,
            'id_richiesta_stato' => 1,
            'data_fine_validita' => $request->data_fine_validita,
            'budget' => $request->budget,
            'rating' => $finalRating,
            'note' => $request->note,
        ]);
        // Risposta di successo
        return response()->json([
            'response' => true,
        ]);
    }

    public function show($id)
    {
        $richiesta = Richiesta::with(['stato', 'tipo'])->findOrFail($id);
        return view('richieste.show', compact('richiesta'));
    }

    public function edit($id)
    {
        $richiesta = Richiesta::findOrFail($id);
        $tipi = RichiestaTipo::all();
        $stati = RichiestaStato::all();
        return view('richieste.edit.edit', compact('richiesta', 'tipi', 'stati'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_richiesta_tipo' => 'required|exists:richiesta_tipo,id',
            'id_richiesta_stato' => 'required|exists:richiesta_stato,id',
            'data_fine_validita' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $minimumDate = Carbon::now()->addDays(10);
                    $inputDate = Carbon::parse($value);
                    if ($inputDate->lt($minimumDate)) {
                        $fail('La data di fine validità deve essere almeno 10 giorni dopo la data di oggi.');
                    }
                }
            ],
            'budget' => 'required|numeric',
            'rating' => 'nullable|integer|min:0|max:10',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => false,
                'errors' => $validator->errors()
            ]);
        }
        $richiesta = Richiesta::findOrFail($request->id);


        $richiesta->update([
            'id_richiesta_tipo' => $request->id_richiesta_tipo,
            'id_richiesta_stato' => $request->id_richiesta_stato,
            'data_fine_validita' => $request->data_fine_validita,
            'budget' => $request->budget,
            'rating' => $request->rating,
            'note' => $request->note,
        ]);

        // Risposta di successo
        /* return response()->json(['success' => 'Utente aggiornato con successo.']); */
        return response()->json([
            'response' => true,
        ]); return redirect()->route('richieste.index')->with('success', 'Richiesta aggiornata con successo.');
    }

    public function destroy($id)
    {
        $richiesta = Richiesta::find($id);

        if ($richiesta) {
            // Cambia lo stato della richiesta a "annullata" (id = 5)
            $richiesta->update(['id_richiesta_stato' => 5]);

            return redirect()->route('richieste.index')->with('success', 'Richiesta annullata con successo.');
        }

        return redirect()->route('richieste.index')->with('error', 'Richiesta non trovata.');
    }
}
