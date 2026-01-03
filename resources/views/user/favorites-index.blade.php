@extends('layouts.app')

@section('title', 'I Miei Preferiti - You Price')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-heart text-danger me-2"></i>I Miei Preferiti</h2>
            <p class="text-muted">Le crociere che hai salvato per dopo</p>
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

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($favorites->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-heart fa-4x text-muted mb-3"></i>
            <h4>Nessun preferito salvato</h4>
            <p class="text-muted mb-4">Inizia a salvare le crociere che ti interessano!</p>
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
                Hai salvato <strong>{{ $favorites->total() }}</strong> 
                {{ $favorites->total() == 1 ? 'crociera' : 'crociere' }}
            </p>
        </div>
        <div class="col-md-6 text-end">
            @if($favorites->total() > 0)
            <button class="btn btn-sm btn-outline-danger" id="removeAllFavorites">
                <i class="fas fa-trash me-2"></i>Rimuovi Tutti
            </button>
            @endif
        </div>
    </div>

    <div class="row">
        @foreach($favorites as $favorite)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card favorite-card h-100 border-0 shadow-sm" data-cruise-id="{{ $favorite->cruise->id }}">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-{{ $favorite->cruise->getAvailabilityStatus()['class'] }}">
                        {{ $favorite->cruise->getAvailabilityStatus()['label'] }}
                    </span>
                    <button class="btn btn-sm btn-link text-danger p-0 remove-favorite" 
                            data-cruise-id="{{ $favorite->cruise->id }}"
                            title="Rimuovi dai preferiti">
                        <i class="fas fa-heart fa-lg"></i>
                    </button>
                </div>
                
                <div class="card-body">
                    <h5 class="card-title fw-bold">{{ $favorite->cruise->ship }}</h5>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-ship me-1"></i>{{ $favorite->cruise->line }}
                    </p>
                    <p class="text-muted mb-2">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        {{ $favorite->cruise->getFormattedItinerary() }}
                    </p>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">
                                <i class="fas fa-calendar me-1"></i>Partenza
                            </small>
                            <small class="fw-bold">
                                {{ $favorite->cruise->getFormattedDepartureDate('d M Y') ?? 'N/D' }}
                            </small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">
                                <i class="fas fa-moon me-1"></i>Durata
                            </small>
                            <small class="fw-bold">{{ $favorite->cruise->getFormattedDuration() }}</small>
                        </div>
                    </div>

                    @if($favorite->note)
                    <div class="alert alert-light border mb-3">
                        <small class="text-muted d-block mb-1">
                            <i class="fas fa-sticky-note me-1"></i>Nota personale:
                        </small>
                        <small>{{ $favorite->note }}</small>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="text-success mb-0">
                                {{ $favorite->cruise->getLowestPrice() ? '€' . number_format($favorite->cruise->getLowestPrice(), 0, ',', '.') : 'N/D' }}
                            </h4>
                            <small class="text-muted">a persona</small>
                        </div>
                        <small class="text-muted">
                            Salvato {{ $favorite->created_at->locale('it')->diffForHumans() }}
                        </small>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('crociere.create') }}?cruise_id={{ $favorite->cruise->id }}" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-eye me-2"></i>Vedi Dettagli
                        </a>
                        <button class="btn btn-outline-secondary btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#noteModal{{ $favorite->cruise->id }}">
                            <i class="fas fa-edit me-2"></i>
                            {{ $favorite->note ? 'Modifica Nota' : 'Aggiungi Nota' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal per modificare la nota -->
        <div class="modal fade" id="noteModal{{ $favorite->cruise->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $favorite->note ? 'Modifica Nota' : 'Aggiungi Nota' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form class="update-note-form" data-cruise-id="{{ $favorite->cruise->id }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nota personale</label>
                                <textarea class="form-control" name="note" rows="3" maxlength="500">{{ $favorite->note }}</textarea>
                                <div class="form-text">Massimo 500 caratteri</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Salva Nota
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Paginazione -->
    <div class="d-flex justify-content-center mt-4">
        {{ $favorites->links() }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/favorites.js') }}"></script>
@endpush
