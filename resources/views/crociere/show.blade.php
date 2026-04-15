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

        {{-- Nave — Task 5 --}}
      </div>

      {{-- SIDEBAR --}}
      <div class="cd-sidebar">
        {{-- Price box, alert, riepilogo — Task 6 --}}
      </div>

    </div>{{-- /cd-main-layout --}}
  </div>

</div>{{-- /cruise-detail-page --}}
@endsection

@section('scripts')
{{-- Script favoriti + alert — Task 6 --}}
@endsection
