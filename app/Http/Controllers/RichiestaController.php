<?php

namespace App\Http\Controllers;

use App\Mail\QuoteRequestAdmin;
use App\Mail\QuoteRequestConfirm;
use App\Models\CustomQuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class RichiestaController extends Controller
{
    public function store(Request $request)
    {
        try {
            $dateRange = $request->input('date_range', 'Richiesta rapida');
            $budget = $request->input('budget', 1);
            $participants = $request->input('participants', 1);
            $portStart = $request->input('port_start');
            $notes = $request->input('notes');
            $phone = $request->input('phone');

            $validated = $request->validate([
                'date_range'   => 'nullable|string|max:100',
                'budget'       => 'nullable|numeric|min:1',
                'participants' => 'nullable|integer|min:1|max:50',
                'port_start'   => 'nullable|string|max:255',
                'notes'        => 'nullable|string|max:2000',
                'phone'        => 'nullable|string|max:30',
            ], [
                'budget.numeric'        => 'Il budget deve essere un numero.',
                'budget.min'            => 'Il budget deve essere maggiore di zero.',
                'participants.integer'  => 'Il numero di partecipanti deve essere intero.',
                'participants.min'      => 'Almeno 1 partecipante.',
            ]);

            $quoteRequest = CustomQuoteRequest::create([
                'user_id'      => Auth::id(),
                'date_range'   => $validated['date_range'] ?? $dateRange,
                'budget'       => $validated['budget'] ?? $budget,
                'participants' => $validated['participants'] ?? $participants,
                'port_start'   => $validated['port_start'] ?? $portStart,
                'notes'        => $validated['notes'] ?? $notes,
                'phone'        => $validated['phone'] ?? $phone,
                'status'       => 'pending',
            ]);

            // Carica la relazione utente per le email
            $quoteRequest->load('user');

            Mail::send(new QuoteRequestAdmin($quoteRequest));
            Mail::send(new QuoteRequestConfirm($quoteRequest));

            $message = 'Richiesta inviata con successo. La tua richiesta è stata registrata e verrà elaborata a breve.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->validator->errors()->first(),
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', $e->validator->errors()->first());
        } catch (\Exception $e) {
            Log::error('Errore invio richiesta quotazione: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Si è verificato un errore: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Si è verificato un errore: ' . $e->getMessage());
        }
    }
}
