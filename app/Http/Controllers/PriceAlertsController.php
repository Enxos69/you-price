<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Cruise;
use App\Models\PriceAlert;
use App\Models\UserActivity;

class PriceAlertsController extends Controller
{
    /**
     * Costruttore - applica middleware autenticazione
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostra tutti gli alert dell'utente
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        $alerts = PriceAlert::forUser($user->id)
            ->with('cruise')
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('user.alerts-index', compact('alerts'));
    }

    /**
     * Crea un nuovo alert
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cruise_id' => 'required|exists:cruises,id',
            'target_price' => 'required|numeric|min:0',
            'cabin_type' => 'required|in:interior,oceanview,balcony,minisuite,suite',
            'alert_type' => 'nullable|in:fixed_price,percentage_discount',
            'percentage_threshold' => 'nullable|numeric|min:0|max:100'
        ]);

        $user = Auth::user();
        $cruise = Cruise::findOrFail($validated['cruise_id']);

        // Verifica se esiste già un alert attivo per questa combinazione
        $existingAlert = PriceAlert::where('user_id', $user->id)
            ->where('cruise_id', $cruise->id)
            ->where('cabin_type', $validated['cabin_type'])
            ->where('is_active', true)
            ->first();

        if ($existingAlert) {
            return response()->json([
                'success' => false,
                'message' => 'Hai già un alert attivo per questa crociera e tipo di cabina'
            ], 422);
        }

        // Crea l'alert
        $alert = PriceAlert::create([
            'user_id' => $user->id,
            'cruise_id' => $cruise->id,
            'target_price' => $validated['target_price'],
            'cabin_type' => $validated['cabin_type'],
            'alert_type' => $validated['alert_type'] ?? 'fixed_price',
            'percentage_threshold' => $validated['percentage_threshold'] ?? null,
            'is_active' => true
        ]);

        // Log dell'attività
        UserActivity::log(
            $user->id,
            'alert_create',
            $alert,
            [
                'cruise_name' => $cruise->cruise,
                'target_price' => $validated['target_price'],
                'cabin_type' => $validated['cabin_type']
            ]
        );

        // Invalida la cache della dashboard
        Cache::forget("dashboard_user_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Alert prezzo creato con successo',
            'alert' => $alert
        ], 201);
    }

    /**
     * Aggiorna un alert esistente
     *
     * @param  \App\Models\PriceAlert  $alert
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PriceAlert $alert, Request $request)
    {
        $user = Auth::user();

        // Verifica che l'alert appartenga all'utente
        if ($alert->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorizzato'
            ], 403);
        }

        $validated = $request->validate([
            'target_price' => 'nullable|numeric|min:0',
            'cabin_type' => 'nullable|in:interior,oceanview,balcony,minisuite,suite',
            'alert_type' => 'nullable|in:fixed_price,percentage_discount',
            'percentage_threshold' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean'
        ]);

        $alert->update($validated);

        // Log dell'attività
        UserActivity::log(
            $user->id,
            'alert_modify',
            $alert,
            ['cruise_name' => $alert->cruise->cruise]
        );

        // Invalida la cache della dashboard
        Cache::forget("dashboard_user_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Alert aggiornato con successo',
            'alert' => $alert
        ]);
    }

    /**
     * Elimina un alert
     *
     * @param  \App\Models\PriceAlert  $alert
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(PriceAlert $alert)
    {
        $user = Auth::user();

        // Verifica che l'alert appartenga all'utente
        if ($alert->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorizzato'
            ], 403);
        }

        $cruiseName = $alert->cruise->cruise;

        // Log dell'attività prima di eliminare
        UserActivity::log(
            $user->id,
            'alert_delete',
            null,
            ['cruise_name' => $cruiseName]
        );

        $alert->delete();

        // Invalida la cache della dashboard
        Cache::forget("dashboard_user_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Alert eliminato con successo'
        ]);
    }

    /**
     * Attiva/Disattiva un alert
     *
     * @param  \App\Models\PriceAlert  $alert
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive(PriceAlert $alert)
    {
        $user = Auth::user();

        // Verifica che l'alert appartenga all'utente
        if ($alert->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorizzato'
            ], 403);
        }

        $alert->is_active = !$alert->is_active;
        
        // Reset notifica se viene riattivato
        if ($alert->is_active) {
            $alert->notification_sent = false;
        }
        
        $alert->save();

        // Invalida la cache della dashboard
        Cache::forget("dashboard_user_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => $alert->is_active ? 'Alert attivato' : 'Alert disattivato',
            'is_active' => $alert->is_active
        ]);
    }

    /**
     * Ottieni tutti gli alert dell'utente (API)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAlerts()
    {
        $user = Auth::user();
        
        $alerts = PriceAlert::forUser($user->id)
            ->with('cruise')
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($alert) {
                $cruise = $alert->cruise;
                $currentPrice = $cruise->getCabinPrice($alert->cabin_type);
                
                return [
                    'id' => $alert->id,
                    'cruise_id' => $cruise->id,
                    'ship' => $cruise->ship,
                    'cruise_name' => $cruise->cruise,
                    'cabin_type' => $alert->cabin_type,
                    'target_price' => $alert->target_price,
                    'current_price' => $currentPrice,
                    'is_active' => $alert->is_active,
                    'is_reached' => $alert->isPriceReached(),
                    'progress_percentage' => $alert->getProgressPercentage(),
                    'discount_percentage' => $alert->getDiscountPercentage(),
                    'created_at' => $alert->created_at->locale('it')->diffForHumans()
                ];
            });

        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'active_count' => $alerts->where('is_active', true)->count(),
            'reached_count' => $alerts->where('is_reached', true)->count()
        ]);
    }

    /**
     * Ottieni gli alert attivi (solo quelli attivi)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveAlerts()
    {
        $user = Auth::user();
        
        $alerts = PriceAlert::forUser($user->id)
            ->active()
            ->with('cruise')
            ->get();

        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'count' => $alerts->count()
        ]);
    }

    /**
     * Ottieni gli alert che hanno raggiunto il target
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTriggeredAlerts()
    {
        $user = Auth::user();
        
        $alerts = PriceAlert::getTriggeredAlerts($user->id);

        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'count' => $alerts->count()
        ]);
    }

    /**
     * Reset notifica per un alert
     * (utile se l'utente vuole essere rinotificato)
     *
     * @param  \App\Models\PriceAlert  $alert
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetNotification(PriceAlert $alert)
    {
        $user = Auth::user();

        // Verifica che l'alert appartenga all'utente
        if ($alert->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorizzato'
            ], 403);
        }

        $alert->resetNotification();

        return response()->json([
            'success' => true,
            'message' => 'Notifica resettata, verrai avvisato nuovamente se il prezzo scende'
        ]);
    }

    /**
     * Elimina tutti gli alert inattivi
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyInactive()
    {
        $user = Auth::user();
        
        $count = PriceAlert::where('user_id', $user->id)
                          ->where('is_active', false)
                          ->delete();

        // Invalida la cache della dashboard
        Cache::forget("dashboard_user_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => "Rimossi {$count} alert inattivi",
            'deleted_count' => $count
        ]);
    }
}
