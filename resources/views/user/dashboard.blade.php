@extends('layouts.app')

@section('title', 'Dashboard - You Price')

@section('styles')
<style>
  /* ============================================================================
   DASHBOARD STYLES - You Price
   ============================================================================ */

:root {
    --db-teal:        #1a7a8a;
    --db-teal-dark:   #145f6d;
    --db-teal-light:  #2a9aaa;
    --db-green:       #4caf50;
    --db-bg:          #f4f7f9;
    --db-card-shadow: 0 2px 8px rgba(0,0,0,.08);
}

body { background: var(--db-bg); }

/* Annulla il py-4 hardcoded nel <main> del layout */
main.py-4 { padding-top: 0 !important; padding-bottom: 0 !important; }

/* ── Profile Header ───────────────────────────────────────────────────────── */
.dash-profile-header {
    background: linear-gradient(135deg, var(--db-teal-dark) 0%, var(--db-teal) 60%, var(--db-teal-light) 100%);
    color: #fff;
    padding: 1.75rem 0 1.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
}

.dash-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(255,255,255,.25);
    border: 3px solid rgba(255,255,255,.6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: 1px;
    flex-shrink: 0;
}

.dash-profile-header h4  { font-weight: 700; margin-bottom: .25rem; }
.dash-profile-header p   { font-size: .9rem; opacity: .85; margin-bottom: .4rem; }

.dash-pill {
    display: inline-block;
    background: rgba(255,255,255,.2);
    border: 1px solid rgba(255,255,255,.35);
    border-radius: 20px;
    padding: .2rem .75rem;
    font-size: .78rem;
    font-weight: 500;
    color: #fff;
    margin-right: .4rem;
    margin-top: .2rem;
}

/* ── Stat Cards ───────────────────────────────────────────────────────────── */
.stat-card {
    background: #fff;
    border-radius: 10px;
    padding: 1.25rem 1.5rem;
    box-shadow: var(--db-card-shadow);
    border-left: 4px solid var(--db-teal);
    transition: transform .2s, box-shadow .2s;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0,0,0,.12);
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: var(--db-teal);
    line-height: 1;
    margin-bottom: .3rem;
}

.stat-icon { color: var(--db-teal); opacity: .2; }

/* ── Section Headers ──────────────────────────────────────────────────────── */
.dash-section-title {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #aaa;
    margin-bottom: .75rem;
}

/* ── Cards ────────────────────────────────────────────────────────────────── */
.card {
    border: none;
    border-radius: 10px;
    box-shadow: var(--db-card-shadow);
}

.card-header {
    background: #fff;
    border-bottom: 1px solid #f0f0f0;
    border-radius: 10px 10px 0 0 !important;
    padding: .9rem 1.25rem;
    font-weight: 600;
    font-size: .95rem;
    color: #333;
}

.card-body { padding: 1.25rem; }

/* ── Recent Search Items ──────────────────────────────────────────────────── */
.search-item {
    padding: .85rem 1rem;
    border-bottom: 1px solid #f5f5f5;
    transition: background .15s;
}

.search-item:last-child { border-bottom: none; }

