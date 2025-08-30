<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cruise;
use App\Models\SearchLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CrocieraController extends Controller
{
    public function index()
    {
        return view('crociere.create');
    }

    public function search(Request $request)
    {
        $startTime = microtime(true); // Inizio misurazione tempo

        $data = $request->validate([
            'date_range' => 'required|string',
            'budget' => 'required|numeric|min:100',
            'participants' => 'required|integer|min:1|max:10',
            'port_start' => 'nullable|string',
            'port_end' => 'nullable|string',
        ]);

        $searchResults = null;
        $errorMessage = null;

        try {
            // Parse delle date
            $dateRange = explode(' - ', $data['date_range']);
            $startDate = Carbon::createFromFormat('d/m/Y', trim($dateRange[0]));
            $endDate = isset($dateRange[1]) ?
                Carbon::createFromFormat('d/m/Y', trim($dateRange[1])) :
                $startDate->copy();

            $budgetPerPerson = $data['budget'] / $data['participants'];
            $participants = $data['participants'];

            // Ricerca crociere compatibili con tutti i parametri
            $matchesQuery = Cruise::available()
                ->future()
                ->whereNotNull('interior')
                ->whereRaw('CAST(interior AS DECIMAL(10,2)) >= ?', [$budgetPerPerson * 0.5])
                ->whereRaw('CAST(interior AS DECIMAL(10,2)) <= ?', [$budgetPerPerson]);

            // Applica filtri per date se disponibili
            if ($startDate && $endDate) {
                $matchesQuery->whereBetween('partenza', [
                    $startDate->format('Y-m-d'),
                    $endDate->addDays(30)->format('Y-m-d') // Flessibilità di 30 giorni
                ]);
            }

            // Applica filtri opzionali per porti
            if (!empty($data['port_start'])) {
                $matchesQuery->where(function ($q) use ($data) {
                    $q->where('from', 'LIKE', '%' . $data['port_start'] . '%')
                        ->orWhere('partenza', 'LIKE', '%' . $data['port_start'] . '%');
                });
            }

            if (!empty($data['port_end'])) {
                $matchesQuery->where(function ($q) use ($data) {
                    $q->where('to', 'LIKE', '%' . $data['port_end'] . '%')
                        ->orWhere('arrivo', 'LIKE', '%' . $data['port_end'] . '%');
                });
            }

            $matches = $matchesQuery
                ->orderBy('interior', 'ASC')
                ->take(10)
                ->get()
                ->map(function ($cruise) use ($budgetPerPerson, $participants) {
                    return $this->enrichCruiseData($cruise, $budgetPerPerson, $participants);
                });
            log::info('Matches trovati: ' . count($matches) . '  ');
            log::info('data: ' . json_encode($data) . '  ');
            // Calcola soddisfazione attuale
            $soddisfazioneAttuale = $this->calculateSatisfaction($matches, $data);

            // Ricerca alternative ottimizzate (budget aumentato del 20%, date più flessibili)
            $alternativeQuery = Cruise::available()
                ->future()
                ->whereNotNull('interior')
                ->whereRaw('CAST(interior AS DECIMAL(10,2)) > 0')
                ->whereRaw('CAST(interior AS DECIMAL(10,2)) >= ?', [$budgetPerPerson * 0.8])
                ->whereRaw('CAST(interior AS DECIMAL(10,2)) <= ?', [$budgetPerPerson * 1.2]);

            // Solo filtro porto di partenza per le alternative
            if (!empty($data['port_start'])) {
                $alternativeQuery->where(function ($q) use ($data) {
                    $q->where('from', 'LIKE', '%' . $data['port_start'] . '%')
                        ->orWhere('partenza', 'LIKE', '%' . $data['port_start'] . '%');
                });
            }

            // Date più flessibili per le alternative (3 mesi)
            if ($startDate) {
                $flexibleStart = $startDate->copy()->subMonth();
                $flexibleEnd = $endDate->copy()->addMonths(2);

                $alternativeQuery->whereBetween('partenza', [
                    $flexibleStart->format('Y-m-d'),
                    $flexibleEnd->format('Y-m-d')
                ]);
            }

            $alternative = $alternativeQuery
                ->orderBy('line', 'ASC')
                ->orderBy('interior', 'ASC')
                ->take(10)
                ->get()
                ->map(function ($cruise) use ($budgetPerPerson, $participants) {
                    return $this->enrichCruiseData($cruise, $budgetPerPerson, $participants, true);
                });

            log::info('alternative trovate: ' . count($alternative) . '  ');
            log::info('data: ' . json_encode($data) . '  ');

            // Calcola soddisfazione ottimale
            $soddisfazioneOttimale = $this->calculateOptimalSatisfaction($alternative, $data);

            // Genera consigli personalizzati
            $consigli = $this->generateSuggestions($matches, $alternative, $data);

            // Statistiche aggiuntive
            $statistiche = $this->getSearchStatistics($data, $matches, $alternative);

            $searchResults = [
                'success' => true,
                'soddisfazione_attuale' => $soddisfazioneAttuale,
                'soddisfazione_ottimale' => $soddisfazioneOttimale,
                'matches' => $matches,
                'alternative' => $alternative,
                'consigli' => $consigli,
                'suggerimento_ottimale' => $this->getOptimalSuggestion($soddisfazioneAttuale, $soddisfazioneOttimale),
                'statistiche' => $statistiche
            ];
        } catch (\Exception $e) {
            Log::error('Errore ricerca crociere: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            $errorMessage = 'Si è verificato un errore durante la ricerca. Riprova più tardi.';

            $searchResults = [
                'success' => false,
                'error' => $errorMessage,
                'soddisfazione_attuale' => 0,
                'soddisfazione_ottimale' => 0,
                'matches' => [],
                'alternative' => [],
                'consigli' => ['Si è verificato un errore. Controlla i parametri di ricerca.'],
                'suggerimento_ottimale' => 'Riprova la ricerca'
            ];
        }

        // Calcola durata ricerca
        $endTime = microtime(true);
        $searchDuration = round(($endTime - $startTime) * 1000); // in millisecondi

        // Registra il log della ricerca
        try {
            SearchLog::createFromRequest(
                $data,
                $searchResults,
                $searchDuration,
                $errorMessage
            );
        } catch (\Exception $e) {
            // Log l'errore del logging ma non bloccare la risposta
            Log::warning('Errore durante il logging della ricerca: ' . $e->getMessage());
        }

        // Restituisci i risultati
        if ($errorMessage) {
            return response()->json($searchResults, 500);
        }

        return response()->json($searchResults);
    }

    private function enrichCruiseData($cruise, $budgetPerPerson, $participants, $isAlternative = false)
    {
        $pricePerPerson = (float) $cruise->interior;
        $totalPrice = $pricePerPerson * $participants;
        $nights = $cruise->night ?: $cruise->duration ?: 1;

        // Calcolo costo giornaliero
        $dailyCostPerPerson = $nights > 0 ? round($pricePerPerson / $nights, 2) : 0;
        $dailyCostTotal = $dailyCostPerPerson * $participants;

        $enriched = [
            'id' => $cruise->id,
            'ship' => $cruise->ship ?? 'N/D',
            'line' => $cruise->line ?? 'N/D',
            'night' => $nights,
            'interior' => $cruise->interior ?? 'N/D',
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
            'match_percentage' => $this->calculateMatchPercentage($cruise, $budgetPerPerson),
            'formatted_price' => '€' . number_format($pricePerPerson, 0, ',', '.'),
            'formatted_total' => '€' . number_format($totalPrice, 0, ',', '.'),
            'formatted_daily_cost' => '€' . number_format($dailyCostTotal, 0, ',', '.'),
            'formatted_daily_per_person' => '€' . number_format($dailyCostPerPerson, 0, ',', '.'),
            'savings' => $this->calculateSavings($pricePerPerson, $budgetPerPerson),
            'quality_score' => $this->calculateQualityScore($cruise),
            'value_rating' => $this->calculateValueRating($pricePerPerson, $budgetPerPerson, $nights)
        ];

        if ($isAlternative) {
            $enriched['benefit'] = $this->calculateBenefit($cruise, $budgetPerPerson);
            $enriched['recommendation_reason'] = $this->getRecommendationReason($cruise, $budgetPerPerson);
        }

        return $enriched;
    }

    private function calculateMatchPercentage($cruise, $budgetPerPerson)
    {
        $cruisePrice = (float) $cruise->interior;
        $priceRatio = $cruisePrice / $budgetPerPerson;

        // Base score basato sul prezzo
        if ($priceRatio <= 0.7) {
            $baseScore = mt_rand(88, 95); // Ottimo affare
        } elseif ($priceRatio <= 0.85) {
            $baseScore = mt_rand(78, 87); // Buon prezzo
        } elseif ($priceRatio <= 0.95) {
            $baseScore = mt_rand(68, 77); // Prezzo accettabile
        } else {
            $baseScore = mt_rand(55, 67); // Prezzo al limite
        }

        // Bonus qualità 
        $qualityBonus = 0;
        if (!empty($cruise->details)) $qualityBonus += 2;
        if ($cruise->night >= 7) $qualityBonus += 3;
        if (in_array(strtolower($cruise->line), ['royal caribbean', 'norwegian', 'celebrity'])) {
            $qualityBonus += 5;
        }

        return min($baseScore + $qualityBonus, 100);
    }

    private function calculateBenefit($cruise, $budgetPerPerson)
    {
        $cruisePrice = (float) $cruise->interior;
        $benefits = [];

        // Calcola risparmio
        if ($cruisePrice < $budgetPerPerson * 0.8) {
            $savings = round((1 - $cruisePrice / $budgetPerPerson) * 100);
            $benefits[] = "Risparmio {$savings}%";
        }

        // Analizza durata
        if (!empty($cruise->night) && is_numeric($cruise->night)) {
            if ($cruise->night >= 14) {
                $benefits[] = 'Crociera lunga';
            } elseif ($cruise->night >= 7) {
                $benefits[] = 'Durata ottimale';
            }
        }

        // Analizza compagnia
        $premiumLines = ['royal caribbean', 'norwegian', 'celebrity', 'princess'];
        if (in_array(strtolower($cruise->line), $premiumLines)) {
            $benefits[] = 'Compagnia premium';
        }

        // Analizza date
        if ($cruise->partenza) {
            $departure = Carbon::parse($cruise->partenza);
            $now = Carbon::now();
            $daysUntil = $now->diffInDays($departure, false);

            if ($daysUntil >= 60 && $daysUntil <= 120) {
                $benefits[] = 'Anticipo ottimale';
            }
        }

        return empty($benefits) ? 'Buona opzione' : implode(', ', array_slice($benefits, 0, 2));
    }

    private function calculateSavings($cruisePrice, $budgetPerPerson)
    {
        if ($cruisePrice >= $budgetPerPerson) return 0;
        return round((1 - $cruisePrice / $budgetPerPerson) * 100);
    }

    private function calculateQualityScore($cruise)
    {
        $score = 60; // Base score

        // Bonus compagnia
        $premiumLines = ['royal caribbean', 'norwegian', 'celebrity', 'princess'];
        if (in_array(strtolower($cruise->line), $premiumLines)) {
            $score += 15;
        }

        // Bonus dettagli
        if (!empty($cruise->details)) $score += 10;

        // Bonus durata
        if ($cruise->night >= 7) $score += 10;
        if ($cruise->night >= 14) $score += 5;

        return min($score, 100);
    }

    private function getRecommendationReason($cruise, $budgetPerPerson)
    {
        $cruisePrice = (float) $cruise->interior;
        $reasons = [];

        if ($cruisePrice < $budgetPerPerson * 0.8) {
            $reasons[] = 'Prezzo molto conveniente';
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

    private function calculateSatisfaction($matches, $searchParams)
    {
        $matchCount = count($matches);

        // Se non ci sono risultati, la soddisfazione è 0
        if ($matchCount === 0) {
            return 0;
        }

        // Punteggio base quando ci sono risultati
        $baseScore = 30;

        // Bonus per numero di risultati (massimo 25 punti)
        // Più risultati = maggiore soddisfazione fino a un massimo
        if ($matchCount >= 10) {
            $countBonus = 25;
        } elseif ($matchCount >= 5) {
            $countBonus = 20;
        } elseif ($matchCount >= 3) {
            $countBonus = 15;
        } elseif ($matchCount >= 2) {
            $countBonus = 10;
        } else {
            $countBonus = 5; // Solo 1 risultato
        }

        // Bonus per compatibilità budget (massimo 25 punti)
        $budgetBonus = 0;
        $budgetPerPerson = $searchParams['budget'] / $searchParams['participants'];

        foreach ($matches as $match) {
            $price = $match['prezzo_persona'];
            if ($price <= $budgetPerPerson * 0.6) {
                $budgetBonus += 5; // Prezzo molto conveniente
            } elseif ($price <= $budgetPerPerson * 0.8) {
                $budgetBonus += 3; // Prezzo buono
            } elseif ($price <= $budgetPerPerson * 0.95) {
                $budgetBonus += 1; // Prezzo accettabile
            }
            // Se il prezzo supera il budget, non aggiungiamo bonus
        }
        $budgetBonus = min($budgetBonus, 25);

        // Bonus qualità media (massimo 15 punti)
        $avgQuality = collect($matches)->avg('quality_score') ?? 60;
        $qualityBonus = max(0, ($avgQuality - 60) / 40 * 15);

        // Bonus filtri soddisfatti (massimo 5 punti)
        $filterBonus = 0;
        if (!empty($searchParams['port_start'])) $filterBonus += 2;
        if (!empty($searchParams['port_end'])) $filterBonus += 3;

        // Penalità se tutti i risultati superano il budget
        $overBudgetPenalty = 0;
        $overBudgetCount = 0;
        foreach ($matches as $match) {
            if ($match['prezzo_persona'] > $budgetPerPerson) {
                $overBudgetCount++;
            }
        }

        // Se più del 50% dei risultati supera il budget, applica penalità
        if ($overBudgetCount > $matchCount * 0.5) {
            $overBudgetPenalty = 10;
        }

        $finalScore = $baseScore + $countBonus + $budgetBonus + $qualityBonus + $filterBonus - $overBudgetPenalty;

        return max(0, min($finalScore, 100));
    }

    private function calculateOptimalSatisfaction($alternatives, $searchParams)
    {
        $alternativeCount = count($alternatives);

        // Se non ci sono alternative, il potenziale ottimale è basso
        if ($alternativeCount === 0) {
            return 15; // Punteggio molto basso ma non zero (indica che c'è sempre margine di miglioramento)
        }

        // Punteggio base quando ci sono alternative
        $baseScore = 50;

        // Bonus varietà basato sul numero di alternative (massimo 25 punti)
        if ($alternativeCount >= 15) {
            $varietyBonus = 25;
        } elseif ($alternativeCount >= 10) {
            $varietyBonus = 20;
        } elseif ($alternativeCount >= 7) {
            $varietyBonus = 15;
        } elseif ($alternativeCount >= 5) {
            $varietyBonus = 12;
        } elseif ($alternativeCount >= 3) {
            $varietyBonus = 8;
        } else {
            $varietyBonus = 5;
        }

        // Bonus diversità compagnie (massimo 15 punti)
        $companies = collect($alternatives)->pluck('line')->unique();
        $companyCount = $companies->count();
        if ($companyCount >= 6) {
            $diversityBonus = 15;
        } elseif ($companyCount >= 4) {
            $diversityBonus = 12;
        } elseif ($companyCount >= 3) {
            $diversityBonus = 8;
        } elseif ($companyCount >= 2) {
            $diversityBonus = 5;
        } else {
            $diversityBonus = 2;
        }

        // Bonus qualità media delle alternative (massimo 10 punti)
        $avgQuality = collect($alternatives)->avg('quality_score') ?? 60;
        $qualityBonus = max(0, min(($avgQuality - 60) / 40 * 10, 10));

        // Bonus per flessibilità di budget (massimo 10 punti)
        $budgetFlexibilityBonus = 0;
        $budgetPerPerson = $searchParams['budget'] / $searchParams['participants'];

        // Conta quante alternative offrono vantaggi significativi
        $betterOptionsCount = 0;
        foreach ($alternatives as $alt) {
            $altPrice = $alt['prezzo_persona'] ?? 0;

            // Considera "migliore" se:
            // - Costa meno del 90% del budget E ha qualità alta
            // - Offre durata superiore a prezzo simile
            if (($altPrice <= $budgetPerPerson * 0.9 && ($alt['quality_score'] ?? 60) > 75) ||
                ($altPrice <= $budgetPerPerson * 1.1 && ($alt['night'] ?? 7) > 10)
            ) {
                $betterOptionsCount++;
            }
        }

        if ($betterOptionsCount >= 5) {
            $budgetFlexibilityBonus = 10;
        } elseif ($betterOptionsCount >= 3) {
            $budgetFlexibilityBonus = 7;
        } elseif ($betterOptionsCount >= 1) {
            $budgetFlexibilityBonus = 4;
        }

        // Bonus per range di prezzi ampio (indica più opzioni) (massimo 5 punti)
        $prices = collect($alternatives)->pluck('prezzo_persona')->filter();
        $priceRangeBonus = 0;
        if ($prices->count() >= 3) {
            $minPrice = $prices->min();
            $maxPrice = $prices->max();
            $priceRange = $maxPrice - $minPrice;

            // Se il range è ampio (più del 50% del budget medio), bonus pieno
            if ($priceRange > $budgetPerPerson * 0.5) {
                $priceRangeBonus = 5;
            } elseif ($priceRange > $budgetPerPerson * 0.3) {
                $priceRangeBonus = 3;
            } elseif ($priceRange > $budgetPerPerson * 0.1) {
                $priceRangeBonus = 2;
            }
        }

        $finalScore = $baseScore + $varietyBonus + $diversityBonus + $qualityBonus +
            $budgetFlexibilityBonus + $priceRangeBonus;

        return min($finalScore, 100);
    }

    private function generateSuggestions($matches, $alternatives, $searchParams)
    {
        $suggestions = [];
        $matchCount = count($matches);
        $budgetPerPerson = $searchParams['budget'] / $searchParams['participants'];

        // Analisi risultati
        if ($matchCount === 0) {
            $suggestions[] = "🔍 Nessuna crociera trovata con i parametri attuali. Prova ad espandere le date o aumentare il budget.";
        } elseif ($matchCount <= 2) {
            $suggestions[] = "🔍 Poche opzioni disponibili. Considera date più flessibili per vedere più offerte.";
        } elseif ($matchCount >= 8) {
            $suggestions[] = "🎯 Ottima selezione! Hai molte opzioni tra cui scegliere.";
        }

        // Analisi budget
        if ($matchCount > 0) {
            $avgPrice = collect($matches)->avg('prezzo_persona');
            $minPrice = collect($matches)->min('prezzo_persona');

            if ($avgPrice < $budgetPerPerson * 0.7) {
                $suggestions[] = "💰 Il tuo budget ti permette crociere premium! Considera upgrade di cabina.";
            } elseif ($minPrice < $budgetPerPerson * 0.6) {
                $suggestions[] = "💡 Abbiamo trovato alcune offerte eccezionali sotto budget.";
            }
        }

        // Analisi filtri
        if (!empty($searchParams['port_start']) && !empty($searchParams['port_end'])) {
            $suggestions[] = "🚢 Specificando entrambi i porti limiti le opzioni. Prova a rimuovere il porto di destinazione.";
        }

        // Analisi stagionale
        $dateRange = $searchParams['date_range'];
        if (preg_match('/\/(07|08)\//', $dateRange)) {
            $suggestions[] = "☀️ Estate = alta stagione. Considera giugno o settembre per prezzi migliori.";
        } elseif (preg_match('/\/(12|01|02)\//', $dateRange)) {
            $suggestions[] = "❄️ Ottimo periodo per Caraibi e destinazioni calde!";
        }

        // Suggerimenti basati sulle alternative
        if (count($alternatives) > $matchCount + 3) {
            $suggestions[] = "🔄 Molte più opzioni disponibili con parametri leggermente diversi.";
        }

        return array_slice($suggestions, 0, 4); // Massimo 4 suggerimenti
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
        $score = 50; // Base score

        // Bonus per prezzo conveniente
        $priceRatio = $cruisePrice / $budgetPerPerson;
        if ($priceRatio <= 0.7) {
            $score += 30;
        } elseif ($priceRatio <= 0.85) {
            $score += 20;
        } elseif ($priceRatio <= 0.95) {
            $score += 10;
        }

        // Bonus per durata ottimale (7-14 notti)
        if ($nights >= 7 && $nights <= 14) {
            $score += 15;
        } elseif ($nights > 14) {
            $score += 10;
        } elseif ($nights >= 5) {
            $score += 5;
        }

        // Calcolo costo giornaliero competitivo
        $dailyCost = $nights > 0 ? $cruisePrice / $nights : $cruisePrice;
        $expectedDailyCost = $budgetPerPerson / 7; // Assumiamo 7 notti come baseline

        if ($dailyCost < $expectedDailyCost * 0.8) {
            $score += 10;
        } elseif ($dailyCost < $expectedDailyCost) {
            $score += 5;
        }

        return min($score, 100);
    }

    private function getSearchStatistics($searchParams, $matches, $alternatives)
    {
        $budgetPerPerson = $searchParams['budget'] / $searchParams['participants'];

        return [
            'budget_per_persona' => $budgetPerPerson,
            'budget_totale' => $searchParams['budget'],
            'partecipanti' => $searchParams['participants'],
            'periodo_ricerca' => $searchParams['date_range'],
            'risultati_trovati' => count($matches),
            'alternative_disponibili' => count($alternatives),
            'risparmio_medio' => $matches ? collect($matches)->avg('savings') : 0,
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
            'costo_giornaliero' => $bestValue['formatted_daily_per_person'] ?? 'N/D'
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
}
