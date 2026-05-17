<?php

namespace App\Http\Controllers;

use App\Models\PriceAlert;
use App\Models\PriceAlertsCheckLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminAlertsLogController extends Controller
{
    private function checkAdmin(): void
    {
        if (! Auth::check() || Auth::user()->role !== '1') {
            abort(403, 'Accesso negato');
        }
    }

    public function index(Request $request)
    {
        $this->checkAdmin();

        $statusFilter = $request->get('status', '');

        $query = PriceAlertsCheckLog::query()->orderByDesc('started_at');

        if (in_array($statusFilter, ['completed', 'failed', 'running'])) {
            $query->where('status', $statusFilter);
        }

        $logs = $query->paginate(20)->withQueryString();

        $kpi = DB::table('price_alerts_check_log')->selectRaw('
            COUNT(*)                          AS total_runs,
            COALESCE(SUM(alerts_triggered),0) AS total_triggered,
            COALESCE(SUM(emails_failed),0)    AS total_failed,
            COALESCE(SUM(alerts_skipped),0)   AS total_skipped
        ')->first();

        $lastRun = PriceAlertsCheckLog::orderByDesc('started_at')->first();

        $activeAlerts = PriceAlert::where('is_active', true)->count();

        return view('admin.alerts-log.index', compact(
            'logs', 'kpi', 'lastRun', 'activeAlerts', 'statusFilter'
        ));
    }
}
