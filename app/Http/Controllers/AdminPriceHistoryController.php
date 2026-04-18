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
        return response()->json(['data' => []]);
    }

    public function departureHistory(Request $request, string $departureId)
    {
        $this->checkAdmin();
        return response()->json(['departure' => null, 'series' => []]);
    }
}
