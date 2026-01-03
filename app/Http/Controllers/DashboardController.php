<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Cruise;
use App\Models\SearchLog;
use App\Models\UserFavorite;
use App\Models\UserCruiseView;
use App\Models\PriceAlert;
use App\Models\UserActivity;

class DashboardController extends Controller
{
    /**
     * Costruttore - applica middleware autenticazione
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostra la dashboard principale dell'utente
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Usa caching per migliorare le performance (cache di 5 minuti)
        $dashboardData = Cache::remember("dashboard_user_{$user->id}", 300, function () use ($user) {
            return $this->getDashboardData($user);
        });

        return view('user.dashboard', $dashboardData);
    }

    /**
     * Ottiene tutti i dati necessari per la dashboard
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    private function getDashboardData(User $user)
    {
        return [
            'user' => $user,
            'stats' => $this->getUserStats($user),
            'recent_searches' => $this->getRecentSearches($user),
            'favorites' => $this->getFavorites($user),
            'price_alerts' => $this->getPriceAlerts($user),
            'activity_timeline' => $this->getActivityTimeline($user),
            'recommendations' => $this->getRecommendations($user),
            'most_viewed' => $this->getMostViewedCruises($user)
        ];
    }

    /**
     * Statistiche personali utente per le card in alto
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    private function getUserStats(User $user)
    {
        return [
            'total_searches' => SearchLog::where('user_id', $user->id)->count(),
            'cruises_viewed' => UserCruiseView::getTotalViewedByUser($user->id),
            'favorites_count' => UserFavorite::forUser($user->id)->count(),
            'active_alerts' => PriceAlert::getActiveCountForUser($user->id),
            'member_since' => $user->created_at->locale('it')->isoFormat('MMMM YYYY')
        ];
    }

    /**
     * Ultime 5 ricerche effettuate dall'utente
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Support\Collection
     */
    private function getRecentSearches(User $user)
    {
        return SearchLog::where('user_id', $user->id)
            ->orderBy('search_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($search) {
                return [
                    'id' => $search->id,
                    'date_range' => $search->date_range,
                    'port_start' => $search->port_start,
                    'port_end' => $search->port_end,
                    'budget' => $search->budget ? '€' . number_format($search->budget, 0, ',', '.') : null,
                    'participants' => $search->participants,
                    'total_matches' => $search->total_matches,
                    'total_alternatives' => $search->total_alternatives,
                    'avg_price_found' => $search->avg_price_found ? '€' . number_format($search->avg_price_found, 0, ',', '.') : null,
                    'searched_at' => $search->search_date,
                    'time_ago' => $search->search_date->locale('it')->diffForHumans(),
                    'search_params' => $this->formatSearchParams($search)
                ];
            });
    }

    /**
     * Formatta i parametri di ricerca in una stringa leggibile
     *
     * @param  \App\Models\SearchLog  $search
     * @return string
     */
    private function formatSearchParams(SearchLog $search)
    {
        $params = [];
        
        if ($search->port_start) {
            $params[] = "da {$search->port_start}";
        }
        
        if ($search->port_end) {
            $params[] = "a {$search->port_end}";
        }
        
        if ($search->participants) {
            $params[] = "{$search->participants} " . ($search->participants == 1 ? 'persona' : 'persone');
        }
        
        if ($search->budget) {
            $params[] = "budget €" . number_format($search->budget, 0, ',', '.');
        }

        return implode(' • ', $params);
    }

    /**
     * Crociere nei preferiti (ultimi 4 per la dashboard)
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Support\Collection
     */
    private function getFavorites(User $user)
    {
        return UserFavorite::forUser($user->id)
            ->with('cruise')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($favorite) {
                $cruise = $favorite->cruise;
                $availability = $cruise->getAvailabilityStatus();
                
                return [
                    'id' => $cruise->id,
                    'ship' => $cruise->ship,
                    'cruise_name' => $cruise->cruise,
                    'line' => $cruise->line,
                    'duration' => $cruise->getFormattedDuration(),
                    'nights' => $cruise->night,
                    'departure_date' => $cruise->partenza ? $cruise->partenza->locale('it')->isoFormat('DD MMM YYYY') : null,
                    'arrival_date' => $cruise->arrivo ? $cruise->arrivo->locale('it')->isoFormat('DD MMM YYYY') : null,
                    'itinerary' => $cruise->getFormattedItinerary(),
                    'price' => $cruise->getLowestPrice(),
                    'price_formatted' => $cruise->getLowestPrice() ? '€' . number_format($cruise->getLowestPrice(), 0, ',', '.') : 'N/D',
                    'availability' => $availability,
                    'note' => $favorite->note,
                    'added_at' => $favorite->created_at->locale('it')->diffForHumans()
                ];
            });
    }

    /**
     * Crociere più visualizzate dall'utente
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Support\Collection
     */
    private function getMostViewedCruises(User $user)
    {
        return UserCruiseView::getMostViewedByUser($user->id, 3)
            ->map(function ($view) {
                $cruise = $view->cruise;
                return [
                    'id' => $cruise->id,
                    'ship' => $cruise->ship,
                    'cruise_name' => $cruise->cruise,
                    'view_count' => $view->view_count,
                    'last_viewed' => $view->last_viewed_at->locale('it')->diffForHumans()
                ];
            });
    }

    /**
     * Alert prezzi attivi (ultimi 2 per la dashboard)
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Support\Collection
     */
    private function getPriceAlerts(User $user)
    {
        return PriceAlert::forUser($user->id)
            ->active()
            ->with('cruise')
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get()
            ->map(function ($alert) {
                $cruise = $alert->cruise;
                
                // Ottieni il prezzo corrente dalla crociera
                $currentPrice = $cruise->getCabinPrice($alert->cabin_type);
                $targetPrice = $alert->target_price;
                
                // Calcola la percentuale di progresso
                $progressPercentage = 0;
                $discountPercentage = 0;
                
                if ($currentPrice && $targetPrice) {
                    $progressPercentage = min(100, round(($targetPrice / $currentPrice) * 100));
                    
                    if ($currentPrice > $targetPrice) {
                        $discountPercentage = round((($currentPrice - $targetPrice) / $currentPrice) * 100);
                    }
                }
                
                $isReached = $currentPrice && $targetPrice && $currentPrice <= $targetPrice;
                
                return [
                    'id' => $alert->id,
                    'ship' => $cruise->ship,
                    'cruise_name' => $cruise->cruise,
                    'line' => $cruise->line,
                    'itinerary' => $cruise->getFormattedItinerary(),
                    'departure_date' => $cruise->partenza ? $cruise->partenza->locale('it')->isoFormat('MMM YYYY') : null,
                    'cabin_type' => $alert->cabin_type,
                    'cabin_type_label' => $this->getCabinTypeLabel($alert->cabin_type),
                    'target_price' => $targetPrice,
                    'target_price_formatted' => '€' . number_format($targetPrice, 0, ',', '.'),
                    'current_price' => $currentPrice,
                    'current_price_formatted' => $currentPrice ? '€' . number_format($currentPrice, 0, ',', '.') : 'N/D',
                    'discount_percentage' => $discountPercentage,
                    'progress_percentage' => $progressPercentage,
                    'is_reached' => $isReached,
                    'status_badge' => $isReached ? 'danger' : 'info',
                    'status_label' => $isReached ? 'Obiettivo raggiunto!' : 'In monitoraggio'
                ];
            });
    }

    /**
     * Timeline attività recenti (ultimi 4 eventi)
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Support\Collection
     */
    private function getActivityTimeline(User $user)
    {
        return UserActivity::getTimeline($user->id, 4);
    }

    /**
     * Consigli personalizzati basati sulle ricerche
     *
     * @param  \App\Models\User  $user
     * @return array|null
     */
    private function getRecommendations(User $user)
    {
        // Ottieni le ultime ricerche dell'utente
        $recentSearches = SearchLog::where('user_id', $user->id)
            ->orderBy('search_date', 'desc')
            ->limit(10)
            ->get();

        if ($recentSearches->isEmpty()) {
            return null;
        }

        // Analizza pattern di ricerca - trova la destinazione più cercata
        $destinations = $recentSearches->map(function($search) {
            return $search->port_start ?? $search->port_end;
        })->filter()->toArray();
        
        if (empty($destinations)) {
            return null;
        }
        
        $destinationCounts = array_count_values($destinations);
        arsort($destinationCounts);
        $topDestination = array_key_first($destinationCounts);

        // Budget medio
        $averageBudget = $recentSearches->avg('budget');

        // Trova crociere simili non ancora viste o nei preferiti
        $viewedCruiseIds = UserCruiseView::where('user_id', $user->id)
            ->pluck('cruise_id')
            ->toArray();
            
        $favoriteCruiseIds = UserFavorite::where('user_id', $user->id)
            ->pluck('cruise_id')
            ->toArray();
            
        $excludeIds = array_merge($viewedCruiseIds, $favoriteCruiseIds);

        $recommendations = Cruise::query()
            ->when($topDestination, function ($query) use ($topDestination) {
                $query->byDestination($topDestination);
            })
            ->whereNotIn('id', $excludeIds)
            ->limit(3)
            ->get();

        if ($recommendations->isNotEmpty()) {
            return [
                'message' => "Basandoci sulle tue ricerche, abbiamo trovato <strong>{$recommendations->count()} nuove offerte</strong> per {$topDestination} a prezzi vantaggiosi!",
                'count' => $recommendations->count(),
                'destination' => $topDestination,
                'cruises' => $recommendations->map(function ($cruise) {
                    return [
                        'id' => $cruise->id,
                        'ship' => $cruise->ship,
                        'cruise_name' => $cruise->cruise,
                        'price' => $cruise->getLowestPrice(),
                        'price_formatted' => $cruise->getLowestPrice() ? '€' . number_format($cruise->getLowestPrice(), 0, ',', '.') : 'N/D'
                    ];
                })
            ];
        }

        return null;
    }

    /**
     * Traduce il tipo di cabina in etichetta italiana
     *
     * @param  string  $cabinType
     * @return string
     */
    private function getCabinTypeLabel($cabinType)
    {
        $labels = [
            'interior' => 'Interna',
            'oceanview' => 'Vista Mare',
            'balcony' => 'Balcone',
            'minisuite' => 'Mini Suite',
            'suite' => 'Suite'
        ];

        return $labels[$cabinType] ?? ucfirst($cabinType);
    }

    /**
     * API: Ritorna solo le statistiche (AJAX)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        $user = Auth::user();
        return response()->json($this->getUserStats($user));
    }

    /**
     * API: Ritorna i preferiti dell'utente (AJAX)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFavoritesJson()
    {
        $user = Auth::user();
        $favorites = $this->getFavorites($user);
        
        return response()->json([
            'success' => true,
            'favorites' => $favorites
        ]);
    }

    /**
     * API: Ritorna gli alert attivi (AJAX)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAlertsJson()
    {
        $user = Auth::user();
        $alerts = $this->getPriceAlerts($user);
        
        return response()->json([
            'success' => true,
            'alerts' => $alerts
        ]);
    }

    /**
     * API: Ritorna la timeline attività (AJAX)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivityTimelineJson()
    {
        $user = Auth::user();
        $timeline = $this->getActivityTimeline($user);
        
        return response()->json([
            'success' => true,
            'timeline' => $timeline
        ]);
    }

    /**
     * Invalida la cache della dashboard per l'utente corrente
     *
     * @return void
     */
    private function clearDashboardCache()
    {
        $user = Auth::user();
        Cache::forget("dashboard_user_{$user->id}");
    }
}
