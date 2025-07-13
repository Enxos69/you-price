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
            <!-- Menu orizzontale per ruolo -->
            @auth               
                   
                        @if(Auth::user()->isAdmin())
                        <li class="nav-item dropdown">
                            <a class="dropdown-item" href="#">Dashboard</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="dropdown-item" href="{{ route('user.index') }}">Gestione Utenti</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="dropdown-item" href="#">Impostazioni</a>
                        </li>
                        @elseif(Auth::user()->isUser())                            
                            <a class="dropdown-item" href="#">Profilo</a>
                            <a class="dropdown-item" href="{{ route('richieste.index') }}">Le Mie Richieste</a>
                            <a class="dropdown-item" href="#">Supporto</a>
                        @endif
                    </div>
                </li>
            @endauth
        </ul>
        <ul class="navbar-nav ml-auto">
            @if (Route::has('login'))
                @auth
                    <li class="nav-item w-100">  
                        <a class="nav-link" href="{{ url('/home') }}">
                            <i class="fa-solid fa-house"></i> Home
                        </a>
                        <a class="nav-link ml-3" href="{{ route('logout') }}"
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
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus"></i> Registrati wewe
                            </a>
                        </li>
                    @endif                       
                    </li>
                @endauth
            @endif
        </ul>
    </div>
</nav>