.search-item:hover { background: #f9fbfc; }

.search-item__params { font-weight: 600; font-size: .9rem; color: #333; margin-bottom: .2rem; }
.search-item__meta   { font-size: .78rem; color: #888; }

/* ── Price Alert Card ─────────────────────────────────────────────────────── */
.alert-card-inner {
    padding: .85rem 0;
    border-bottom: 1px solid #fde8e8;
}

.alert-card-inner:last-child { border-bottom: none; padding-bottom: 0; }

.progress { height: 6px; border-radius: 6px; background: #e9ecef; }
.progress-bar { border-radius: 6px; transition: width .6s ease; }

/* ── Timeline ─────────────────────────────────────────────────────────────── */
.timeline-item {
    padding-left: 1.25rem;
    padding-bottom: 1rem;
    border-left: 2px solid #e0e0e0;
    position: relative;
    font-size: .85rem;
    color: #555;
}

.timeline-item::before {
    content: '';
    width: 10px;
    height: 10px;
    background: var(--db-teal);
    border: 2px solid #fff;
    border-radius: 50%;
    position: absolute;
    left: -6px;
    top: 2px;
    box-shadow: 0 0 0 2px rgba(26,122,138,.2);
}

.timeline-item:last-child { border-left-color: transparent; padding-bottom: 0; }
.timeline-item .time-ago  { font-size: .75rem; color: #aaa; display: block; margin-bottom: .15rem; }

/* ── Favorite Cards ───────────────────────────────────────────────────────── */
.fav-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: var(--db-card-shadow);
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.fav-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,.12);
}

.fav-card__body  { padding: 1rem; flex: 1; display: flex; flex-direction: column; }
.fav-card__ship  { font-weight: 700; font-size: .95rem; margin-bottom: .2rem; color: #222; }
.fav-card__route { font-size: .78rem; color: #888; margin-bottom: .6rem; }
.fav-card__meta  { font-size: .78rem; color: #888; margin-bottom: .75rem; }

.fav-card__price {
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--db-green);
    margin-bottom: .15rem;
}

.fav-card__price-label { font-size: .72rem; color: #aaa; }

/* ── Recommendations Banner ───────────────────────────────────────────────── */
.reco-card {
    background: linear-gradient(135deg, var(--db-teal) 0%, var(--db-teal-light) 100%);
    border-radius: 10px;
    color: #fff;
    padding: 1.5rem;
    box-shadow: var(--db-card-shadow);
}

.reco-card h6 { font-weight: 700; margin-bottom: .5rem; }
.reco-card p  { font-size: .9rem; opacity: .9; margin-bottom: 1rem; }

/* ── Floating Action Button ───────────────────────────────────────────────── */
.dash-fab {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 52px;
    height: 52px;
    background: var(--db-teal);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 4px 14px rgba(26,122,138,.5);
    transition: transform .2s, box-shadow .2s;
    z-index: 999;
    text-decoration: none;
}

.dash-fab:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(26,122,138,.6);
    color: #fff;
    text-decoration: none;
}

/* ── Badges Bootstrap 4 ───────────────────────────────────────────────────── */
.badge-teal    { background: var(--db-teal);  color: #fff; }
.badge-reached { background: var(--db-green); color: #fff; }

/* ── Responsive ───────────────────────────────────────────────────────────── */
@media (max-width: 767px) {
    .dash-profile-header { padding: 1.25rem 0 1rem; }
    .dash-avatar         { width: 50px; height: 50px; font-size: 1.1rem; }
    .stat-number         { font-size: 1.6rem; }
    .dash-fab            { bottom: 1.25rem; right: 1.25rem; }
}

/* ── Animations ───────────────────────────────────────────────────────────── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.stat-card, .card, .fav-card, .reco-card {
    animation: fadeUp .4s ease-out both;
}
</style>




@section('content')

{{-- ═══ PROFILO HEADER ═══════════════════════════════════════════════════════ --}}
<div class="dash-profile-header">
  <div class="container">
    <div class="row align-items-center">

      <div class="col-auto">
        <div class="dash-avatar">
          {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->surname, 0, 1)) }}
        </div>
      </div>

      <div class="col">
        <h4 class="mb-1">{{ $user->name }} {{ $user->surname }}</h4>
        <p class="mb-2"><i class="fas fa-envelope mr-1"></i>{{ $user->email }}</p>
        <div>
          <span class="dash-pill"><i class="fas fa-calendar mr-1"></i>Membro da {{ $stats['member_since'] }}</span>
          <span class="dash-pill"><i class="fas fa-user mr-1"></i>Utente registrato</span>
          @if($stats['favorites_count'] > 0)
            <span class="dash-pill"><i class="fas fa-heart mr-1"></i>{{ $stats['favorites_count'] }} preferit{{ $stats['favorites_count'] == 1 ? 'a' : 'e' }}</span>
          @endif
        </div>
      </div>

      <div class="col-auto d-none d-md-block">
        <a href="{{ route('crociere.index') }}" class="btn btn-light btn-sm font-weight-bold">
          <i class="fas fa-search mr-1"></i> Nuova Ricerca
        </a>
      </div>

    </div>
  </div>
</div>

{{-- ═══ CORPO ══════════════════════════════════════════════════════════════════ --}}
<div class="container mt-4 pb-5">

  {{-- ── Stat Cards ────────────────────────────────────────────────────────── --}}
  <div class="row mb-4">
    <div class="col-md-4 mb-3">
      <div class="stat-card">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="stat-number" id="stat-total-searches">{{ $stats['total_searches'] }}</div>
            <div class="text-muted small">Ricerche effettuate</div>
          </div>
          <i class="fas fa-search fa-2x stat-icon"></i>
        </div>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="stat-card">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="stat-number" id="stat-cruises-viewed">{{ $stats['cruises_viewed'] }}</div>
            <div class="text-muted small">Crociere viste</div>
          </div>
          <i class="fas fa-eye fa-2x stat-icon"></i>
        </div>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <a href="#favorites-section" class="text-decoration-none"
         onclick="event.preventDefault(); document.getElementById('favorites-section')?.scrollIntoView({behavior:'smooth'});">
        <div class="stat-card" style="cursor:pointer;">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="stat-number" id="stat-favorites-count">{{ $stats['favorites_count'] }}</div>
              <div class="text-muted small">Crociere preferite</div>
            </div>
            <i class="fas fa-heart fa-2x" style="color:#e53935; opacity:.2;"></i>
          </div>
        </div>
      </a>
    </div>
  </div>

  {{-- ── Layout 2 colonne ──────────────────────────────────────────────────── --}}
  <div class="row mb-2">

    {{-- Colonna sinistra: Ricerche Recenti ──────────────────────────────── --}}
    <div class="col-lg-7 mb-4">
      @if($recent_searches->isNotEmpty())
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="fas fa-history mr-2" style="color:#1a7a8a;"></i>Ricerche Recenti</span>
          <span class="badge badge-secondary">{{ $recent_searches->count() }}</span>
        </div>
        <div class="card-body p-0">
          @foreach($recent_searches as $search)
          <div class="search-item">
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1 mr-3">
                <div class="search-item__params">{{ $search['search_params'] ?: 'Ricerca generica' }}</div>
                <div class="search-item__meta">
                  <i class="fas fa-clock mr-1"></i>{{ $search['time_ago'] }}
                  @if($search['avg_price_found'])
                    &nbsp;·&nbsp; Prezzo medio: <strong style="color:#4caf50;">{{ $search['avg_price_found'] }}</strong>
                  @endif
                </div>
              </div>
              <div class="text-right flex-shrink-0">
                @if($search['total_matches'] > 0)
                  <span class="badge badge-success">{{ $search['total_matches'] }} risultati</span>
                @else
                  <span class="badge badge-secondary">Nessun risultato</span>
                @endif
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @else
      <div class="card">
        <div class="card-body text-center text-muted py-5">
          <i class="fas fa-search fa-3x mb-3 d-block" style="opacity:.2;"></i>
          <p class="mb-2">Nessuna ricerca ancora effettuata.</p>
          <a href="{{ route('crociere.index') }}" class="btn btn-sm" style="background:#1a7a8a; color:#fff;">
            Inizia a cercare
          </a>
        </div>
      </div>
      @endif
    </div>

    {{-- Colonna destra: Alert + Attività ────────────────────────────────── --}}
    <div class="col-lg-5 mb-4">

      {{-- Alert Prezzi Attivi --}}
      @if($price_alerts->isNotEmpty())
      <div class="card mb-4" style="border-left: 4px solid #e53935;">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="fas fa-bell mr-2 text-danger"></i>Alert Prezzi Attivi</span>
          <span class="badge badge-danger">{{ $price_alerts->count() }}</span>
        </div>
        <div class="card-body">
          @foreach($price_alerts as $alert)
          <div class="alert-card-inner">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <strong class="small">{{ $alert['ship'] }}</strong>
              @if($alert['is_reached'])
                <span class="badge badge-success">Raggiunto!</span>
              @else
                <span class="badge" style="background:#1a7a8a; color:#fff;">In monitoraggio</span>
              @endif
            </div>
            <div class="text-muted small mb-1">{{ $alert['itinerary'] }} · {{ $alert['departure_date'] }}</div>
            <div class="progress mb-1">
              <div class="progress-bar {{ $alert['is_reached'] ? 'bg-success' : '' }}"
                   style="width:{{ $alert['progress_percentage'] }}%; {{ $alert['is_reached'] ? '' : 'background:#1a7a8a;' }}">
              </div>
            </div>
            <div class="d-flex justify-content-between">
              <span class="small text-muted">Target: <strong>{{ $alert['target_price_formatted'] }}</strong></span>
              <span class="small {{ $alert['is_reached'] ? 'text-success font-weight-bold' : 'text-muted' }}">
                Ora: {{ $alert['current_price_formatted'] }}
                @if($alert['is_reached'])<i class="fas fa-check-circle ml-1"></i>@endif
              </span>
            </div>
          </div>
          @endforeach
          <a href="{{ route('alerts.index') }}" class="btn btn-sm btn-outline-danger btn-block mt-3">
            Gestisci Alert
          </a>
        </div>
      </div>
      @endif

      {{-- Attività Recente --}}
      @if($activity_timeline->isNotEmpty())
      <div class="card">
        <div class="card-header">
          <i class="fas fa-clock mr-2" style="color:#1a7a8a;"></i>Attività Recente
        </div>
        <div class="card-body">
          @foreach($activity_timeline as $activity)
          <div class="timeline-item">
            <span class="time-ago">{{ $activity['time_ago'] }}</span>
            <span>{!! $activity['description'] !!}</span>
          </div>
          @endforeach
        </div>
      </div>
      @endif

    </div>
  </div>

  {{-- ── Preferiti (tutta larghezza) ────────────────────────────────────────── --}}
  @if($favorites->isNotEmpty())
  <div class="mb-4" id="favorites-section">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-heart text-danger mr-2"></i>I Miei Preferiti</span>
        <span class="badge badge-danger">{{ $stats['favorites_count'] }} {{ $stats['favorites_count'] == 1 ? 'crociera' : 'crociere' }}</span>
      </div>
      <div class="card-body">
        <div class="row">
          @foreach($favorites as $fav)
          <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="fav-card">
              <div class="fav-card__body">
                <span class="badge {{ $fav['availability']['badge_class'] }} mb-2">{{ $fav['availability']['label'] }}</span>
                <div class="fav-card__ship">{{ $fav['ship'] }}</div>
                <div class="fav-card__route"><i class="fas fa-map-marker-alt mr-1"></i>{{ $fav['itinerary'] }}</div>
                <div class="fav-card__meta">
                  <i class="fas fa-calendar mr-1"></i>{{ $fav['departure_date'] }}
                  &nbsp;·&nbsp;
                  <i class="fas fa-moon mr-1"></i>{{ $fav['duration'] }}
                </div>
                <div class="mt-auto pt-2 d-flex justify-content-between align-items-end">
                  <div>
                    <div class="fav-card__price">{{ $fav['price_formatted'] }}</div>
                    <div class="fav-card__price-label">a persona</div>
                  </div>
                  <a href="{{ route('crociere.show', $fav['id']) }}"
                     class="btn btn-sm" style="background:#1a7a8a; color:#fff;">
                    <i class="fas fa-eye mr-1"></i>Dettagli
                  </a>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  @endif

  {{-- ── Consigli Personalizzati ─────────────────────────────────────────── --}}
  @if($recommendations)
  <div class="reco-card">
    <div class="d-flex align-items-start">
      <i class="fas fa-magic fa-2x mr-3 mt-1" style="opacity:.8;"></i>
      <div class="flex-grow-1">
        <h6><i class="fas fa-lightbulb mr-1"></i>Consiglio per te</h6>
        <p>{!! $recommendations['message'] !!}</p>
        <a href="{{ route('crociere.index') }}" class="btn btn-light btn-sm font-weight-bold">
          <i class="fas fa-arrow-right mr-1"></i>Scopri le offerte
        </a>
      </div>
    </div>
  </div>
  @endif

</div>

{{-- ── FAB Nuova Ricerca (mobile) ────────────────────────────────────────────── --}}
<a href="{{ route('crociere.index') }}" class="dash-fab d-md-none" title="Nuova Ricerca">
  <i class="fas fa-search"></i>
</a>

@endsection

@section('scripts')
<script src="{{ asset('js/dashboard.js') }}"></script>
<script>
(function () {
    fetch('{{ route('api.dashboard.stats') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json(); })
        .then(function (s) {
            var map = {
                'stat-total-searches':  s.total_searches,
                'stat-cruises-viewed':  s.cruises_viewed,
                'stat-favorites-count': s.favorites_count,
            };
            Object.keys(map).forEach(function (id) {
                var el = document.getElementById(id);
                if (el && map[id] !== undefined) el.textContent = map[id];
            });
        });
})();
</script>
@endsection
