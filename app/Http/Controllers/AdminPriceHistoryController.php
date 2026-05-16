<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PriceHistory;

class AdminPriceHistoryController extends Controller
{
    private function checkAdmin(): void
    {
        if (!Auth::check() || Auth::user()->role !== '1') {
            abort(403, 'Accesso negato');
        }
    }

    public function index()
    {
        if (Auth::user()->role !== '1') {
            return redirect('/home')->with('error', 'Accesso negato');
        }
        return view('admin.price-history.index');
    }

    public function topVariations(Request $request)
    {
        $this->checkAdmin();

        $days = max(1, (int) $request->get('days', 30));

        $sql = "
            SELECT
                curr.departure_id,
                curr.category_code,
                curr.price                                                AS current_price,
                ref.price                                                 AS ref_price,
                (curr.price - ref.price)                                  AS delta_eur,
                ROUND(((curr.price - ref.price) / ref.price) * 100, 2)   AS delta_pct,
                p.cruise_name,
                DATE_FORMAT(d.dep_date, '%Y-%m-%d')                       AS dep_date
            FROM (
                SELECT departure_id, category_code, price
                FROM price_history ph1
                WHERE ph1.id = (
                    SELECT MAX(id) FROM price_history
                    WHERE departure_id = ph1.departure_id
                      AND category_code = ph1.category_code
                )
            ) curr
            JOIN (
                SELECT departure_id, category_code, price
                FROM price_history ph2
                WHERE ph2.recorded_at = (
                    SELECT MAX(recorded_at) FROM price_history
                    WHERE departure_id = ph2.departure_id
                      AND category_code = ph2.category_code
                      AND recorded_at <= DATE_SUB(NOW(), INTERVAL ? DAY)
                )
            ) ref ON curr.departure_id = ref.departure_id
                 AND curr.category_code = ref.category_code
            JOIN departures d ON curr.departure_id = d.id
                AND d.deleted_at IS NULL
            JOIN products p ON d.product_id = p.id
                AND p.deleted_at IS NULL
            WHERE curr.price <> ref.price
            ORDER BY ABS(curr.price - ref.price) DESC
            LIMIT 10
        ";

        $rows = DB::select($sql, [$days]);

        if (empty($rows)) {
            return response()->json(['data' => [], 'insufficient_data' => true]);
        }

        return response()->json(['data' => $rows, 'insufficient_data' => false]);
    }

    public function search(Request $request)
    {
        $this->checkAdmin();

        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $results = DB::table('departures as d')
            ->join('products as p', 'd.product_id', '=', 'p.id')
            ->where(function ($query) use ($q) {
                $query->where('p.cruise_name', 'LIKE', "%{$q}%")
                      ->orWhere('d.id', 'LIKE', "%{$q}%");
            })
            ->whereNull('d.deleted_at')
            ->whereNull('p.deleted_at')
            ->select(
                'd.id',
                'p.cruise_name',
                DB::raw("DATE_FORMAT(d.dep_date, '%Y-%m-%d') as dep_date"),
                'd.min_price'
            )
            ->orderBy('d.dep_date')
            ->limit(20)
            ->get();

        return response()->json(['data' => $results]);
    }

