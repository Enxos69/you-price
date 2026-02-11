<?php

namespace App\Http\Controllers;

use App\Mail\QuoteRequestAdmin;
use App\Mail\QuoteRequestConfirm;
use App\Models\CustomQuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RichiestaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_range'   => 'required|string|max:100',
            'budget'       => 'required|numeric|min:1',
            'participants' => 'required|integer|min:1|max:50',
            'port_start'   => 'nullable|string|max:255',
            'notes'        => 'nullable|string|max:2000',
            'phone'        => 'nullable|string|max:30',
        ], [
            'date_range.required'   => 'Il periodo di viaggio è obbligatorio.',
            'budget.required'       => 'Il budget è obbligatorio.',
            'budget.numeric'        => 'Il budget deve essere un numero.',
            'budget.min'            => 'Il budget deve essere maggiore di zero.',
            'participants.required' => 'Il numero di partecipanti è obbligatorio.',
            'participants.integer'  => 'Il numero di partecipanti deve essere intero.',
            'participants.min'      => 'Almeno 1 partecipante.',
        ]);

        try {
            $quoteRequest = CustomQuoteRequest::create([
                'user_id'      => Auth::id(),
                'date_range'   => $validated['date_range'],
                'budget'       => $validated['budget'],
                'participants' => $validated['participants'],
                'port_start'   => $validated['port_start'] ?? null,
                'notes'        => $validated['notes'] ?? null,
                'phone'        => $validated['phone'] ?? null,
                'status'       => 'pending',
            ]);

            // Carica la relazione utente per le email
            $quoteRequest->load('user');

            // Mail all'admin
            Mail::send(new QuoteRequestAdmin($quoteRequest));

            // Mail di conferma all'utente
            Mail::send(new QuoteRequestConfirm($quoteRequest));

            return response()->json([
                'success' => true,
                'message' => 'Richiesta inviata con successo! Ti abbiamo inviato una email di conferma.',
            ]);
        } catch (\Exception $e) {
            Log::error('Errore invio richiesta quotazione: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Si è verificato un errore. Riprova più tardi.',
            ], 500);
        }
    }
}
