<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Departure;
use App\Models\CruiseLine;
use App\Models\Ship;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class AdminCatalogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Auth::user()->role !== '1') {
            return redirect('/home')->with('error', 'Accesso negato');
        }

        $stats = $this->getCatalogStats();
        $lastSync = DB::table('catalog_sync_log')
            ->where('status', 'completed')
            ->orderByDesc('finished_at')
            ->first();

        $todaySyncs = DB::table('catalog_sync_log')
            ->whereDate('started_at', today())
            ->count();

        $history = DB::table('catalog_sync_log')
            ->orderByDesc('started_at')
            ->limit(20)
            ->get()
            ->map(fn($row) => $this->formatLogRow($row));

        return view('admin.catalog.index', compact('stats', 'lastSync', 'todaySyncs', 'history'));
    }

    public function startSync(Request $request)
    {
        if (Auth::user()->role !== '1') {
            return response()->json(['error' => 'Accesso negato'], 403);
        }

        $todaySyncs = DB::table('catalog_sync_log')
            ->whereDate('started_at', today())
            ->where('status', '!=', 'failed')
            ->count();

        if ($todaySyncs >= 4) {
            return response()->json([
                'error' => 'Limite giornaliero di 4 sincronizzazioni raggiunto.',
                'today_count' => $todaySyncs,
            ], 429);
        }

        // Verifica che non ci sia già un sync in corso
        $running = DB::table('catalog_sync_log')
            ->where('status', 'running')
            ->where('started_at', '>=', now()->subMinutes(30))
            ->exists();

        if ($running) {
            return response()->json(['error' => 'Sincronizzazione già in corso.'], 409);
        }

        // Pre-crea il log entry e passa l'ID al comando
        $logId = DB::table('catalog_sync_log')->insertGetId([
            'started_at'   => now(),
            'status'       => 'running',
            'triggered_by' => 'manual',
        ]);

        // PHP_BINARY in Apache mod_php (Windows) returns httpd.exe, not the PHP CLI.
        // PHP_BINARY in PHP-FPM (Linux) may return the fpm binary instead of the CLI.
        // PHP_BINDIR may also point to a wrong/system PHP on Windows.
        // Use PHP_CLI_BINARY from .env if set, otherwise fall back to PHP_BINDIR.
        $isWin     = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $phpBinary = env('PHP_CLI_BINARY') ?: (
            $isWin ? PHP_BINDIR . DIRECTORY_SEPARATOR . 'php.exe' : 'php'
        );
        $artisan   = base_path('artisan');
        $logFile   = storage_path('logs/catalog_sync_' . $logId . '.log');

        Log::info('[CatalogSync] Avvio processo', [
            'log_id'     => $logId,
            'php_binary' => $phpBinary,
            'artisan'    => $artisan,
            'base_path'  => base_path(),
            'os'         => PHP_OS,
        ]);

        try {
            if ($isWin) {
                // Windows (Laragon dev): usa popen con START /B per distaccare il processo
                $cmd = sprintf(
                    'START /B "" "%s" "%s" catalog:sync --source=manual --log-id=%d > "%s" 2>&1',
                    str_replace('/', '\\', $phpBinary),
                    str_replace('/', '\\', $artisan),
                    $logId,
                    str_replace('/', '\\', $logFile)
                );
                Log::info('[CatalogSync] CMD Windows', ['cmd' => $cmd]);
                pclose(popen($cmd, 'r'));
            } else {
                // Linux/Mac: HTTP self-request con timeout 2s
                // ignore_user_abort(true) nell'endpoint interno garantisce che il sync
                // continui anche dopo il timeout della richiesta (exec() non necessario)
                $secret = hash('sha256', config('app.key') . ':' . $logId);
                Log::info('[CatalogSync] HTTP self-request', ['log_id' => $logId]);
                try {
                    Http::timeout(2)
                        ->withoutVerifying()
                        ->withHeader('X-Sync-Secret', $secret)
                        ->post(url('/internal/catalog-sync'), ['log_id' => $logId]);
                } catch (\Throwable $e) {
                    // Timeout atteso: il sync sta girando in background
                    Log::info('[CatalogSync] Self-request timeout (atteso)', ['log_id' => $logId]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('[CatalogSync] Errore avvio processo', ['error' => $e->getMessage()]);
            DB::table('catalog_sync_log')->where('id', $logId)->update([
                'status'      => 'failed',
                'finished_at' => now(),
                'notes'       => 'Errore avvio: ' . $e->getMessage(),
            ]);
            return response()->json(['error' => 'Impossibile avviare il processo: ' . $e->getMessage()], 500);
        }

        Log::info('[CatalogSync] Processo avviato, output in: ' . $logFile);

        return response()->json([
            'success'  => true,
            'log_id'   => $logId,
            'message'  => 'Sincronizzazione avviata.',
            'log_file' => basename($logFile),
        ]);
    }

    public function runInternalSync(Request $request)
    {
        $logId    = (int) $request->input('log_id');
        $secret   = (string) $request->header('X-Sync-Secret');
        $expected = hash('sha256', config('app.key') . ':' . $logId);

        if (! $logId || ! hash_equals($expected, $secret)) {
            return response()->json(['error' => 'Non autorizzato'], 403);
        }

        ignore_user_abort(true);
        set_time_limit(0);

        \Artisan::call('catalog:sync', [
            '--source' => 'manual',
            '--log-id' => $logId,
            '--force'  => true,
        ]);

        return response()->json(['done' => true]);
    }

    public function syncStatus(int $logId)
    {
        if (Auth::user()->role !== '1') {
            return response()->json(['error' => 'Accesso negato'], 403);
        }

        $row = DB::table('catalog_sync_log')->find($logId);

        if (! $row) {
            return response()->json(['error' => 'Log non trovato'], 404);
        }

        $result = $this->formatLogRow($row);

        // Aggiunge le ultime righe del log file di processo se esiste
        $logFile = storage_path('logs/catalog_sync_' . $logId . '.log');
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $result['process_output'] = implode("\n", array_slice($lines, -20));
        }

        return response()->json($result);
    }

    public function stopSync(int $logId)
    {
        if (Auth::user()->role !== '1') {
            return response()->json(['error' => 'Accesso negato'], 403);
        }

        $updated = DB::table('catalog_sync_log')
            ->where('id', $logId)
            ->where('status', 'running')
            ->update([
                'status'      => 'failed',
                'finished_at' => now(),
                'notes'       => 'Terminato manualmente dall\'amministratore.',
            ]);

        return response()->json([
            'success' => (bool) $updated,
            'message' => $updated ? 'Sync marcato come terminato.' : 'Nessun sync attivo trovato.',
        ]);
    }

    public function history()
    {
        if (Auth::user()->role !== '1') {
            return response()->json(['error' => 'Accesso negato'], 403);
        }

        $rows = DB::table('catalog_sync_log')
            ->orderByDesc('started_at')
            ->limit(50)
            ->get()
            ->map(fn($row) => $this->formatLogRow($row));

        return response()->json($rows);
    }

    // -------------------------------------------------------------------------
    // Helpers privati
    // -------------------------------------------------------------------------

    private function getCatalogStats(): array
    {
        return [
            'departures'   => Departure::count(),
            'future'       => Departure::future()->whereNotNull('min_price')->count(),
            'products'     => Product::count(),
            'cruise_lines' => CruiseLine::where('is_online', true)->count(),
            'ships'        => Ship::count(),
        ];
    }

    private function formatLogRow(object $row): array
    {
        $started  = $row->started_at  ? \Carbon\Carbon::parse($row->started_at)  : null;
        $finished = $row->finished_at ? \Carbon\Carbon::parse($row->finished_at) : null;

        $duration = null;
        if ($started && $finished) {
            $secs = $finished->diffInSeconds($started);
            $duration = $secs >= 60
                ? floor($secs / 60) . 'm ' . ($secs % 60) . 's'
                : $secs . 's';
        }

        return [
            'id'               => $row->id,
            'started_at'       => $started  ? $started->format('d/m/Y H:i:s')  : '—',
            'finished_at'      => $finished ? $finished->format('d/m/Y H:i:s') : '—',
            'status'           => $row->status,
            'triggered_by'     => $row->triggered_by ?? 'cron',
            'products_imported'=> $row->products_imported ?? 0,
            'prices_recorded'  => $row->prices_recorded  ?? 0,
            'duration'         => $duration ?? ($row->status === 'running' ? 'in corso...' : '—'),
            'notes'            => $row->notes ?? null,
        ];
    }
}
