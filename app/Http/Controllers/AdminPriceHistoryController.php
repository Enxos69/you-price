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

        $history = PriceHistory::where('departure_id', $departureId)
            ->when($from, fn($q) => $q->where('recorded_at', '>=', $from))
            ->when($to,   fn($q) => $q->where('recorded_at', '<=', $to . ' 23:59:59'))
            ->orderBy('recorded_at')
            ->get(['category_code', 'price', 'recorded_at', 'source']);

        $series = [];
        foreach ($history->groupBy('category_code') as $category => $records) {
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
                    'x'         => $record->recorded_at->format('Y-m-d H:i:s'),
                    'y'         => (float) $record->price,
                    'delta_eur' => $deltaEur,
                    'delta_pct' => $deltaPct,
                    'source'    => $record->source,
                ];
                $prevPrice = (float) $record->price;
            }
            $series[] = ['name' => $category, 'data' => $data];
        }

        return response()->json(['departure' => $departure, 'series' => $series]);
    }
}
