<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCatalogController;
use App\Http\Controllers\CrocieraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\RichiestaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PriceAlertsController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SearchAnalyticsController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ConfirmPasswordController;

// Pagina principale
Route::get('/', fn() => view('index'));

// Pagine legali
Route::get('/privacy-policy',        fn() => view('legal.privacy'))->name('privacy');
Route::get('/cookie-policy',         fn() => view('legal.cookie'))->name('cookie');
Route::get('/termini-di-servizio',   fn() => view('legal.termini'))->name('termini');

// Richiesta preventivo (pubblica)
Route::post('/richiesta', [RichiestaController::class, 'store'])->name('richiesta.store');

// Autenticazione
Route::get('login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout',[LoginController::class, 'logout'])->name('logout');

Route::get('register',  [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

Route::get('password/reset',          [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email',         [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}',  [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset',         [ResetPasswordController::class, 'reset'])->name('password.update');
Route::get('email/verify',            [VerificationController::class, 'show'])->name('verification.notice');
Route::get('email/verify/{id}/{hash}',[VerificationController::class, 'verify'])->name('verification.verify');
Route::post('email/resend',           [VerificationController::class, 'resend'])->name('verification.resend');
Route::get('password/confirm',        [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
Route::post('password/confirm',       [ConfirmPasswordController::class, 'confirm']);

// ─── Crociere (pubbliche) ────────────────────────────────────────────────────
Route::get('/crociere',               [CrocieraController::class, 'index'])->name('crociere.index');
Route::post('/crociere/search',       [CrocieraController::class, 'search'])->name('crociere.search');
Route::get('/crociere/stats',         [CrocieraController::class, 'getStats'])->name('crociere.stats');
Route::get('/crociere/ports/search',  [CrocieraController::class, 'searchPorts'])->name('crociere.ports.search');
Route::get('/crociere/{id}',          [CrocieraController::class, 'show'])->name('crociere.show')->middleware('auth');

// ─── Rotte autenticate ───────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/home',      [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // API Dashboard (AJAX)
    Route::prefix('api/dashboard')->name('api.dashboard.')->group(function () {
        Route::get('/stats',     [DashboardController::class, 'getStats'])->name('stats');
        Route::get('/favorites', [DashboardController::class, 'getFavoritesJson'])->name('favorites');
        Route::get('/alerts',    [DashboardController::class, 'getAlertsJson'])->name('alerts');
        Route::get('/timeline',  [DashboardController::class, 'getActivityTimelineJson'])->name('timeline');
    });

    // Preferiti
    Route::get('/departures/{departure}/favorite/check',        [FavoritesController::class, 'check'])->name('favorites.check');
    Route::post('/departures/{departure}/favorite/toggle',      [FavoritesController::class, 'toggle'])->name('favorites.toggle');
    Route::patch('/departures/{departure}/favorite/note',       [FavoritesController::class, 'updateNote'])->name('favorites.update-note');

    Route::prefix('api/favorites')->name('api.favorites.')->group(function () {
        Route::get('/',              [FavoritesController::class, 'getFavorites'])->name('list');
        Route::delete('/all',        [FavoritesController::class, 'destroyAll'])->name('destroy-all');
    });

    // Alert Prezzi
    Route::get('/alert-prezzi',               [PriceAlertsController::class, 'index'])->name('alerts.index');
    Route::post('/alert-prezzi',              [PriceAlertsController::class, 'store'])->name('alerts.store');
    Route::patch('/alert-prezzi/{alert}',     [PriceAlertsController::class, 'update'])->name('alerts.update');
    Route::delete('/alert-prezzi/{alert}',    [PriceAlertsController::class, 'destroy'])->name('alerts.destroy');
    Route::post('/alert-prezzi/{alert}/toggle',[PriceAlertsController::class, 'toggleActive'])->name('alerts.toggle');

    Route::prefix('api/alerts')->name('api.alerts.')->group(function () {
        Route::get('/',          [PriceAlertsController::class, 'getAlerts'])->name('list');
        Route::get('/active',    [PriceAlertsController::class, 'getActiveAlerts'])->name('active');
        Route::delete('/inactive',[PriceAlertsController::class, 'destroyInactive'])->name('destroy-inactive');
    });

    // ─── Admin ───────────────────────────────────────────────────────────────

    Route::get('/admin/index', [AdminController::class, 'index'])->name('admin.index');

    // Gestione Catalogo CruiseHost
    Route::get('/admin/catalog',                            [AdminCatalogController::class, 'index'])->name('admin.catalog.index');
    Route::post('/admin/catalog/sync',                      [AdminCatalogController::class, 'startSync'])->name('admin.catalog.sync');
    Route::get('/admin/catalog/sync/{logId}/status',        [AdminCatalogController::class, 'syncStatus'])->name('admin.catalog.sync.status');
    Route::get('/admin/catalog/history',                    [AdminCatalogController::class, 'history'])->name('admin.catalog.history');
    Route::post('/admin/catalog/sync/{logId}/stop',         [AdminCatalogController::class, 'stopSync'])->name('admin.catalog.sync.stop');

    // Gestione Utenti
    Route::get('/admin/users',                              [UserController::class, 'index'])->name('users.index');
    Route::get('/admin/users/data',                         [UserController::class, 'getUsersData'])->name('users.data');
    Route::get('/admin/users/edit/{id}',                    [UserController::class, 'edit'])->name('users.edit');
    Route::post('/admin/users/update',                      [UserController::class, 'update'])->name('users.update');
    Route::post('/admin/users/lock',                        [UserController::class, 'lock'])->name('users.lock');
    Route::post('/admin/users/unlock',                      [UserController::class, 'unlock'])->name('users.unlock');
    Route::post('/admin/users/resend-verification',         [UserController::class, 'resendVerification'])->name('users.resend-verification');
    Route::post('/admin/users/force-verify',                [UserController::class, 'forceVerify'])->name('users.force-verify');

    // Analytics
    Route::get('/admin/analytics', [SearchAnalyticsController::class, 'index'])->name('admin.analytics.index');

    Route::prefix('api/analytics')->name('api.analytics.')->group(function () {
        Route::get('/general-stats',        [SearchAnalyticsController::class, 'getGeneralStats'])->name('general-stats');
        Route::get('/search-trends',        [SearchAnalyticsController::class, 'getSearchTrends'])->name('search-trends');
        Route::get('/device-stats',         [SearchAnalyticsController::class, 'getDeviceStats'])->name('device-stats');
        Route::get('/geographic-stats',     [SearchAnalyticsController::class, 'getGeographicStats'])->name('geographic-stats');
        Route::get('/search-parameters',    [SearchAnalyticsController::class, 'getSearchParametersStats'])->name('search-parameters');
        Route::get('/performance-metrics',  [SearchAnalyticsController::class, 'getPerformanceMetrics'])->name('performance-metrics');
        Route::get('/search-logs',          [SearchAnalyticsController::class, 'getSearchLogs'])->name('search-logs');
        Route::get('/time-heatmap',         [SearchAnalyticsController::class, 'getTimeHeatmap'])->name('time-heatmap');
        Route::get('/advanced-funnel',      [SearchAnalyticsController::class, 'getAdvancedFunnel'])->name('advanced-funnel');
        Route::get('/error-analytics',      [SearchAnalyticsController::class, 'getErrorAnalytics'])->name('error-analytics');
        Route::get('/browser-compatibility',[SearchAnalyticsController::class, 'getBrowserCompatibility'])->name('browser-compatibility');
        Route::get('/real-time',            [SearchAnalyticsController::class, 'getRealTimeStats'])->name('real-time');
        Route::get('/executive-summary',    [SearchAnalyticsController::class, 'getExecutiveSummary'])->name('executive-summary');
        Route::delete('/cleanup',           [SearchAnalyticsController::class, 'cleanupOldLogs'])->name('cleanup');
        Route::get('/weekly-report',        [SearchAnalyticsController::class, 'generateWeeklyReport'])->name('weekly-report');
    });

    Route::get('/admin/analytics/export',       [SearchAnalyticsController::class, 'exportCsv'])->name('admin.analytics.export');
    Route::get('/admin/analytics/clear-cache',  [SearchAnalyticsController::class, 'clearAnalyticsCache'])->name('analytics.clear-cache');

});

// Endpoint interno per sync asincrono — protetto da token, niente auth/CSRF
Route::post('/internal/catalog-sync', [AdminCatalogController::class, 'runInternalSync']);
