<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class CrocieraController extends Controller
{
    public function index()
    {
        return view('crociere.create');
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'date_range' => 'required|string',
            'budget' => 'required|numeric|min:100',
            'participants' => 'required|integer|min:1|max:10',
            'port_start' => 'nullable|string',
            'port_end' => 'nullable|string',
        ]);

        // Parse delle date
        $dateRange = explode(' - ', $data['date_range']);
        $startDate = Carbon::createFromFormat('d/m/Y', trim($dateRange[0]))->format('Y-m-d');
        $endDate = isset($dateRange[1]) ? 
            Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))->format('Y-m-d') : 
            $startDate;

        $budgetPerPerson = $data['budget'] / $data['participants'];
        $participants = $data['participants'];

        // Ricerca crociere compatibili con tutti i parametri
        $matchesQuery = DB::table('___import_crociere')
            ->whereNotNull('cruise')
            ->whereNotNull('interior')
            ->where('interior', '!=', '')
            ->whereRaw("CAST(REPLACE(REPLACE(interior, '€', ''), ',', '') AS UNSIGNED) <= ?", [$budgetPerPerson]);

        // Applica filtri opzionali
        if (!empty($data['port_start'])) {
            $matchesQuery->where('partenza', 'LIKE', '%' . $data['port_start'] . '%');
        }

        if (!empty($data['port_end'])) {
            $matchesQuery->where('arrivo', 'LIKE', '%' . $data['port_end'] . '%');
        }

        // Filtro per date (se disponibile nel database)
        if (Schema::hasColumn('___import_crociere', 'data_partenza')) {
            $matchesQuery->whereBetween('data_partenza', [$startDate, $endDate]);
        }

        $matches = $matchesQuery
            ->orderByRaw("CAST(REPLACE(REPLACE(interior, '€', ''), ',', '') AS UNSIGNED) ASC")
            ->take(10)
            ->get()
            ->map(function($cruise) use ($budgetPerPerson, $participants) {
                return $this->enrichCruiseData($cruise, $budgetPerPerson, $participants);
            });

        // Calcola soddisfazione attuale
        $soddisfazioneAttuale = $this->calculateSatisfaction($matches, $data);

        // Ricerca alternative ottimizzate (rimuovendo alcuni filtri)
        $alternativeQuery = DB::table('___import_crociere')
            ->whereNotNull('cruise')
            ->whereNotNull('interior')
            ->where('interior', '!=', '')
            ->whereRaw("CAST(REPLACE(REPLACE(interior, '€', ''), ',', '') AS UNSIGNED) <= ?", [$budgetPerPerson * 1.2]); // Budget leggermente più alto

        // Mantieni solo il filtro del porto di partenza se specificato
        if (!empty($data['port_start'])) {
            $alternativeQuery->where('partenza', 'LIKE', '%' . $data['port_start'] . '%');
        }

        $alternative = $alternativeQuery
            ->orderBy('line', 'ASC')
            ->orderByRaw("CAST(REPLACE(REPLACE(interior, '€', ''), ',', '') AS UNSIGNED) ASC")
            ->take(10)
            ->get()
            ->map(function($cruise) use ($budgetPerPerson, $participants) {
                return $this->enrichCruiseData($cruise, $budgetPerPerson, $participants, true);
            });

        // Calcola soddisfazione ottimale
        $soddisfazioneOttimale = $this->calculateOptimalSatisfaction($alternative, $data);

        // Genera consigli personalizzati
        $consigli = $this->generateSuggestions($matches, $alternative, $data);

        return response()->json([
            'soddisfazione_attuale' => $soddisfazioneAttuale,
            'soddisfazione_ottimale' => $soddisfazioneOttimale,
            'matches' => $matches,
            'alternative' => $alternative,
            'consigli' => $consigli,
            'suggerimento_ottimale' => $this->getOptimalSuggestion($soddisfazioneAttuale, $soddisfazioneOttimale),
            'statistiche' => [
                'budget_per_persona' => $budgetPerPerson,
                'partecipanti' => $participants,
                'periodo_ricerca' => $data['date_range']
            ]
        ]);
    }

    private function enrichCruiseData($cruise, $budgetPerPerson, $participants, $isAlternative = false)
    {
        $pricePerPerson = $this->extractPrice($cruise->interior);
        $totalPrice = $pricePerPerson * $participants;
        
        $enriched = [
            'ship' => $cruise->ship ?? 'N/D',
            'line' => $cruise->line ?? 'N/D',
            'night' => $cruise->night ?? 'N/D',
            'interior' => $cruise->interior ?? 'N/D',
            'partenza' => $cruise->partenza ?? 'N/D',
            'arrivo' => $cruise->arrivo ?? 'N/D',
            'cruise' => $cruise->cruise ?? 'N/D',
            'prezzo_totale' => $totalPrice,
            'prezzo_persona' => $pricePerPerson,
            'match_percentage' => $this->calculateMatchPercentage($cruise, $budgetPerPerson)
        ];

        if ($isAlternative) {
            $enriched['benefit'] = $this->calculateBenefit($cruise, $budgetPerPerson);
        }

        return $enriched;
    }

    private function extractPrice($priceString)
    {
        if (empty($priceString)) return 0;
        
        // Rimuovi simboli e converti in numero
        $price = preg_replace('/[€,\s]/', '', $priceString);
        return (int) $price;
    }

    private function calculateMatchPercentage($cruise, $budgetPerPerson)
    {
        $cruisePrice = $this->extractPrice($cruise->interior);
        
        if ($cruisePrice <= $budgetPerPerson * 0.8) {
            return mt_rand(85, 95); // Ottimo affare
        } elseif ($cruisePrice <= $budgetPerPerson * 0.95) {
            return mt_rand(75, 84); // Buon prezzo
        } else {
            return mt_rand(60, 74); // Prezzo al limite
        }
    }

    private function calculateBenefit($cruise, $budgetPerPerson)
    {
        $cruisePrice = $this->extractPrice($cruise->interior);
        $benefits = [];

        if ($cruisePrice < $budgetPerPerson * 0.8) {
            $benefits[] = 'Risparmio del ' . round((1 - $cruisePrice / $budgetPerPerson) * 100) . '%';
        }

        if (!empty($cruise->night) && is_numeric($cruise->night) && $cruise->night >= 7) {
            $benefits[] = 'Itinerario lungo';
        }

        if (strpos(strtolower($cruise->line ?? ''), 'royal') !== false || 
            strpos(strtolower($cruise->line ?? ''), 'norwegian') !== false) {
            $benefits[] = 'Compagnia premium';
        }

        return empty($benefits) ? 'Buona opzione' : implode(', ', $benefits);
    }

    private function calculateSatisfaction($matches, $searchParams)
    {
        $baseScore = 30; // Punteggio base
        $matchCount = count($matches);

        if ($matchCount === 0) {
            return $baseScore;
        }

        // Bonus per numero di risultati
        $countBonus = min($matchCount * 8, 40);

        // Bonus per compatibilità con budget
        $budgetBonus = 0;
        $budgetPerPerson = $searchParams['budget'] / $searchParams['participants'];
        
        foreach ($matches as $match) {
            $price = $match['prezzo_persona'];
            if ($price <= $budgetPerPerson * 0.8) {
                $budgetBonus += 5;
            } elseif ($price <= $budgetPerPerson * 0.95) {
                $budgetBonus += 3;
            }
        }
        $budgetBonus = min($budgetBonus, 20);

        // Bonus per filtri soddisfatti
        $filterBonus = 0;
        if (!empty($searchParams['port_start'])) {
            $filterBonus += 5;
        }
        if (!empty($searchParams['port_end'])) {
            $filterBonus += 5;
        }

        return min($baseScore + $countBonus + $budgetBonus + $filterBonus, 100);
    }

    private function calculateOptimalSatisfaction($alternatives, $searchParams)
    {
        $baseScore = 60; // Punteggio base più alto per alternative
        $alternativeCount = count($alternatives);
        if ($alternativeCount === 0) {
            return $baseScore;
        }
        // Maggiore varietà di opzioni
        $varietyBonus = min($alternativeCount * 3, 25); 

        // Bonus per diversità di compagnie
        $companies = array_unique(array_column($alternatives->toArray(), 'line'));
        $diversityBonus = min(count($companies) * 2, 15);

        return min($baseScore + $varietyBonus + $diversityBonus, 100);
    }

    private function generateSuggestions($matches, $alternatives, $searchParams)
    {
        $suggestions = [];
        $matchCount = count($matches);
        $budgetPerPerson = $searchParams['budget'] / $searchParams['participants'];

        if ($matchCount === 0) {
            $suggestions[] = "Non abbiamo trovato crociere con i tuoi parametri esatti. Prova ad espandere le date o rimuovere alcuni filtri.";
        } elseif ($matchCount < 3) {
            $suggestions[] = "Hai poche opzioni disponibili. Considera di essere più flessibile sulle date di partenza.";
        }

        if (!empty($searchParams['port_start']) && !empty($searchParams['port_end'])) {
            $suggestions[] = "Specificando sia porto di partenza che di arrivo limiti molto le opzioni. Prova a rimuovere uno dei due.";
        }

        // Analisi budget
        $avgPrice = 0;
        if ($matchCount > 0) {
            $avgPrice = array_sum(array_column($matches->toArray(), 'prezzo_persona')) / $matchCount;
        }

        if ($avgPrice > 0 && $avgPrice < $budgetPerPerson * 0.7) {
            $suggestions[] = "Il tuo budget ti permette di accedere a crociere di categoria superiore!";
        } elseif ($avgPrice > $budgetPerPerson * 0.95) {
            $suggestions[] = "Aumentando leggermente il budget potresti avere molte più opzioni.";
        }

        // Suggerimenti stagionali
        $dateRange = $searchParams['date_range'];
        if (strpos($dateRange, '/07/') !== false || strpos($dateRange, '/08/') !== false) {
            $suggestions[] = "Stai cercando in alta stagione. Considera periodi come maggio-giugno o settembre per prezzi migliori.";
        }

        return array_slice($suggestions, 0, 3); // Massimo 3 suggerimenti
    }

    private function getOptimalSuggestion($currentSatisfaction, $optimalSatisfaction)
    {
        $difference = $optimalSatisfaction - $currentSatisfaction;

        if ($difference <= 10) {
            return 'La tua ricerca è già ottimizzata!';
        } elseif ($difference <= 25) {
            return 'Piccole modifiche potrebbero migliorare i risultati';
        } elseif ($difference <= 40) {
            return 'Espandi le date per più opzioni';
        } else {
            return 'Considera di modificare i parametri di ricerca';
        }
    }

    /**
     * Metodo di utilità per verificare se una colonna esiste
     */
    private function hasColumn($table, $column)
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Metodo per ottenere statistiche generali
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_cruises' => DB::table('___import_crociere')->whereNotNull('cruise')->count(),
                'companies' => DB::table('___import_crociere')->whereNotNull('line')->distinct('line')->count(),
                'avg_price' => DB::table('___import_crociere')
                    ->whereNotNull('interior')
                    ->where('interior', '!=', '')
                    ->avg(DB::raw("CAST(REPLACE(REPLACE(interior, '€', ''), ',', '') AS UNSIGNED)")),
                'destinations' => DB::table('___import_crociere')->whereNotNull('arrivo')->distinct('arrivo')->count()
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Impossibile recuperare le statistiche'], 500);
        }
    }
}