<?php

namespace App\Http\Controllers;

use App\Models\SearchLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SearchAnalyticsController extends Controller
{
    /**
     * Costruttore - middleware di autenticazione admin
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role !== '1') {
                return redirect('/home')->with('error', 'Accesso negato - Solo amministratori');
            }
            return $next($request);
        });
    }

    /**
     * Dashboard principale analytics
     */
    public function index()
    {
        return view('admin.analytics.index');
    }

    /**
     * Statistiche generali con cache
     */
    public function getGeneralStats()
    {
        return $this->cacheResponse('general_stats', function () {
            $today = Carbon::today();
            $yesterday = $today->copy()->subDay();

            $stats = [
                // Contatori principali
                'total_searches' => SearchLog::count(),
                'successful_searches' => SearchLog::where('search_successful', true)->count(),
                'registered_users_searches' => SearchLog::whereNotNull('user_id')->count(),
                'guest_searches' => SearchLog::whereNull('user_id')->count(),
                
                // Metriche qualitative
                'avg_satisfaction' => round(SearchLog::avg('satisfaction_score') ?? 0, 1),
                'avg_search_duration' => round(SearchLog::avg('search_duration_ms') ?? 0),
                'total_matches_found' => SearchLog::sum('total_matches') ?? 0,
                'avg_budget' => round(SearchLog::avg('budget') ?? 0, 2),
                
                // Statistiche giornaliere
                'today_searches' => SearchLog::whereDate('search_date', $today)->count(),
                'yesterday_searches' => SearchLog::whereDate('search_date', $yesterday)->count(),
                
                // Partecipanti più comuni
                'most_popular_participants' => $this->getMostPopularParticipants(),
            ];

            // Calcola variazione percentuale giornaliera
            $stats['daily_change_percent'] = $this->calculatePercentageChange(
                $stats['today_searches'],
                $stats['yesterday_searches']
            );

            return $stats;
        }, 300); // Cache per 5 minuti
    }

    /**
     * Trend ricerche con parametri personalizzabili
     */
    public function getSearchTrends(Request $request)
    {
        $days = min(max($request->get('days', 30), 1), 365); // Limita tra 1 e 365 giorni
        
        return $this->cacheResponse("search_trends_{$days}", function () use ($days) {
            return SearchLog::selectRaw('
                DATE(search_date) as date,
                COUNT(*) as total_searches,
                COUNT(CASE WHEN search_successful = 1 THEN 1 END) as successful_searches,
                ROUND(AVG(satisfaction_score), 1) as avg_satisfaction,
                ROUND(AVG(search_duration_ms)) as avg_duration,
                COUNT(DISTINCT user_id) as unique_users
            ')
            ->where('search_date', '>=', Carbon::now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        }, 600); // Cache per 10 minuti
    }

    /**
     * Statistiche dispositivi e browser ottimizzate
     */
    public function getDeviceStats()
    {
        return $this->cacheResponse('device_stats', function () {
            // Query ottimizzata con una sola chiamata al DB
            $deviceData = SearchLog::selectRaw('
                device_type,
                browser,
                operating_system,
                COUNT(*) as count,
                AVG(search_duration_ms) as avg_duration,
                AVG(satisfaction_score) as avg_satisfaction,
                COUNT(CASE WHEN search_successful = 1 THEN 1 END) as successful_count
            ')
            ->whereNotNull('device_type')
            ->groupBy('device_type', 'browser', 'operating_system')
            ->get();

            return [
                'devices' => $this->aggregateByField($deviceData, 'device_type'),
                'browsers' => $this->aggregateByField($deviceData, 'browser', 10),
                'operating_systems' => $this->aggregateByField($deviceData, 'operating_system', 10)
            ];
        }, 900); // Cache per 15 minuti
    }

    /**
     * Statistiche geografiche ottimizzate
     */
    public function getGeographicStats()
    {
        return $this->cacheResponse('geographic_stats', function () {
            $geoData = SearchLog::selectRaw('
                country,
                city,
                isp,
                COUNT(*) as searches,
                AVG(search_duration_ms) as avg_duration,
                COUNT(CASE WHEN search_successful = 1 THEN 1 END) as successful_searches
            ')
            ->whereNotNull('country')
            ->where('country', '!=', 'Local')
            ->groupBy('country', 'city', 'isp')
            ->get();

            return [
                'countries' => $this->aggregateByField($geoData, 'country', 15),
                'cities' => $this->aggregateTopCities($geoData, 10),
                'isps' => $this->aggregateByField($geoData, 'isp', 10)
            ];
        }, 1800); // Cache per 30 minuti
    }

    /**
     * Statistiche parametri di ricerca
     */
    public function getSearchParametersStats()
    {
        return $this->cacheResponse('search_parameters', function () {
            return [
                'budget_ranges' => $this->getBudgetDistribution(),
                'participants' => $this->getParticipantsDistribution(),
                'popular_ports' => $this->getPopularPorts(),
                'monthly_patterns' => $this->getMonthlyPatterns(),
                'seasonal_trends' => $this->getSeasonalTrends()
            ];
        }, 1800); // Cache per 30 minuti
    }

    /**
     * Performance metrics avanzate
     */
    public function getPerformanceMetrics()
    {
        return $this->cacheResponse('performance_metrics', function () {
            // Usa query SQL ottimizzata per calcolare percentili
            $metrics = DB::selectOne('
                SELECT 
                    AVG(search_duration_ms) as avg_duration,
                    MIN(search_duration_ms) as min_duration,
                    MAX(search_duration_ms) as max_duration,
                    COUNT(CASE WHEN search_duration_ms > 3000 THEN 1 END) as slow_searches,
                    COUNT(CASE WHEN search_successful = 0 THEN 1 END) as failed_searches,
                    COUNT(*) as total_searches,
                    (SELECT search_duration_ms FROM search_logs 
                     WHERE search_duration_ms IS NOT NULL 
                     ORDER BY search_duration_ms 
                     LIMIT 1 OFFSET (SELECT COUNT(*) * 0.95 FROM search_logs WHERE search_duration_ms IS NOT NULL)
                    ) as p95_duration
                FROM search_logs 
                WHERE search_duration_ms IS NOT NULL
            ');

            $devicePerformance = SearchLog::selectRaw('
                device_type,
                AVG(search_duration_ms) as avg_duration,
                COUNT(*) as searches,
                AVG(satisfaction_score) as avg_satisfaction,
                COUNT(CASE WHEN search_successful = 1 THEN 1 END) as successful_searches
            ')
            ->whereNotNull('device_type')
            ->whereNotNull('search_duration_ms')
            ->groupBy('device_type')
            ->get();

            $commonErrors = SearchLog::selectRaw('
                error_message,
                COUNT(*) as occurrences,
                DATE(search_date) as last_occurrence
            ')
            ->whereNotNull('error_message')
            ->where('search_successful', false)
            ->groupBy('error_message')
            ->orderBy('occurrences', 'desc')
            ->limit(5)
            ->get();

            return [
                'overall_metrics' => $metrics,
                'device_performance' => $devicePerformance,
                'common_errors' => $commonErrors,
                'performance_score' => $this->calculatePerformanceScore($metrics)
            ];
        }, 600); // Cache per 10 minuti
    }

    /**
     * Log ricerche con filtri avanzati e paginazione ottimizzata
     */
    public function getSearchLogs(Request $request)
    {
        $query = SearchLog::with(['user:id,name,surname,email'])
            ->select([
                'id', 'user_id', 'search_date', 'date_range', 'budget', 'participants',
                'port_start', 'total_matches', 'total_alternatives', 'satisfaction_score',
                'device_type', 'operating_system', 'browser', 'browser_version',
                'country', 'city', 'isp', 'ip_address', 'search_duration_ms',
                'search_successful', 'error_message', 'is_guest'
            ])
            ->orderBy('search_date', 'desc');

        // Applica filtri in modo efficiente
        $this->applyFilters($query, $request);

        try {
            $logs = $query->paginate($request->get('per_page', 25));
            
            // Trasforma i dati per ottimizzare la response
            $logs->getCollection()->transform(function ($log) {
                return $this->transformLogForResponse($log);
            });

            return response()->json($logs);
        } catch (\Exception $e) {
            Log::error('Errore caricamento logs: ' . $e->getMessage());
            return response()->json(['error' => 'Errore nel caricamento dei log'], 500);
        }
    }

    /**
     * Export CSV ottimizzato
     */
    public function exportCsv(Request $request)
    {
        try {
            $query = SearchLog::with('user:id,name,surname,email')
                ->orderBy('search_date', 'desc');

            $this->applyFilters($query, $request);

            $filename = 'search_analytics_' . date('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ];

            return response()->stream(function () use ($query) {
                $this->generateCsvStream($query);
            }, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Errore export CSV: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Errore durante l\'export: ' . $e->getMessage());
        }
    }

    /**
     * Executive summary per dashboard dirigenziale
     */
    public function getExecutiveSummary()
    {
        return $this->cacheResponse('executive_summary', function () {
            $now = Carbon::now();
            $today = $now->copy()->startOfDay();
            $lastWeek = $now->copy()->subWeek();
            $lastMonth = $now->copy()->subMonth();

            $summary = [
                // KPI principali
                'kpis' => $this->getMainKPIs($today, $lastWeek, $lastMonth),
                
                // Trend crescita
                'growth_trends' => $this->getGrowthTrends($today, $lastWeek, $lastMonth),
                
                // Performance indicators
                'performance_indicators' => $this->getPerformanceIndicators(),
                
                // Top insights
                'top_insights' => $this->getTopInsights(),
                
                // Alerting
                'alerts' => $this->getSystemAlerts()
            ];

            return $summary;
        }, 300); // Cache per 5 minuti
    }

    /**
     * Pulizia log vecchi con controlli di sicurezza
     */
    public function cleanupOldLogs(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:90|max:730', // Min 3 mesi, max 2 anni
            'confirm' => 'required|boolean|accepted'
        ]);

        try {
            DB::beginTransaction();
            
            $cutoffDate = Carbon::now()->subDays($request->days);
            $toDeleteCount = SearchLog::where('search_date', '<', $cutoffDate)->count();
            
            // Controllo di sicurezza: non eliminare più del 70% dei dati
            $totalRecords = SearchLog::count();
            if ($toDeleteCount > ($totalRecords * 0.7)) {
                throw new \Exception('Operazione bloccata: si sta tentando di eliminare troppi record');
            }

            $deletedCount = SearchLog::where('search_date', '<', $cutoffDate)->delete();
            
            DB::commit();
            
            // Invalida cache
            $this->clearCache();
            
            Log::info("Pulizia log completata: eliminati {$deletedCount} record più vecchi di {$request->days} giorni", [
                'admin_user' => Auth::id(),
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate->toDateString()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Eliminati {$deletedCount} log più vecchi di {$request->days} giorni",
                'deleted_count' => $deletedCount,
                'remaining_count' => $totalRecords - $deletedCount
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Errore pulizia logs: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== METODI HELPER PRIVATI ====================

    /**
     * Cache response con gestione errori
     */
    private function cacheResponse($key, $callback, $ttl = 300)
    {
        try {
            return Cache::remember("analytics_{$key}", $ttl, $callback);
        } catch (\Exception $e) {
            Log::error("Errore cache analytics ({$key}): " . $e->getMessage());
            return $callback(); // Fallback senza cache
        }
    }

    /**
     * Calcola variazione percentuale
     */
    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Ottiene partecipanti più comuni
     */
    private function getMostPopularParticipants()
    {
        return SearchLog::selectRaw('participants, COUNT(*) as count')
            ->whereNotNull('participants')
            ->groupBy('participants')
            ->orderBy('count', 'desc')
            ->value('participants') ?? 2;
    }

    /**
     * Aggrega dati per campo specifico
     */
    private function aggregateByField($collection, $field, $limit = null)
    {
        $aggregated = $collection->groupBy($field)->map(function ($group) {
            return [
                'count' => $group->sum('count'),
                'avg_duration' => round($group->avg('avg_duration') ?? 0),
                'avg_satisfaction' => round($group->avg('avg_satisfaction') ?? 0, 1),
                'success_rate' => $group->sum('count') > 0 ? 
                    round(($group->sum('successful_count') / $group->sum('count')) * 100, 1) : 0
            ];
        })->sortByDesc('count');

        return $limit ? $aggregated->take($limit)->values() : $aggregated->values();
    }

    /**
     * Aggrega top città con paese
     */
    private function aggregateTopCities($geoData, $limit)
    {
        return $geoData->groupBy('city')->map(function ($group, $city) {
            $country = $group->first()->country;
            return [
                'city' => $city,
                'country' => $country,
                'searches' => $group->sum('searches'),
                'avg_duration' => round($group->avg('avg_duration') ?? 0),
                'success_rate' => $group->sum('searches') > 0 ? 
                    round(($group->sum('successful_searches') / $group->sum('searches')) * 100, 1) : 0
            ];
        })->sortByDesc('searches')->take($limit)->values();
    }

    /**
     * Distribuzione budget ottimizzata
     */
    private function getBudgetDistribution()
    {
        return SearchLog::selectRaw('
            CASE 
                WHEN budget < 1000 THEN "0-999"
                WHEN budget < 2000 THEN "1000-1999"
                WHEN budget < 3000 THEN "2000-2999"
                WHEN budget < 5000 THEN "3000-4999"
                ELSE "5000+"
            END as budget_range,
            COUNT(*) as count,
            AVG(satisfaction_score) as avg_satisfaction
        ')
        ->whereNotNull('budget')
        ->groupBy('budget_range')
        ->orderByRaw('MIN(budget)')
        ->get()
        ->mapWithKeys(function ($item) {
            return [$item->budget_range => [
                'count' => $item->count,
                'avg_satisfaction' => round($item->avg_satisfaction ?? 0, 1)
            ]];
        });
    }

    /**
     * Distribuzione partecipanti
     */
    private function getParticipantsDistribution()
    {
        return SearchLog::selectRaw('
            participants,
            COUNT(*) as count,
            AVG(budget) as avg_budget,
            AVG(satisfaction_score) as avg_satisfaction
        ')
        ->whereNotNull('participants')
        ->groupBy('participants')
        ->orderBy('participants')
        ->get();
    }

    /**
     * Porti più ricercati
     */
    private function getPopularPorts()
    {
        return SearchLog::selectRaw('
            port_start,
            COUNT(*) as searches,
            AVG(satisfaction_score) as avg_satisfaction,
            COUNT(CASE WHEN total_matches > 0 THEN 1 END) as searches_with_results
        ')
        ->whereNotNull('port_start')
        ->where('port_start', '!=', '')
        ->groupBy('port_start')
        ->orderBy('searches', 'desc')
        ->limit(10)
        ->get();
    }

    /**
     * Pattern mensili dalle date_range
     */
    private function getMonthlyPatterns()
    {
        return SearchLog::selectRaw('
            CASE 
                WHEN date_range REGEXP "(^|/)0?1/" THEN "Gennaio"
                WHEN date_range REGEXP "(^|/)0?2/" THEN "Febbraio"
                WHEN date_range REGEXP "(^|/)0?3/" THEN "Marzo"
                WHEN date_range REGEXP "(^|/)0?4/" THEN "Aprile"
                WHEN date_range REGEXP "(^|/)0?5/" THEN "Maggio"
                WHEN date_range REGEXP "(^|/)0?6/" THEN "Giugno"
                WHEN date_range REGEXP "(^|/)0?7/" THEN "Luglio"
                WHEN date_range REGEXP "(^|/)0?8/" THEN "Agosto"
                WHEN date_range REGEXP "(^|/)0?9/" THEN "Settembre"
                WHEN date_range REGEXP "(^|/)10/" THEN "Ottobre"
                WHEN date_range REGEXP "(^|/)11/" THEN "Novembre"
                WHEN date_range REGEXP "(^|/)12/" THEN "Dicembre"
                ELSE "Altro"
            END as month,
            COUNT(*) as searches,
            AVG(budget) as avg_budget
        ')
        ->whereNotNull('date_range')
        ->groupBy('month')
        ->orderBy('searches', 'desc')
        ->get();
    }

    /**
     * Trend stagionali
     */
    private function getSeasonalTrends()
    {
        return SearchLog::selectRaw('
            QUARTER(search_date) as quarter,
            YEAR(search_date) as year,
            COUNT(*) as searches,
            AVG(satisfaction_score) as avg_satisfaction
        ')
        ->groupBy('quarter', 'year')
        ->orderBy('year', 'desc')
        ->orderBy('quarter', 'desc')
        ->limit(8) // Ultimi 2 anni
        ->get();
    }

    /**
     * Calcola performance score
     */
    private function calculatePerformanceScore($metrics)
    {
        if (!$metrics) return 0;

        $score = 100;
        
        // Penalizza per durata alta
        if ($metrics->avg_duration > 2000) $score -= 20;
        elseif ($metrics->avg_duration > 1500) $score -= 10;
        
        // Penalizza per ricerche lente
        $slowPercentage = ($metrics->slow_searches / $metrics->total_searches) * 100;
        if ($slowPercentage > 10) $score -= 30;
        elseif ($slowPercentage > 5) $score -= 15;
        
        // Penalizza per fallimenti
        $failureRate = ($metrics->failed_searches / $metrics->total_searches) * 100;
        if ($failureRate > 5) $score -= 25;
        elseif ($failureRate > 2) $score -= 10;

        return max($score, 0);
    }

    /**
     * Applica filtri alla query
     */
    private function applyFilters($query, $request)
    {
        if ($request->filled('user_type')) {
            if ($request->user_type === 'registered') {
                $query->whereNotNull('user_id');
            } elseif ($request->user_type === 'guest') {
                $query->whereNull('user_id');
            }
        }

        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('date_from')) {
            $query->where('search_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('search_date', '<=', $request->date_to);
        }

        if ($request->filled('successful_only')) {
            $query->where('search_successful', $request->successful_only === 'true');
        }

        return $query;
    }

    /**
     * Trasforma log per response API
     */
    private function transformLogForResponse($log)
    {
        return [
            'id' => $log->id,
            'search_date' => $log->search_date,
            'user' => $log->user ? [
                'name' => trim($log->user->name . ' ' . $log->user->surname),
                'email' => $log->user->email
            ] : null,
            'user_type' => $log->user ? 'Registrato' : 'Ospite',
            'parameters' => [
                'date_range' => $log->date_range,
                'budget' => $log->budget,
                'participants' => $log->participants,
                'port_start' => $log->port_start
            ],
            'results' => [
                'total_matches' => $log->total_matches,
                'total_alternatives' => $log->total_alternatives,
                'satisfaction_score' => $log->satisfaction_score
            ],
            'device_info' => [
                'type' => $log->device_type,
                'os' => $log->operating_system,
                'browser' => $log->browser . ($log->browser_version ? ' ' . $log->browser_version : '')
            ],
            'location' => [
                'country' => $log->country,
                'city' => $log->city,
                'isp' => $log->isp
            ],
            'performance' => [
                'duration_ms' => $log->search_duration_ms,
                'successful' => $log->search_successful,
                'error_message' => $log->error_message
            ]
        ];
    }

    /**
     * Genera stream CSV ottimizzato
     */
    private function generateCsvStream($query)
    {
        $file = fopen('php://output', 'w');
        fputs($file, "\xEF\xBB\xBF"); // BOM UTF-8

        // Header CSV
        fputcsv($file, [
            'ID', 'Data Ricerca', 'Utente', 'Tipo Utente', 'Periodo Ricerca',
            'Budget', 'Partecipanti', 'Porto Partenza', 'Risultati Trovati',
            'Alternative', 'Soddisfazione %', 'Dispositivo', 'Sistema Operativo',
            'Browser', 'Paese', 'Città', 'ISP', 'IP', 'Durata (ms)',
            'Successo', 'Messaggio Errore'
        ], ';');

        // Dati in chunks per ottimizzare memoria
        $query->chunk(500, function ($logs) use ($file) {
            foreach ($logs as $log) {
                $userName = $log->user ? 
                    trim($log->user->name . ' ' . $log->user->surname) . ' (' . $log->user->email . ')' : 
                    'Ospite';

                fputcsv($file, [
                    $log->id,
                    $log->search_date->format('d/m/Y H:i:s'),
                    $userName,
                    $log->user ? 'Registrato' : 'Ospite',
                    $log->date_range,
                    $log->budget,
                    $log->participants,
                    $log->port_start,
                    $log->total_matches,
                    $log->total_alternatives,
                    $log->satisfaction_score,
                    $log->device_type,
                    $log->operating_system,
                    $log->browser . ($log->browser_version ? ' ' . $log->browser_version : ''),
                    $log->country,
                    $log->city,
                    $log->isp,
                    $log->ip_address,
                    $log->search_duration_ms,
                    $log->search_successful ? 'Sì' : 'No',
                    $log->error_message
                ], ';');
            }
        });

        fclose($file);
    }

    /**
     * KPI principali per executive summary
     */
    private function getMainKPIs($today, $lastWeek, $lastMonth)
    {
        return [
            'total_searches' => SearchLog::count(),
            'success_rate' => $this->calculateSuccessRate(),
            'avg_satisfaction' => round(SearchLog::avg('satisfaction_score') ?? 0, 1),
            'avg_response_time' => round(SearchLog::avg('search_duration_ms') ?? 0),
            'unique_users_month' => SearchLog::where('search_date', '>=', $lastMonth)
                ->distinct('user_id')->count('user_id'),
            'mobile_usage_rate' => $this->calculateDevicePercentage('mobile')
        ];
    }

    /**
     * Trend di crescita
     */
    private function getGrowthTrends($today, $lastWeek, $lastMonth)
    {
        return [
            'daily_growth' => $this->calculateGrowthRate('day'),
            'weekly_growth' => $this->calculateGrowthRate('week'),
            'monthly_growth' => $this->calculateGrowthRate('month')
        ];
    }

    /**
     * Indicatori di performance
     */
    private function getPerformanceIndicators()
    {
        $avgDuration = SearchLog::avg('search_duration_ms') ?? 0;
        $successRate = $this->calculateSuccessRate();
        
        return [
            'response_time_status' => $avgDuration < 1500 ? 'excellent' : ($avgDuration < 3000 ? 'good' : 'poor'),
            'success_rate_status' => $successRate > 95 ? 'excellent' : ($successRate > 90 ? 'good' : 'poor'),
            'error_rate' => round((1 - $successRate / 100) * 100, 2),
            'slow_queries_percentage' => $this->getSlowQueriesPercentage()
        ];
    }

    /**
     * Top insights
     */
    private function getTopInsights()
    {
        return [
            'peak_hour' => $this->getPeakHour(),
            'top_country' => $this->getTopCountry(),
            'avg_budget' => round(SearchLog::avg('budget') ?? 0),
            'most_common_participants' => $this->getMostPopularParticipants()
        ];
    }

    /**
     * System alerts
     */
    private function getSystemAlerts()
    {
        $alerts = [];
        
        // Alert per performance
        $avgDuration = SearchLog::avg('search_duration_ms') ?? 0;
        if ($avgDuration > 3000) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Tempo di risposta medio elevato: ' . round($avgDuration) . 'ms'
            ];
        }
        
        // Alert per tasso di errore
        $errorRate = 100 - $this->calculateSuccessRate();
        if ($errorRate > 5) {
            $alerts[] = [
                'type' => 'error',
                'message' => 'Tasso di errore elevato: ' . round($errorRate, 1) . '%'
            ];
        }
    }
}