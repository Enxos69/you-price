{{-- resources/views/partials/navbar.blade.php --}}
@php $isHome = request()->is('/'); @endphp

<nav class="navbar navbar-expand-lg fixed-top {{ $isHome ? 'navbar-glass' : 'navbar-premium' }}">

    <a class="navbar-brand" href="/">
        <img src="{{ config('app.asset_url') ? config('app.asset_url') . '/img/logo.png' : asset('assets/img/logo.png') }}"
             alt="You-Price"
             class="{{ $isHome ? 'logo-white' : '' }}">
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">

        <ul class="navbar-nav mx-auto">
            @auth
                @if(Auth::user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.index') }}">
                            <i class="fas fa-users"></i> Gestione Utenti
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cruises.index') }}">
                            <i class="fas fa-ship"></i> Gestione Crociere
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.analytics.index') }}">
                            <i class="fas fa-chart-bar"></i> Analytics
                        </a>
                    </li>
                @elseif(Auth::user()->isUser())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> La Mia Dashboard
                        </a>
                    </li>
                @endif
            @endauth
        </ul>

        <ul class="navbar-nav navbar-right-group">
            @if(Route::has('login'))
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/home') }}">
                            <i class="fa-solid fa-house"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('crociere.index') }}" class="btn btn-navbar-cta">
                            <i class="fas fa-search"></i> Fai la tua Ricerca!
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-logout" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                            @csrf
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    @if(Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus"></i> Registrati
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('crociere.index') }}" class="btn btn-navbar-cta">
                            <i class="fas fa-search"></i> Fai la tua Ricerca!
                        </a>
                    </li>
                @endauth
            @endif
        </ul>

    </div>
</nav>