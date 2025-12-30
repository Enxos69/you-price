<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Override the authenticated method to customize redirection based on user role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Contracts\Auth\User  $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function authenticated(Request $request, $user)
    {     
        // CONTROLLO 1: Email deve essere verificata
        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            return redirect()->route('login')
                ->with('warning', 'Devi verificare la tua email prima di accedere. Controlla la tua casella di posta e clicca sul link di verifica.');
        }

        // CONTROLLO 2: Utente deve essere abilitato
        if (!$user->abilitato) {
            Auth::logout();
            return redirect()->route('login')
                ->with('status', 'L\'utente non è abilitato. Contatta l\'amministratore.');
        }

        // Implementa la logica di redirezione basata sul ruolo
        if ($user->role === '1') {
            return redirect()->route('admin.index');
        } elseif ($user->role === '2') {
            return redirect()->route('user.index');
        } else {
            return redirect()->intended('/home');
        }
    }
}