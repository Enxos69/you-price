@extends('layouts.app')

@section('content')
    <div class="analytics-page-wrapper">
        <div class="container-fluid">
            {{-- Header --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">📊 Analytics Ricerche Crociere</h2>
                            <p class="text-muted mb-0">Dashboard completa delle ricerche e comportamenti utente</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button id="refresh-btn" class="btn btn-outline-primary">
                                <i class="fas fa-sync-alt"></i> Aggiorna
                            </button>
                            <button id="export-btn" class="btn btn-success">
                                <i class="fas fa-download"></i> Esporta CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistiche Generali --}}
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stats-card stats-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="main-stat">
                                            <div class="stats-number" id="total-searches">0</div>
                                            <div class="stats-label">RICERCHE TOTALI</div>
                                        </div>
                                        <div class="daily-comparison">
                                            <div class="comparison-row">
                                                <span class="comp-label">Oggi:</span>
                                                <span class="comp-value" id="today-searches">0</span>
                                            </div>
                                            <div class="comparison-row">
                                                <span class="comp-label">Ieri:</span>
                                                <span class="comp-value" id="yesterday-searches">0</span>
                                            </div>
                                            <div class="comparison-row variation">
                                                <span class="stats-change" id="daily-change">+0%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stats-card stats-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="stats-number" id="success-rate">0</div>
                                    <div class="stats-label">TASSO SUCCESSO</div>
                                    <div class="stats-change text-success">
                                        <span id="successful-searches">0</span> / <span id="total-searches-small">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stats-card stats-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="stats-number" id="registered-users-pct">0</div>
                                    <div class="stats-label">UTENTI REGISTRATI</div>
                                    <div class="stats-change" id="registered-vs-guest">vs Ospiti</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stats-card stats-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="stats-number" id="avg-duration">0</div>
                                    <div class="stats-label">DURATA MEDIA (ms)</div>
                                    <div class="stats-change">
                                        Soddisfazione: <span id="avg-satisfaction">0</span>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grafici Trend --}}
            <div class="row mb-4">
                <div class="col-lg-8 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">📈 Trend Ricerche (30 giorni)</h6>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary active"
                                    data-period="7">7g</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-period="30">30g</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-period="90">90g</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="trends-chart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">📱 Dispositivi</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="devices-chart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistiche Geografiche e Parametri --}}
            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">🌍 Top Paesi</h6>
                        </div>
                        <div class="card-body">
                            <div id="countries-list" class="list-group list-group-flush">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Caricamento...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">🏖️ Porti Più Ricercati</h6>
                        </div>
                        <div class="card-body">
                            <div id="ports-list" class="list-group list-group-flush">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Caricamento...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-anchor me-2"></i>🏖️ Porti Più Ricercati
                            </h6>
                            <span class="badge bg-primary" id="ports-count">0</span>
                        </div>
                        <div class="card-body p-0" style="position: relative;">
                            <div id="ports-list" class="list-group list-group-flush">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Caricamento porti...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Budget e Partecipanti --}}
            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">💰 Distribuzione Budget</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="budget-chart" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">👥 Numero Partecipanti</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="participants-chart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Performance Metrics --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">⚡ Metriche Performance</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 text-center">
                                    <div class="performance-metric">
                                        <div class="metric-value" id="avg-duration-detailed">0ms</div>
                                        <div class="metric-label">Durata Media</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="performance-metric">
                                        <div class="metric-value" id="p95-duration">0ms</div>
                                        <div class="metric-label">95° Percentile</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="performance-metric">
                                        <div class="metric-value" id="slow-searches">0</div>
                                        <div class="metric-label">Ricerche Lente</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="performance-metric">
                                        <div class="metric-value" id="failed-searches">0</div>
                                        <div class="metric-label">Ricerche Fallite</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="performance-metric">
                                        <div class="metric-value" id="mobile-performance">0ms</div>
                                        <div class="metric-label">Performance Mobile</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="performance-metric">
                                        <div class="metric-value" id="desktop-performance">0ms</div>
                                        <div class="metric-label">Performance Desktop</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabella Log Ricerche --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">📋 Log Ricerche Recenti</h6>
                            <div class="d-flex gap-2">
                                <select id="filter-user-type" class="form-select form-select-sm">
                                    <option value="">Tutti gli utenti</option>
                                    <option value="registered">Solo registrati</option>
                                    <option value="guest">Solo ospiti</option>
                                </select>
                                <select id="filter-device" class="form-select form-select-sm">
                                    <option value="">Tutti i dispositivi</option>
                                    <option value="mobile">Mobile</option>
                                    <option value="tablet">Tablet</option>
                                    <option value="desktop">Desktop</option>
                                </select>
                                <select id="filter-success" class="form-select form-select-sm">
                                    <option value="">Tutti i risultati</option>
                                    <option value="true">Solo successi</option>
                                    <option value="false">Solo fallimenti</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="search-logs-table">
                                    <thead class="table-header">
                                        <tr>
                                            <th>Data</th>
                                            <th>Utente</th>
                                            <th>Dispositivo</th>
                                            <th>Parametri</th>
                                            <th>Risultati</th>
                                            <th>Performance</th>
                                            <th>Località</th>
                                            <th>Stato</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                                <span class="ms-2">Caricamento log...</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="pagination">
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Export Modal --}}
        <div class="modal fade" id="exportModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Esporta Dati Analytics</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="export-form">
                            <div class="mb-3">
                                <label class="form-label">Tipo Utente</label>
                                <select name="user_type" class="form-select">
                                    <option value="">Tutti</option>
                                    <option value="registered">Solo registrati</option>
                                    <option value="guest">Solo ospiti</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dispositivo</label>
                                <select name="device_type" class="form-select">
                                    <option value="">Tutti</option>
                                    <option value="mobile">Mobile</option>
                                    <option value="tablet">Tablet</option>
                                    <option value="desktop">Desktop</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Data Inizio</label>
                                        <input type="date" name="date_from" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Data Fine</label>
                                        <input type="date" name="date_to" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="button" class="btn btn-success" id="confirm-export">
                            <i class="fas fa-download"></i> Esporta CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@include('admin.analytics.css_analytics')
@section('scripts')
    @parent
    @include('admin.analytics.js_analytics')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
@endsection
