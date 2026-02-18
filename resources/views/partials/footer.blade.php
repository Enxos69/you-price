{{-- resources/views/partials/footer.blade.php --}}
<footer class="footer-split">
    <div class="fs-top">
        {{-- SINISTRA: brand + CTA --}}
        <div class="fs-left">
            <a href="{{ url('/') }}">
                <img src="{{ config('app.asset_url') ? config('app.asset_url') . '/img/logo.png' : asset('assets/img/logo.png') }}"
                     alt="You-Price" class="fs-logo">
            </a>
            <p>Il primo servizio di crociere dove sei tu a decidere il prezzo. Trova la crociera dei tuoi sogni al budget che hai in testa.</p>
            <a href="{{ route('crociere.index') }}" class="fs-cta">
                <i class="fas fa-search"></i> Fai la tua Ricerca!
            </a>
            <div class="fs-social">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" aria-label="X"><i class="fab fa-x-twitter"></i></a>
            </div>
        </div>

        {{-- DESTRA: link --}}
        <div class="fs-right">
            <div class="fs-col">
                <h4>Naviga</h4>
                <ul>
                    <li><a href="{{ url('/') }}"><i class="fas fa-angle-right"></i>Home</a></li>
                    <li><a href="{{ route('crociere.index') }}"><i class="fas fa-angle-right"></i>Cerca Crociere</a></li>
                    <li><a href="{{ url('/#come-funziona') }}"><i class="fas fa-angle-right"></i>Come Funziona</a></li>
                    @guest
                        <li><a href="{{ route('register') }}"><i class="fas fa-angle-right"></i>Registrati</a></li>
                        <li><a href="{{ route('login') }}"><i class="fas fa-angle-right"></i>Accedi</a></li>
                    @endguest
                    @auth
                        <li><a href="{{ route('dashboard') }}"><i class="fas fa-angle-right"></i>Dashboard</a></li>
                    @endauth
                </ul>
            </div>
            <div class="fs-col">
                <h4>Account</h4>
                <ul>
                    @auth
                        <li><a href="{{ route('dashboard') }}"><i class="fas fa-angle-right"></i>La Mia Dashboard</a></li>
                        {{-- <li><a href="{{ route('alerts.index') }}"><i class="fas fa-angle-right"></i>Alert Prezzi</a></li> --}}
                    @else
                        <li><a href="{{ route('register') }}"><i class="fas fa-angle-right"></i>Crea Account</a></li>
                        <li><a href="{{ route('login') }}"><i class="fas fa-angle-right"></i>Accedi</a></li>
                    @endauth
                </ul>
                <h4 class="fs-col-spacer">Legale</h4>
                <ul>
                    <li><a href="{{ route('privacy') }}"><i class="fas fa-angle-right"></i>Privacy Policy</a></li>
                    <li><a href="{{ route('cookie') }}"><i class="fas fa-angle-right"></i>Cookie Policy</a></li>
                    <li><a href="{{ route('termini') }}"><i class="fas fa-angle-right"></i>Termini di Servizio</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="fs-bottom">
        <span>© {{ date('Y') }} You-Price.it — Tutti i diritti riservati</span>
        <div class="fs-bottom-links">
            <a href="{{ route('privacy') }}">Privacy</a>
            <a href="{{ route('cookie') }}">Cookie</a>
            <a href="{{ route('termini') }}">Termini</a>
        </div>
    </div>
</footer>
