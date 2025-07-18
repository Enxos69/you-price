@extends('layouts.app')

@section('content')
    <div class="cruise-show-wrapper">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">

                    <!-- Header della pagina -->
                    <div class="page-header">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="fas fa-ship"></i>
                            </div>
                            <div class="header-text">
                                <h1>{{ $cruise->ship }}</h1>
                                <p>{{ $cruise->cruise }} - {{ $cruise->line }}</p>
                            </div>
                        </div>
                        <div class="header-actions">
                            <a href="{{ route('cruises.edit', $cruise->id) }}" class="btn btn-action" style="color: white">
                                <i class="fas fa-edit me-2"></i>Modifica
                            </a>
                            <a href="{{ route('cruises.index') }}" class="btn btn-action" style="color: white">
                                <i class="fas fa-arrow-left me-2"></i>Torna alla Lista
                            </a>
                        </div>
                    </div>

                    <!-- Card principale -->
                    <div class="show-card">
                        <div class="card-body">

                            <!-- Alert Messages -->
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <!-- Informazioni Base -->
                            <div class="info-section">
                                <h5 class="section-title">
                                    <i class="fas fa-info-circle me-2"></i>Informazioni Base
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label>Nome Nave:</label>
                                            <span class="value-highlight">{{ $cruise->ship }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label>Compagnia:</label>
                                            <span class="badge-company {{ strtolower(str_replace(' ', '-', $cruise->line)) }}">
                                                {{ $cruise->line }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <label>Nome Crociera:</label>
                                    <span class="value-highlight">{{ $cruise->cruise }}</span>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label>Durata:</label>
                                            <span>{{ $cruise->formatted_duration }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label>ID Crociera:</label>
                                            <span class="text-muted">#{{ $cruise->id }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Itinerario -->
                            <div class="info-section">
                                <h5 class="section-title">
                                    <i class="fas fa-route me-2"></i>Itinerario
                                </h5>
                                
                                <div class="itinerary-container">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="itinerary-point departure">
                                                <div class="point-icon">
                                                    <i class="fas fa-play-circle"></i>
                                                </div>
                                                <div class="point-details">
                                                    <h6>Partenza</h6>
                                                    <div class="date">
                                                        {{ $cruise->partenza ? $cruise->partenza->format('d/m/Y') : 'Non specificata' }}
                                                    </div>
                                                    <div class="port">
                                                        {{ $cruise->from ?: 'Porto non specificato' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="itinerary-point arrival">
                                                <div class="point-icon">
                                                    <i class="fas fa-stop-circle"></i>
                                                </div>
                                                <div class="point-details">
                                                    <h6>Arrivo</h6>
                                                    <div class="date">
                                                        {{ $cruise->arrivo ? $cruise->arrivo->format('d/m/Y') : 'Non specificata' }}
                                                    </div>
                                                    <div class="port">
                                                        {{ $cruise->to ?: 'Porto non specificato' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($cruise->partenza && $cruise->arrivo)
                                        <div class="duration-info">
                                            <i class="fas fa-clock me-2"></i>
                                            Durata viaggio: {{ $cruise->partenza->diffInDays($cruise->arrivo) }} giorni
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Prezzi Cabine -->
                            <div class="info-section">
                                <h5 class="section-title">
                                    <i class="fas fa-euro-sign me-2"></i>Prezzi Cabine
                                </h5>
                                
                                <div class="prices-grid">
                                    <div class="price-card {{ $cruise->interior ? 'available' : 'unavailable' }}">
                                        <div class="price-icon">
                                            <i class="fas fa-bed"></i>
                                        </div>
                                        <div class="price-details">
                                            <h6>Cabina Interna</h6>
                                            <div class="price">
                                                {{ $cruise->interior ? '€' . number_format($cruise->interior, 0, ',', '.') : 'Non disponibile' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="price-card {{ $cruise->oceanview ? 'available' : 'unavailable' }}">
                                        <div class="price-icon">
                                            <i class="fas fa-window-maximize"></i>
                                        </div>
                                        <div class="price-details">
                                            <h6>Vista Mare</h6>
                                            <div class="price">
                                                {{ $cruise->oceanview ? '€' . number_format($cruise->oceanview, 0, ',', '.') : 'Non disponibile' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="price-card {{ $cruise->balcony ? 'available' : 'unavailable' }}">
                                        <div class="price-icon">
                                            <i class="fas fa-home"></i>
                                        </div>
                                        <div class="price-details">
                                            <h6>Balcone</h6>
                                            <div class="price">
                                                {{ $cruise->balcony ? '€' . number_format($cruise->balcony, 0, ',', '.') : 'Non disponibile' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="price-card {{ $cruise->minisuite ? 'available' : 'unavailable' }}">
                                        <div class="price-icon">
                                            <i class="fas fa-crown"></i>
                                        </div>
                                        <div class="price-details">
                                            <h6>Mini Suite</h6>
                                            <div class="price">
                                                {{ $cruise->minisuite ? '€' . number_format($cruise->minisuite, 0, ',', '.') : 'Non disponibile' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="price-card {{ $cruise->suite ? 'available' : 'unavailable' }}">
                                        <div class="price-icon">
                                            <i class="fas fa-gem"></i>
                                        </div>
                                        <div class="price-details">
                                            <h6>Suite</h6>
                                            <div class="price">
                                                {{ $cruise->suite ? '€' . number_format($cruise->suite, 0, ',', '.') : 'Non disponibile' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($cruise->has_prices)
                                    <div class="price-summary">
                                        <div class="summary-item">
                                            <span class="label">Range Prezzi:</span>
                                            <span class="value">{{ $cruise->price_range }}</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="label">Prezzo Minimo:</span>
                                            <span class="value min-price">€{{ number_format($cruise->min_price, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="label">Prezzo Massimo:</span>
                                            <span class="value max-price">€{{ number_format($cruise->max_price, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Dettagli -->
                            @if($cruise->details)
                                <div class="info-section">
                                    <h5 class="section-title">
                                        <i class="fas fa-file-text me-2"></i>Dettagli e Note
                                    </h5>
                                    
                                    <div class="details-content">
                                        {{ $cruise->details }}
                                    </div>
                                </div>
                            @endif

                            <!-- Informazioni Sistema -->
                            <div class="info-section">
                                <h5 class="section-title">
                                    <i class="fas fa-cog me-2"></i>Informazioni Sistema
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label>Data Creazione:</label>
                                            <span>{{ $cruise->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label>Ultima Modifica:</label>
                                            <span>{{ $cruise->updated_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label>Stato:</label>
                                            @if($cruise->partenza && $cruise->partenza->isFuture())
                                                <span class="badge bg-success">Futura</span>
                                            @elseif($cruise->partenza && $cruise->partenza->isPast())
                                                <span class="badge bg-secondary">Passata</span>
                                            @else
                                                <span class="badge bg-warning">Data non specificata</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label>Disponibilità Prezzi:</label>
                                            @if($cruise->has_prices)
                                                <span class="badge bg-success">Disponibile</span>
                                            @else
                                                <span class="badge bg-warning">Non disponibile</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Azioni -->
                            <div class="actions-section">
                                <div class="actions-grid">
                                    <a href="{{ route('cruises.edit', $cruise->id) }}" class="action-btn primary">
                                        <i class="fas fa-edit me-2"></i>
                                        <div class="action-content">
                                            <div class="action-title">Modifica</div>
                                            <div class="action-subtitle">Modifica i dettagli</div>
                                        </div>
                                    </a>

                                    <button class="action-btn danger" onclick="deleteCruise({{ $cruise->id }}, '{{ $cruise->ship }}', '{{ $cruise->cruise }}')">
                                        <i class="fas fa-trash me-2"></i>
                                        <div class="action-content">
                                            <div class="action-title">Elimina</div>
                                            <div class="action-subtitle">Elimina crociera</div>
                                        </div>
                                    </button>

                                    <a href="{{ route('cruises.index') }}" class="action-btn secondary">
                                        <i class="fas fa-list me-2"></i>
                                        <div class="action-content">
                                            <div class="action-title">Lista</div>
                                            <div class="action-subtitle">Torna alla lista</div>
                                        </div>
                                    </a>

                                    <button class="action-btn info" onclick="printCruise()">
                                        <i class="fas fa-print me-2"></i>
                                        <div class="action-content">
                                            <div class="action-title">Stampa</div>
                                            <div class="action-subtitle">Stampa dettagli</div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Crociere Simili -->
                    @if($cruise->getSimilarCruises()->count() > 0)
                        <div class="similar-cruises-card">
                            <div class="card-body">
                                <h5 class="section-title">
                                    <i class="fas fa-search me-2"></i>Crociere Simili
                                </h5>
                                
                                <div class="similar-cruises-grid">
                                    @foreach($cruise->getSimilarCruises() as $similar)
                                        <div class="similar-cruise-item">
                                            <div class="similar-header">
                                                <h6>{{ $similar->ship }}</h6>
                                                <span class="badge bg-primary">{{ $similar->line }}</span>
                                            </div>
                                            <div class="similar-details">
                                                <div class="similar-cruise">{{ $similar->cruise }}</div>
                                                <div class="similar-price">{{ $similar->price_range }}</div>
                                            </div>
                                            <div class="similar-actions">
                                                <a href="{{ route('cruises.show', $similar->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('cruises.edit', $similar->id) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection

@include('admin.cruises.assets.css_cruises_show')

@section('scripts')
    @parent
    @include('admin.cruises.assets.js_cruises_show')
@endsection