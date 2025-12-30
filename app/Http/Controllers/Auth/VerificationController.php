<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

class VerificationController extends Controller
{
    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // IMPORTANTE: guest per verify, auth per show e resend
        $this->middleware('guest')->only('verify');
        $this->middleware('auth')->only(['show', 'resend']);
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Show the email verification notice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect($this->redirectPath())->with('verified', true)
            : view('auth.verify');
    }

    /**
     * Mark the authenticated user's email address as verified.
     * OVERRIDE per permettere verifica anche senza login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        // Trova l'utente dall'ID nella URL
        $user = User::findOrFail($request->route('id'));

        // Verifica che l'hash corrisponda
        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return redirect('/login')->with('error', 'Link di verifica non valido o scaduto.');
        }

        // Verifica se è già verificato
        if ($user->hasVerifiedEmail()) {
            return redirect('/login')->with('status', 'Email già verificata! Puoi effettuare il login.');
        }

        // Marca come verificato E abilita l'utente
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            // Abilita automaticamente l'utente alla verifica email
            $user->abilitato = true;
            $user->save();
        }

        return redirect('/login')->with('verified', 'Email verificata con successo! Ora puoi effettuare il login.');
    }

    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectPath())->with('verified', true);
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('resent', 'Link di verifica inviato! Controlla la tua email.');
    }
}
