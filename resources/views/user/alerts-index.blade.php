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
                    <h5 class="card-title fw-bold">{{ $alert->cruise->ship }}</h5>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-ship me-1"></i>{{ $alert->cruise->line }}
                    </p>
                    <p class="text-muted mb-3">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        {{ $alert->cruise->getFormattedItinerary() }}
                    </p>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">
                                <i class="fas fa-calendar me-1"></i>Partenza
                            </small>
                            <small class="fw-bold">
                                {{ $alert->cruise->getFormattedDepartureDate('d M Y') ?? 'N/D' }}
                            </small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">
                                <i class="fas fa-bed me-1"></i>Cabina
                            </small>
                            <small class="fw-bold">
                                @switch($alert->cabin_type)
                                    @case('interior') Interna @break
                                    @case('oceanview') Vista Mare @break
                                    @case('balcony') Balcone @break
                                    @case('minisuite') Mini Suite @break
                                    @case('suite') Suite @break
                                @endswitch
                            </small>
                        </div>
                    </div>

                    <!-- Progresso verso target -->
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

                    <!-- Prezzi -->
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
                                @if($alert->cruise->getCabinPrice($alert->cabin_type))
                                    €{{ number_format($alert->cruise->getCabinPrice($alert->cabin_type), 0, ',', '.') }}
                                @else
                                    N/D
                                @endif
                            </h5>
                        </div>
                    </div>

                    @if($alert->getDiscountPercentage() > 0)
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-tag me-2"></i>
                        <strong>Sconto {{ $alert->getDiscountPercentage() }}%</strong> dal target!
                    </div>
                    @endif

                    <!-- Info aggiuntive -->
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

                    <!-- Azioni -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('crociere.create') }}?cruise_id={{ $alert->cruise->id }}" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-eye me-2"></i>Vedi Crociera
                        </a>
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

    <!-- Paginazione -->
    <div class="d-flex justify-content-center mt-4">
        {{ $alerts->links() }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/alerts.js') }}"></script>
@endpush
