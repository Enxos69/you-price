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
                                    <div class="stats-number" id="total-searches">0</div>
                                    <div class="stats-label">RICERCHE TOTALI</div>
                                    <div class="stats-change" id="daily-change">+0%</div>
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

                <div class="col-lg-6 mb-3">
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

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>

    <style>
        /* Stili personalizzati per la dashboard analytics */
        .stats-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.15s ease-in-out;
            overflow: hidden;
            position: relative;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #007bff, #0056b3);
        }

        .stats-card.stats-primary::before {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }

        .stats-card.stats-success::before {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }

        .stats-card.stats-info::before {
            background: linear-gradient(135deg, #17a2b8, #117a8b);
        }

        .stats-card.stats-warning::before {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }

        .stats-icon {
            width: 3rem;
            height: 3rem;
            background: linear-gradient(135deg, var(--bs-primary), var(--bs-primary-dark, #0056b3));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .stats-success .stats-icon {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }

        .stats-info .stats-icon {
            background: linear-gradient(135deg, #17a2b8, #117a8b);
        }

        .stats-warning .stats-icon {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .analytics-page-wrapper {
            padding-top: 50px;
            padding-bottom: 20px;
            background: linear-gradient(135deg, #f8fdfc 0%, #e8f5f3 100%);
            min-height: 100vh;
        }

        .stats-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1;
        }

        .stats-change {
            font-size: 0.875rem;
            color: #28a745;
            font-weight: 500;
        }

        .performance-metric {
            padding: 1rem 0;
        }

        .metric-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 0.25rem;
        }

        .metric-label {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }

        .table-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }

        .table-header th {
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .list-group-item {
            border: none;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .country-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .country-name {
            font-weight: 500;
        }

        .country-count {
            background: #e9ecef;
            color: #495057;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
        }

        .device-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .user-type-badge {
            background: #f3e5f5;
            color: #7b1fa2;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .btn-group .btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-number {
                font-size: 1.5rem;
            }

            .stats-icon {
                width: 2.5rem;
                height: 2.5rem;
                font-size: 1rem;
            }

            .metric-value {
                font-size: 1.25rem;
            }

            .performance-metric {
                padding: 0.5rem 0;
            }
        }
    </style>

    <script>
        // Analytics Dashboard JavaScript
        $(document).ready(function() {
            let trendsChart, devicesChart, budgetChart, participantsChart;
            let currentPage = 1;

            // Inizializzazione
            loadGeneralStats();
            loadCharts();
            loadGeographicData();
            loadSearchParameters();
            loadPerformanceMetrics();
            loadSearchLogs();

            // Event Listeners
            $('#refresh-btn').click(function() {
                refreshAllData();
            });

            $('#export-btn').click(function() {
                $('#exportModal').modal('show');
            });

            $('#confirm-export').click(function() {
                exportData();
            });

            // Filtri tabella
            $('#filter-user-type, #filter-device, #filter-success').change(function() {
                currentPage = 1;
                loadSearchLogs();
            });

            // Period buttons per trend chart
            $('.btn-group button[data-period]').click(function() {
                $('.btn-group button').removeClass('active');
                $(this).addClass('active');
                const period = $(this).data('period');
                loadTrendsChart(period);
            });

            // Funzioni principali
            async function loadGeneralStats() {
                try {
                    const response = await fetch('/api/analytics/general-stats');
                    const data = await response.json();

                    if (response.ok) {
                        updateGeneralStatsUI(data);
                    } else {
                        showError('Errore caricamento statistiche generali');
                    }
                } catch (error) {
                    showError('Errore di connessione: ' + error.message);
                }
            }

            function updateGeneralStatsUI(data) {
                animateCounter('#total-searches', data.total_searches || 0);
                animateCounter('#successful-searches', data.successful_searches || 0);
                animateCounter('#total-searches-small', data.total_searches || 0);

                // Calcola tasso di successo
                const successRate = data.total_searches > 0 ?
                    Math.round((data.successful_searches / data.total_searches) * 100) : 0;
                animateCounter('#success-rate', successRate, '%');

                // Calcola percentuale utenti registrati
                const registeredPct = data.total_searches > 0 ?
                    Math.round((data.registered_users_searches / data.total_searches) * 100) : 0;
                animateCounter('#registered-users-pct', registeredPct, '%');

                // Durata media
                $('#avg-duration').text(Math.round(data.avg_search_duration || 0));
                $('#avg-satisfaction').text(Math.round(data.avg_satisfaction || 0));

                // Variazione giornaliera
                const changeClass = data.daily_change_percent >= 0 ? 'text-success' : 'text-danger';
                const changeIcon = data.daily_change_percent >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                $('#daily-change').html(`<i class="fas ${changeIcon}"></i> ${data.daily_change_percent}%`)
                    .removeClass('text-success text-danger')
                    .addClass(changeClass);

                // Rapporto registrati vs ospiti
                $('#registered-vs-guest').text(`${data.registered_users_searches} vs ${data.guest_searches}`);
            }

            async function loadCharts() {
                await loadTrendsChart(30);
                await loadDevicesChart();
            }

            async function loadTrendsChart(days = 30) {
                try {
                    const response = await fetch(`/api/analytics/search-trends?days=${days}`);
                    const data = await response.json();

                    if (response.ok) {
                        updateTrendsChart(data);
                    }
                } catch (error) {
                    console.error('Errore caricamento trend:', error);
                }
            }

            function updateTrendsChart(data) {
                const ctx = document.getElementById('trends-chart').getContext('2d');

                if (trendsChart) {
                    trendsChart.destroy();
                }

                const labels = data.map(item => moment(item.date).format('DD/MM'));
                const searches = data.map(item => item.total_searches);
                const successful = data.map(item => item.successful_searches);
                const satisfaction = data.map(item => Math.round(item.avg_satisfaction || 0));

                trendsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ricerche Totali',
                            data: searches,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y'
                        }, {
                            label: 'Ricerche Riuscite',
                            data: successful,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y'
                        }, {
                            label: 'Soddisfazione Media (%)',
                            data: satisfaction,
                            borderColor: '#ffc107',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Numero Ricerche'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Soddisfazione (%)'
                                },
                                grid: {
                                    drawOnChartArea: false,
                                },
                                max: 100
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        }
                    }
                });
            }

            async function loadDevicesChart() {
                try {
                    const response = await fetch('/api/analytics/device-stats');
                    const data = await response.json();

                    if (response.ok) {
                        updateDevicesChart(data.devices);
                    }
                } catch (error) {
                    console.error('Errore caricamento dispositivi:', error);
                }
            }

            function updateDevicesChart(devices) {
                const ctx = document.getElementById('devices-chart').getContext('2d');

                if (devicesChart) {
                    devicesChart.destroy();
                }

                const labels = devices.map(d => {
                    switch (d.device_type) {
                        case 'mobile':
                            return 'Mobile';
                        case 'tablet':
                            return 'Tablet';
                        case 'desktop':
                            return 'Desktop';
                        default:
                            return 'Altro';
                    }
                });
                const counts = devices.map(d => d.count);
                const colors = ['#007bff', '#28a745', '#ffc107', '#dc3545'];

                devicesChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: counts,
                            backgroundColor: colors.slice(0, labels.length),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            async function loadGeographicData() {
                try {
                    const response = await fetch('/api/analytics/geographic-stats');
                    const data = await response.json();

                    if (response.ok) {
                        updateCountriesList(data.countries);
                    }
                } catch (error) {
                    console.error('Errore caricamento dati geografici:', error);
                }
            }

            function updateCountriesList(countries) {
                const container = $('#countries-list');
                container.empty();

                if (countries.length === 0) {
                    container.html('<div class="text-center py-3 text-muted">Nessun dato disponibile</div>');
                    return;
                }

                countries.forEach(country => {
                    const flag = getCountryFlag(country.country);
                    container.append(`
                <div class="list-group-item">
                    <div class="country-item">
                        <span class="country-name">${flag} ${country.country}</span>
                        <span class="country-count">${country.searches}</span>
                    </div>
                </div>
            `);
                });
            }

            async function loadSearchParameters() {
                try {
                    const response = await fetch('/api/analytics/search-parameters');
                    const data = await response.json();

                    if (response.ok) {
                        updatePortsList(data.popular_ports);
                        updateBudgetChart(data.budget_ranges);
                        updateParticipantsChart(data.participants);
                    }
                } catch (error) {
                    console.error('Errore caricamento parametri ricerca:', error);
                }
            }

            function updatePortsList(ports) {
                const container = $('#ports-list');
                container.empty();

                if (ports.length === 0) {
                    container.html('<div class="text-center py-3 text-muted">Nessun dato disponibile</div>');
                    return;
                }

                ports.forEach(port => {
                    container.append(`
                <div class="list-group-item">
                    <div class="country-item">
                        <span class="country-name">🏖️ ${port.port_start}</span>
                        <span class="country-count">${port.searches}</span>
                    </div>
                </div>
            `);
                });
            }

            function updateBudgetChart(budgetRanges) {
                const ctx = document.getElementById('budget-chart').getContext('2d');

                if (budgetChart) {
                    budgetChart.destroy();
                }

                const labels = Object.keys(budgetRanges).map(range => `€${range}`);
                const values = Object.values(budgetRanges);

                budgetChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ricerche',
                            data: values,
                            backgroundColor: '#007bff',
                            borderColor: '#0056b3',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            function updateParticipantsChart(participants) {
                const ctx = document.getElementById('participants-chart').getContext('2d');

                if (participantsChart) {
                    participantsChart.destroy();
                }

                const labels = participants.map(p =>
                    `${p.participants} ${p.participants === 1 ? 'persona' : 'persone'}`);
                const values = participants.map(p => p.count);

                participantsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ricerche',
                            data: values,
                            backgroundColor: '#28a745',
                            borderColor: '#1e7e34',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            async function loadPerformanceMetrics() {
                try {
                    const response = await fetch('/api/analytics/performance-metrics');
                    const data = await response.json();

                    if (response.ok) {
                        updatePerformanceMetrics(data);
                    }
                } catch (error) {
                    console.error('Errore caricamento metriche performance:', error);
                }
            }

            function updatePerformanceMetrics(data) {
                const metrics = data.overall_metrics;
                const devicePerformance = data.device_performance;

                $('#avg-duration-detailed').text(Math.round(metrics.avg_duration || 0) + 'ms');
                $('#p95-duration').text(Math.round(metrics.p95_duration || 0) + 'ms');
                $('#slow-searches').text(metrics.slow_searches || 0);
                $('#failed-searches').text(metrics.failed_searches || 0);

                // Performance per dispositivo
                const mobilePerf = devicePerformance.find(d => d.device_type === 'mobile');
                const desktopPerf = devicePerformance.find(d => d.device_type === 'desktop');

                $('#mobile-performance').text(Math.round(mobilePerf?.avg_duration || 0) + 'ms');
                $('#desktop-performance').text(Math.round(desktopPerf?.avg_duration || 0) + 'ms');
            }

            async function loadSearchLogs() {
                try {
                    const params = new URLSearchParams({
                        page: currentPage,
                        per_page: 25,
                        user_type: $('#filter-user-type').val(),
                        device_type: $('#filter-device').val(),
                        successful_only: $('#filter-success').val()
                    });

                    const response = await fetch(`/api/analytics/search-logs?${params}`);
                    const data = await response.json();

                    if (response.ok) {
                        updateSearchLogsTable(data.data);
                        updatePagination(data);
                    }
                } catch (error) {
                    console.error('Errore caricamento log:', error);
                }
            }

            function updateSearchLogsTable(logs) {
                const tbody = $('#search-logs-table tbody');
                tbody.empty();

                if (logs.length === 0) {
                    tbody.html('<tr><td colspan="8" class="text-center py-4">Nessun log trovato</td></tr>');
                    return;
                }

                logs.forEach(log => {
                    const userName = log.user ?
                        `${log.user.name} ${log.user.surname}` :
                        'Ospite';

                    const userBadge = log.user ?
                        '<span class="user-type-badge">Registrato</span>' :
                        '<span class="user-type-badge" style="background: #fff3cd; color: #856404;">Ospite</span>';

                    const deviceIcon = getDeviceIcon(log.device_type);
                    const statusBadge = log.search_successful ?
                        '<span class="status-badge status-success">Successo</span>' :
                        '<span class="status-badge status-error">Errore</span>';

                    const parameters = `${log.date_range || 'N/D'}<br>
                               €${log.budget || 0} (${log.participants || 0} pers.)`;

                    const results = `${log.total_matches || 0} match<br>
                           ${log.total_alternatives || 0} alternative`;

                    const performance = `${Math.round(log.search_duration_ms || 0)}ms<br>
                               Soddisf: ${Math.round(log.satisfaction_score || 0)}%`;

                    const location = `${getCountryFlag(log.country)} ${log.country || 'N/D'}<br>
                            <small class="text-muted">${log.city || ''}</small>`;

                    tbody.append(`
                <tr>
                    <td>${moment(log.search_date).format('DD/MM/YY HH:mm')}</td>
                    <td>${userName}<br>${userBadge}</td>
                    <td>${deviceIcon} <span class="device-badge">${log.device_type || 'N/D'}</span><br>
                        <small class="text-muted">${log.operating_system || ''}</small></td>
                    <td>${parameters}</td>
                    <td>${results}</td>
                    <td>${performance}</td>
                    <td>${location}</td>
                    <td>${statusBadge}</td>
                </tr>
            `);
                });
            }

            function updatePagination(data) {
                const pagination = $('#pagination');
                pagination.empty();

                const totalPages = data.last_page;
                const currentPageNum = data.current_page;

                // Previous button
                if (currentPageNum > 1) {
                    pagination.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${currentPageNum - 1}">Precedente</a>
                </li>
            `);
                }

                // Page numbers
                for (let i = Math.max(1, currentPageNum - 2); i <= Math.min(totalPages, currentPageNum + 2); i++) {
                    const activeClass = i === currentPageNum ? 'active' : '';
                    pagination.append(`
                <li class="page-item ${activeClass}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
                }

                // Next button
                if (currentPageNum < totalPages) {
                    pagination.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${currentPageNum + 1}">Successiva</a>
                </li>
            `);
                }

                // Event listeners for pagination
                pagination.find('a').click(function(e) {
                    e.preventDefault();
                    currentPage = parseInt($(this).data('page'));
                    loadSearchLogs();
                });
            }

            function exportData() {
                const formData = new FormData(document.getElementById('export-form'));
                const params = new URLSearchParams(formData);

                window.location.href = `/admin/analytics/export?${params}`;
                $('#exportModal').modal('hide');
            }

            function refreshAllData() {
                const btn = $('#refresh-btn');
                const originalHtml = btn.html();

                btn.html('<i class="fas fa-sync-alt fa-spin"></i> Aggiornamento...')
                    .prop('disabled', true);

                Promise.all([
                    loadGeneralStats(),
                    loadCharts(),
                    loadGeographicData(),
                    loadSearchParameters(),
                    loadPerformanceMetrics(),
                    loadSearchLogs()
                ]).finally(() => {
                    btn.html(originalHtml).prop('disabled', false);
                    showSuccess('Dati aggiornati con successo!');
                });
            }

            // Utility functions
            function animateCounter(selector, target, suffix = '') {
                const element = $(selector);
                const start = 0;
                const duration = 1500;
                const startTime = performance.now();

                function updateCounter(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);

                    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                    const current = Math.floor(start + (target - start) * easeOutQuart);

                    element.text(current + suffix);

                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    }
                }

                requestAnimationFrame(updateCounter);
            }

            function getCountryFlag(country) {
                const flags = {
                    'Italy': '🇮🇹',
                    'United States': '🇺🇸',
                    'Germany': '🇩🇪',
                    'France': '🇫🇷',
                    'Spain': '🇪🇸',
                    'United Kingdom': '🇬🇧',
                    'Netherlands': '🇳🇱',
                    'Austria': '🇦🇹',
                    'Switzerland': '🇨🇭',
                    'Local': '🏠'
                };
                return flags[country] || '🌍';
            }

            function getDeviceIcon(deviceType) {
                const icons = {
                    'mobile': '📱',
                    'tablet': '📱',
                    'desktop': '💻'
                };
                return icons[deviceType] || '❓';
            }

            function showSuccess(message) {
                // Implementa toast di successo
                console.log('Success:', message);
            }

            function showError(message) {
                // Implementa toast di errore
                console.error('Error:', message);
            }
        });
    </script>
@endsection
