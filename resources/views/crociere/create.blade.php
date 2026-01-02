@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        {{-- Header Section --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">🚢 Ricerca Crociere</h2>
                        <p class="text-muted mb-0">Trova le migliori offerte personalizzate</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Cards - Senza crociere scadute --}}
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card stats-card stats-total">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon">
                                <i class="fas fa-ship"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stats-number" id="total-cruises">0</div>
                                <div class="stats-label">TOTALE CROCIERE</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card stats-card stats-available">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stats-number" id="available-cruises">0</div>
                                <div class="stats-label">DISPONIBILI</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card stats-card stats-companies">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stats-number" id="total-companies">0</div>
                                <div class="stats-label">COMPAGNIE</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search Form --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form id="search-form" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="date-range" class="form-label">Periodo di Viaggio <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="date-range" name="date_range" class="form-control"
                                        placeholder="Seleziona periodo" required data-field="periodo">
                                    <div class="invalid-feedback">Seleziona un periodo di viaggio</div>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="budget" class="form-label">Budget Totale (€) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="budget" id="budget" class="form-control"
                                        placeholder="Es. 2000" required min="100" step="50" data-field="budget">
                                    <div class="form-text"><small id="budget-per-person" class="text-muted"></small></div>
                                    <div class="invalid-feedback">Inserisci un budget valido</div>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="participants" class="form-label">Numero Partecipanti <span
                                            class="text-danger">*</span></label>
                                    <select name="participants" id="participants" class="form-control" required
                                        data-field="partecipanti">
                                        <option value="">Seleziona partecipanti</option>
                                        <option value="1">1 persona</option>
                                        <option value="2" selected>2 persone</option>
                                        <option value="3">3 persone</option>
                                        <option value="4">4 persone</option>
                                        <option value="5">5 persone</option>
                                        <option value="6">6 persone</option>
                                        <option value="7">7 persone</option>
                                        <option value="8">8 persone</option>
                                        <option value="9">9 persone</option>
                                        <option value="10">10 persone</option>
                                    </select>
                                    <div class="invalid-feedback">Inserisci numero partecipanti (1-10)</div>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="port_start" class="form-label">Porto di Imbarco</label>
                                    <input type="text" name="port_start" id="port_start" class="form-control"
                                        placeholder="Es. Civitavecchia" list="ports-start" data-field="porto">
                                    <datalist id="ports-start">
                                        <option value="Civitavecchia">
                                        <option value="Barcellona">
                                        <option value="Venezia">
                                        <option value="Genova">
                                        <option value="Napoli">
                                        <option value="Palermo">
                                    </datalist>
                                </div>
                                <div class="col-md-12 col-lg-12 mb-12 " style="text-align: center">
                                    <button type="submit" class="btn btn-primary me-2" id="search-btn">
                                        <i class="fas fa-search me-2"></i>Cerca Crociere
                                        <span class="spinner-border spinner-border-sm ms-2 d-none"
                                            id="loading-spinner"></span>
                                    </button>
                                    <button type="reset" class="btn btn-secondary">Reset</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dynamic Message Box --}}
        <div class="row mb-4">
            <div class="col-12">
                <div id="dynamic-message" class="message-box message-default">
                    <div class="text-center">
                        <i class="fas fa-info-circle icon-large"></i>
                        <strong>Compila i campi sopra per iniziare la ricerca</strong>
                        <div class="progress-indicator">
                            <span class="progress-step" data-step="periodo" title="Periodo di viaggio"></span>
                            <span class="progress-step" data-step="partecipanti" title="Numero partecipanti"></span>
                            <span class="progress-step" data-step="budget" title="Budget totale"></span>
                            <span class="progress-step" data-step="porto" title="Porto di imbarco (opzionale)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Results Section --}}
        <div id="results-section" class="d-none">

            {{-- Search Results Summary - Enhanced Gauges --}}
            <div class="row mb-3">
                <div class="col-xl-6 mb-3">
                    <div class="card satisfaction-card">
                        <div class="card-body text-center py-4">
                            <h6 class="mb-3 text-dark fw-bold">Soddisfazione Ricerca Attuale</h6>
                            <div class="satisfaction-gauge-container mx-auto mb-3">
                                <div id="satisfaction-gauge" style="width: 120px; height: 120px;"></div>
                                <div class="gauge-overlay">
                                    <div class="gauge-score" id="satisfaction-score">0</div>
                                    <div class="gauge-percent">%</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-center">
                                    <div class="fw-bold text-dark" id="total-matches">0</div>
                                    <small class="text-muted">Risultati</small>
                                </div>
                                <div class="text-center">
                                    <div class="fw-bold" id="satisfaction-rating">Scarso</div>
                                    <small class="text-muted">Valutazione</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 mb-3">
                    <div class="card optimization-card">
                        <div class="card-body text-center py-4">
                            <h6 class="mb-3 text-dark fw-bold">Potenziale di Ottimizzazione</h6>
                            <div class="satisfaction-gauge-container mx-auto mb-3">
                                <div id="optimization-gauge" style="width: 120px; height: 120px;"></div>
                                <div class="gauge-overlay">
                                    <div class="gauge-score" id="optimization-score">0</div>
                                    <div class="gauge-percent">%</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-center">
                                    <div class="fw-bold text-dark" id="total-alternatives">0</div>
                                    <small class="text-muted">Alternative</small>
                                </div>
                                <div class="text-center">
                                    <div class="fw-bold" id="optimization-suggestion">Espandi criteri</div>
                                    <small class="text-muted">Suggerimento</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search Statistics --}}
            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <div class="card mini-stat-card">
                        <div class="card-body text-center py-3">
                            <div class="stats-mini-icon mb-2">
                                <i class="fas fa-piggy-bank"></i>
                            </div>
                            <h6 class="mb-1"><span id="avg-savings">0</span>%</h6>
                            <p class="text-muted mb-0 small">Risparmio Medio</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card mini-stat-card">
                        <div class="card-body text-center py-3">
                            <div class="stats-mini-icon mb-2">
                                <i class="fas fa-building"></i>
                            </div>
                            <h6 class="mb-1"><span id="companies-found">0</span></h6>
                            <p class="text-muted mb-0 small">Compagnie</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card mini-stat-card">
                        <div class="card-body text-center py-3">
                            <div class="stats-mini-icon mb-2">
                                <i class="fas fa-moon"></i>
                            </div>
                            <h6 class="mb-1"><span id="avg-duration">0</span></h6>
                            <p class="text-muted mb-0 small">Durata Media (notti)</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card mini-stat-card">
                        <div class="card-body text-center py-3">
                            <div class="stats-mini-icon mb-2">
                                <i class="fas fa-euro-sign"></i>
                            </div>
                            <h6 class="mb-1">€<span id="avg-price-found">0</span></h6>
                            <p class="text-muted mb-0 small">Prezzo Medio</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Results Tables --}}
            <div class="row mb-3">
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h6 class="mb-0">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Crociere Compatibili
                            </h6>
                            <span class="badge bg-success" id="matches-count">0</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 table-sm" id="matches-table">
                                    <thead class="table-header">
                                        <tr>
                                            <th>NAVE</th>
                                            <th>CROCIERA</th>
                                            <th>COMPAGNIA</th>
                                            <th>DURATA</th>
                                            <th>PREZZO GRUPPO</th>
                                            <th>COSTO/GIORNO</th>
                                            @auth
                                                <th class="text-center">AZIONI</th>
                                            @endauth
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div id="no-matches" class="text-center py-4 d-none">
                                <i class="fas fa-search fa-2x text-muted mb-2"></i>
                                <h6 class="text-muted">Nessuna crociera trovata</h6>
                                <p class="text-muted mb-0 small">Modifica i parametri di ricerca</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h6 class="mb-0">
                                <i class="fas fa-star text-warning me-2"></i>
                                Suggerimenti Alternativi
                            </h6>
                            <span class="badge bg-warning text-dark" id="alternatives-count">0</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 table-sm" id="alternatives-table">
                                    <thead class="table-header">
                                        <tr>
                                            <th>NAVE</th>
                                            <th>CROCIERA</th>
                                            <th>COMPAGNIA</th>
                                            <th>DURATA</th>
                                            <th>PREZZO GRUPPO</th>
                                            <th>COSTO/GIORNO</th>
                                            <th>BENEFIT</th>
                                            @auth
                                                <th class="text-center">AZIONI</th>
                                            @endauth
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div id="no-alternatives" class="text-center py-4 d-none">
                                <i class="fas fa-lightbulb fa-2x text-muted mb-2"></i>
                                <h6 class="text-muted">Nessun suggerimento disponibile</h6>
                                <p class="text-muted mb-0 small">Modifica i criteri per vedere alternative</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- AI Suggestions --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header py-2">
                            <h6 class="mb-0">
                                <i class="fas fa-robot text-primary me-2"></i>
                                Consigli Intelligenti AI
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div id="ai-suggestions">
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm text-primary me-3" role="status"></div>
                                    <span class="text-muted">Analizzando i tuoi parametri...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Dettaglio Crociera (solo per utenti autenticati) --}}
    @auth
        <div class="modal fade" id="cruiseDetailModal" tabindex="-1" aria-labelledby="cruiseDetailModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="cruiseDetailModalLabel">
                            <i class="fas fa-ship me-2"></i>
                            <span id="modal-cruise-name">Dettaglio Crociera</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modal-cruise-body">
                        {{-- Contenuto dinamico caricato via JavaScript --}}
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Caricamento...</span>
                            </div>
                            <p class="mt-3 text-muted">Caricamento dettagli...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Chiudi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endauth

    {{-- Toast Container --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
        <div id="toast-container"></div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    @include('crociere.assets.css')
    @include('crociere.assets.js')
    @include('crociere.assets.js_modal_details')
@endsection
