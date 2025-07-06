<?php

// routes/web.php
use Livewire\Livewire;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CrocieraController;
use App\Http\Controllers\RichiestaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ConfirmPasswordController;

Route::get('/', function () {
    return view('index');
});

// Route di default per autenticazione
//Auth::routes();

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

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('/crociere', [CrocieraController::class, 'index'])->name('crociere.index');
Route::post('/crociere/search', [CrocieraController::class, 'search'])->name('crociere.search');
Route::get('/crociere/stats', [CrocieraController::class, 'getStats'])->name('crociere.stats');


Route::middleware('auth')->group(function () {
    // Rotte per gli admin
    Route::get('/admin/index', [AdminController::class, 'index'])->name('admin.index');


    // Rotte per gli utenti
    Route::get('/user/index', [UserController::class, 'index'])->name('user.index');

    // Rotta generica per gli utenti autenticati
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    Route::get('/admin/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/admin/users/data', [UserController::class, 'getUsersData'])->name('users.data');
    Route::get('/admin/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
    Route::post('/admin/users/update', [UserController::class, 'update'])->name('users.update');
    Route::post('/admin/users/lock', [UserController::class, 'lock'])->name('users.lock');
    Route::post('/admin/users/unlock', [UserController::class, 'unlock'])->name('users.unlock');

    // RICHIESTE UTENTE
    Route::get('/richieste', [RichiestaController::class, 'index'])->name('richieste.index');
    Route::get('/richieste/create', [RichiestaController::class, 'create'])->name('richieste.create');
    Route::post('/richieste', [RichiestaController::class, 'store'])->name('richieste.store');
    Route::get('/richieste/{id}', [RichiestaController::class, 'show'])->name('richieste.show');
    Route::get('/richieste/{id}/edit', [RichiestaController::class, 'edit'])->name('richieste.edit');
    Route::post('/richieste/update', [RichiestaController::class, 'update'])->name('richieste.update');
    Route::delete('/richieste/{id}', [RichiestaController::class, 'destroy'])->name('richieste.destroy');
});
