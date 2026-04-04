<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Departure;
use App\Models\UserFavorite;
use App\Models\UserActivity;

class FavoritesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $favorites = UserFavorite::forUser(Auth::id())
            ->with(['departure.product.ship', 'departure.product.cruiseLine', 'departure.latestPrices'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('user.favorites-index', compact('favorites'));
    }

    public function toggle(Departure $departure, Request $request)
    {
        $user       = Auth::user();
        $isFavorite = UserFavorite::toggle($user->id, $departure->id, $request->input('note'));

        UserActivity::log($user->id, $isFavorite ? 'favorite_add' : 'favorite_remove', $departure, [
            'cruise_name' => $departure->product->cruise_name,
        ]);

        Cache::forget("dashboard_user_{$user->id}");

        return response()->json([
            'success'         => true,
            'is_favorite'     => $isFavorite,
            'message'         => $isFavorite ? 'Crociera aggiunta ai preferiti' : 'Crociera rimossa dai preferiti',
            'favorites_count' => UserFavorite::forUser($user->id)->count(),
        ]);
    }

    public function store(Departure $departure, Request $request)
    {
        $user      = Auth::user();
        $validated = $request->validate(['note' => 'nullable|string|max:500']);

        if (UserFavorite::isFavorite($user->id, $departure->id)) {
            return redirect()->back()->with('info', 'Questa crociera è già nei tuoi preferiti');
        }

        UserFavorite::create([
            'user_id'      => $user->id,
            'departure_id' => $departure->id,
            'note'         => $validated['note'] ?? null,
        ]);

        UserActivity::log($user->id, 'favorite_add', $departure, [
            'cruise_name' => $departure->product->cruise_name,
        ]);

        Cache::forget("dashboard_user_{$user->id}");

        return redirect()->back()->with('success', 'Crociera aggiunta ai preferiti!');
    }

    public function destroy(Departure $departure)
    {
        $user     = Auth::user();
        $favorite = UserFavorite::where('user_id', $user->id)
                                ->where('departure_id', $departure->id)
                                ->first();

        if (! $favorite) {
            return redirect()->back()->with('error', 'Preferito non trovato');
        }

        $favorite->delete();

        UserActivity::log($user->id, 'favorite_remove', $departure, [
            'cruise_name' => $departure->product->cruise_name,
        ]);

        Cache::forget("dashboard_user_{$user->id}");

        return redirect()->back()->with('success', 'Crociera rimossa dai preferiti');
    }

    public function updateNote(Departure $departure, Request $request)
    {
        $user      = Auth::user();
        $validated = $request->validate(['note' => 'nullable|string|max:500']);

        $favorite = UserFavorite::where('user_id', $user->id)
                                ->where('departure_id', $departure->id)
                                ->first();

        if (! $favorite) {
            return response()->json(['success' => false, 'message' => 'Preferito non trovato'], 404);
        }

        $favorite->note = $validated['note'] ?? null;
        $favorite->save();

        Cache::forget("dashboard_user_{$user->id}");

        return response()->json(['success' => true, 'message' => 'Nota aggiornata con successo', 'note' => $favorite->note]);
    }

    public function check(Departure $departure)
    {
        return response()->json([
            'success'     => true,
            'is_favorite' => UserFavorite::isFavorite(Auth::id(), $departure->id),
        ]);
    }

    public function getFavorites()
    {
        $favorites = UserFavorite::forUser(Auth::id())
            ->with(['departure.product.ship', 'departure.product.cruiseLine', 'departure.product.portFrom', 'departure.product.portTo', 'departure.latestPrices'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($favorite) {
                $departure = $favorite->departure;
                $product   = $departure->product;
                $minPrice  = $departure->min_price;

                return [
                    'id'              => $departure->id,
                    'ship'            => $product->ship->name ?? 'N/D',
                    'cruise_name'     => $product->cruise_name ?? 'N/D',
                    'line'            => $product->cruiseLine->name ?? 'N/D',
                    'itinerary'       => ($product->portFrom->name ?? 'N/D') . ' - ' . ($product->portTo->name ?? 'N/D'),
                    'price'           => $minPrice,
                    'price_formatted' => $minPrice ? '€' . number_format($minPrice, 0, ',', '.') : 'N/D',
                    'note'            => $favorite->note,
                    'added_at'        => $favorite->created_at->locale('it')->diffForHumans(),
                ];
            });

        return response()->json(['success' => true, 'favorites' => $favorites, 'count' => $favorites->count()]);
    }

    public function destroyAll()
    {
        $user  = Auth::user();
        $count = UserFavorite::where('user_id', $user->id)->delete();

        Cache::forget("dashboard_user_{$user->id}");

        return response()->json(['success' => true, 'message' => "Rimossi {$count} preferiti", 'deleted_count' => $count]);
    }
}
