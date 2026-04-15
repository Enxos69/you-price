@extends('layouts.app')

@section('content')
<div class="cruise-detail-page">

  {{-- ═══ HERO ════════════════════════════════════════════════════════════ --}}
  @php
    $ship      = $departure->product->ship;
    $line      = $departure->product->cruiseLine;
    $portFrom  = $departure->product->portFrom;
    $portTo    = $departure->product->portTo;
    $area      = $departure->product->area;
    $itinerary = $departure->product->itinerary;
  @endphp

  <div class="cd-hero"
       @if($ship->image_url) style="background-image: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.65)), url('{{ $ship->image_url }}');" @endif>
    <div class="container-fluid px-4">
      <div class="d-flex align-items-start justify-content-between flex-wrap">
        <div>
          <div class="cd-hero__eyebrow">
            {{ $line->name ?? 'N/D' }}
            @if($area) · {{ $area->name }} @endif
          </div>
          <h1 class="cd-hero__title">{{ $departure->product->cruise_name }}</h1>
          <p class="cd-hero__subtitle">
            <i class="fas fa-ship mr-1"></i> {{ $ship->name ?? 'N/D' }}
            @if($departure->product->is_package)
              <span class="cd-badge cd-badge--green ml-2"><i class="fas fa-box mr-1"></i>Pacchetto</span>
            @endif
          </p>
        </div>
        <div class="d-flex mt-2">
          <a href="javascript:history.back()" class="btn btn-outline-light btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Torna ai risultati
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══ BARRA FATTI RAPIDI ═══════════════════════════════════════════════ --}}
  <div class="cd-facts-bar">
    <div class="cd-fact">
      <i class="fas fa-calendar-alt"></i>
      <span class="cd-fact__value">{{ $departure->dep_date->format('d/m/Y') }}</span>
      <span class="cd-fact__label">Partenza</span>
    </div>
    <div class="cd-fact">
      <i class="fas fa-flag-checkered"></i>
      <span class="cd-fact__value">{{ $departure->arr_date->format('d/m/Y') }}</span>
      <span class="cd-fact__label">Ritorno</span>
    </div>
    <div class="cd-fact">
      <i class="fas fa-moon"></i>
      <span class="cd-fact__value">{{ $departure->duration }} nott{{ $departure->duration === 1 ? 'e' : 'i' }}</span>
      <span class="cd-fact__label">Durata</span>
    </div>
    <div class="cd-fact">
      <i class="fas fa-anchor"></i>
      <span class="cd-fact__value">{{ $portFrom->name ?? 'N/D' }}</span>
      <span class="cd-fact__label">Porto partenza</span>
    </div>
    <div class="cd-fact">
      <i class="fas fa-map-marker-alt"></i>
      <span class="cd-fact__value">{{ $portTo->name ?? 'N/D' }}</span>
      <span class="cd-fact__label">Porto arrivo</span>
    </div>
    <div class="cd-fact cd-fact--price">
      <i class="fas fa-euro-sign"></i>
      <span class="cd-fact__value">{{ \App\Models\Departure::formatPrice($departure->min_price) }}</span>
      <span class="cd-fact__label">Da / persona</span>
    </div>
  </div>

  {{-- ═══ LAYOUT PRINCIPALE ═════════════════════════════════════════════════ --}}
  <div class="container-fluid px-3 px-md-4 mt-4 mb-5">
    <div class="cd-main-layout">

      {{-- COLONNA SINISTRA --}}
      <div class="cd-left-col">
        {{-- ── ITINERARIO ─────────────────────────────────────────────── --}}
        @if($itinerary->isNotEmpty())
        <div class="cd-section">
          <div class="cd-section__header">
            <i class="fas fa-map-marked-alt"></i>
            <h2>Itinerario</h2>
          </div>
          <div class="cd-section__body">
            <ul class="cd-itinerary">
              @foreach($itinerary as $stop)
              @php
                $isFirst = $loop->first;
                $isLast  = $loop->last;
              @endphp
              <li class="cd-itin-item">
                <div class="cd-itin-day {{ $isFirst || $isLast ? 'cd-itin-day--accent' : '' }}">
                  Gg {{ $stop->day_number }}
                </div>
                <div class="cd-itin-info">
                  <div class="cd-itin-port">
                    {{ $stop->port->name ?? 'N/D' }}
                    @if($isFirst) <span class="cd-itin-tag">imbarco</span> @endif
                    @if($isLast)  <span class="cd-itin-tag cd-itin-tag--end">sbarco</span> @endif
                  </div>
                  <div class="cd-itin-times">
                    @if($stop->arrival_time)
                      <span><i class="fas fa-circle text-success" style="font-size:7px;vertical-align:middle;"></i> arr. {{ \Carbon\Carbon::parse($stop->arrival_time)->format('H:i') }}</span>
                    @endif
                    @if($stop->departure_time)
                      <span><i class="fas fa-circle text-danger" style="font-size:7px;vertical-align:middle;"></i> prt. {{ \Carbon\Carbon::parse($stop->departure_time)->format('H:i') }}</span>
                    @endif
                    @if(!$stop->arrival_time && !$stop->departure_time)
                      <span class="text-muted" style="font-style:italic;">navigazione in mare</span>
                    @endif
                  </div>
                </div>
              </li>
              @endforeach
            </ul>
          </div>
        </div>
        @endif

        {{-- ── CABINE & PREZZI ─────────────────────────────────────────── --}}
        @if($cabins->isNotEmpty())
        <div class="cd-section">
          <div class="cd-section__header">
            <i class="fas fa-bed"></i>
            <h2>Tipologie di cabina e prezzi</h2>
          </div>
          <div class="cd-section__body">
            <div class="cd-cabins-grid">
              @foreach($cabins as $cabin)
              <div class="cd-cabin-card">
                <div class="cd-cabin-img">
                  @if($cabin['image_url'])
                    <img src="{{ $cabin['image_url'] }}"
                         alt="Cabina {{ $cabin['cl_cat'] }}"
                         loading="lazy">
                  @else
                    <i class="fas fa-bed fa-2x text-muted"></i>
                  @endif
                  <span class="cd-cabin-badge">{{ $cabin['cl_cat'] }}</span>
                </div>
                <div class="cd-cabin-body">
                  <div class="cd-cabin-code">{{ $cabin['category_code'] }}</div>
                  @if($cabin['description'])
                    <p class="cd-cabin-desc">{{ $cabin['description'] }}</p>
                  @endif
                  <div class="cd-cabin-price">
                    {{ \App\Models\Departure::formatPrice($cabin['price']) }}
                    <span class="cd-cabin-price-sub">/ persona</span>
                  </div>
                  @if($cabin['recorded_at'])
                    <div class="cd-cabin-updated">
                      agg. {{ \Carbon\Carbon::parse($cabin['recorded_at'])->format('d/m/Y') }}
                    </div>
                  @endif
                </div>
              </div>
              @endforeach
            </div>
            <p class="cd-cabins-note">* Prezzi per persona in camera doppia. Soggetti a variazioni.</p>
          </div>
        </div>
        @endif

        {{-- ── NAVE ──────────────────────────────────────────────────────── --}}
        <div class="cd-section">
          <div class="cd-section__header">
            <i class="fas fa-ship"></i>
            <h2>La Nave — {{ $ship->name }}</h2>
          </div>
          <div class="cd-section__body">
            @if($ship->description)
              <p class="cd-ship-desc">{{ $ship->description }}</p>
            @endif

            @if(!empty($ship->features))
              <div class="cd-ship-features">
                @foreach($ship->features as $feature)
                  <span class="cd-feature-pill">{{ $feature }}</span>
                @endforeach
              </div>
            @endif

            @if(!empty($ship->decks))
              @php
                $deckCount = count($ship->decks);
              @endphp
              <div class="cd-ship-stats mt-3">
                <div class="cd-ship-stat">
                  <span class="cd-ship-stat__value">{{ $deckCount }}</span>
                  <span class="cd-ship-stat__label">Ponti</span>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- SIDEBAR --}}
      <div class="cd-sidebar">
        {{-- ── PRICE BOX ───────────────────────────────────────────────── --}}
        <div class="cd-price-box">
          <div class="cd-price-box__header">
            <p class="cd-price-from">Prezzo a partire da</p>
            <div class="cd-price-main">{{ \App\Models\Departure::formatPrice($departure->min_price) }}</div>
            <p class="cd-price-sub">per persona · camera doppia · tasse incluse</p>
          </div>
          <div class="cd-price-box__body">
            @if($cabins->isNotEmpty())
              <div class="cd-price-box__cats-label">Tutte le categorie</div>
              @foreach($cabins as $cabin)
                <div class="cd-price-row">
                  <span class="cd-price-row__cat">{{ $cabin['cl_cat'] ?: $cabin['category_code'] }}</span>
                  <span class="cd-price-row__val">{{ \App\Models\Departure::formatPrice($cabin['price']) }}</span>
                </div>
              @endforeach
            @endif

            <a href="{{ route('richiesta.store') }}"
               class="btn btn-success btn-block cd-btn-cta mt-3">
              <i class="fas fa-paper-plane mr-2"></i>Richiedi preventivo
            </a>

            <button id="cd-favorite-btn"
                    class="btn btn-block mt-2 {{ $isFavorite ? 'btn-danger' : 'btn-outline-danger' }}"
                    data-departure-id="{{ $departure->id }}"
                    data-is-favorite="{{ $isFavorite ? '1' : '0' }}">
              <i id="cd-favorite-icon" class="{{ $isFavorite ? 'fas' : 'far' }} fa-heart mr-2"></i>
              <span id="cd-favorite-text">{{ $isFavorite ? 'Rimuovi dai Preferiti' : 'Aggiungi ai Preferiti' }}</span>
            </button>
          </div>
        </div>

        {{-- ── ALERT PREZZO ─────────────────────────────────────────────── --}}
        <div class="cd-alert-box">
          <h3><i class="fas fa-bell mr-2 text-warning"></i>Monitora il prezzo</h3>
          <p>Ricevi una notifica email quando il prezzo scende sotto la soglia.</p>
          <a href="{{ route('alerts.index') }}" class="btn btn-warning btn-sm btn-block">
            Gestisci i tuoi alert
          </a>
        </div>

        {{-- ── RIEPILOGO RAPIDO ─────────────────────────────────────────── --}}
        <div class="cd-info-mini">
          <div class="cd-info-mini__title">Riepilogo</div>
          <div class="cd-info-row">
            <span class="cd-info-row__lbl">Compagnia</span>
            <span class="cd-info-row__val">{{ $line->name ?? 'N/D' }}</span>
          </div>
          @if($area)
          <div class="cd-info-row">
            <span class="cd-info-row__lbl">Area</span>
            <span class="cd-info-row__val">{{ $area->name }}</span>
          </div>
          @endif
          <div class="cd-info-row">
            <span class="cd-info-row__lbl">Tipo</span>
            <span class="cd-info-row__val">{{ $departure->product->is_package ? 'Pacchetto' : 'Solo crociera' }}</span>
          </div>
          <div class="cd-info-row">
            <span class="cd-info-row__lbl">Codice</span>
            <span class="cd-info-row__val" style="font-size:11px;">{{ $departure->product->matchcode ?? $departure->id }}</span>
          </div>
        </div>
      </div>

    </div>{{-- /cd-main-layout --}}
  </div>

</div>{{-- /cruise-detail-page --}}
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const btn  = document.getElementById('cd-favorite-btn');
  if (!btn) return;

  btn.addEventListener('click', async function () {
    const depId = btn.dataset.departureId;
    btn.disabled = true;

    try {
      const res = await fetch(`/departures/${depId}/favorite/toggle`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
      });
      const data = await res.json();

      if (!data.success) throw new Error(data.message || 'Errore');

      const icon = document.getElementById('cd-favorite-icon');
      const text = document.getElementById('cd-favorite-text');

      if (data.is_favorite) {
        btn.classList.replace('btn-outline-danger', 'btn-danger');
        icon.classList.replace('far', 'fas');
        text.textContent = 'Rimuovi dai Preferiti';
        btn.dataset.isFavorite = '1';
      } else {
        btn.classList.replace('btn-danger', 'btn-outline-danger');
        icon.classList.replace('fas', 'far');
        text.textContent = 'Aggiungi ai Preferiti';
        btn.dataset.isFavorite = '0';
      }
    } catch (e) {
      alert('Errore durante l\'operazione');
    } finally {
      btn.disabled = false;
    }
  });
});
</script>
@endsection