    public function departureHistory(Request $request, string $departureId)
    {
        $this->checkAdmin();

        $from = $request->get('from');
        $to   = $request->get('to');

        $departure = DB::table('departures as d')
            ->join('products as p', 'd.product_id', '=', 'p.id')
            ->where('d.id', $departureId)
            ->whereNull('d.deleted_at')
            ->select('d.id', 'p.cruise_name', DB::raw("DATE_FORMAT(d.dep_date, '%Y-%m-%d') as dep_date"))
            ->first();

        if (! $departure) {
            return response()->json(['error' => 'Partenza non trovata'], 404);
        }

        $history = DB::table('price_history as ph')
            ->join('departures as d', 'd.id', '=', 'ph.departure_id')
            ->join('products as p', 'p.id', '=', 'd.product_id')
            ->leftJoin('ship_categories as sc', function ($join) {
                $join->on('sc.ship_id', '=', 'p.ship_id')
                     ->on('sc.cl_cat', '=', 'ph.category_code');
            })
            ->where('ph.departure_id', $departureId)
            ->when($from, fn($q) => $q->where('ph.recorded_at', '>=', $from))
            ->when($to,   fn($q) => $q->where('ph.recorded_at', '<=', $to . ' 23:59:59'))
            ->orderBy('ph.recorded_at')
            ->select(
                'ph.category_code',
                'ph.price',
                'ph.recorded_at',
                'ph.source',
                DB::raw("COALESCE(sc.cruisehost_cat, 'ND') as cruisehost_cat")
            )
            ->get();

        $series = [];
        foreach ($history->groupBy('category_code') as $category => $records) {
            $macro     = $records->first()->cruisehost_cat ?? 'ND';
            $data      = [];
            $prevPrice = null;
            foreach ($records as $record) {
                $deltaEur = $prevPrice !== null
                    ? round((float) $record->price - $prevPrice, 2)
                    : null;
                $deltaPct = ($prevPrice !== null && $prevPrice > 0)
                    ? round(((float) $record->price - $prevPrice) / $prevPrice * 100, 2)
                    : null;
                $data[] = [
                    'x'         => $record->recorded_at,
                    'y'         => (float) $record->price,
                    'delta_eur' => $deltaEur,
                    'delta_pct' => $deltaPct,
                    'source'    => $record->source,
                ];
                $prevPrice = (float) $record->price;
            }
            $series[] = ['name' => $category, 'macro' => $macro, 'data' => $data];
        }

        return response()->json(['departure' => $departure, 'series' => $series]);
    }

    // ── Helper ───────────────────────────────────────────────────────────────────

    private function getItineraryFromDeparture(string $departureId): ?object
    {
        return DB::table('departures as d')
            ->join('products as p', 'p.id', '=', 'd.product_id')
            ->where('d.id', $departureId)
            ->whereNull('d.deleted_at')
            ->whereNull('p.deleted_at')
            ->select('p.cruise_name', 'p.cruise_line_id', 'p.port_from_id', 'p.port_to_id')
            ->first();
    }

    // ── Analisi stagionale ───────────────────────────────────────────────────────

    public function seasonalWeekly(Request $request)
    {
        $this->checkAdmin();

        $departureId = trim($request->get('departure_id', ''));
        $category    = trim($request->get('category', 'IC'));

        if ($departureId === '') {
            return response()->json(['error' => 'departure_id richiesto'], 422);
        }

        $itinerary = $this->getItineraryFromDeparture($departureId);
        if (! $itinerary) {
            return response()->json(['error' => 'Partenza non trovata'], 404);
        }

        // Categorie disponibili per questo itinerario
        $availableCats = DB::table('price_history as ph')
            ->join('departures as d', 'd.id', '=', 'ph.departure_id')
            ->join('products as p', 'p.id', '=', 'd.product_id')
            ->where('p.cruise_name',    $itinerary->cruise_name)
            ->where('p.cruise_line_id', $itinerary->cruise_line_id)
            ->where('p.port_from_id',   $itinerary->port_from_id)
            ->where('p.port_to_id',     $itinerary->port_to_id)
            ->whereNull('d.deleted_at')
            ->whereNull('p.deleted_at')
            ->distinct()
            ->orderBy('ph.category_code')
            ->pluck('ph.category_code')
            ->values()
            ->toArray();

        // Fallback categoria se non disponibile
        if (! in_array($category, $availableCats)) {
            $category = in_array('IC', $availableCats) ? 'IC' : ($availableCats[0] ?? 'IC');
        }

        $rows = DB::select("
            SELECT
                YEAR(d.dep_date)                                AS edition_year,
                TIMESTAMPDIFF(WEEK, ph.recorded_at, d.dep_date) AS weeks_before,
                ROUND(AVG(ph.price), 2)                         AS avg_price
            FROM price_history ph
            JOIN departures d ON d.id = ph.departure_id
            JOIN products p   ON p.id = d.product_id
            WHERE p.cruise_name    = ?
              AND p.cruise_line_id = ?
              AND p.port_from_id   = ?
              AND p.port_to_id     = ?
              AND ph.category_code = ?
              AND d.deleted_at IS NULL
              AND p.deleted_at IS NULL
              AND TIMESTAMPDIFF(WEEK, ph.recorded_at, d.dep_date) BETWEEN 0 AND 24
            GROUP BY edition_year, weeks_before
            ORDER BY edition_year ASC, weeks_before DESC
        ", [
            $itinerary->cruise_name,
            $itinerary->cruise_line_id,
            $itinerary->port_from_id,
            $itinerary->port_to_id,
            $category,
        ]);

