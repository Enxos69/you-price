@extends('layouts.app')

@section('title', 'Alert Prezzi - You Price')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-bell text-warning me-2"></i>I Miei Alert Prezzi</h2>
            <p class="text-muted">Ricevi notifiche quando il prezzo scende</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Torna alla Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($alerts->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-bell fa-4x text-muted mb-3"></i>
            <h4>Nessun alert attivo</h4>
            <p class="text-muted mb-4">Crea un alert per essere notificato quando il prezzo di una crociera scende!</p>
            <a href="{{ route('crociere.index') }}" class="btn btn-primary">
                <i class="fas fa-search me-2"></i>Cerca Crociere
            </a>
        </div>
    </div>
    @else
    <div class="row mb-3">
        <div class="col-md-6">
            <p class="text-muted">
                <i class="fas fa-info-circle me-2"></i>
                Hai <strong>{{ $alerts->where('is_active', true)->count() }}</strong> alert attivi su {{ $alerts->total() }} totali
            </p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-sm btn-outline-danger" id="deleteInactiveAlerts">
                <i class="fas fa-trash me-2"></i>Elimina Inattivi
            </button>
        </div>
    </div>

    <div class="row">
        @foreach($alerts as $alert)
        @php
            $departure   = $alert->departure;
            $product     = $departure->product;
            $currentPrice = $alert->current_price;
        @endphp
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card alert-card h-100 border-0 shadow-sm {{ $alert->is_active ? '' : 'opacity-75' }}"
                 data-alert-id="{{ $alert->id }}">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    @if($alert->isPriceReached())
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>Obiettivo Raggiunto!
                        </span>
                    @else
                        <span class="badge bg-{{ $alert->is_active ? 'info' : 'secondary' }}">
                            {{ $alert->is_active ? 'In Monitoraggio' : 'Disattivato' }}
                        </span>
                    @endif
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-sm btn-link text-{{ $alert->is_active ? 'warning' : 'success' }} p-0 toggle-alert"
                                data-alert-id="{{ $alert->id }}"
                                title="{{ $alert->is_active ? 'Disattiva' : 'Attiva' }}">
                            <i class="fas fa-power-off"></i>
                        </button>
                        <button class="btn btn-sm btn-link text-danger p-0 ms-2 delete-alert"
                                data-alert-id="{{ $alert->id }}"
                                title="Elimina">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <h5 class="card-title fw-bold">{{ $product->ship->name ?? 'N/D' }}</h5>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-ship me-1"></i>{{ $product->cruiseLine->name ?? 'N/D' }}
                    </p>
                    <p class="text-muted mb-3">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        {{ ($product->portFrom->name ?? 'N/D') }} - {{ ($product->portTo->name ?? 'N/D') }}
                    </p>

                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">
                                <i class="fas fa-calendar me-1"></i>Partenza
                            </small>
                            <small class="fw-bold">{{ $departure->dep_date->format('d M Y') }}</small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">
                                <i class="fas fa-bed me-1"></i>Categoria
                            </small>
                            <small class="fw-bold">{{ $alert->category_code }}</small>
                        </div>
                    </div>

                    {{-- Progresso verso target --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Progresso verso target</small>
                            <small class="fw-bold">{{ $alert->getProgressPercentage() }}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $alert->isPriceReached() ? 'bg-success' : 'bg-info' }}"
                                 role="progressbar"
                                 style="width: {{ $alert->getProgressPercentage() }}%">
                            </div>
                        </div>
                    </div>

                    {{-- Prezzi --}}
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Prezzo Target</small>
                            <h5 class="text-primary mb-0">
                                €{{ number_format($alert->target_price, 0, ',', '.') }}
                            </h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Prezzo Attuale</small>
                            <h5 class="{{ $alert->isPriceReached() ? 'text-success' : 'text-secondary' }} mb-0">
                                {{ $currentPrice ? '€' . number_format($currentPrice, 0, ',', '.') : 'N/D' }}
                            </h5>
                        </div>
                    </div>

                    @if($alert->getDiscountPercentage() > 0)
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-tag me-2"></i>
                        <strong>Sconto {{ $alert->getDiscountPercentage() }}%</strong> dal target!
                    </div>
                    @endif

                    <div class="small text-muted mb-3">
                        <div class="mb-1">
                            <i class="fas fa-clock me-1"></i>
                            Creato {{ $alert->created_at->locale('it')->diffForHumans() }}
                        </div>
                        @if($alert->last_checked_at)
                        <div>
                            <i class="fas fa-sync me-1"></i>
                            Ultimo controllo {{ $alert->last_checked_at->locale('it')->diffForHumans() }}
                        </div>
                        @endif
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-sm open-cruise-details"
                                data-cruise-id="{{ $departure->id }}">
                            <i class="fas fa-eye me-2"></i>Vedi Crociera
                        </button>
                        @if($alert->notification_sent && $alert->is_active)
                        <button class="btn btn-outline-secondary btn-sm reset-notification"
                                data-alert-id="{{ $alert->id }}">
                            <i class="fas fa-redo me-2"></i>Reimposta Notifica
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $alerts->links() }}
    </div>
    @endif
</div>

@include('partials.cruise-detail-modal')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/alerts.js') }}"></script>
@include('crociere.assets.js_modal_details')
@endpush
