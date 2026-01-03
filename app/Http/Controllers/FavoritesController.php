<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Cruise;
use App\Models\UserFavorite;
use App\Models\UserActivity;

class FavoritesController extends Controller
{
    /**
     * Costruttore - applica middleware autenticazione
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostra tutti i preferiti dell'utente
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        $favorites = UserFavorite::forUser($user->id)
            ->with('cruise')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('user.favorites-index', compact('favorites'));
    }

    /**
     * Toggle preferito (Aggiungi/Rimuovi)
     * Endpoint API per AJAX
     *
     * @param  \App\Models\Cruise  $cruise
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Cruise $cruise, Request $request)
    {
        $user = Auth::user();
        $note = $request->input('note');

        $isFavorite = UserFavorite::toggle($user->id, $cruise->id, $note);

        // Log dell'attività
        UserActivity::log(
            $user->id,
            $isFavorite ? 'favorite_add' : 'favorite_remove',
            $cruise,
            ['cruise_name' => $cruise->cruise]
        );

        // Invalida la cache della dashboard
        Cache::forget("dashboard_user_{$user->id}");

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
            'message' => $isFavorite 
                ? 'Crociera aggiunta ai preferiti' 
                : 'Crociera rimossa dai preferiti',
            'favorites_count' => UserFavorite::forUser($user->id)->count()
        ]);
    }

    /**
     * Aggiungi ai preferiti (form tradizionale)
     *
     * @param  \App\Models\Cruise  $cruise
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Cruise $cruise, Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'note' => 'nullable|string|max:500'
        ]);

        // Verifica se è già nei preferiti
        if (UserFavorite::isFavorite($user->id, $cruise->id)) {
            return redirect()->back()->with('info', 'Questa crociera è già nei tuoi preferiti');
        }

        UserFavorite::create([
            'user_id' => $user->id,
            'cruise_id' => $cruise->id,
            'note' => $validated['note'] ?? null
        ]);

        // Log dell'attività
        UserActivity::log(
            $user->id,
            'favorite_add',
            $cruise,
            ['cruise_name' => $cruise->cruise]
        );

        // Invalida la cache della dashboard
        Cache::forget("dashboard_user_{$user->id}");

        return redirect()->back()->with('success', 'Crociera aggiunta ai preferiti!');
    }

    /**
     * Rimuovi dai preferiti
     *
     * @param  \App\Models\Cruise  $cruise
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Cruise $cruise)
    {
        $user = Auth::user();

        $favorite = UserFavorite::where('user_id', $user->id)
                                ->where('cruise_id', $cruise->id)
                                ->first();

        if (!$favorite) {
            return redirect()->back()->with('error', 'Preferito non trovato');
        }

        $favorite->delete();

        // Log dell'attività
        UserActivity::log(
            $user->id,
            'favorite_remove',
            $cruise,
            ['cruise_name' => $cruise->cruise]
        );

        // Invalida la cache della dashboard
        Cache::forget("dashboard_user_{$user->id}");

        return redirect()->back()->with('success', 'Crociera rimossa dai preferiti');
    }

    /**
     * Aggiorna la nota di un preferito
     *
     * @param  \App\Models\Cruise  $cruise
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNote(Cruise $cruise, Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'note' => 'nullable|string|max:500'
        ]);

        $favorite = UserFavorite::where('user_id', $user->id)
                                ->where('cruise_id', $cruise->id)
                                ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Preferito non trovato'
            ], 404);
        }

        $favorite->note = $validated['note'] ?? null;
        $favorite->save();

        // Invalida la cache della dashboard
        Cache::forget("dashboard_user_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Nota aggiornata con successo',
            'note' => $favorite->note
        ]);
    }

    /**
     * Verifica se una crociera è nei preferiti
     * Endpoint API per AJAX
     *
     * @param  \App\Models\Cruise  $cruise
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Cruise $cruise)
    {
        $user = Auth::user();
        $isFavorite = UserFavorite::isFavorite($user->id, $cruise->id);

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite
        ]);
    }

    /**
     * Ottieni tutti i preferiti dell'utente (API JSON)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFavorites()
    {
        $user = Auth::user();
        
        $favorites = UserFavorite::forUser($user->id)
            ->with('cruise')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($favorite) {
                $cruise = $favorite->cruise;
                return [
                    'id' => $cruise->id,
                    'ship' => $cruise->ship,
                    'cruise_name' => $cruise->cruise,
                    'line' => $cruise->line,
                    'itinerary' => $cruise->getFormattedItinerary(),
                    'price' => $cruise->getLowestPrice(),
                    'price_formatted' => $cruise->getLowestPrice() ? '€' . number_format($cruise->getLowestPrice(), 0, ',', '.') : 'N/D',
                    'note' => $favorite->note,
                    'added_at' => $favorite->created_at->locale('it')->diffForHumans()
                ];
            });

        return response()->json([
            'success' => true,
            'favorites' => $favorites,
            'count' => $favorites->count()
        ]);
    }

    /**
     * Rimuovi tutti i preferiti (bulk delete)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyAll()
    {
        $user = Auth::user();
        
        $count = UserFavorite::where('user_id', $user->id)->delete();

        // Invalida la cache della dashboard
        Cache::forget("dashboard_user_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => "Rimossi {$count} preferiti",
            'deleted_count' => $count
        ]);
    }
}
