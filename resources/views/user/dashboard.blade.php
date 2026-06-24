@extends('layouts.app')

@section('title', 'Dashboard - You Price')

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
