<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Departure;
use App\Models\CruiseLine;
use App\Models\PriceHistory;
use App\Models\SearchLog;
use App\Models\UserActivity;
use App\Models\UserCruiseView;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CrocieraController extends Controller
{
    public function index()
    {
        return view('crociere.create');
    }

    public function search(Request $request)
    {
        $startTime = microtime(true);

        $data = $request->validate([
            'date_range'   => 'required|string',
            'budget'       => 'required|numeric|min:100',
            'participants' => 'required|integer|min:1|max:10',
            'port_start'   => 'nullable|string',
            'port_end'     => 'nullable|string',
        ]);

        $searchResults = null;
        $errorMessage  = null;
        $user          = Auth::user();

        try {
            $dateRange = explode(' - ', $data['date_range']);
            $startDate = Carbon::createFromFormat('d/m/Y', trim($dateRange[0]));
            $endDate   = isset($dateRange[1])
                ? Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))
                : $startDate->copy();

            $budgetPerPerson = $data['budget'] / $data['participants'];
            $participants    = $data['participants'];

            // --- Risultati principali ---
            $matchesQuery = Departure::with([
                    'product.ship',
                    'product.cruiseLine',
                    'product.portFrom',
                    'product.portTo',
                ])
                ->whereNotNull('min_price')
                ->future()
                ->inDateRange(
                    $startDate->format('Y-m-d'),
                    $endDate->copy()->addDays(30)->format('Y-m-d')
                )
                ->withinBudget($budgetPerPerson)
                ->where('min_price', '>=', $budgetPerPerson * 0.5);

            if (! empty($data['port_start'])) {
                $matchesQuery->whereHas('product.portFrom', fn($q) =>
                    $q->where('name', 'LIKE', '%' . $data['port_start'] . '%')
                );
            }

            if (! empty($data['port_end'])) {
                $matchesQuery->whereHas('product.portTo', fn($q) =>
                    $q->where('name', 'LIKE', '%' . $data['port_end'] . '%')
                );
            }

            $matches = $matchesQuery
                ->orderBy('min_price')
                ->limit(10)
                ->get()
                ->map(fn($departure) => $this->enrichDepartureData($departure, $budgetPerPerson, $participants));

            Log::info('Matches trovati: ' . count($matches));

            $soddisfazioneAttuale = $this->calculateSatisfaction($matches, $data);

            // --- Alternative (budget ±20%, date flessibili) ---
            $flexStart = $startDate->copy()->subMonth();
            $flexEnd   = $endDate->copy()->addMonths(2);

            $alternativeQuery = Departure::with([
                    'product.ship',
                    'product.cruiseLine',
                    'product.portFrom',
                    'product.portTo',
                ])
                ->whereNotNull('min_price')
                ->future()
                ->withinBudget($budgetPerPerson * 1.2)
                ->where('min_price', '>=', $budgetPerPerson * 0.8)
                ->inDateRange($flexStart->format('Y-m-d'), $flexEnd->format('Y-m-d'));

            if (! empty($data['port_start'])) {
                $alternativeQuery->whereHas('product.portFrom', fn($q) =>
                    $q->where('name', 'LIKE', '%' . $data['port_start'] . '%')
                );
            }

            $alternative = $alternativeQuery
                ->orderBy('min_price')
                ->limit(10)
                ->get()
                ->map(fn($departure) => $this->enrichDepartureData($departure, $budgetPerPerson, $participants, true));

            Log::info('Alternative trovate: ' . count($alternative));

            $soddisfazioneOttimale = $this->calculateOptimalSatisfaction($alternative, $data);
            $consigli              = $this->generateSuggestions($matches, $alternative, $data);
            $statistiche           = $this->getSearchStatistics($data, $matches, $alternative);

            $searchResults = [
                'success'                => true,
                'soddisfazione_attuale'  => $soddisfazioneAttuale,
                'soddisfazione_ottimale' => $soddisfazioneOttimale,
                'matches'                => $matches,
                'alternative'            => $alternative,
                'consigli'               => $consigli,
                'suggerimento_ottimale'  => $this->getOptimalSuggestion($soddisfazioneAttuale, $soddisfazioneOttimale),
                'statistiche'            => $statistiche,
            ];

        } catch (\Exception $e) {
            Log::error('Errore ricerca crociere: ' . $e->getMessage(), [
                'data'  => $data,
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage  = 'Si è verificato un errore durante la ricerca. Riprova più tardi.';
            $searchResults = [
                'success'                => false,
                'error'                  => $errorMessage,
                'soddisfazione_attuale'  => 0,
                'soddisfazione_ottimale' => 0,
                'matches'                => [],
                'alternative'            => [],
                'consigli'               => ['Si è verificato un errore. Controlla i parametri di ricerca.'],
                'suggerimento_ottimale'  => 'Riprova la ricerca',
            ];
        }

        $searchDuration = round((microtime(true) - $startTime) * 1000);

        try {
            SearchLog::createFromRequest($data, $searchResults, $searchDuration, $errorMessage);
            if (Auth::check()) {
                Cache::forget("dashboard_user_{$user->id}");
            }
        } catch (\Exception $e) {
            Log::warning('Errore logging ricerca: ' . $e->getMessage());
        }

        return $errorMessage
            ? response()->json($searchResults, 500)
            : response()->json($searchResults);
    }

    // -------------------------------------------------------------------------
    // Dettaglio singola partenza (utenti autenticati)
    // -------------------------------------------------------------------------

    public function show($id)
    {
        try {
            $departure = Departure::with([
                'product.ship.cabinImages',
                'product.ship.categories',
                'product.cruiseLine',
                'product.portFrom',
                'product.portTo',
                'product.itinerary.port',
                'product.area',
                'latestPrices',
            ])->findOrFail($id);

            if (Auth::check()) {
                try {
                    UserCruiseView::recordView(Auth::id(), $departure->id);
                    UserActivity::log(Auth::id(), 'view', $departure, [
                        'cruise_name' => $departure->product->cruise_name,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Errore logging attività: ' . $e->getMessage());
                }
            }

            // Risposta JSON per chiamate AJAX legacy
            if (request()->wantsJson()) {
                return response()->json([
                    'success'   => true,
                    'departure' => $departure,
                ]);
            }

            $ship = $departure->product->ship;

            // Merge prezzi + categorie + immagini per category_code
            $cabins = $departure->latestPrices
                ->groupBy('category_code')
                ->map(function ($prices, $categoryCode) use ($ship) {
                    $category = $ship->categories->firstWhere('cruisehost_cat', $categoryCode);
                    $image    = $ship->cabinImages->firstWhere('category_code', $categoryCode);

                    return [
                        'category_code' => $categoryCode,
                        'price'         => (float) $prices->first()->price,
                        'description'   => $category->description ?? null,
                        'cl_cat'        => $category->cl_cat ?? $categoryCode,
                        'image_url'     => $image->image_url ?? null,
                        'recorded_at'   => $prices->first()->recorded_at,
                    ];
                })
                ->sortBy('price')
                ->values();

            $isFavorite = Auth::check()
                ? UserFavorite::isFavorite(Auth::id(), $departure->id)
                : false;

            return view('crociere.show', compact('departure', 'cabins', 'isFavorite'));

        } catch (\Exception $e) {
            Log::error('Errore recupero dettagli partenza: ' . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Crociera non trovata'], 404);
            }

            abort(404, 'Crociera non trovata');
        }
    }

    // -------------------------------------------------------------------------
    // Statistiche globali
    // -------------------------------------------------------------------------

    public function searchPorts(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $ports = \App\Models\Port::where('name', 'LIKE', '%' . $q . '%')
            ->orderBy('name')
            ->limit(10)
            ->pluck('name');

        return response()->json($ports);
    }

    public function getStats()
    {
        try {
            $stats = Cache::remember('crociere_stats', 600, function () {
                return [
                    'total_cruises'     => Departure::count(),
                    'available_cruises' => Departure::future()->whereNotNull('min_price')->count(),
                    'companies'         => CruiseLine::where('is_online', true)->count(),
                    'companies_list'    => CruiseLine::where('is_online', true)
                        ->orderBy('name')
                        ->pluck('name'),
                    'avg_price'         => Departure::future()->whereNotNull('min_price')->avg('min_price'),
                    'min_price'         => Departure::future()->whereNotNull('min_price')->min('min_price'),
                    'max_price'         => Departure::future()->whereNotNull('min_price')->max('min_price'),
                ];
            });

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Errore statistiche: ' . $e->getMessage());
            return response()->json(['error' => 'Impossibile recuperare le statistiche'], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers privati
    // -------------------------------------------------------------------------

    private function enrichDepartureData(Departure $departure, float $budgetPerPerson, int $participants, bool $isAlternative = false): array
    {
        $product         = $departure->product;
        $pricePerPerson  = (float) $departure->min_price;
        $totalPrice      = $pricePerPerson * $participants;
        $nights          = $departure->duration ?: 1;

        $dailyCostPerPerson = $nights > 0 ? round($pricePerPerson / $nights, 2) : 0;
        $dailyCostTotal     = $dailyCostPerPerson * $participants;

        $enriched = [
            'id'                        => $departure->id,
            'ship'                      => $product->ship->name ?? 'N/D',
            'line'                      => $product->cruiseLine->name ?? 'N/D',
            'night'                     => $nights,
            'interior'                  => $pricePerPerson,
            'partenza'                  => $departure->dep_date->format('Y-m-d'),
            'arrivo'                    => $departure->arr_date->format('Y-m-d'),
            'cruise'                    => $product->cruise_name ?? 'N/D',
            'from'                      => $product->portFrom->name ?? 'N/D',
            'to'                        => $product->portTo->name ?? 'N/D',
            'details'                   => '',
            'prezzo_totale'             => $totalPrice,
            'prezzo_persona'            => $pricePerPerson,
            'costo_giornaliero_persona' => $dailyCostPerPerson,
            'costo_giornaliero_totale'  => $dailyCostTotal,
            'match_percentage'          => $this->calculateMatchPercentage($departure, $budgetPerPerson),
            'formatted_price'           => '€' . number_format($pricePerPerson, 0, ',', '.'),
            'formatted_total'           => '€' . number_format($totalPrice, 0, ',', '.'),
            'formatted_daily_cost'      => '€' . number_format($dailyCostTotal, 0, ',', '.'),
            'formatted_daily_per_person'=> '€' . number_format($dailyCostPerPerson, 0, ',', '.'),
            'savings'                   => $this->calculateSavings($pricePerPerson, $budgetPerPerson),
            'quality_score'             => $this->calculateQualityScore($departure),
            'value_rating'              => $this->calculateValueRating($pricePerPerson, $budgetPerPerson, $nights),
        ];

        if ($isAlternative) {
            $enriched['benefit']               = $this->calculateBenefit($departure, $budgetPerPerson);
            $enriched['recommendation_reason'] = $this->getRecommendationReason($departure, $budgetPerPerson);
        }

        return $enriched;
    }

    private function calculateMatchPercentage(Departure $departure, float $budgetPerPerson): int
    {
        $price      = (float) $departure->min_price;
        $priceRatio = $price / $budgetPerPerson;

        if ($priceRatio <= 0.7) {
            $baseScore = mt_rand(88, 95);
        } elseif ($priceRatio <= 0.85) {
            $baseScore = mt_rand(78, 87);
        } elseif ($priceRatio <= 0.95) {
            $baseScore = mt_rand(68, 77);
        } else {
            $baseScore = mt_rand(55, 67);
        }

        $qualityBonus = 0;
        if ($departure->duration >= 7) $qualityBonus += 3;

        $lineName = strtolower($departure->product->cruiseLine->name ?? '');
        if (in_array($lineName, ['royal caribbean', 'norwegian cruise line', 'celebrity cruises'])) {
            $qualityBonus += 5;
        }

        return min($baseScore + $qualityBonus, 100);
    }

    private function calculateBenefit(Departure $departure, float $budgetPerPerson): string
    {
        $price    = (float) $departure->min_price;
        $benefits = [];

        if ($price < $budgetPerPerson * 0.8) {
            $savings    = round((1 - $price / $budgetPerPerson) * 100);
            $benefits[] = "Risparmio {$savings}%";
        }

        if ($departure->duration >= 14) {
            $benefits[] = 'Crociera lunga';
        } elseif ($departure->duration >= 7) {
            $benefits[] = 'Durata ottimale';
        }

        $premiumLines = ['royal caribbean', 'norwegian cruise line', 'celebrity cruises', 'princess cruises'];
        if (in_array(strtolower($departure->product->cruiseLine->name ?? ''), $premiumLines)) {
            $benefits[] = 'Compagnia premium';
        }

        $daysUntil = Carbon::now()->diffInDays($departure->dep_date, false);
        if ($daysUntil >= 60 && $daysUntil <= 120) {
            $benefits[] = 'Anticipo ottimale';
        }

        return empty($benefits) ? 'Buona opzione' : implode(', ', array_slice($benefits, 0, 2));
    }

    private function calculateSavings(float $price, float $budgetPerPerson): int
    {
        if ($price >= $budgetPerPerson) return 0;
        return round((1 - $price / $budgetPerPerson) * 100);
    }

    private function calculateQualityScore(Departure $departure): int
    {
        $score = 60;

        $premiumLines = ['royal caribbean', 'norwegian cruise line', 'celebrity cruises', 'princess cruises'];
        if (in_array(strtolower($departure->product->cruiseLine->name ?? ''), $premiumLines)) {
            $score += 15;
        }

        if ($departure->duration >= 7)  $score += 10;
        if ($departure->duration >= 14) $score += 5;

        return min($score, 100);
    }

    private function getRecommendationReason(Departure $departure, float $budgetPerPerson): string
    {
        $price   = (float) $departure->min_price;
        $reasons = [];

        if ($price < $budgetPerPerson * 0.8) {
            $reasons[] = 'Prezzo molto conveniente';
        }

        if ($departure->duration >= 10) {
            $reasons[] = 'Crociera lunga';
        }

        $premiumLines = ['royal caribbean', 'norwegian cruise line', 'celebrity cruises'];
        if (in_array(strtolower($departure->product->cruiseLine->name ?? ''), $premiumLines)) {
            $reasons[] = 'Compagnia di alta qualità';
        }

        return empty($reasons) ? 'Opzione interessante' : implode(' • ', array_slice($reasons, 0, 2));
    }

    private function calculateValueRating(float $price, float $budgetPerPerson, int $nights): int
    {
        $score      = 50;
        $priceRatio = $price / $budgetPerPerson;

        if ($priceRatio <= 0.7)       $score += 30;
        elseif ($priceRatio <= 0.85)  $score += 20;
        elseif ($priceRatio <= 0.95)  $score += 10;

        if ($nights >= 7 && $nights <= 14) $score += 15;
        elseif ($nights > 14)              $score += 10;
        elseif ($nights >= 5)              $score += 5;

        $dailyCost         = $nights > 0 ? $price / $nights : $price;
        $expectedDailyCost = $budgetPerPerson / 7;

        if ($dailyCost < $expectedDailyCost * 0.8) $score += 10;
        elseif ($dailyCost < $expectedDailyCost)   $score += 5;

        return min($score, 100);
    }

    private function calculateSatisfaction($matches, array $searchParams): float
    {
        $matchCount = count($matches);
        if ($matchCount === 0) return 0;

        $baseScore        = 30;
        $budgetPerPerson  = $searchParams['budget'] / $searchParams['participants'];

        if ($matchCount >= 10)     $countBonus = 25;
        elseif ($matchCount >= 5)  $countBonus = 20;
        elseif ($matchCount >= 3)  $countBonus = 15;
        elseif ($matchCount >= 2)  $countBonus = 10;
        else                       $countBonus = 5;

        $budgetBonus = 0;
        foreach ($matches as $match) {
            $price = $match['prezzo_persona'];
            if ($price <= $budgetPerPerson * 0.6)      $budgetBonus += 5;
            elseif ($price <= $budgetPerPerson * 0.8)  $budgetBonus += 3;
            elseif ($price <= $budgetPerPerson * 0.95) $budgetBonus += 1;
        }
        $budgetBonus = min($budgetBonus, 25);

        $avgQuality   = collect($matches)->avg('quality_score') ?? 60;
        $qualityBonus = max(0, ($avgQuality - 60) / 40 * 15);

        $filterBonus = 0;
        if (! empty($searchParams['port_start'])) $filterBonus += 2;
        if (! empty($searchParams['port_end']))   $filterBonus += 3;

        $overBudgetCount = collect($matches)->filter(fn($m) => $m['prezzo_persona'] > $budgetPerPerson)->count();
        $overBudgetPenalty = ($overBudgetCount > $matchCount * 0.5) ? 10 : 0;

        return max(0, min($baseScore + $countBonus + $budgetBonus + $qualityBonus + $filterBonus - $overBudgetPenalty, 100));
    }

    private function calculateOptimalSatisfaction($alternatives, array $searchParams): float
    {
        $alternativeCount = count($alternatives);
        if ($alternativeCount === 0) return 15;

        $baseScore       = 50;
        $budgetPerPerson = $searchParams['budget'] / $searchParams['participants'];

        if ($alternativeCount >= 15)    $varietyBonus = 25;
        elseif ($alternativeCount >= 10) $varietyBonus = 20;
        elseif ($alternativeCount >= 7)  $varietyBonus = 15;
        elseif ($alternativeCount >= 5)  $varietyBonus = 12;
        elseif ($alternativeCount >= 3)  $varietyBonus = 8;
        else                             $varietyBonus = 5;

        $companyCount = collect($alternatives)->pluck('line')->unique()->count();
        if ($companyCount >= 6)     $diversityBonus = 15;
        elseif ($companyCount >= 4) $diversityBonus = 12;
        elseif ($companyCount >= 3) $diversityBonus = 8;
        elseif ($companyCount >= 2) $diversityBonus = 5;
        else                        $diversityBonus = 2;

        $avgQuality   = collect($alternatives)->avg('quality_score') ?? 60;
        $qualityBonus = max(0, min(($avgQuality - 60) / 40 * 10, 10));

        $betterOptionsCount = collect($alternatives)->filter(fn($a) =>
            ($a['prezzo_persona'] <= $budgetPerPerson * 0.9 && ($a['quality_score'] ?? 60) > 75) ||
            ($a['prezzo_persona'] <= $budgetPerPerson * 1.1 && ($a['night'] ?? 7) > 10)
        )->count();

        if ($betterOptionsCount >= 5)     $budgetFlexibilityBonus = 10;
        elseif ($betterOptionsCount >= 3) $budgetFlexibilityBonus = 7;
        elseif ($betterOptionsCount >= 1) $budgetFlexibilityBonus = 4;
        else                              $budgetFlexibilityBonus = 0;

        $prices = collect($alternatives)->pluck('prezzo_persona')->filter();
        $priceRangeBonus = 0;
        if ($prices->count() >= 3) {
            $priceRange = $prices->max() - $prices->min();
            if ($priceRange > $budgetPerPerson * 0.5)      $priceRangeBonus = 5;
            elseif ($priceRange > $budgetPerPerson * 0.3)  $priceRangeBonus = 3;
            elseif ($priceRange > $budgetPerPerson * 0.1)  $priceRangeBonus = 2;
        }

        return min($baseScore + $varietyBonus + $diversityBonus + $qualityBonus + $budgetFlexibilityBonus + $priceRangeBonus, 100);
    }

    private function generateSuggestions($matches, $alternatives, array $searchParams): array
    {
        $suggestions     = [];
        $matchCount      = count($matches);
        $budgetPerPerson = $searchParams['budget'] / $searchParams['participants'];

        if ($matchCount === 0) {
            $suggestions[] = "🔍 Nessuna crociera trovata con i parametri attuali. Prova ad espandere le date o aumentare il budget.";
        } elseif ($matchCount <= 2) {
            $suggestions[] = "🔍 Poche opzioni disponibili. Considera date più flessibili per vedere più offerte.";
        } elseif ($matchCount >= 8) {
            $suggestions[] = "🎯 Ottima selezione! Hai molte opzioni tra cui scegliere.";
        }

        if ($matchCount > 0) {
            $avgPrice = collect($matches)->avg('prezzo_persona');
            $minPrice = collect($matches)->min('prezzo_persona');
            if ($avgPrice < $budgetPerPerson * 0.7) {
                $suggestions[] = "💰 Il tuo budget ti permette crociere premium! Considera upgrade di cabina.";
            } elseif ($minPrice < $budgetPerPerson * 0.6) {
                $suggestions[] = "💡 Abbiamo trovato alcune offerte eccezionali sotto budget.";
            }
        }

        if (! empty($searchParams['port_start']) && ! empty($searchParams['port_end'])) {
            $suggestions[] = "🚢 Specificando entrambi i porti limiti le opzioni. Prova a rimuovere il porto di destinazione.";
        }

        $dateRange = $searchParams['date_range'];
        if (preg_match('/\/(07|08)\//', $dateRange)) {
            $suggestions[] = "☀️ Estate = alta stagione. Considera giugno o settembre per prezzi migliori.";
        } elseif (preg_match('/\/(12|01|02)\//', $dateRange)) {
            $suggestions[] = "❄️ Ottimo periodo per Caraibi e destinazioni calde!";
        }

        if (count($alternatives) > $matchCount + 3) {
            $suggestions[] = "🔄 Molte più opzioni disponibili con parametri leggermente diversi.";
        }

        return array_slice($suggestions, 0, 4);
    }

    private function getOptimalSuggestion(float $currentSatisfaction, float $optimalSatisfaction): string
    {
        $difference = $optimalSatisfaction - $currentSatisfaction;

        if ($difference <= 5)       return '🎯 Ricerca già ottimizzata perfettamente!';
        elseif ($difference <= 15)  return '✨ Piccoli aggiustamenti per risultati migliori';
        elseif ($difference <= 30)  return '📅 Espandi le date per il +' . round($difference) . '% di soddisfazione';
        else                        return '🔧 Modifica i parametri per sbloccare +' . round($difference) . '% opzioni';
    }

    private function getSearchStatistics(array $searchParams, $matches, $alternatives): array
    {
        $budgetPerPerson = $searchParams['budget'] / $searchParams['participants'];

        $bestValue = collect($matches)->sortByDesc('value_rating')->first();

        return [
            'budget_per_persona'          => $budgetPerPerson,
            'budget_totale'               => $searchParams['budget'],
            'partecipanti'                => $searchParams['participants'],
            'periodo_ricerca'             => $searchParams['date_range'],
            'risultati_trovati'           => count($matches),
            'alternative_disponibili'     => count($alternatives),
            'risparmio_medio'             => $matches ? collect($matches)->avg('savings') : 0,
            'prezzo_medio_trovato'        => $matches ? collect($matches)->avg('prezzo_persona') : 0,
            'compagnie_diverse'           => collect($matches)->pluck('line')->unique()->count(),
            'durata_media'                => $matches ? collect($matches)->avg('night') : 0,
            'costo_giornaliero_medio'     => $matches ? collect($matches)->avg('costo_giornaliero_persona') : 0,
            'value_rating_medio'          => $matches ? collect($matches)->avg('value_rating') : 0,
            'miglior_rapporto_qualita_prezzo' => $bestValue ? [
                'ship'            => $bestValue['ship'],
                'line'            => $bestValue['line'],
                'value_rating'    => $bestValue['value_rating'],
                'costo_giornaliero' => $bestValue['formatted_daily_per_person'],
            ] : null,
        ];
    }
}
