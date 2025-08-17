<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cruise;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CrocieraController extends Controller
{
    public function search(Request $request)
    {
        $data = $request->validate([
            'date_range' => 'required|string',
            'budget' => 'required|string',
            'participants' => 'required|integer|min:1|max:10',
            'port_start' => 'nullable|string',
            'port_end' => 'nullable|string',
        ]);

        try {
            // Parse delle date
            $dateRange = explode(' - ', $data['date_range']);
            $startDate = Carbon::createFromFormat('d/m/Y', trim($dateRange[0]));
            $endDate = isset($dateRange[1]) ? 
                Carbon::createFromFormat('d/m/Y', trim($dateRange[1])) : 
                $startDate->copy();

            // Normalizza il budget
            $normalizedBudget = $this->normalizeBudget($data['budget']);
            $budgetPerPerson = $normalizedBudget / $data['participants'];
            $participants = $data['participants'];

            Log::info('Budget Search Debug', [
                'original_budget' => $data['budget'],
                'normalized_budget' => $normalizedBudget,
                'participants' => $participants,
                'budget_per_person' => $budgetPerPerson
            ]);

            // DEBUG: Controlla i tipi di dato nel database
            $samplePrices = DB::table('cruises')
                ->select('interior')
                ->whereNotNull('interior')
                ->where('interior', '!=', '')
                ->limit(5)
                ->get();

            Log::info('Sample Prices Debug', [
                'sample_prices' => $samplePrices->toArray(),
                'budget_per_person_type' => gettype($budgetPerPerson),
                'budget_per_person_value' => $budgetPerPerson
            ]);

            // CORREZIONE: Usa CAST per forzare il confronto numerico
            $matchesQuery = Cruise::available()
                ->future()
                ->whereNotNull('interior')
                ->where('interior', '!=', '')
                ->where('interior', '>', 0)
                ->whereRaw('CAST(interior AS DECIMAL(10,2)) <= ?', [$budgetPerPerson]);

            // DEBUG: Controlla la query SQL generata
            $sqlQuery = $matchesQuery->toSql();
            $bindings = $matchesQuery->getBindings();
            Log::info('SQL Query Debug', [
                'sql' => $sqlQuery,
                'bindings' => $bindings
            ]);

            // Applica filtri per date se disponibili
            if ($startDate && $endDate) {
                $matchesQuery->whereBetween('partenza', [
                    $startDate->format('Y-m-d'),
                    $endDate->copy()->addDays(30)->format('Y-m-d')
                ]);
            }

            // Applica filtri opzionali per porti
            if (!empty($data['port_start'])) {
                $matchesQuery->where(function($q) use ($data) {
                    $q->where('from', 'LIKE', '%' . $data['port_start'] . '%')
                      ->orWhere('partenza', 'LIKE', '%' . $data['port_start'] . '%');
                });
            }

            if (!empty($data['port_end'])) {
                $matchesQuery->where(function($q) use ($data) {
                    $q->where('to', 'LIKE', '%' . $data['port_end'] . '%')
                      ->orWhere('arrivo', 'LIKE', '%' . $data['port_end'] . '%');
                });
            }

            // DEBUG: Conta DOPO il filtro budget
            $budgetFilteredCount = $matchesQuery->count();
            
            // DEBUG: Prendi qualche esempio per verificare
            $sampleResults = $matchesQuery->limit(3)->get(['interior', 'ship']);
            
            Log::info('Budget Filter Results Debug', [
                'cruises_within_budget' => $budgetFilteredCount,
                'budget_per_person' => $budgetPerPerson,
                'sample_results' => $sampleResults->map(function($cruise) {
                    return [
                        'ship' => $cruise->ship,
                        'interior' => $cruise->interior,
                        'interior_type' => gettype($cruise->interior),
                        'interior_as_float' => (float)$cruise->interior
                    ];
                })->toArray()
            ]);

            $matches = $matchesQuery
                ->orderByRaw('CAST(interior AS DECIMAL(10,2)) ASC')
                ->take(10)
                ->get()
                ->map(function($cruise) use ($budgetPerPerson, $participants) {
                    return $this->enrichCruiseData($cruise, $budgetPerPerson, $participants);
                });

            // Se ancora non ci sono risultati nel budget reale, cerca alternative
            if ($matches->isEmpty() || collect($matches)->every(function($match) { return $match['is_over_budget']; })) {
                Log::info('No valid matches found within budget, searching alternatives');
                
                // Alternative con budget fino a 2x
                $alternativeQuery = Cruise::available()
                    ->future()
                    ->whereNotNull('interior')
                    ->where('interior', '!=', '')
                    ->where('interior', '>', 0)
                    ->whereRaw('CAST(interior AS DECIMAL(10,2)) <= ?', [$budgetPerPerson * 2]);

                if ($startDate && $endDate) {
                    $alternativeQuery->whereBetween('partenza', [
                        $startDate->format('Y-m-d'),
                        $endDate->copy()->addDays(60)->format('Y-m-d')
                    ]);
                }

                if (!empty($data['port_start'])) {
                    $alternativeQuery->where(function($q) use ($data) {
                        $q->where('from', 'LIKE', '%' . $data['port_start'] . '%')
                          ->orWhere('partenza', 'LIKE', '%' . $data['port_start'] . '%');
                    });
                }

                $matches = $alternativeQuery
                    ->orderByRaw('CAST(interior AS DECIMAL(10,2)) ASC')
                    ->take(10)
                    ->get()
                    ->map(function($cruise) use ($budgetPerPerson, $participants) {
                        return $this->enrichCruiseData($cruise, $budgetPerPerson, $participants);
                    });
            }

            // Ricerca alternative standard
            $alternativeQuery = Cruise::available()
                ->future()
                ->whereNotNull('interior')
                ->where('interior', '!=', '')
                ->where('interior', '>', 0)
                ->whereRaw('CAST(interior AS DECIMAL(10,2)) <= ?', [$budgetPerPerson * 1.5]);

            if (!empty($data['port_start'])) {
                $alternativeQuery->where(function($q) use ($data) {
                    $q->where('from', 'LIKE', '%' . $data['port_start'] . '%')
                      ->orWhere('partenza', 'LIKE', '%' . $data['port_start'] . '%');
                });
            }

            if ($startDate) {
                $flexibleStart = $startDate->copy()->subMonths(2);
                $flexibleEnd = $endDate->copy()->addMonths(4);
                
                $alternativeQuery->whereBetween('partenza', [
                    $flexibleStart->format('Y-m-d'),
                    $flexibleEnd->format('Y-m-d')
                ]);
            }

            $alternative = $alternativeQuery
                ->orderBy('line', 'ASC')
                ->orderByRaw('CAST(interior AS DECIMAL(10,2)) ASC')
                ->take(10)
                ->get()
                ->map(function($cruise) use ($budgetPerPerson, $participants) {
                    return $this->enrichCruiseData($cruise, $budgetPerPerson, $participants, true);
                });

            // Calcola soddisfazione
            $soddisfazioneAttuale = $this->calculateSatisfaction($matches, $data);
            $soddisfazioneOttimale = $this->calculateOptimalSatisfaction($alternative, $data);

            // Genera consigli e statistiche
            $consigli = $this->generateSuggestions($matches, $alternative, $data);
            $statistiche = $this->getSearchStatistics($data, $matches, $alternative);

            // DEBUG: Log finale
            Log::info('Final Search Results Debug', [
                'matches_count' => $matches->count(),
                'first_match_price' => $matches->first()['prezzo_persona'] ?? 'N/A',
                'within_budget_count' => collect($matches)->where('is_over_budget', false)->count(),
                'over_budget_count' => collect($matches)->where('is_over_budget', true)->count(),
                'soddisfazione_attuale' => $soddisfazioneAttuale
            ]);

            return response()->json([
                'success' => true,
                'soddisfazione_attuale' => $soddisfazioneAttuale,
                'soddisfazione_ottimale' => $soddisfazioneOttimale,
                'matches' => $matches,
                'alternative' => $alternative,
                'consigli' => $consigli,
                'suggerimento_ottimale' => $this->getOptimalSuggestion($soddisfazioneAttuale, $soddisfazioneOttimale),
                'statistiche' => $statistiche
            ]);

        } catch (\Exception $e) {
            Log::error('Errore ricerca crociere: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Si è verificato un errore durante la ricerca. Riprova più tardi.',
                'soddisfazione_attuale' => 0,
                'soddisfazione_ottimale' => 0,
                'matches' => [],
                'alternative' => [],
                'consigli' => ['Si è verificato un errore. Controlla i parametri di ricerca.'],
                'suggerimento_ottimale' => 'Riprova la ricerca'
            ], 500);
        }
    }

    /**
     * Normalizza il budget convertendo formati diversi in un numero float
     */
    private function normalizeBudget($budget)
    {
        if (is_numeric($budget)) {
            return (float) $budget;
        }

        $cleaned = preg_replace('/[€$£\s]/', '', $budget);
        
        if (preg_match('/^(\d{1,3}(?:\.\d{3})*),(\d{1,2})$/', $cleaned, $matches)) {
            $integerPart = str_replace('.', '', $matches[1]);
            $decimalPart = $matches[2];
            return (float) ($integerPart . '.' . $decimalPart);
        } elseif (preg_match('/^(\d{1,3}(?:,\d{3})*).(\d{1,2})$/', $cleaned, $matches)) {
            $integerPart = str_replace(',', '', $matches[1]);
            $decimalPart = $matches[2];
            return (float) ($integerPart . '.' . $decimalPart);
        } elseif (strpos($cleaned, ',') !== false && strpos($cleaned, '.') === false) {
            $cleaned = str_replace(',', '.', $cleaned);
            return (float) $cleaned;
        } elseif (strpos($cleaned, '.') !== false && strpos($cleaned, ',') === false) {
            if (preg_match('/\.\d{1,2}$/', $cleaned)) {
                return (float) $cleaned;
            } else {
                $cleaned = str_replace('.', '', $cleaned);
                return (float) $cleaned;
            }
        } else {
            return (float) $cleaned;
        }
    }

    private function enrichCruiseData($cruise, $budgetPerPerson, $participants, $isAlternative = false)
    {
        // CORREZIONE: Forza conversione a float per evitare problemi di tipo
        $pricePerPerson = (float) str_replace(',', '.', $cruise->interior);
        $totalPrice = $pricePerPerson * $participants;
        $nights = $cruise->night ?: $cruise->duration ?: 1;
        
        $dailyCostPerPerson = $nights > 0 ? round($pricePerPerson / $nights, 2) : 0;
        $dailyCostTotal = $dailyCostPerPerson * $participants;
        
        // Verifica REALE se è oltre budget
        $isOverBudget = $pricePerPerson > $budgetPerPerson;
        $budgetExcess = $isOverBudget ? $pricePerPerson - $budgetPerPerson : 0;
        $budgetExcessPercentage = $budgetPerPerson > 0 ? round(($budgetExcess / $budgetPerPerson) * 100, 1) : 0;
        
        $enriched = [
            'id' => $cruise->id,
            'ship' => $cruise->ship ?? 'N/D',
            'line' => $cruise->line ?? 'N/D',
            'night' => $nights,
            'interior' => $pricePerPerson, // Usa il valore convertito
            'partenza' => $cruise->partenza ? $cruise->partenza->format('Y-m-d') : 'N/D',
            'arrivo' => $cruise->arrivo ? $cruise->arrivo->format('Y-m-d') : 'N/D',
            'cruise' => $cruise->cruise ?? 'N/D',
            'from' => $cruise->from ?? 'N/D',
            'to' => $cruise->to ?? 'N/D',
            'details' => $cruise->details ?? '',
            'prezzo_totale' => $totalPrice,
            'prezzo_persona' => $pricePerPerson,
            'costo_giornaliero_persona' => $dailyCostPerPerson,
            'costo_giornaliero_totale' => $dailyCostTotal,
            'match_percentage' => $this->calculateMatchPercentage($cruise, $budgetPerPerson, $pricePerPerson),
            'formatted_price' => '€' . number_format($pricePerPerson, 0, ',', '.'),
            'formatted_total' => '€' . number_format($totalPrice, 0, ',', '.'),
            'formatted_daily_cost' => '€' . number_format($dailyCostTotal, 0, ',', '.'),
            'formatted_daily_per_person' => '€' . number_format($dailyCostPerPerson, 0, ',', '.'),
            'savings' => $this->calculateSavings($pricePerPerson, $budgetPerPerson),
            'quality_score' => $this->calculateQualityScore($cruise),
            'value_rating' => $this->calculateValueRating($pricePerPerson, $budgetPerPerson, $nights),
            'is_over_budget' => $isOverBudget,
            'budget_excess' => $budgetExcess,
            'budget_excess_percentage' => $budgetExcessPercentage,
            'budget_excess_formatted' => $isOverBudget ? '+€' . number_format($budgetExcess, 0, ',', '.') : null
        ];

        if ($isAlternative) {
            $enriched['benefit'] = $this->calculateBenefit($cruise, $budgetPerPerson, $pricePerPerson);
            $enriched['recommendation_reason'] = $this->getRecommendationReason($cruise, $budgetPerPerson, $pricePerPerson);
        }

        return $enriched;
    }

    private function calculateMatchPercentage($cruise, $budgetPerPerson, $pricePerPerson = null)
    {
        $cruisePrice = $pricePerPerson ?? (float) str_replace(',', '.', $cruise->interior);
        $priceRatio = $cruisePrice / $budgetPerPerson;
        
        if ($priceRatio <= 0.7) {
            $baseScore = mt_rand(88, 95);
        } elseif ($priceRatio <= 0.85) {
            $baseScore = mt_rand(78, 87);
        } elseif ($priceRatio <= 0.95) {
            $baseScore = mt_rand(68, 77);
        } elseif ($priceRatio <= 1.0) {
            $baseScore = mt_rand(55, 67);
        } else {
            $baseScore = mt_rand(20, 40);
        }

        $qualityBonus = 0;
        if (!empty($cruise->details)) $qualityBonus += 2;
        if ($cruise->night >= 7) $qualityBonus += 3;
        if (in_array(strtolower($cruise->line), ['royal caribbean', 'norwegian', 'celebrity'])) {
            $qualityBonus += 5;
        }

        return min($baseScore + $qualityBonus, 100);
    }

    private function calculateBenefit($cruise, $budgetPerPerson, $pricePerPerson = null)
    {
        $cruisePrice = $pricePerPerson ?? (float) str_replace(',', '.', $cruise->interior);
        $benefits = [];

        if ($cruisePrice <= $budgetPerPerson) {
            if ($cruisePrice < $budgetPerPerson * 0.8) {
                $savings = round((1 - $cruisePrice / $budgetPerPerson) * 100);
                $benefits[] = "Risparmio {$savings}%";
            }
        } else {
            $excess = round((($cruisePrice - $budgetPerPerson) / $budgetPerPerson) * 100);
            $benefits[] = "Oltre budget +{$excess}%";
        }

        if (!empty($cruise->night) && is_numeric($cruise->night)) {
            if ($cruise->night >= 14) {
                $benefits[] = 'Crociera lunga';
            } elseif ($cruise->night >= 7) {
                $benefits[] = 'Durata ottimale';
            }
        }

        $premiumLines = ['royal caribbean', 'norwegian', 'celebrity', 'princess'];
        if (in_array(strtolower($cruise->line), $premiumLines)) {
            $benefits[] = 'Compagnia premium';
        }

        return empty($benefits) ? 'Opzione interessante' : implode(', ', array_slice($benefits, 0, 2));
    }

    private function getRecommendationReason($cruise, $budgetPerPerson, $pricePerPerson = null)
    {
        $cruisePrice = $pricePerPerson ?? (float) str_replace(',', '.', $cruise->interior);
        $reasons = [];

        if ($cruisePrice <= $budgetPerPerson * 0.8) {
            $reasons[] = 'Prezzo molto conveniente';
        } elseif ($cruisePrice > $budgetPerPerson) {
            $excess = round((($cruisePrice - $budgetPerPerson) / $budgetPerPerson) * 100);
            $reasons[] = "Oltre budget +{$excess}%";
        }

        if ($cruise->night >= 10) {
            $reasons[] = 'Crociera lunga';
        }

        $premiumLines = ['royal caribbean', 'norwegian', 'celebrity'];
        if (in_array(strtolower($cruise->line), $premiumLines)) {
            $reasons[] = 'Compagnia di alta qualità';
        }

        return empty($reasons) ? 'Opzione interessante' : implode(' • ', array_slice($reasons, 0, 2));
    }

    // Altri metodi rimangono uguali...
    private function calculateSatisfaction($matches, $searchParams)
    {
        $baseScore = 25;
        $matchCount = count($matches);

        if ($matchCount === 0) {
            return $baseScore;
        }

        $normalizedBudget = $this->normalizeBudget($searchParams['budget']);
        $budgetPerPerson = $normalizedBudget / $searchParams['participants'];
        
        $withinBudgetCount = collect($matches)->where('is_over_budget', false)->count();
        $countBonus = min($withinBudgetCount * 8, 40); // Bonus maggiore per risultati nel budget

        $overBudgetCount = collect($matches)->where('is_over_budget', true)->count();
        $overBudgetPenalty = $overBudgetCount * 5; // Penalità per risultati oltre budget

        $budgetBonus = 0;
        foreach ($matches as $match) {
            if (!$match['is_over_budget']) {
                $price = $match['prezzo_persona'];
                if ($price <= $budgetPerPerson * 0.6) {
                    $budgetBonus += 8;
                } elseif ($price <= $budgetPerPerson * 0.8) {
                    $budgetBonus += 5;
                } elseif ($price <= $budgetPerPerson * 0.95) {
                    $budgetBonus += 2;
                }
            }
        }
        $budgetBonus = min($budgetBonus, 25);

        $avgQuality = collect($matches)->avg('quality_score') ?? 60;
        $qualityBonus = max(0, ($avgQuality - 60) / 40 * 10);

        $filterBonus = 0;
        if (!empty($searchParams['port_start'])) $filterBonus += 2;
        if (!empty($searchParams['port_end'])) $filterBonus += 3;

        $finalScore = $baseScore + $countBonus + $budgetBonus + $qualityBonus + $filterBonus - $overBudgetPenalty;
        
        return max(min($finalScore, 100), 0);
    }

    private function calculateSavings($cruisePrice, $budgetPerPerson)
    {
        if ($cruisePrice >= $budgetPerPerson) return 0;
        return round((1 - $cruisePrice / $budgetPerPerson) * 100);
    }

    private function calculateQualityScore($cruise)
    {
        $score = 60;
        $premiumLines = ['royal caribbean', 'norwegian', 'celebrity', 'princess'];
        if (in_array(strtolower($cruise->line), $premiumLines)) {
            $score += 15;
        }
        if (!empty($cruise->details)) $score += 10;
        if ($cruise->night >= 7) $score += 10;
        if ($cruise->night >= 14) $score += 5;
        return min($score, 100);
    }

    private function calculateOptimalSatisfaction($alternatives, $searchParams)
    {
        $baseScore = 65;
        $alternativeCount = count($alternatives);
        
        if ($alternativeCount === 0) {
            return $baseScore;
        }

        $varietyBonus = min($alternativeCount * 2, 20);
        $companies = collect($alternatives)->pluck('line')->unique();
        $diversityBonus = min($companies->count() * 2, 10);
        $avgQuality = collect($alternatives)->avg('quality_score') ?? 60;
        $qualityBonus = max(0, ($avgQuality - 60) / 40 * 5);

        return min($baseScore + $varietyBonus + $diversityBonus + $qualityBonus, 100);
    }

    private function generateSuggestions($matches, $alternatives, $searchParams)
    {
        $suggestions = [];
        $matchCount = count($matches);
        $normalizedBudget = $this->normalizeBudget($searchParams['budget']);
        $budgetPerPerson = $normalizedBudget / $searchParams['participants'];

        $withinBudgetCount = collect($matches)->where('is_over_budget', false)->count();
        $overBudgetCount = collect($matches)->where('is_over_budget', true)->count();

        if ($withinBudgetCount === 0) {
            $suggestions[] = "🔍 Nessuna crociera trovata nel budget di €" . number_format($budgetPerPerson, 0, ',', '.') . " per persona.";
            $suggestions[] = "💡 Considera di aumentare il budget o espandere le date di ricerca.";
        } elseif ($withinBudgetCount <= 2) {
            $suggestions[] = "🔍 Poche opzioni disponibili nel tuo budget. Considera date più flessibili.";
        } else {
            $suggestions[] = "🎯 Trovate {$withinBudgetCount} crociere nel tuo budget!";
        }

        if ($overBudgetCount > 0) {
            $suggestions[] = "⚠️ {$overBudgetCount} risultati superano il budget. Considera un aumento del budget.";
        }

        $dateRange = $searchParams['date_range'];
        if (preg_match('/\/(06|07|08)\//', $dateRange)) {
            $suggestions[] = "☀️ Estate = alta stagione. Prezzi più alti ma clima perfetto.";
        } elseif (preg_match('/\/(09|10|11)\//', $dateRange)) {
            $suggestions[] = "🍂 Autunno = prezzi migliori e meno affollamento.";
        } elseif (preg_match('/\/(12|01|02)\//', $dateRange)) {
            $suggestions[] = "❄️ Inverno = ottime offerte per destinazioni calde!";
        }

        return array_slice($suggestions, 0, 4);
    }

    private function getOptimalSuggestion($currentSatisfaction, $optimalSatisfaction)
    {
        $difference = $optimalSatisfaction - $currentSatisfaction;

        if ($difference <= 5) {
            return '🎯 Ricerca già ottimizzata perfettamente!';
        } elseif ($difference <= 15) {
            return '✨ Piccoli aggiustamenti per risultati migliori';
        } elseif ($difference <= 30) {
            return '📅 Espandi le date per il +' . round($difference) . '% di soddisfazione';
        } else {
            return '🔧 Modifica i parametri per sbloccare +' . round($difference) . '% opzioni';
        }
    }

    private function calculateValueRating($cruisePrice, $budgetPerPerson, $nights)
    {
        $score = 50;
        
        $priceRatio = $cruisePrice / $budgetPerPerson;
        if ($priceRatio <= 0.7) {
            $score += 30;
        } elseif ($priceRatio <= 0.85) {
            $score += 20;
        } elseif ($priceRatio <= 0.95) {
            $score += 10;
        } elseif ($priceRatio <= 1.0) {
            $score += 5;
        } else {
            $score -= 20;
        }
        
        if ($nights >= 7 && $nights <= 14) {
            $score += 15;
        } elseif ($nights > 14) {
            $score += 10;
        } elseif ($nights >= 5) {
            $score += 5;
        }
        
        $dailyCost = $nights > 0 ? $cruisePrice / $nights : $cruisePrice;
        $expectedDailyCost = $budgetPerPerson / 7;
        
        if ($dailyCost < $expectedDailyCost * 0.8) {
            $score += 10;
        } elseif ($dailyCost < $expectedDailyCost) {
            $score += 5;
        }
        
        return max(min($score, 100), 0);
    }

    private function getSearchStatistics($searchParams, $matches, $alternatives)
    {
        $normalizedBudget = $this->normalizeBudget($searchParams['budget']);
        $budgetPerPerson = $normalizedBudget / $searchParams['participants'];
        
        $withinBudgetCount = collect($matches)->where('is_over_budget', false)->count();
        $overBudgetCount = collect($matches)->where('is_over_budget', true)->count();
        
        return [
            'budget_per_persona' => $budgetPerPerson,
            'budget_totale' => $normalizedBudget,
            'partecipanti' => $searchParams['participants'],
            'periodo_ricerca' => $searchParams['date_range'],
            'risultati_trovati' => count($matches),
            'risultati_nel_budget' => $withinBudgetCount,
            'risultati_oltre_budget' => $overBudgetCount,
            'alternative_disponibili' => count($alternatives),
            'risparmio_medio' => $matches ? collect($matches)->where('is_over_budget', false)->avg('savings') : 0,
            'prezzo_medio_trovato' => $matches ? collect($matches)->avg('prezzo_persona') : 0,
            'compagnie_diverse' => collect($matches)->pluck('line')->unique()->count(),
            'durata_media' => $matches ? collect($matches)->avg('night') : 0,
            'costo_giornaliero_medio' => $matches ? collect($matches)->avg('costo_giornaliero_persona') : 0,
            'value_rating_medio' => $matches ? collect($matches)->avg('value_rating') : 0,
            'miglior_rapporto_qualita_prezzo' => $this->getBestValueCruise($matches)
        ];
    }

    private function getBestValueCruise($matches)
    {
        if (empty($matches)) return null;
        
        $bestValue = collect($matches)->sortByDesc('value_rating')->first();
        
        return [
            'ship' => $bestValue['ship'] ?? 'N/D',
            'line' => $bestValue['line'] ?? 'N/D',
            'value_rating' => $bestValue['value_rating'] ?? 0,
            'costo_giornaliero' => $bestValue['formatted_daily_per_person'] ?? 'N/D',
            'is_over_budget' => $bestValue['is_over_budget'] ?? false
        ];
    }

    /**
     * Metodo per ottenere statistiche generali del database
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_cruises' => Cruise::count(),
                'available_cruises' => Cruise::available()->future()->count(),
                'companies' => Cruise::distinct('line')->whereNotNull('line')->count(),
                'avg_price' => Cruise::whereNotNull('interior')->where('interior', '>', 0)->avg('interior'),
                'min_price' => Cruise::whereNotNull('interior')->where('interior', '>', 0)->min('interior'),
                'max_price' => Cruise::whereNotNull('interior')->where('interior', '>', 0)->max('interior'),
                'destinations' => Cruise::distinct('to')->whereNotNull('to')->count()
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Errore statistiche: ' . $e->getMessage());
            return response()->json(['error' => 'Impossibile recuperare le statistiche'], 500);
        }
    }

    public function index()
    {
        return view('crociere.create');
    }
}