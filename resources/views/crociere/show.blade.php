@extends('layouts.app')

@section('content')
<style>
{{-- CSS sarà aggiunto nel Task 7 — placeholder --}}
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
        {{-- Sezioni: itinerario, cabine, nave — Task 3/4/5 --}}
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
