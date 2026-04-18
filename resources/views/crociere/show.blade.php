@extends('layouts.app')

@section('content')
<style>
/* ── CRUISE DETAIL PAGE ──────────────────────────────────────────── */
.cruise-detail-page { background: #f4f6f8; min-height: 100vh; }

/* Hero */
.cd-hero {
  background: linear-gradient(135deg, #0d4f5c 0%, #1a7a8a 100%);
  background-size: cover;
  background-position: center;
  color: #fff;
  padding: 80px 0 32px;
}
.cd-hero__eyebrow { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; opacity: .75; margin-bottom: 6px; }
.cd-hero__title   { font-size: 28px; font-weight: 700; margin-bottom: 6px; line-height: 1.25; }
.cd-hero__subtitle { font-size: 15px; opacity: .85; margin-bottom: 0; }
.cd-badge { display: inline-block; border-radius: 20px; padding: 2px 10px; font-size: 12px; background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.3); }
.cd-badge--green  { background: #4caf50; border-color: #4caf50; }

/* Barra fatti */
.cd-facts-bar { background: #fff; border-bottom: 2px solid #e8ecef; display: flex; overflow-x: auto; padding: 0 8px; }
.cd-fact { flex: 1; min-width: 110px; display: flex; flex-direction: column; align-items: center; padding: 12px 8px; border-right: 1px solid #e8ecef; text-align: center; }
.cd-fact:last-child  { border-right: none; }
.cd-fact i           { color: #1a7a8a; margin-bottom: 4px; }
.cd-fact__value      { font-size: 14px; font-weight: 700; color: #1a7a8a; }
.cd-fact__label      { font-size: 10px; text-transform: uppercase; color: #999; letter-spacing: .5px; }
.cd-fact--price .cd-fact__value { font-size: 16px; color: #4caf50; }

/* Layout principale */
.cd-main-layout { display: grid; grid-template-columns: 1fr 300px; gap: 24px; align-items: start; }
.cd-left-col    { display: flex; flex-direction: column; gap: 20px; }
.cd-sidebar     { display: flex; flex-direction: column; gap: 16px; position: sticky; top: 80px; }

/* Sezioni */
.cd-section         { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
.cd-section__header { padding: 14px 20px; border-bottom: 1px solid #e8ecef; display: flex; align-items: center; gap: 10px; }
.cd-section__header i  { color: #1a7a8a; font-size: 16px; }
.cd-section__header h2 { font-size: 15px; font-weight: 700; color: #1a7a8a; margin: 0; }
.cd-section__body   { padding: 16px 20px; }

/* Itinerario — griglia 2 colonne */
.cd-itinerary { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 6px 16px; }
.cd-itin-item { display: flex; align-items: flex-start; gap: 8px; padding: 6px 8px; border-radius: 8px; background: #f8fdfe; border: 1px solid #e8f4f6; }
.cd-itin-day  { min-width: 30px; height: 30px; border-radius: 6px; background: #1a7a8a; color: #fff; font-size: 9px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; text-align: center; line-height: 1.2; }
.cd-itin-day--accent { background: #4caf50; }
.cd-itin-info   { flex: 1; min-width: 0; }
.cd-itin-port   { font-weight: 600; font-size: 13px; color: #222; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cd-itin-tag    { display: inline-block; background: #1a7a8a; color: #fff; font-size: 9px; padding: 1px 5px; border-radius: 3px; margin-left: 4px; vertical-align: middle; }
.cd-itin-tag--end { background: #4caf50; }
.cd-itin-times  { font-size: 10px; color: #aaa; margin-top: 2px; }
.cd-itin-times span { margin-right: 6px; }

/* Nave — foto */
.cd-ship-photo { width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 14px; }

/* Cabine — accordion gruppi */
.cd-cabin-group { border: 1px solid #e8ecef; border-radius: 10px; margin-bottom: 10px; overflow: hidden; }
.cd-cabin-group__header { display: flex; align-items: center; gap: 10px; padding: 14px 16px; background: #f8fdfe; cursor: pointer; user-select: none; }
.cd-cabin-group__header:hover { background: #eef7f9; }
.cd-cabin-group__icon { width: 32px; height: 32px; border-radius: 50%; background: #1a7a8a; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
.cd-cabin-group__title { font-size: 15px; font-weight: 700; color: #1a7a8a; flex: 1; }
.cd-cabin-group__meta { font-size: 12px; color: #888; white-space: nowrap; }
.cd-cabin-group__chevron { color: #aaa; font-size: 12px; transition: transform .25s; flex-shrink: 0; }
.cd-cabin-group__header[aria-expanded="true"] .cd-cabin-group__chevron { transform: rotate(180deg); }
.cd-cabin-group .collapse, .cd-cabin-group .collapsing { padding: 14px; border-top: 1px solid #e8ecef; background: #fff; }

/* Cabine — card */
.cd-cabins-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 14px; }
.cd-cabin-card  { border: 1px solid #e8ecef; border-radius: 10px; overflow: hidden; transition: box-shadow .2s, border-color .2s; }
.cd-cabin-card:hover { box-shadow: 0 4px 12px rgba(26,122,138,.15); border-color: #1a7a8a; }
.cd-cabin-img   { height: 110px; background: #d0e8ec; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; }
.cd-cabin-img img { width: 100%; height: 100%; object-fit: cover; }
.cd-cabin-badge { position: absolute; top: 8px; left: 8px; background: #1a7a8a; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 4px; }
.cd-cabin-body  { padding: 10px 12px; }
.cd-cabin-code  { font-size: 11px; color: #999; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px; }
.cd-cabin-desc  { font-size: 12px; color: #666; line-height: 1.4; margin-bottom: 8px; min-height: 34px; }
.cd-cabin-price { font-size: 20px; font-weight: 800; color: #1a7a8a; }
.cd-cabin-price-sub { font-size: 11px; font-weight: 400; color: #aaa; }
.cd-cabin-updated { font-size: 10px; color: #ccc; margin-top: 3px; }
.cd-cabins-note { font-size: 11px; color: #bbb; margin-top: 12px; margin-bottom: 0; }

/* Nave */
.cd-ship-desc     { font-size: 13px; color: #555; line-height: 1.7; margin-bottom: 14px; }
.cd-ship-stats    { display: flex; gap: 12px; flex-wrap: wrap; }
.cd-ship-stat     { background: #f0f9fa; border-radius: 8px; padding: 10px 16px; text-align: center; min-width: 90px; }
.cd-ship-stat__value { display: block; font-size: 22px; font-weight: 800; color: #1a7a8a; }
.cd-ship-stat__label { display: block; font-size: 11px; color: #888; }

/* Sidebar */
.cd-price-box        { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.1); border: 2px solid #1a7a8a; }
.cd-price-box__header{ background: #1a7a8a; color: #fff; padding: 16px 20px; }
.cd-price-from       { font-size: 11px; opacity: .8; margin-bottom: 2px; }
.cd-price-main       { font-size: 34px; font-weight: 800; line-height: 1; }
.cd-price-sub        { font-size: 11px; opacity: .7; margin-top: 4px; margin-bottom: 0; }
.cd-price-box__body  { padding: 16px 20px; }
.cd-price-box__cats-label { font-size: 11px; font-weight: 700; color: #aaa; text-transform: uppercase; margin-bottom: 8px; }
.cd-price-row        { display: flex; justify-content: space-between; padding: 5px 0; font-size: 13px; border-bottom: 1px solid #f5f5f5; }
.cd-price-row:last-of-type { border-bottom: none; }
.cd-price-row__cat   { color: #666; }
.cd-price-row__val   { font-weight: 700; color: #1a7a8a; }
.cd-btn-cta          { background: #4caf50; border-color: #4caf50; font-weight: 700; }
.cd-btn-cta:hover    { background: #43a047; border-color: #43a047; }

.cd-alert-box        { background: #fff; border-radius: 10px; padding: 16px 20px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
.cd-alert-box h3     { font-size: 14px; font-weight: 700; color: #333; margin-bottom: 8px; }
.cd-alert-box p      { font-size: 12px; color: #888; margin-bottom: 10px; }

.cd-info-mini        { background: #fff; border-radius: 10px; padding: 14px 16px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
.cd-info-mini__title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #999; margin-bottom: 8px; }
.cd-info-row         { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; border-bottom: 1px solid #f5f5f5; }
.cd-info-row:last-child { border-bottom: none; }
.cd-info-row__lbl    { color: #999; }
.cd-info-row__val    { font-weight: 600; color: #333; }

/* Responsive */
@media (max-width: 768px) {
  .cd-main-layout { grid-template-columns: 1fr; }
  .cd-sidebar { position: static; }
  .cd-hero { padding: 70px 0 24px; }
  .cd-hero__title { font-size: 22px; }
  .cd-cabins-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) {
  .cd-cabins-grid { grid-template-columns: 1fr; }
  .cd-cabin-group__meta { display: none; }
  .cd-facts-bar { gap: 0; }
  .cd-itinerary { grid-template-columns: 1fr; }
}
</style>
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
        @php
            $cabinLabels = ['IS' => 'Cabina Interna', 'OS' => 'Cabina Esterna', 'BK' => 'Balcone', 'MS' => 'Mini Suite', 'SU' => 'Suite'];
            $cabinOrder  = ['IS', 'OS', 'BK', 'MS', 'SU'];
            $cabinIcons  = ['IS' => 'fa-moon', 'OS' => 'fa-sun', 'BK' => 'fa-door-open', 'MS' => 'fa-star-half-alt', 'SU' => 'fa-crown'];
            $grouped = $cabins->groupBy('cruisehost_cat');
            $firstKey = null;
            foreach ($cabinOrder as $k) { if ($grouped->has($k)) { $firstKey = $k; break; } }
        @endphp
        <div class="cd-section">
          <div class="cd-section__header">
            <i class="fas fa-bed"></i>
            <h2>Tipologie di cabina e prezzi</h2>
          </div>
          <div class="cd-section__body">
            <div id="accordionCabine">
              @foreach($cabinOrder as $code)
              @if($grouped->has($code))
              @php $group = $grouped[$code]; $isFirst = ($code === $firstKey); @endphp
              <div class="cd-cabin-group">
                <div class="cd-cabin-group__header" data-toggle="collapse"
                     data-target="#cabine-{{ $code }}"
                     aria-expanded="{{ $isFirst ? 'true' : 'false' }}">
                  <span class="cd-cabin-group__icon"><i class="fas {{ $cabinIcons[$code] }}"></i></span>
                  <span class="cd-cabin-group__title">{{ $cabinLabels[$code] }}</span>
                  <span class="cd-cabin-group__meta">
                    {{ $group->count() }} {{ $group->count() === 1 ? 'categoria' : 'categorie' }}
                    &nbsp;·&nbsp; da {{ \App\Models\Departure::formatPrice($group->min('price')) }}
                  </span>
                  <i class="fas fa-chevron-down cd-cabin-group__chevron"></i>
                </div>
                <div id="cabine-{{ $code }}" class="collapse {{ $isFirst ? 'show' : '' }}">
                  <div class="cd-cabins-grid">
                    @foreach($group as $cabin)
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
                </div>
              </div>
              @endif
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
            @if($ship->image_url)
              <img src="{{ $ship->image_url }}" alt="{{ $ship->name }}" class="cd-ship-photo">
            @endif
            @if($ship->description)
              <p class="cd-ship-desc">{{ $ship->description }}</p>
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
              @php
                $sbLabels = ['IS' => 'Cabina Interna', 'OS' => 'Cabina Esterna', 'BK' => 'Balcone', 'MS' => 'Mini Suite', 'SU' => 'Suite'];
                $sbOrder  = ['IS', 'OS', 'BK', 'MS', 'SU'];
                $sbGroups = $cabins->groupBy('cruisehost_cat');
              @endphp
              <div class="cd-price-box__cats-label">Prezzi per tipologia</div>
              @foreach($sbOrder as $sbCode)
                @if($sbGroups->has($sbCode))
                <div class="cd-price-row">
                  <span class="cd-price-row__cat">{{ $sbLabels[$sbCode] }}</span>
                  <span class="cd-price-row__val">da {{ \App\Models\Departure::formatPrice($sbGroups[$sbCode]->min('price')) }}</span>
                </div>
                @endif
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
