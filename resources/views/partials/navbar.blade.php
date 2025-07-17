<!-- resources/views/partials/navbar.blade.php -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <a class="navbar-brand" href="/">
        <img src="{{ config('app.asset_url') }}/img/logo.png" alt="Logo" style="height: 50px;">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav"
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto">
            <!-- Menu orizzontale -->
            @auth
                <!-- Controllo semplice per admin: se l'utente ha ID 1 o email contiene admin -->
                @if (Auth::user()->id == 1 || strpos(Auth::user()->email, 'admin') !== false)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.index') }}">Dashboard Admin</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.index') }}">Gestione Utenti</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cruises.import.form') }}">Importa Crociere</a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.index') }}">Profilo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('richieste.index') }}">Le Mie Richieste</a>
                    </li>
                @endif
            @endauth
        </ul>
        <ul class="navbar-nav ml-auto">
            @if (Route::has('login'))
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/home') }}">
                            <i class="fa-solid fa-house"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ route('crociere.index') }}" class="btn btn-primary ml-3">
                            Fai la tua Ricerca!
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus"></i> Registrati
                            </a>
                        </li>
                    @endif
                @endauth
            @endif
        </ul>
    </div>
</nav>