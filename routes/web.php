<?php

// routes/web.php - Versione semplificata senza middleware admin
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CruiseController;
use App\Http\Controllers\CrocieraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\RichiestaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PriceAlertsController;
use App\Http\Controllers\CruiseImportController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SearchAnalyticsController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ConfirmPasswordController;

Route::get('/', function () {
    return view('index');
});


// Login Routes...
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Registration Routes...
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Password Reset Routes...
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Email Verification Routes...
Route::get('email/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

// Password Confirmation Routes...
Route::get('password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
Route::post('password/confirm', [ConfirmPasswordController::class, 'confirm']);

// Rotte per le crociere (pubbliche)
Route::get('/crociere', [CrocieraController::class, 'index'])->name('crociere.index');
Route::post('/crociere/search', [CrocieraController::class, 'search'])->name('crociere.search');
Route::get('/crociere/stats', [CrocieraController::class, 'getStats'])->name('crociere.stats');

// Dettagli singola crociera (solo utenti autenticati)
Route::get('/crociere/{id}', [CrocieraController::class, 'show'])
    ->name('crociere.show')
    ->middleware('auth');

// Tutte le rotte protette solo da autenticazione
Route::middleware('auth')->group(function () {

    // Rotte admin (senza controllo isAdmin per ora)
    Route::get('/admin/index', [AdminController::class, 'index'])->name('admin.index');

    // Dashboard principale
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Preferiti
    # Route::get('/preferiti', [FavoritesController::class, 'index'])->name('favorites.index');
    Route::post('/cruises/{cruise}/favorite/toggle', [FavoritesController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/cruises/{cruise}/favorite', [FavoritesController::class, 'store'])->name('favorites.store');
    Route::delete('/cruises/{cruise}/favorite', [FavoritesController::class, 'destroy'])->name('favorites.destroy');
    Route::patch('/cruises/{cruise}/favorite/note', [FavoritesController::class, 'updateNote'])->name('favorites.update-note');

    // Alert Prezzi
    Route::get('/alert-prezzi', [PriceAlertsController::class, 'index'])->name('alerts.index');
    Route::post('/alert-prezzi', [PriceAlertsController::class, 'store'])->name('alerts.store');
    Route::patch('/alert-prezzi/{alert}', [PriceAlertsController::class, 'update'])->name('alerts.update');
    Route::delete('/alert-prezzi/{alert}', [PriceAlertsController::class, 'destroy'])->name('alerts.destroy');
    Route::post('/alert-prezzi/{alert}/toggle', [PriceAlertsController::class, 'toggleActive'])->name('alerts.toggle');

    // API Dashboard (AJAX)
    Route::prefix('api/dashboard')->name('api.dashboard.')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats'])->name('stats');
        Route::get('/favorites', [DashboardController::class, 'getFavoritesJson'])->name('favorites');
        Route::get('/alerts', [DashboardController::class, 'getAlertsJson'])->name('alerts');
        Route::get('/timeline', [DashboardController::class, 'getActivityTimelineJson'])->name('timeline');
    });

    // API Favorites (AJAX)
    Route::prefix('api/favorites')->name('api.favorites.')->group(function () {
        Route::get('/', [FavoritesController::class, 'getFavorites'])->name('list');
        Route::get('/check/{cruise}', [FavoritesController::class, 'check'])->name('check');
        Route::delete('/all', [FavoritesController::class, 'destroyAll'])->name('destroy-all');
    });

    // API Alerts (AJAX)
    Route::prefix('api/alerts')->name('api.alerts.')->group(function () {
        Route::get('/', [PriceAlertsController::class, 'getAlerts'])->name('list');
        Route::get('/active', [PriceAlertsController::class, 'getActiveAlerts'])->name('active');
        Route::delete('/inactive', [PriceAlertsController::class, 'destroyInactive'])->name('destroy-inactive');
    });
    Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    # Route::get('/preferiti', [FavoritesController::class, 'index'])->name('user.favorites.index');
    Route::get('/alert-prezzi', [PriceAlertsController::class, 'index'])->name('alerts.index');
});

    // CRUD Crociere (Admin)
    Route::get('/admin/cruises', [CruiseController::class, 'index'])->name('cruises.index');
    Route::get('/admin/cruises/create', [CruiseController::class, 'create'])->name('cruises.create');
    Route::post('/admin/cruises', [CruiseController::class, 'store'])->name('cruises.store');
    Route::get('/admin/cruises/{cruise}', [CruiseController::class, 'show'])->name('cruises.show');
    Route::get('/admin/cruises/{cruise}/edit', [CruiseController::class, 'edit'])->name('cruises.edit');
    Route::put('/admin/cruises/{cruise}', [CruiseController::class, 'update'])->name('cruises.update');
    Route::delete('/admin/cruises/{cruise}', [CruiseController::class, 'destroy'])->name('cruises.destroy');

    // Route aggiuntive per CRUD Crociere
    Route::get('/admin/cruises-data', [CruiseController::class, 'getData'])->name('cruises.data');
    Route::get('/admin/cruises-stats', [CruiseController::class, 'getStats'])->name('cruises.stats');
    Route::post('/admin/cruises/bulk-delete', [CruiseController::class, 'bulkDelete'])->name('cruises.bulk-delete');
    Route::get('/admin/cruises/export', [CruiseController::class, 'export'])->name('cruises.export');

    // Import Crociere (come funzionalitÃ  aggiuntiva)
    Route::get('/admin/import-crociere', [CruiseImportController::class, 'showForm'])->name('cruises.import.form');
    Route::post('/admin/import-crociere', [CruiseImportController::class, 'import'])->name('cruises.import');
    Route::get('/admin/import-results', [CruiseImportController::class, 'showResults'])->name('cruises.import.results');
    Route::get('/admin/import-data', [CruiseImportController::class, 'getImportedCruisesData'])->name('cruises.import.data');
    Route::get('/admin/download-skipped', [CruiseImportController::class, 'downloadSkippedRecords'])->name('cruises.import.download-skipped');

    // Gestione Utenti
    Route::get('/admin/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/admin/users/data', [UserController::class, 'getUsersData'])->name('users.data');
    Route::get('/admin/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
    Route::post('/admin/users/update', [UserController::class, 'update'])->name('users.update');
    Route::post('/admin/users/lock', [UserController::class, 'lock'])->name('users.lock');
    Route::post('/admin/users/unlock', [UserController::class, 'unlock'])->name('users.unlock');
    Route::post('/admin/users/resend-verification', [UserController::class, 'resendVerification'])->name('users.resend-verification');
    Route::post('/admin/users/force-verify', [UserController::class, 'forceVerify'])->name('users.force-verify');
    Route::post('/admin/users/resend-verification', [UserController::class, 'resendVerification'])->name('users.resend-verification');
    Route::post('/admin/users/force-verify', [UserController::class, 'forceVerify'])->name('users.force-verify');

    // Rotte per gli utenti normali
    Route::get('/user/index', [UserController::class, 'index'])->name('user.index');

    // Rotta generica per gli utenti autenticati
    Route::get('/home', function () {
        return view('home');
    })->name('home');
});


// Route per Analytics (solo admin) - Raggruppa tutto sotto middleware auth
Route::middleware(['auth'])->group(function () {

    // === DASHBOARD PRINCIPALE ===
    Route::get('/admin/analytics', [SearchAnalyticsController::class, 'index'])
        ->name('admin.analytics.index');

    // === API STATISTICHE BASE ===
    Route::prefix('api/analytics')->name('api.analytics.')->group(function () {

        // Statistiche generali
        Route::get('/general-stats', [SearchAnalyticsController::class, 'getGeneralStats'])
            ->name('general-stats');

        // Trend e serie temporali
        Route::get('/search-trends', [SearchAnalyticsController::class, 'getSearchTrends'])
            ->name('search-trends');

        // Dispositivi e browser
        Route::get('/device-stats', [SearchAnalyticsController::class, 'getDeviceStats'])
            ->name('device-stats');

        // Statistiche geografiche
        Route::get('/geographic-stats', [SearchAnalyticsController::class, 'getGeographicStats'])
            ->name('geographic-stats');

        // Parametri di ricerca
        Route::get('/search-parameters', [SearchAnalyticsController::class, 'getSearchParametersStats'])
            ->name('search-parameters');

        // Performance metrics
        Route::get('/performance-metrics', [SearchAnalyticsController::class, 'getPerformanceMetrics'])
            ->name('performance-metrics');

        // Log ricerche con paginazione
        Route::get('/search-logs', [SearchAnalyticsController::class, 'getSearchLogs'])
            ->name('search-logs');

        // === API AVANZATE ===

        // Time analytics
        Route::get('/time-heatmap', [SearchAnalyticsController::class, 'getTimeHeatmap'])
            ->name('time-heatmap');

        // Conversion funnel
        Route::get('/advanced-funnel', [SearchAnalyticsController::class, 'getAdvancedFunnel'])
            ->name('advanced-funnel');

        // Error analytics
        Route::get('/error-analytics', [SearchAnalyticsController::class, 'getErrorAnalytics'])
            ->name('error-analytics');

        // Browser compatibility
        Route::get('/browser-compatibility', [SearchAnalyticsController::class, 'getBrowserCompatibility'])
            ->name('browser-compatibility');

        // Real-time stats
        Route::get('/real-time', [SearchAnalyticsController::class, 'getRealTimeStats'])
            ->name('real-time');

        // Executive summary
        Route::get('/executive-summary', [SearchAnalyticsController::class, 'getExecutiveSummary'])
            ->name('executive-summary');

        // === AZIONI AMMINISTRATIVE ===

        // Pulizia log (solo DELETE per sicurezza)
        Route::delete('/cleanup', [SearchAnalyticsController::class, 'cleanupOldLogs'])
            ->name('cleanup');

        // Report settimanale
        Route::get('/weekly-report', [SearchAnalyticsController::class, 'generateWeeklyReport'])
            ->name('weekly-report');
    });

    // === EXPORT E DOWNLOAD ===
    Route::get('/admin/analytics/export', [SearchAnalyticsController::class, 'exportCsv'])
        ->name('admin.analytics.export');



    // PULIZIA CACHE ANALYTICS
    Route::get('/admin/analytics/clear-cache', [SearchAnalyticsController::class, 'clearAnalyticsCache'])
        ->name('analytics.clear-cache');
});
