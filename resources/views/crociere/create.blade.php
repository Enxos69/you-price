@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4 gradient-bg" style="min-height: 100vh;">
        
        {{-- HERO SECTION --}}
        <div class="text-center text-white mb-5">
            <h1 class="display-4 fw-bold mb-3">🚢 Trova la Tua Crociera Ideale</h1>
            <p class="lead">Confronta prezzi e trova le migliori offerte personalizzate</p>
        </div>

        {{-- FORM RICHIESTA CROCIERA --}}
        <div class="card shadow-lg mb-5" style="border-radius: 20px; backdrop-filter: blur(10px); background: rgba(255,255,255,0.95);">
            <div class="card-body p-5">
                <div class="row align-items-center mb-4">
                    <div class="col">
                        <h2 class="mb-0 text-primary">
                            <i class="fas fa-search me-2"></i>Cerca la tua crociera
                        </h2>
                    </div>
                </div>
                
                <form id="search-form">
                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-floating">
                                <input type="text" id="date-range" name="date_range" class="form-control form-control-lg"
                                    placeholder="Seleziona il periodo" required style="border-radius: 12px;">
                                <label><i class="fas fa-calendar-alt me-2"></i>Periodo *</label>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6">
                            <div class="form-floating">
                                <input type="number" name="budget" class="form-control form-control-lg" 
                                    placeholder="Budget" required style="border-radius: 12px;" min="100" step="50">
                                <label><i class="fas fa-euro-sign me-2"></i>Budget totale (€) *</label>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6">
                            <div class="form-floating">
                                <input type="number" name="participants" class="form-control form-control-lg" 
                                    placeholder="Partecipanti" required style="border-radius: 12px;" min="1" max="10">
                                <label><i class="fas fa-users me-2"></i>Numero partecipanti *</label>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 col-md-6">
                            <div class="form-floating">
                                <input type="text" name="port_start" class="form-control form-control-lg" 
                                    placeholder="Porto di imbarco" style="border-radius: 12px;">
                                <label><i class="fas fa-anchor me-2"></i>Porto di imbarco (opzionale)</label>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 col-md-6">
                            <div class="form-floating">
                                <input type="text" name="port_end" class="form-control form-control-lg" 
                                    placeholder="Porto di destinazione" style="border-radius: 12px;">
                                <label><i class="fas fa-map-marker-alt me-2"></i>Porto di destinazione (opzionale)</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5 py-3" 
                            style="border-radius: 50px; background: linear-gradient(45deg, #667eea, #764ba2); border: none; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                            <i class="fas fa-ship me-2"></i>Trova Crociere
                            <div class="spinner-border spinner-border-sm ms-2 d-none" id="loading-spinner"></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- SEZIONE RISULTATI --}}
        <div id="results-section" class="d-none">
            
            {{-- METRICHE DI SODDISFAZIONE --}}
            <div class="row g-4 mb-5">
                <div class="col-lg-6">
                    <div class="card h-100 shadow-lg" style="border-radius: 20px; background: linear-gradient(135deg, #ff6b6b, #ee5a52);">
                        <div class="card-body text-center text-white p-4">
                            <h5 class="mb-4"><i class="fas fa-chart-pie me-2"></i>Soddisfazione Ricerca Attuale</h5>
                            <div style="position: relative; height: 250px;">
                                <canvas id="gaugeAttuale"></canvas>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <h2 class="mb-0" id="score-current">0%</h2>
                                    <small>Compatibilità</small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark px-3 py-2" id="current-matches">0 crociere trovate</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card h-100 shadow-lg" style="border-radius: 20px; background: linear-gradient(135deg, #4ecdc4, #44a08d);">
                        <div class="card-body text-center text-white p-4">
                            <h5 class="mb-4"><i class="fas fa-lightbulb me-2"></i>Suggerimento Ottimizzato</h5>
                            <div style="position: relative; height: 250px;">
                                <canvas id="gaugeOttimale"></canvas>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <h2 class="mb-0" id="score-optimal">0%</h2>
                                    <small>Potenziale</small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark px-3 py-2" id="optimal-suggestion">Modifica i parametri</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABELLE CROCIERE --}}
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card shadow-lg h-100" style="border-radius: 20px;">
                        <div class="card-header bg-primary text-white" style="border-radius: 20px 20px 0 0;">
                            <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Crociere Compatibili</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="table-matches">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="fas fa-ship me-1"></i>Nave</th>
                                            <th><i class="fas fa-building me-1"></i>Compagnia</th>
                                            <th><i class="fas fa-moon me-1"></i>Notti</th>
                                            <th><i class="fas fa-euro-sign me-1"></i>Prezzo</th>
                                            <th><i class="fas fa-calendar me-1"></i>Partenza</th>
                                            <th><i class="fas fa-percentage me-1"></i>Match</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div id="no-matches" class="text-center py-5 d-none">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nessuna crociera trovata</h5>
                                <p class="text-muted">Prova a modificare i parametri di ricerca</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card shadow-lg h-100" style="border-radius: 20px;">
                        <div class="card-header bg-success text-white" style="border-radius: 20px 20px 0 0;">
                            <h6 class="mb-0"><i class="fas fa-star me-2"></i>Suggerimenti Alternativi</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="table-suggestions">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="fas fa-ship me-1"></i>Nave</th>
                                            <th><i class="fas fa-building me-1"></i>Compagnia</th>
                                            <th><i class="fas fa-moon me-1"></i>Notti</th>
                                            <th><i class="fas fa-euro-sign me-1"></i>Prezzo</th>
                                            <th><i class="fas fa-calendar me-1"></i>Partenza</th>
                                            <th><i class="fas fa-magic me-1"></i>Benefit</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- SEZIONE CONSIGLI --}}
            <div class="card shadow-lg mt-4" style="border-radius: 20px; background: linear-gradient(135deg, #ffecd2, #fcb69f);">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="fas fa-robot me-2"></i>Consigli per Migliorare la Ricerca</h5>
                    <div id="suggestions-text">
                        <p class="mb-0">I nostri algoritmi analizzeranno i tuoi parametri e ti suggeriranno come ottimizzare la ricerca...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@include('crociere.assets.css')
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    @parent
    @include('crociere.assets.js')
@endsection