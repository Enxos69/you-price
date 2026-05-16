@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
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

/* Itinerario — layout mappa + lista */
.cd-itin-split      { display: flex; gap: 16px; align-items: stretch; }
.cd-itin-map-wrap   { flex: 1; min-width: 0; border-radius: 8px; overflow: hidden; border: 1px solid #dde9eb; }
#cd-itin-map        { width: 100%; height: 360px; }
.cd-itin-list-wrap  { flex: 1; min-width: 0; }
#cd-itin-route      { width: 100%; overflow: visible; }
.cd-route-stop      { cursor: pointer; }
.cd-route-stop:hover circle { filter: brightness(1.15); }

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
.cd-ai-item          { display: flex; align-items: center; justify-content: space-between; background: rgba(255,193,7,.1); border: 1px solid rgba(255,193,7,.35); border-radius: 6px; padding: 6px 10px; margin-bottom: 6px; }
.cd-ai-item__info    { display: flex; flex-direction: column; line-height: 1.2; }
.cd-ai-item__cat     { font-size: 11px; color: #666; }
.cd-ai-item__price   { font-size: 14px; font-weight: 700; color: #c49000; }
.cd-ai-item__del     { background: none; border: none; color: #dc3545; padding: 2px 5px; line-height: 1; cursor: pointer; }
.cd-ai-item__del:hover { color: #a71d2a; }
.am-row              { padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
.am-row:last-child   { border-bottom: none; }
.am-row__macro       { font-weight: 600; font-size: 14px; color: #1a7a8a; margin-bottom: 2px; }
.am-row__current     { font-size: 12px; color: #999; margin-bottom: 8px; }

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
  .cd-itin-split    { flex-direction: column; }
  .cd-itin-map-wrap { flex: none; }
  #cd-itin-map      { height: 220px; }
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
        @php
          $itinArr   = $itinerary->values();
          $itinCount = $itinArr->count();
          $mapPorts  = [];
          $routeStops = [];
          foreach ($itinArr as $idx => $stop) {
            $hasCoords = $stop->port && $stop->port->latitude && $stop->port->longitude;
            if ($hasCoords) {
              $mapPorts[] = [
                'lat'  => (float) $stop->port->latitude,
                'lng'  => (float) $stop->port->longitude,
                'name' => $stop->port->name,
                'day'  => $stop->day_number,
                'type' => $idx === 0 ? 'start' : ($idx === $itinCount - 1 ? 'end' : 'stop'),
              ];
            }
            $routeStops[] = [
              'day'     => $stop->day_number,
              'name'    => $stop->port ? $stop->port->name : null,
              'lat'     => $hasCoords ? (float) $stop->port->latitude : null,
              'lng'     => $hasCoords ? (float) $stop->port->longitude : null,
              'arr'     => $stop->arrival_time   ? \Carbon\Carbon::parse($stop->arrival_time)->format('H:i')   : null,
              'dep'     => $stop->departure_time ? \Carbon\Carbon::parse($stop->departure_time)->format('H:i') : null,
              'isFirst' => $idx === 0,
              'isLast'  => $idx === $itinCount - 1,
            ];
          }
        @endphp
        <div class="cd-section">
          <div class="cd-section__header">
            <i class="fas fa-map-marked-alt"></i>
            <h2>Itinerario</h2>
          </div>
          <div class="cd-section__body">
            <div class="cd-itin-split">
              <div class="cd-itin-map-wrap">
                <div id="cd-itin-map"></div>
              </div>
              <div class="cd-itin-list-wrap">
                <div id="cd-itin-route"></div>
              </div>
            </div>
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

        {{-- ── STORICO PREZZI ─────────────────────────────────────────────── --}}
        <div class="cd-section" id="cd-price-history-section">
          <div class="cd-section__header">
            <i class="fas fa-chart-line"></i>
            <h2>Storico &amp; Stagionalità Prezzi</h2>
          </div>
          <div class="cd-section__body">
            <div id="cd-ph-cat-wrapper" style="display:none;" class="d-flex align-items-center flex-wrap mb-3" style="gap:12px;">
              <div class="d-flex align-items-center mr-3">
                <label class="mr-2 mb-0" style="font-size:12px;color:#999;white-space:nowrap;">Tipo cabina:</label>
                <select id="cd-ph-macro-select" class="form-control form-control-sm" style="max-width:180px;"></select>
              </div>
              <div class="d-flex align-items-center" id="cd-ph-sub-wrapper" style="display:none!important;">
                <label class="mr-2 mb-0" style="font-size:12px;color:#999;white-space:nowrap;">Cabina:</label>
                <select id="cd-ph-sub-select" class="form-control form-control-sm" style="max-width:120px;"></select>
              </div>
            </div>
            <ul class="nav nav-tabs mb-3" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#cd-ph-panel-weekly" role="tab">
                  <i class="fas fa-calendar-week mr-1"></i>Evoluzione settimanale
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#cd-ph-panel-monthly" role="tab">
                  <i class="fas fa-calendar-alt mr-1"></i>Stagionalità mensile
                </a>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="cd-ph-panel-weekly" role="tabpanel">
                <p id="cd-ph-weekly-msg" class="text-center text-muted py-3 mb-0" style="font-size:13px;">Caricamento...</p>
                <div id="cd-ph-weekly-chart"></div>
              </div>
              <div class="tab-pane fade" id="cd-ph-panel-monthly" role="tabpanel">
                <p id="cd-ph-monthly-msg" class="text-center text-muted py-3 mb-0" style="font-size:13px;">Caricamento...</p>
                <div id="cd-ph-monthly-chart"></div>
              </div>
            </div>
          </div>
        </div>

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
          <div id="cd-alert-active-list"></div>
          <button id="cd-alert-modal-btn" class="btn btn-warning btn-sm btn-block mt-1">
            <i class="fas fa-bell mr-1"></i>Gestisci i tuoi alert
          </button>
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

{{-- ── MODAL ALERT PREZZI ────────────────────────────────────────────────── --}}
<div class="modal fade" id="alertPriceModal" tabindex="-1" role="dialog" aria-labelledby="alertPriceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="alertPriceModalLabel">
          <i class="fas fa-bell mr-2 text-warning"></i>Alert Prezzi
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Inserisci il prezzo soglia per ciascuna categoria. Riceverai un'email quando il prezzo scenderà sotto il valore indicato. Lascia vuoto o a zero per non impostare l'alert.</p>
        <div id="alert-modal-rows"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Annulla</button>
        <button type="button" class="btn btn-warning btn-sm" id="alert-modal-save-btn">
          <i class="fas fa-save mr-1"></i>Salva
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  // ── Mappa itinerario ───────────────────────────────────────────────────────
  const mapPorts = @json($mapPorts ?? []);
  const mapEl = document.getElementById('cd-itin-map');
  let itinMap = null;

  if (mapEl && mapPorts.length > 0) {
    // Partenza e arrivo coincidono: unifica in un unico marker bicolore
    if (mapPorts.length > 1) {
      const first = mapPorts[0], last = mapPorts[mapPorts.length - 1];
      if (first.lat === last.lat && first.lng === last.lng) {
        first.type = 'both';
        mapPorts.pop();
      }
    }

    itinMap = L.map('cd-itin-map', { zoomControl: true, scrollWheelZoom: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      maxZoom: 18
    }).addTo(itinMap);

    const latlngs = mapPorts.map(p => [p.lat, p.lng]);

    mapPorts.forEach(p => {
      let bg, label;
      if (p.type === 'both') {
        bg = 'linear-gradient(135deg, #4caf50 50%, #e05252 50%)';
        label = ' — imbarco / sbarco';
      } else if (p.type === 'start') {
        bg = '#4caf50'; label = ' — imbarco';
      } else if (p.type === 'end') {
        bg = '#e05252'; label = ' — sbarco';
      } else {
        bg = '#1a7a8a'; label = '';
      }
      const tipColor = p.type === 'end' ? '#e05252' : (p.type === 'both' ? '#4caf50' : bg);
      const icon = L.divIcon({
        className: '',
        html: `<div style="position:relative;width:28px;height:36px;text-align:center;">
          <div style="background:${bg};width:28px;height:28px;border-radius:50%;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;line-height:1;">${p.day}</div>
          <div style="width:0;height:0;border-left:7px solid transparent;border-right:7px solid transparent;border-top:10px solid ${tipColor};margin:0 auto;"></div>
        </div>`,
        iconSize: [28, 38],
        iconAnchor: [14, 38],
        popupAnchor: [0, -40]
      });
      L.marker([p.lat, p.lng], { icon }).addTo(itinMap)
        .bindPopup(`<b>Gg ${p.day} — ${p.name}</b>${label}`);
    });

    itinMap.fitBounds(latlngs, { padding: [32, 32] });
    setTimeout(() => itinMap.invalidateSize(), 0);
  } else if (mapEl) {
    mapEl.closest('.cd-itin-map-wrap').style.display = 'none';
  }

  // ── Toast avviso mappa ─────────────────────────────────────────────────────
  function showMapToast(msg) {
    let t = document.getElementById('cd-map-toast');
    if (!t) {
      t = document.createElement('div');
      t.id = 'cd-map-toast';
      t.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,.75);color:#fff;padding:7px 18px;border-radius:20px;font-size:13px;z-index:9999;pointer-events:none;transition:opacity .3s;';
      document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.opacity = '1';
    clearTimeout(t._hide);
    t._hide = setTimeout(() => t.style.opacity = '0', 2500);
  }

  // ── Rotta SVG itinerario ───────────────────────────────────────────────────
  (function buildItineraryRoute() {
    const stops = @json($routeStops ?? []);
    const container = document.getElementById('cd-itin-route');
    if (!container || stops.length === 0) return;

    const NS = 'http://www.w3.org/2000/svg';
    const W       = container.clientWidth || 320;
    const PAD_X   = 26;
    const PAD_TOP = 22;
    const PAD_BOT = 14;
    const R       = 13;
    const LABEL_H = 72;   // giorno + nome + tag + arr + prt
    const ROW_GAP = 14;
    const STEP_Y  = R * 2 + LABEL_H + ROW_GAP;

    // Ports per row: min spacing 68px, max 6 per row
    const n = stops.length;
    const maxFit   = Math.max(2, Math.floor((W - PAD_X * 2) / 68) + 1);
    const perRow   = Math.min(maxFit, 6, n);
    const spacingX = perRow > 1 ? (W - PAD_X * 2) / (perRow - 1) : 0;
    const rows     = Math.ceil(n / perRow);
    const svgH     = PAD_TOP + rows * STEP_Y - ROW_GAP + PAD_BOT + R;

    // Port positions (serpentine)
    const pts = stops.map((stop, i) => {
      const row    = Math.floor(i / perRow);
      const posRow = i % perRow;
      const ltr    = row % 2 === 0;
      const col    = ltr ? posRow : (perRow - 1 - posRow);
      return { x: PAD_X + col * spacingX, y: PAD_TOP + R + row * STEP_Y, stop };
    });

    // SVG path (dashed serpentine)
    let d = `M ${pts[0].x} ${pts[0].y}`;
    for (let i = 1; i < pts.length; i++) {
      const p = pts[i - 1], c = pts[i];
      if (p.y === c.y) {
        d += ` L ${c.x} ${c.y}`;
      } else {
        // U-curve bezier between rows
        const ltr  = Math.floor((i - 1) / perRow) % 2 === 0;
        const ext  = STEP_Y * 0.48 * (ltr ? 1 : -1);
        d += ` C ${p.x + ext} ${p.y} ${c.x + ext} ${c.y} ${c.x} ${c.y}`;
      }
    }

    const svg = document.createElementNS(NS, 'svg');
    svg.setAttribute('width', '100%');
    svg.setAttribute('height', svgH);
    svg.setAttribute('viewBox', `0 0 ${W} ${svgH}`);
    svg.style.overflow = 'visible';
    svg.style.display  = 'block';

    // Path
    const path = document.createElementNS(NS, 'path');
    path.setAttribute('d', d);
    path.setAttribute('stroke', '#c5e4e9');
    path.setAttribute('stroke-width', '2.5');
    path.setAttribute('stroke-dasharray', '6 4');
    path.setAttribute('stroke-linecap', 'round');
    path.setAttribute('fill', 'none');
    svg.appendChild(path);

    // Max chars for port name based on spacing
    const maxCh = Math.max(6, Math.floor(spacingX / 6.2));

    pts.forEach(({ x, y, stop }) => {
      const isSea   = !stop.name;
      const color   = isSea ? '#b8cdd1' : (stop.isFirst ? '#4caf50' : (stop.isLast ? '#e05252' : '#1a7a8a'));
      const g = document.createElementNS(NS, 'g');
      g.classList.add('cd-route-stop');
      if (stop.lat) g.dataset.lat = stop.lat;
      if (stop.lng) g.dataset.lng = stop.lng;

      // Circle
      const circ = document.createElementNS(NS, 'circle');
      circ.setAttribute('cx', x); circ.setAttribute('cy', y); circ.setAttribute('r', R);
      circ.setAttribute('fill', color);
      circ.setAttribute('stroke', '#fff'); circ.setAttribute('stroke-width', '2');
      g.appendChild(circ);

      const mk = (tag, attrs, text) => {
        const el = document.createElementNS(NS, tag);
        Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
        if (text !== undefined) el.textContent = text;
        return el;
      };

      // Day number inside circle
      g.appendChild(mk('text', { x, y: y + 4.5, 'text-anchor': 'middle', fill: '#fff', 'font-size': '12', 'font-weight': '700', 'font-family': 'inherit' }, stop.day));

      // Line 1 — "Giorno X"
      const L1 = y + R + 15;
      g.appendChild(mk('text', { x, y: L1, 'text-anchor': 'middle', fill: isSea ? '#999' : '#1a7a8a', 'font-size': '13', 'font-weight': '700', 'font-family': 'inherit' }, `Giorno ${stop.day}`));

      // Line 2 — port name
      const name = isSea ? 'navigazione' : (stop.name.length > maxCh ? stop.name.slice(0, maxCh - 1) + '…' : stop.name);
      g.appendChild(mk('text', { x, y: L1 + 15, 'text-anchor': 'middle', fill: isSea ? '#aaa' : '#333', 'font-size': '12', 'font-style': isSea ? 'italic' : 'normal', 'font-family': 'inherit' }, name));

      // Line 3 — tag imbarco/sbarco (per prima e ultima tappa)
      let timeOffsetY = L1 + 29;
      if (stop.isFirst || stop.isLast) {
        const tagColor = stop.isFirst ? '#4caf50' : '#e05252';
        const tagLabel = stop.isFirst ? '▲ imbarco' : '▼ sbarco';
        g.appendChild(mk('text', { x, y: timeOffsetY, 'text-anchor': 'middle', fill: tagColor, 'font-size': '10', 'font-weight': '700', 'font-family': 'inherit' }, tagLabel));
        timeOffsetY += 13;
      }

      // Orari arrivo e partenza (tutte le tappe, incluso imbarco/sbarco)
      if (!isSea) {
        if (stop.arr) { g.appendChild(mk('text', { x, y: timeOffsetY,      'text-anchor': 'middle', fill: '#555', 'font-size': '10.5', 'font-family': 'inherit' }, `arr. ${stop.arr}`)); }
        if (stop.dep) { g.appendChild(mk('text', { x, y: timeOffsetY + 13, 'text-anchor': 'middle', fill: '#555', 'font-size': '10.5', 'font-family': 'inherit' }, `prt. ${stop.dep}`)); }
      }

      // Click → centra mappa
      g.addEventListener('click', () => {
        if (stop.lat && stop.lng && itinMap) {
          itinMap.flyTo([stop.lat, stop.lng], 9, { duration: 1.4 });
        } else {
          showMapToast('Tappa in navigazione — nessuna posizione sulla mappa');
        }
      });

      svg.appendChild(g);
    });

    container.appendChild(svg);
  })();


  // ── Preferiti ──────────────────────────────────────────────────────────────
  const btn = document.getElementById('cd-favorite-btn');
  if (btn) {
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
  }

  // ── Storico prezzi ─────────────────────────────────────────────────────────
  const DEP_ID      = '{{ $departure->id }}';
  const MONTH_NAMES = ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
  const COLORS      = ['#1a7a8a','#4caf50','#e67e22','#9b59b6','#e74c3c'];
  // Mapping codice cabina → macro categoria (da PHP)
  const CABIN_MAP = @json($cabins->values()->map(fn($c) => ['code' => $c['category_code'], 'macro' => $c['cruisehost_cat'], 'price' => $c['price']]));
  const MACRO_LABEL = { IS:'Cabina Interna', OS:'Cabina Esterna', BK:'Balcone', MS:'Mini Suite', SU:'Suite' };
  const MACRO_ORDER = ['IS','OS','BK','MS','SU'];

  let weeklyChart  = null;
  let monthlyChart = null;

  function fmtEur(val) {
    return '€ ' + Number(val).toLocaleString('it-IT', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  }

  function chartBase(title) {
    return {
      chart:  { type: 'line', height: 260, toolbar: { show: false }, fontFamily: 'inherit', zoom: { enabled: false } },
      stroke: { width: 2, curve: 'smooth' },
      colors: COLORS,
      xaxis:  {},
      yaxis:  { labels: { formatter: fmtEur } },
      tooltip: { y: { formatter: fmtEur } },
      legend: { position: 'bottom', fontSize: '12px' },
      title:  { text: title, style: { fontSize: '13px', color: '#1a7a8a', fontWeight: 600 } },
      noData: { text: 'Nessun dato disponibile' },
    };
  }

  async function fetchJSON(url) {
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
  }

  async function loadWeekly(category) {
    const msg = document.getElementById('cd-ph-weekly-msg');
    const el  = document.getElementById('cd-ph-weekly-chart');
    try {
      const url  = `/api/crociere/${DEP_ID}/price/seasonal/weekly` + (category ? `?category=${encodeURIComponent(category)}` : '');
      const data = await fetchJSON(url);

      if (!data.series || data.series.length === 0) {
        msg.textContent = 'Dati insufficienti per l\'analisi settimanale.';
        el.innerHTML = '';
        return data;
      }
      msg.textContent = '';
      if (weeklyChart) { weeklyChart.destroy(); weeklyChart = null; }

      // Asse X completo: tutte le settimane da maxWeek a 0 (proporzionale al tempo)
      const maxWeek = Math.max(...data.series.flatMap(s => s.data.map(d => d.x)), 1);
      const allWeeks = Array.from({ length: maxWeek + 1 }, (_, i) => maxWeek - i);

      const opts = chartBase('Prezzo medio per settimane prima della partenza');
      opts.stroke = { width: 2, curve: 'straight' }; // linea piatta dove il prezzo non cambia
      opts.series = data.series.map(s => {
        const map     = new Map(s.data.map(d => [d.x, d.y]));
        const minWeek = Math.min(...s.data.map(d => d.x)); // ultima settimana con dato reale
        let last = null;
        return {
          name: s.name,
          data: allWeeks.map(w => {
            if (map.has(w)) last = map.get(w);
            return w >= minWeek ? last : null; // null per settimane non ancora rilevate
          }),
        };
      });
      opts.xaxis = {
        type: 'category',
        categories: allWeeks.map(w => w === 0 ? 'partenza' : `sett. ${w}`),
        title: { text: 'Settimane prima della partenza', style: { fontSize: '11px', color: '#999' } },
        tickAmount: Math.min(allWeeks.length, 13), // max 13 label sull'asse
        labels: { rotate: -45, style: { fontSize: '10px' } },
      };
      weeklyChart = new ApexCharts(el, opts);
      weeklyChart.render();
      return data;
    } catch (e) {
      msg.textContent = 'Errore nel caricamento dei dati.';
      return null;
    }
  }

  async function loadMonthly(category) {
    const msg = document.getElementById('cd-ph-monthly-msg');
    const el  = document.getElementById('cd-ph-monthly-chart');
    try {
      const url  = `/api/crociere/${DEP_ID}/price/seasonal/monthly` + (category ? `?category=${encodeURIComponent(category)}` : '');
      const data = await fetchJSON(url);

      if (!data.series || data.series.length === 0) {
        msg.textContent = 'Dati insufficienti per l\'analisi mensile.';
        el.innerHTML = '';
        return;
      }
      msg.textContent = '';
      if (monthlyChart) { monthlyChart.destroy(); monthlyChart = null; }

      const opts = chartBase('Prezzo medio per mese di partenza');
      opts.series = data.series;
      opts.xaxis  = {
        categories: MONTH_NAMES,
        title: { text: 'Mese di partenza', style: { fontSize: '11px', color: '#999' } },
      };
      monthlyChart = new ApexCharts(el, opts);
      monthlyChart.render();
    } catch (e) {
      msg.textContent = 'Errore nel caricamento dei dati.';
    }
  }

  async function initPriceHistory() {
    const result = await loadWeekly(null);
    if (!result) return;

    const avail      = result.available_categories || [];
    const wrapper    = document.getElementById('cd-ph-cat-wrapper');
    const macroSel   = document.getElementById('cd-ph-macro-select');
    const subSel     = document.getElementById('cd-ph-sub-select');
    const subWrapper = document.getElementById('cd-ph-sub-wrapper');

    // Codici disponibili raggruppati per macro categoria
    function subsFor(macro) {
      return CABIN_MAP.filter(c => c.macro === macro && avail.includes(c.code))
                      .sort((a, b) => a.price - b.price);
    }

    // Macro disponibili, ordinate dal più economico
    const availMacros = MACRO_ORDER
      .filter(m => subsFor(m).length > 0)
      .sort((a, b) => (subsFor(a)[0]?.price ?? Infinity) - (subsFor(b)[0]?.price ?? Infinity));

    if (availMacros.length === 0) {
      loadMonthly(result.category);
      return;
    }

    // Popola select macro (solo categorie con mapping riconosciuto)
    availMacros.forEach(m => {
      const opt = document.createElement('option');
      opt.value = m; opt.textContent = MACRO_LABEL[m] || m;
      macroSel.appendChild(opt);
    });

    // Popola sub-select e restituisce il codice selezionato
    function populateSubs(macro) {
      subSel.innerHTML = '';
      const subs = subsFor(macro);
      if (subs.length > 1) {
        subs.forEach(c => {
          const opt = document.createElement('option');
          opt.value = c.code; opt.textContent = c.code;
          subSel.appendChild(opt);
        });
        subWrapper.style.cssText = ''; // mostra
      } else {
        subWrapper.style.cssText = 'display:none!important';
      }
      return subs[0]?.code;
    }

    // Default: macro più economica → sub più economica
    const defaultMacro = availMacros[0];
    macroSel.value = defaultMacro;
    let activeCat = populateSubs(defaultMacro);

    // Carica mensile con la categoria di default
    loadMonthly(activeCat);
    wrapper.style.display = '';

    macroSel.addEventListener('change', function () {
      const cat = populateSubs(this.value);
      loadWeekly(cat);
      loadMonthly(cat);
    });

    subSel.addEventListener('change', function () {
      loadWeekly(this.value);
      loadMonthly(this.value);
    });
  }

  initPriceHistory();

  // ── Alert Prezzi Modale ──────────────────────────────────────────────────────
  (function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Stato corrente degli alert (aggiornato client-side)
    let alertsByCategory = {};
    const initAlerts = @json($userAlerts->map(fn($a) => ['id' => $a->id, 'category_code' => $a->category_code, 'target_price' => (float) $a->target_price])->values());
    initAlerts.forEach(function (a) { alertsByCategory[a.category_code] = a; });

    // Macro groups disponibili per questa partenza (da CABIN_MAP)
    const macroGroups = {};
    CABIN_MAP.forEach(function (c) {
      if (!c.macro) return;
      if (!macroGroups[c.macro] || c.price < macroGroups[c.macro].price) {
        macroGroups[c.macro] = { code: c.macro, price: c.price };
      }
    });

    function fmtPrice(val) {
      return '€ ' + Number(val).toLocaleString('it-IT', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function renderAlertBoxes() {
      const container = document.getElementById('cd-alert-active-list');
      if (!container) return;
      container.innerHTML = '';
      MACRO_ORDER.forEach(function (code) {
        const alert = alertsByCategory[code];
        if (!alert) return;
        const div = document.createElement('div');
        div.className = 'cd-ai-item';
        div.innerHTML =
          '<div class="cd-ai-item__info">' +
            '<span class="cd-ai-item__cat">' + (MACRO_LABEL[code] || code) + '</span>' +
            '<span class="cd-ai-item__price">' + fmtPrice(alert.target_price) + '</span>' +
          '</div>' +
          '<button class="cd-ai-item__del" data-alert-id="' + alert.id + '" title="Elimina">' +
            '<i class="fas fa-times"></i>' +
          '</button>';
        container.appendChild(div);
      });
    }

    function populateModal() {
      const container = document.getElementById('alert-modal-rows');
      container.innerHTML = '';
      MACRO_ORDER.forEach(function (code) {
        if (!macroGroups[code]) return;
        const existing = alertsByCategory[code];
        const row = document.createElement('div');
        row.className = 'am-row';
        row.innerHTML =
          '<div class="am-row__macro">' + (MACRO_LABEL[code] || code) + '</div>' +
          '<div class="am-row__current">Prezzo attuale: ' + fmtPrice(macroGroups[code].price) + '</div>' +
          '<div class="input-group input-group-sm">' +
            '<div class="input-group-prepend"><span class="input-group-text">€</span></div>' +
            '<input type="number" class="form-control alert-price-input" placeholder="Es. 800"' +
              ' data-macro="' + code + '"' +
              ' data-alert-id="' + (existing ? existing.id : '') + '"' +
              ' value="' + (existing ? existing.target_price : '') + '"' +
              ' min="0" step="1">' +
          '</div>';
        container.appendChild(row);
      });
    }

    async function doDelete(alertId) {
      const res = await fetch('/alert-prezzi/' + alertId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      });
      return res.json();
    }

    // Apri modale
    document.getElementById('cd-alert-modal-btn').addEventListener('click', function () {
      populateModal();
      $('#alertPriceModal').modal('show');
    });

    // Elimina alert dalle box
    document.getElementById('cd-alert-active-list').addEventListener('click', function (e) {
      const btn = e.target.closest('.cd-ai-item__del');
      if (!btn) return;
      const alertId = btn.dataset.alertId;
      doDelete(alertId).then(function (data) {
        if (data.success) {
          for (const code in alertsByCategory) {
            if (alertsByCategory[code].id == alertId) { delete alertsByCategory[code]; break; }
          }
          renderAlertBoxes();
        }
      }).catch(function () {});
    });

    // Salva alert
    document.getElementById('alert-modal-save-btn').addEventListener('click', async function () {
      const saveBtn = this;
      saveBtn.disabled = true;
      const inputs = document.querySelectorAll('.alert-price-input');
      const promises = [];

      inputs.forEach(function (input) {
        const macro   = input.dataset.macro;
        const alertId = input.dataset.alertId;
        const val     = parseFloat(input.value);

        if (!val || val <= 0) {
          if (alertId) {
            promises.push(
              doDelete(alertId).then(function (data) {
                if (data.success) delete alertsByCategory[macro];
              })
            );
          }
          return;
        }

        if (alertId) {
          promises.push(
            fetch('/alert-prezzi/' + alertId, {
              method: 'PATCH',
              headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
              body: JSON.stringify({ target_price: val }),
            })
            .then(r => r.json())
            .then(function (data) {
              if (data.success) alertsByCategory[macro].target_price = val;
            })
          );
        } else {
          promises.push(
            fetch('/alert-prezzi', {
              method: 'POST',
              headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
              body: JSON.stringify({ departure_id: DEP_ID, category_code: macro, target_price: val, alert_type: 'fixed_price' }),
            })
            .then(r => r.json())
            .then(function (data) {
              if (data.success && data.alert) {
                alertsByCategory[macro] = { id: data.alert.id, category_code: macro, target_price: val };
              }
            })
          );
        }
      });

      await Promise.all(promises).catch(function () {});
      renderAlertBoxes();
      $('#alertPriceModal').modal('hide');
      saveBtn.disabled = false;
    });

    // Render iniziale box
    renderAlertBoxes();
  })();
});
</script>
@endsection
