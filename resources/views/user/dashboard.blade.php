@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('/css/dashboard.css') }}">
@section('title', 'Dashboard - You Price')

@section('content')
<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2">👋 Benvenuto, {{ $user->name }} {{ $user->surname }}</h2>
                <p class="mb-0 opacity-75">La tua prossima avventura in mare ti aspetta</p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-light text-dark px-3 py-2">
                    <i class="fas fa-calendar me-2"></i>Membro da {{ $stats['member_since'] }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <h5><i class="fas fa-bolt text-warning me-2"></i>Azioni Rapide</h5>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="{{ route('crociere.index') }}" class="text-decoration-none">
                <div class="card quick-action-card text-center p-4 bg-primary text-white h-100">
                    <i class="fas fa-search quick-action-icon"></i>
                    <h6 class="mb-0">Nuova Ricerca</h6>
                    <small class="opacity-75">Trova la tua crociera ideale</small>
                </div>
            </a>
        </div>
        
        {{-- <div class="col-md-3 col-sm-6 mb-3">
            <a href="{{ route('favorites.index') }}" class="text-decoration-none">
                <div class="card quick-action-card text-center p-4 h-100">
                    <i class="fas fa-heart quick-action-icon text-danger"></i>
                    <h6 class="mb-0">I Miei Preferiti</h6>
                    <small class="text-muted">{{ $stats['favorites_count'] }} crociere salvate</small>
                </div>
            </a>
        </div> --}}
        
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="{{ route('alerts.index') }}" class="text-decoration-none">
                <div class="card quick-action-card text-center p-4 h-100">
                    <i class="fas fa-bell quick-action-icon text-warning"></i>
                    <h6 class="mb-0">Alert Prezzi</h6>
                    <small class="text-muted">{{ $stats['active_alerts'] }} alert attivi</small>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="{{ route('admin.analytics.index') }}" class="text-decoration-none">
                <div class="card quick-action-card text-center p-4 h-100">
                    <i class="fas fa-chart-line quick-action-icon text-info"></i>
                    <h6 class="mb-0">Le Mie Statistiche</h6>
                    <small class="text-muted">Analizza le tue ricerche</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Statistiche -->
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <h5><i class="fas fa-chart-bar me-2"></i>Panoramica</h5>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-number">{{ $stats['total_searches'] }}</div>
                        <small class="text-muted">Ricerche Effettuate</small>
                    </div>
                    <i class="fas fa-search fa-2x text-primary opacity-25"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-number">{{ $stats['cruises_viewed'] }}</div>
                        <small class="text-muted">Crociere Viste</small>
                    </div>
                    <i class="fas fa-eye fa-2x text-info opacity-25"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-number">{{ $stats['favorites_count'] }}</div>
                        <small class="text-muted">Preferiti</small>
                    </div>
                    <i class="fas fa-heart fa-2x text-danger opacity-25"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-number">{{ $stats['active_alerts'] }}</div>
                        <small class="text-muted">Alert Attivi</small>
                    </div>
                    <i class="fas fa-bell fa-2x text-warning opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Colonna Sinistra -->
        <div class="col-lg-8">
            <!-- Ricerche Recenti -->
            @if($recent_searches->isNotEmpty())
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Ricerche Recenti</h5>
                    </div>
                    
                    @foreach($recent_searches as $search)
                    <div class="recent-search-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <p class="mb-1 fw-bold">{{ $search['search_params'] }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $search['time_ago'] }}
                                </small>
                            </div>
                            <div class="text-end">
                                @if($search['total_matches'] > 0)
                                    <span class="badge bg-success">{{ $search['total_matches'] }} risultati</span>
                                @else
                                    <span class="badge bg-secondary">Nessun risultato</span>
                                @endif
                            </div>
                        </div>
                        @if($search['avg_price_found'])
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Prezzo medio trovato:</small>
                            <small class="fw-bold text-success">{{ $search['avg_price_found'] }}</small>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Crociere nei Preferiti -->
            @if($favorites->isNotEmpty())
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-heart text-danger me-2"></i>I Miei Preferiti</h5>
                        {{-- <a href="{{ route('favorites.index') }}" class="btn btn-sm btn-outline-danger">
                            Vedi tutti ({{ $stats['favorites_count'] }})
                        </a> --}}
                    </div>
                    
                    <div class="row">
                        @foreach($favorites as $favorite)
                        <div class="col-md-6 mb-3">
                            <div class="card favorite-cruise-card border-0">
                                <div class="favorite-badge">
                                    <i class="fas fa-heart text-danger"></i>
                                </div>
                                <div class="card-body">
                                    <span class="badge {{ $favorite['availability']['badge_class'] }} mb-2">
                                        {{ $favorite['availability']['label'] }}
                                    </span>
                                    <h6 class="fw-bold">{{ $favorite['ship'] }}</h6>
                                    <p class="text-muted mb-2 small">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $favorite['itinerary'] }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $favorite['departure_date'] ?? 'Data da definire' }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-moon me-1"></i>{{ $favorite['duration'] }}
                                        </small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="text-success mb-0">{{ $favorite['price_formatted'] }}</h5>
                                            <small class="text-muted">a persona</small>
                                        </div>
                                        <a href="{{ route('crociere.create') }}?cruise_id={{ $favorite['id'] }}" 
                                           class="btn btn-sm btn-primary">
                                            Dettagli
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Colonna Destra -->
        <div class="col-lg-4">
            <!-- Alert Prezzi Attivi -->
            @if($price_alerts->isNotEmpty())
            <div class="card mb-4 border-0 shadow-sm price-alert">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-bell text-danger me-2"></i>Alert Prezzi Attivi
                        <span class="badge bg-danger float-end">{{ $price_alerts->count() }}</span>
                    </h6>
                    
                    @foreach($price_alerts as $alert)
                    <div class="{{ $loop->last ? '' : 'mb-3 pb-3 border-bottom' }}">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="fw-bold">{{ $alert['ship'] }}</small>
                            @if($alert['is_reached'])
                                <span class="badge bg-success">Raggiunto!</span>
                            @else
                                <span class="badge bg-info">In monitoraggio</span>
                            @endif
                        </div>
                        <small class="text-muted d-block mb-1">
                            {{ $alert['itinerary'] }} • {{ $alert['departure_date'] }}
                        </small>
                        <small class="text-muted d-block mb-2">
                            Cabina: {{ $alert['cabin_type_label'] }}
                        </small>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar {{ $alert['is_reached'] ? 'bg-success' : 'bg-info' }}" 
                                 style="width: {{ $alert['progress_percentage'] }}%">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Target: {{ $alert['target_price_formatted'] }}</small>
                            <small class="{{ $alert['is_reached'] ? 'text-success fw-bold' : 'text-muted' }}">
                                Ora: {{ $alert['current_price_formatted'] }}
                                @if($alert['is_reached']) <i class="fas fa-check-circle ms-1"></i> @endif
                            </small>
                        </div>
                    </div>
                    @endforeach
                    
                    <a href="{{ route('alerts.index') }}" class="btn btn-sm btn-outline-danger w-100 mt-3">
                        Gestisci Alert
                    </a>
                </div>
            </div>
            @endif

            <!-- Attività Recente -->
            @if($activity_timeline->isNotEmpty())
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-clock text-primary me-2"></i>Attività Recente</h6>
                    
                    @foreach($activity_timeline as $activity)
                    <div class="timeline-item {{ $loop->last ? '' : 'mb-3' }}" 
                         style="{{ $loop->last ? 'border-left: none; padding-bottom: 0;' : '' }}">
                        <small class="text-muted d-block">{{ $activity['time_ago'] }}</small>
                        <p class="mb-0 small">{!! $activity['description'] !!}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Consigli Personalizzati -->
            @if($recommendations)
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-3">
                        <i class="fas fa-magic me-2"></i>Consiglio per Te
                    </h6>
                    <p class="small mb-3">
                        {!! $recommendations['message'] !!}
                    </p>
                    <a href="{{ route('crociere.index') }}" class="btn btn-light btn-sm w-100">
                        <i class="fas fa-arrow-right me-2"></i>Scopri le offerte
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
