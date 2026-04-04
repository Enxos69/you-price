<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Departure;
use App\Models\PriceHistory;
use App\Models\SearchLog;
use App\Models\UserFavorite;
use App\Models\UserCruiseView;
use App\Models\PriceAlert;
use App\Models\UserActivity;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        $dashboardData = Cache::remember("dashboard_user_{$user->id}", 300, function () use ($user) {
            return $this->getDashboardData($user);
        });

        return view('user.dashboard', $dashboardData);
    }

    // -------------------------------------------------------------------------
    // API endpoints (AJAX)
    // -------------------------------------------------------------------------

    public function getStats()
    {
        return response()->json($this->getUserStats(Auth::user()));
    }

    public function getFavoritesJson()
    {
        return response()->json(['success' => true, 'favorites' => $this->getFavorites(Auth::user())]);
    }

    public function getAlertsJson()
    {
        return response()->json(['success' => true, 'alerts' => $this->getPriceAlerts(Auth::user())]);
    }

    public function getActivityTimelineJson()
    {
        return response()->json(['success' => true, 'timeline' => $this->getActivityTimeline(Auth::user())]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function getDashboardData(User $user): array
    {
        return [
            'user'              => $user,
            'stats'             => $this->getUserStats($user),
            'recent_searches'   => $this->getRecentSearches($user),
            'favorites'         => $this->getFavorites($user),
            'price_alerts'      => $this->getPriceAlerts($user),
            'activity_timeline' => $this->getActivityTimeline($user),
            'recommendations'   => $this->getRecommendations($user),
            'most_viewed'       => $this->getMostViewedCruises($user),
        ];
    }

    private function getUserStats(User $user): array
    {
        return [
            'total_searches'  => SearchLog::where('user_id', $user->id)->count(),
            'cruises_viewed'  => UserCruiseView::getTotalViewedByUser($user->id),
            'favorites_count' => UserFavorite::forUser($user->id)->count(),
            'active_alerts'   => PriceAlert::getActiveCountForUser($user->id),
            'member_since'    => $user->created_at->locale('it')->isoFormat('MMMM YYYY'),
        ];
    }

    private function getRecentSearches(User $user)
    {
        return SearchLog::where('user_id', $user->id)
            ->orderByDesc('search_date')
            ->limit(5)
            ->get()
            ->map(fn($search) => [
                'id'                  => $search->id,
                'date_range'          => $search->date_range,
                'port_start'          => $search->port_start,
                'port_end'            => $search->port_end,
                'budget'              => $search->budget ? '€' . number_format($search->budget, 0, ',', '.') : null,
                'participants'        => $search->participants,
                'total_matches'       => $search->total_matches,
                'total_alternatives'  => $search->total_alternatives,
                'avg_price_found'     => $search->avg_price_found ? '€' . number_format($search->avg_price_found, 0, ',', '.') : null,
                'searched_at'         => $search->search_date,
                'time_ago'            => $search->search_date->locale('it')->diffForHumans(),
                'search_params'       => $this->formatSearchParams($search),
            ]);
    }

    private function formatSearchParams(SearchLog $search): string
    {
        $params = [];
        if ($search->port_start)  $params[] = "da {$search->port_start}";
        if ($search->port_end)    $params[] = "a {$search->port_end}";
        if ($search->participants) $params[] = "{$search->participants} " . ($search->participants == 1 ? 'persona' : 'persone');
        if ($search->budget)      $params[] = 'budget €' . number_format($search->budget, 0, ',', '.');
        return implode(' • ', $params);
    }

    private function getFavorites(User $user)
    {
        return UserFavorite::forUser($user->id)
            ->whereHas('departure')
            ->with([
                'departure.product.ship',
                'departure.product.cruiseLine',
                'departure.product.portFrom',
                'departure.product.portTo',
                'departure.latestPrices',
            ])
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
                    'duration'        => $departure->formatted_duration,
                    'nights'          => $departure->duration,
                    'departure_date'  => $departure->dep_date->locale('it')->isoFormat('DD MMM YYYY'),
                    'arrival_date'    => $departure->arr_date->locale('it')->isoFormat('DD MMM YYYY'),
                    'itinerary'       => ($product->portFrom->name ?? 'N/D') . ' - ' . ($product->portTo->name ?? 'N/D'),
                    'price'           => $minPrice,
                    'price_formatted' => $minPrice ? '€' . number_format($minPrice, 0, ',', '.') : 'N/D',
                    'availability'    => $minPrice
                        ? ['status' => 'available', 'label' => 'Disponibile', 'badge_class' => 'bg-success']
                        : ['status' => 'not_available', 'label' => 'N/D', 'badge_class' => 'bg-secondary'],
                    'note'            => $favorite->note,
                    'added_at'        => $favorite->created_at->locale('it')->diffForHumans(),
                ];
            });
    }

    private function getMostViewedCruises(User $user)
    {
        return UserCruiseView::getMostViewedByUser($user->id, 3)
            ->map(fn($view) => [
                'id'          => $view->departure->id,
                'ship'        => $view->departure->product->ship->name ?? 'N/D',
                'cruise_name' => $view->departure->product->cruise_name ?? 'N/D',
                'view_count'  => $view->view_count,
                'last_viewed' => $view->last_viewed_at->locale('it')->diffForHumans(),
            ]);
    }

    private function getPriceAlerts(User $user)
    {
        return PriceAlert::forUser($user->id)
            ->active()
            ->whereHas('departure')
            ->with([
                'departure.product.ship',
                'departure.product.cruiseLine',
                'departure.product.portFrom',
                'departure.product.portTo',
            ])
            ->orderByDesc('created_at')
            ->limit(2)
            ->get()
            ->map(function ($alert) {
                $departure = $alert->departure;
                $product   = $departure->product;

                $currentPrice = PriceHistory::where('departure_id', $departure->id)
                    ->where('category_code', $alert->category_code)
                    ->orderByDesc('id')
                    ->value('price');

                $targetPrice        = (float) $alert->target_price;
                $progressPercentage = ($currentPrice && $targetPrice) ? min(100, round(($targetPrice / $currentPrice) * 100)) : 0;
                $discountPercentage = ($currentPrice && $targetPrice && $currentPrice > $targetPrice)
                    ? round((($currentPrice - $targetPrice) / $currentPrice) * 100)
                    : 0;
                $isReached = $currentPrice && $currentPrice <= $targetPrice;

                return [
                    'id'                      => $alert->id,
                    'ship'                    => $product->ship->name ?? 'N/D',
                    'cruise_name'             => $product->cruise_name ?? 'N/D',
                    'line'                    => $product->cruiseLine->name ?? 'N/D',
                    'itinerary'               => ($product->portFrom->name ?? 'N/D') . ' - ' . ($product->portTo->name ?? 'N/D'),
                    'departure_date'          => $departure->dep_date->locale('it')->isoFormat('MMM YYYY'),
                    'category_code'           => $alert->category_code,
                    'target_price'            => $targetPrice,
                    'target_price_formatted'  => '€' . number_format($targetPrice, 0, ',', '.'),
                    'current_price'           => $currentPrice,
                    'current_price_formatted' => $currentPrice ? '€' . number_format($currentPrice, 0, ',', '.') : 'N/D',
                    'discount_percentage'     => $discountPercentage,
                    'progress_percentage'     => $progressPercentage,
                    'is_reached'              => $isReached,
                    'status_badge'            => $isReached ? 'danger' : 'info',
                    'status_label'            => $isReached ? 'Obiettivo raggiunto!' : 'In monitoraggio',
                ];
            });
    }

    private function getActivityTimeline(User $user)
    {
        return UserActivity::getTimeline($user->id, 4);
    }

    private function getRecommendations(User $user): ?array
    {
        $recentSearches = SearchLog::where('user_id', $user->id)
            ->orderByDesc('search_date')
            ->limit(10)
            ->get();

        if ($recentSearches->isEmpty()) return null;

        $destinations = $recentSearches->map(fn($s) => $s->port_start ?? $s->port_end)->filter()->toArray();
        if (empty($destinations)) return null;

        $destinationCounts = array_count_values($destinations);
        arsort($destinationCounts);
        $topDestination = array_key_first($destinationCounts);

        $excludeIds = UserCruiseView::where('user_id', $user->id)->pluck('departure_id')
            ->merge(UserFavorite::where('user_id', $user->id)->pluck('departure_id'))
            ->unique()->toArray();

        $recommendations = Departure::with(['product.ship', 'product.cruiseLine', 'product.portFrom', 'latestPrices'])
            ->whereNotIn('id', $excludeIds)
            ->future()
            ->whereHas('product.portFrom', fn($q) => $q->where('name', 'LIKE', "%{$topDestination}%"))
            ->whereHas('latestPrices')
            ->limit(3)
            ->get();

        if ($recommendations->isEmpty()) return null;

        return [
            'message'     => "Basandoci sulle tue ricerche, abbiamo trovato <strong>{$recommendations->count()} nuove offerte</strong> per {$topDestination} a prezzi vantaggiosi!",
            'count'       => $recommendations->count(),
            'destination' => $topDestination,
            'cruises'     => $recommendations->map(fn($d) => [
                'id'              => $d->id,
                'ship'            => $d->product->ship->name ?? 'N/D',
                'cruise_name'     => $d->product->cruise_name ?? 'N/D',
                'price'           => $d->min_price,
                'price_formatted' => $d->min_price ? '€' . number_format($d->min_price, 0, ',', '.') : 'N/D',
            ]),
        ];
    }
}