        $byYear = [];
        foreach ($rows as $row) {
            $byYear[$row->edition_year][] = [
                'x' => (int) $row->weeks_before,
                'y' => (float) $row->avg_price,
            ];
        }

        $series = [];
        foreach ($byYear as $year => $data) {
            $series[] = ['name' => (string) $year, 'data' => $data];
        }

        return response()->json([
            'itinerary'            => $itinerary->cruise_name,
            'category'             => $category,
            'available_categories' => $availableCats,
            'series'               => $series,
            'insufficient_data'    => count($series) < 1,
        ]);
    }

    public function seasonalMonthly(Request $request)
    {
        $this->checkAdmin();

        $departureId = trim($request->get('departure_id', ''));
        $category    = trim($request->get('category', 'IC'));

        if ($departureId === '') {
            return response()->json(['error' => 'departure_id richiesto'], 422);
        }

        $itinerary = $this->getItineraryFromDeparture($departureId);
        if (! $itinerary) {
            return response()->json(['error' => 'Partenza non trovata'], 404);
        }

        $rows = DB::select("
            SELECT
                YEAR(d.dep_date)             AS edition_year,
                MONTH(d.dep_date)            AS dep_month,
                ROUND(AVG(last_ph.price), 2) AS avg_price,
                MIN(last_ph.price)           AS min_price
            FROM departures d
            JOIN products p ON p.id = d.product_id
            JOIN (
                SELECT ph.departure_id, ph.price
                FROM price_history ph
                WHERE ph.category_code = ?
                  AND ph.id = (
                      SELECT MAX(id) FROM price_history
                      WHERE departure_id = ph.departure_id
                        AND category_code = ph.category_code
                  )
            ) last_ph ON last_ph.departure_id = d.id
            WHERE p.cruise_name    = ?
              AND p.cruise_line_id = ?
              AND p.port_from_id   = ?
              AND p.port_to_id     = ?
              AND d.deleted_at IS NULL
              AND p.deleted_at IS NULL
            GROUP BY edition_year, dep_month
            ORDER BY edition_year ASC, dep_month ASC
        ", [
            $category,
            $itinerary->cruise_name,
            $itinerary->cruise_line_id,
            $itinerary->port_from_id,
            $itinerary->port_to_id,
        ]);

        $monthNames = ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];

        $byYear = [];
        foreach ($rows as $row) {
            $byYear[$row->edition_year][(int) $row->dep_month] = (float) $row->avg_price;
        }

        $series = [];
        foreach ($byYear as $year => $monthMap) {
            $data = [];
            for ($m = 1; $m <= 12; $m++) {
                $data[] = $monthMap[$m] ?? null;
            }
            $series[] = ['name' => (string) $year, 'data' => $data];
        }

        return response()->json([
            'itinerary'  => $itinerary->cruise_name,
            'category'   => $category,
            'categories' => $monthNames,
            'series'     => $series,
        ]);
    }
}
