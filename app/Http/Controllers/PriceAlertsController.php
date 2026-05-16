<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Departure;
use App\Models\PriceAlert;
use App\Models\PriceHistory;
use App\Models\UserActivity;

class PriceAlertsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $alertGroups = PriceAlert::forUser(Auth::id())
            ->with(['departure.product.ship', 'departure.product.cruiseLine',
                    'departure.product.portFrom', 'departure.product.portTo'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('departure_id')
            ->sortBy(function ($group) {
                $dep = $group->first()->departure;
                return ($dep->dep_date->isPast() ? '1' : '0') . $dep->dep_date->format('Y-m-d');
            });

        return view('user.alerts-index', compact('alertGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'departure_id'         => 'required|exists:departures,id',
            'target_price'         => 'required|numeric|min:0',
            'category_code'        => 'required|string|max:10',
            'alert_type'           => 'nullable|in:fixed_price,percentage_discount',
            'percentage_threshold' => 'nullable|numeric|min:0|max:100',
        ]);

        $user      = Auth::user();
        $departure = Departure::findOrFail($validated['departure_id']);

        $existingAlert = PriceAlert::where('user_id', $user->id)
            ->where('departure_id', $departure->id)
            ->where('category_code', $validated['category_code'])
            ->where('is_active', true)
            ->first();

        if ($existingAlert) {
            return response()->json([
                'success' => false,
                'message' => 'Hai già un alert attivo per questa partenza e categoria cabina',
            ], 422);
        }

        $departure->load('product');
        $clCats = DB::table('ship_categories')
            ->where('ship_id', $departure->product->ship_id)
            ->where('cruisehost_cat', $validated['category_code'])
            ->pluck('cl_cat');

        $currentPrice = null;
        if ($clCats->isNotEmpty()) {
            $currentPrice = PriceHistory::whereIn('id', function ($sub) use ($departure) {
                    $sub->selectRaw('MAX(id)')
                        ->from('price_history')
                        ->where('departure_id', $departure->id)
                        ->groupBy('category_code');
                })
                ->whereIn('category_code', $clCats)
                ->min('price');
        }

        $alert = PriceAlert::create([
            'user_id'              => $user->id,
            'departure_id'         => $departure->id,
            'target_price'         => $validated['target_price'],
            'category_code'        => $validated['category_code'],
            'alert_type'           => $validated['alert_type'] ?? 'fixed_price',
            'percentage_threshold' => $validated['percentage_threshold'] ?? null,
            'is_active'            => true,
            'current_price'        => $currentPrice,
            'last_checked_at'      => $currentPrice ? now() : null,
        ]);

        UserActivity::log($user->id, 'alert_create', $alert, [
            'cruise_name'    => $departure->product->cruise_name,
            'target_price'   => $validated['target_price'],
            'category_code'  => $validated['category_code'],
        ]);

        Cache::forget("dashboard_user_{$user->id}");

        return response()->json(['success' => true, 'message' => 'Alert prezzo creato con successo', 'alert' => $alert], 201);
    }

    public function update(PriceAlert $alert, Request $request)
    {
        $user = Auth::user();

        if ($alert->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Non autorizzato'], 403);
        }

        $validated = $request->validate([
            'target_price'         => 'nullable|numeric|min:0',
            'category_code'        => 'nullable|string|max:10',
            'alert_type'           => 'nullable|in:fixed_price,percentage_discount',
            'percentage_threshold' => 'nullable|numeric|min:0|max:100',
            'is_active'            => 'nullable|boolean',
        ]);

        $alert->update($validated);

        UserActivity::log($user->id, 'alert_modify', $alert, [
            'cruise_name' => $alert->departure->product->cruise_name,
        ]);

        Cache::forget("dashboard_user_{$user->id}");

        return response()->json(['success' => true, 'message' => 'Alert aggiornato con successo', 'alert' => $alert]);
    }

    public function destroy(PriceAlert $alert)
    {
        $user = Auth::user();

        if ($alert->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Non autorizzato'], 403);
        }

        $cruiseName = $alert->departure->product->cruise_name;

        UserActivity::log($user->id, 'alert_delete', null, ['cruise_name' => $cruiseName]);

        $alert->delete();

        Cache::forget("dashboard_user_{$user->id}");

        return response()->json(['success' => true, 'message' => 'Alert eliminato con successo']);
    }

    public function toggleActive(PriceAlert $alert)
    {
        $user = Auth::user();

        if ($alert->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Non autorizzato'], 403);
        }

        $alert->is_active = ! $alert->is_active;
        if ($alert->is_active) {
            $alert->notification_sent = false;
        }
        $alert->save();

        Cache::forget("dashboard_user_{$user->id}");

        return response()->json([
            'success'   => true,
            'message'   => $alert->is_active ? 'Alert attivato' : 'Alert disattivato',
            'is_active' => $alert->is_active,
        ]);
    }

    public function getAlerts()
    {
        $alerts = PriceAlert::forUser(Auth::id())
            ->with(['departure.product.ship', 'departure.product.cruiseLine', 'departure.latestPrices'])
            ->orderByDesc('is_active')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($alert) {
                $departure    = $alert->departure;
                $currentPrice = PriceHistory::where('departure_id', $departure->id)
                    ->where('category_code', $alert->category_code)
                    ->orderByDesc('id')
                    ->value('price');

                return [
                    'id'                  => $alert->id,
                    'departure_id'        => $departure->id,
                    'ship'                => $departure->product->ship->name ?? 'N/D',
                    'cruise_name'         => $departure->product->cruise_name ?? 'N/D',
                    'category_code'       => $alert->category_code,
                    'target_price'        => $alert->target_price,
                    'current_price'       => $currentPrice,
                    'is_active'           => $alert->is_active,
                    'is_reached'          => $alert->isPriceReached(),
                    'progress_percentage' => $alert->getProgressPercentage(),
                    'discount_percentage' => $alert->getDiscountPercentage(),
                    'created_at'          => $alert->created_at->locale('it')->diffForHumans(),
                ];
            });

        return response()->json([
            'success'       => true,
            'alerts'        => $alerts,
            'active_count'  => $alerts->where('is_active', true)->count(),
            'reached_count' => $alerts->where('is_reached', true)->count(),
        ]);
    }

    public function getActiveAlerts()
    {
        $alerts = PriceAlert::forUser(Auth::id())
            ->active()
            ->with(['departure.product'])
            ->get();

        return response()->json(['success' => true, 'alerts' => $alerts, 'count' => $alerts->count()]);
    }

    public function destroyInactive()
    {
        $user  = Auth::user();
        $count = PriceAlert::where('user_id', $user->id)->where('is_active', false)->delete();

        Cache::forget("dashboard_user_{$user->id}");

        return response()->json(['success' => true, 'message' => "Rimossi {$count} alert inattivi", 'deleted_count' => $count]);
    }

    public function destroyForDeparture(string $departureId)
    {
        $user  = Auth::user();
        $count = PriceAlert::where('user_id', $user->id)
            ->where('departure_id', $departureId)
            ->delete();

        Cache::forget("dashboard_user_{$user->id}");

        return response()->json(['success' => true, 'message' => "Eliminati {$count} alert", 'deleted_count' => $count]);
    }

    public function toggleAllForDeparture(string $departureId)
    {
        $user    = Auth::user();
        $alerts  = PriceAlert::where('user_id', $user->id)->where('departure_id', $departureId)->get();
        $anyActive = $alerts->where('is_active', true)->count() > 0;
        $newState  = ! $anyActive;

        PriceAlert::where('user_id', $user->id)->where('departure_id', $departureId)->update(
            $newState
                ? ['is_active' => true,  'notification_sent' => false]
                : ['is_active' => false]
        );

        Cache::forget("dashboard_user_{$user->id}");

        return response()->json([
            'success'   => true,
            'is_active' => $newState,
            'message'   => $newState ? 'Tutti gli alert attivati' : 'Tutti gli alert disattivati',
        ]);
    }

    public function destroyExpired()
    {
        $user = Auth::user();

        $count = PriceAlert::where('user_id', $user->id)
            ->whereHas('departure', fn($q) => $q->where('dep_date', '<', today()))
            ->delete();

        Cache::forget("dashboard_user_{$user->id}");

        return response()->json(['success' => true, 'message' => "Rimossi {$count} alert per partenze già avvenute", 'deleted_count' => $count]);
    }

    public function resetNotification(PriceAlert $alert)
    {
        if ($alert->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorizzato'], 403);
        }

        $alert->resetNotification();

        return response()->json(['success' => true, 'message' => 'Notifica resettata, verrai avvisato nuovamente se il prezzo scende']);
    }
}
