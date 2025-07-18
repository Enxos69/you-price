@extends('layouts.app')

@section('content')
    <div class="results-page-wrapper">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12 col-xl-11">

                    <!-- Header della pagina -->
                    <div class="page-header">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="header-text">
                                <h1>Risultati Importazione</h1>
                                <p>Riepilogo dettagliato dell'ultima importazione effettuata</p>
                            </div>
                        </div>
                        <div class="header-actions">
                            <a href="{{ route('cruises.import.form') }}" class="btn btn-action" style="color: white">
                                <i class="fas fa-plus me-2"></i>Nuova Importazione
                            </a>
                            <button class="btn btn-action" onclick="exportResults()" style="color: white">
                                <i class="fas fa-download me-2"></i>Esporta
                            </button>
                        </div>
                    </div>

                    <!-- Card principale -->
                    <div class="results-card">
                        <div class="card-body">

                            <!-- Alert di successo se presente -->
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <!-- Statistiche Principali -->
                            <div class="stats-section mb-4">
                                <h6 class="section-title">
                                    <i class="fas fa-chart-pie me-2"></i>Statistiche Importazione
                                </h6>
                                <div class="stats-grid">
                                    <div class="stat-card bg-info">
                                        <div class="stat-icon">
                                            <i class="fas fa-file-csv"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number">{{ $importStats['total_processed'] ?? 0 }}</div>
                                            <div class="stat-label">Processati</div>
                                        </div>
                                    </div>

                                    <div class="stat-card bg-success">
                                        <div class="stat-icon">
                                            <i class="fas fa-plus-circle"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number">{{ $importStats['total_imported'] ?? 0 }}</div>
                                            <div class="stat-label">Nuovi</div>
                                        </div>
                                    </div>

                                    <div class="stat-card bg-primary">
                                        <div class="stat-icon">
                                            <i class="fas fa-sync-alt"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number">{{ $importStats['total_updated'] ?? 0 }}</div>
                                            <div class="stat-label">Aggiornati</div>
                                        </div>
                                    </div>

                                    <div class="stat-card bg-warning">
                                        <div class="stat-icon">
                                            <i class="fas fa-minus-circle"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number">{{ $importStats['total_skipped'] ?? 0 }}</div>
                                            <div class="stat-label">Saltati</div>
                                        </div>
                                    </div>

                                    <div class="stat-card bg-danger">
                                        <div class="stat-icon">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number">{{ count($importStats['errors'] ?? []) }}</div>
                                            <div class="stat-label">Errori</div>
                                        </div>
                                    </div>

                                    <div class="stat-card bg-secondary">
                                        <div class="stat-icon">
                                            <i class="fas fa-percentage"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-number">
                                                {{ $importStats['total_processed'] > 0 ? round(((($importStats['total_imported'] ?? 0) + ($importStats['total_updated'] ?? 0)) / $importStats['total_processed']) * 100) : 0 }}%
                                            </div>
                                            <div class="stat-label">Successo</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtri e Ricerca -->
                            <div class="filters-section">
                                <h6 class="section-title">
                                    <i class="fas fa-filter me-2"></i>Filtri e Ricerca
                                </h6>
                                <div class="filters-grid">
                                    <div class="filter-group">
                                        <label>Ricerca</label>
                                        <div class="search-box">
                                            <i class="fas fa-search"></i>
                                            <input type="text" class="search-input" id="quickSearch"
                                                placeholder="Cerca nave, crociera...">
                                        </div>
                                    </div>
                                    <div class="filter-group">
                                        <label>Compagnia</label>
                                        <select class="filter-select" id="lineFilter">
                                            <option value="">Tutte</option>
                                            <option value="MSC">MSC Cruises</option>
                                            <option value="Costa">Costa Crociere</option>
                                            <option value="Royal">Royal Caribbean</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label>Mese Partenza</label>
                                        <select class="filter-select" id="monthFilter">
                                            <option value="">Tutti</option>
                                            <option value="01">Gennaio</option>
                                            <option value="02">Febbraio</option>
                                            <option value="03">Marzo</option>
                                            <option value="04">Aprile</option>
                                            <option value="05">Maggio</option>
                                            <option value="06">Giugno</option>
                                            <option value="07">Luglio</option>
                                            <option value="08">Agosto</option>
                                            <option value="09">Settembre</option>
                                            <option value="10">Ottobre</option>
                                            <option value="11">Novembre</option>
                                            <option value="12">Dicembre</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label>&nbsp;</label>
                                        <button class="filter-reset-btn" onclick="resetFilters()">
                                            <i class="fas fa-undo me-1"></i>Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Azioni Disponibili -->
                            @if (!empty($importStats['skipped_records']) || !empty($importStats['errors']))
                                <div class="actions-section">
                                    <h6 class="section-title">
                                        <i class="fas fa-tools me-2"></i>Azioni Disponibili
                                    </h6>
                                    <div class="actions-grid">
                                        @if (!empty($importStats['skipped_records']))
                                            <a href="{{ route('cruises.import.download-skipped') }}"
                                                class="action-btn warning">
                                                <i class="fas fa-download me-2"></i>
                                                <div class="action-content">
                                                    <div class="action-title">Scarica Record Saltati</div>
                                                    <div class="action-subtitle">
                                                        {{ count($importStats['skipped_records']) }} elementi</div>
                                                </div>
                                            </a>
                                        @endif

                                        @if (!empty($importStats['errors']))
                                            <button type="button" class="action-btn danger" onclick="showErrorsModal()">
                                                <i class="fas fa-exclamation-circle me-2"></i>
                                                <div class="action-content">
                                                    <div class="action-title">Visualizza Errori</div>
                                                    <div class="action-subtitle">{{ count($importStats['errors']) }}
                                                        errori trovati</div>
                                                </div>
                                            </button>
                                        @endif

                                        <button type="button" class="action-btn success" onclick="exportResults()">
                                            <i class="fas fa-file-excel me-2"></i>
                                            <div class="action-content">
                                                <div class="action-title">Esporta Risultati</div>
                                                <div class="action-subtitle">Excel/CSV</div>
                                            </div>
                                        </button>

                                        @if (!empty($importStats['errors']))
                                            <button type="button" class="action-btn info" onclick="debugErrors()">
                                                <i class="fas fa-bug me-2"></i>
                                                <div class="action-content">
                                                    <div class="action-title">Debug Errori</div>
                                                    <div class="action-subtitle">Console Log</div>
                                                </div>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Tabella Risultati -->
                            <div class="table-section">
                                <h6 class="section-title">
                                    <i class="fas fa-table me-2"></i>Crociere Importate/Aggiornate
                                </h6>

                                @if (($importStats['total_imported'] ?? 0) > 0 || ($importStats['total_updated'] ?? 0) > 0)
                                    <div class="table-container">
                                        <table id="cruisesTable" class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th width="4%">ID</th>
                                                    <th width="12%">Nave</th>
                                                    <th width="15%">Crociera</th>
                                                    <th width="10%">Compagnia</th>
                                                    <th width="8%">Durata</th>
                                                    <th width="8%">Partenza</th>
                                                    <th width="8%">Arrivo</th>
                                                    <th width="8%">Interior</th>
                                                    <th width="8%">Oceanview</th>
                                                    <th width="8%">Balcony</th>
                                                    <th width="11%">Dettagli</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- I dati verranno caricati via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        <h5>Nessuna crociera importata</h5>
                                        <p>Non sono state importate o aggiornate crociere durante questa operazione.</p>
                                        @if (($importStats['total_skipped'] ?? 0) > 0)
                                            <p class="text-muted">Tutti i {{ $importStats['total_skipped'] }} record erano
                                                già presenti con prezzi migliori.</p>
                                        @endif
                                        <a href="{{ route('cruises.import.form') }}" class="btn btn-primary">
                                            <i class="fas fa-upload me-2"></i>Prova Nuova Importazione
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- Loading Overlay -->
                            <div class="loading-overlay d-none" id="loadingOverlay">
                                <div class="loading-content">
                                    <div class="spinner"></div>
                                    <p>Caricamento dati...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Errori -->
    @if (!empty($importStats['errors']))
        <div class="modal fade" id="errorsModal" tabindex="-1" role="dialog" aria-labelledby="errorsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="errorsModalLabel">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Errori durante l'importazione ({{ count($importStats['errors']) }})
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close" onclick="closeErrorsModal()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 600px; overflow-y: auto;">

                        <!-- Ricerca negli errori -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="errorSearch"
                                    placeholder="Cerca negli errori...">
                            </div>
                        </div>

                        <!-- Lista errori -->
                        <div class="errors-list">
                            @foreach ($importStats['errors'] as $index => $error)
                                <div class="error-item mb-3" data-error-text="{{ strtolower($error['error'] ?? '') }}">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong>Errore #{{ $index + 1 }}</strong>
                                                <div>
                                                    @if (isset($error['line']))
                                                        <span class="badge bg-secondary">Riga {{ $error['line'] }}</span>
                                                    @endif
                                                    <button class="btn btn-sm btn-outline-primary" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#errorDetails{{ $index }}"
                                                        aria-expanded="false">
                                                        <i class="fas fa-chevron-down"></i> Dettagli
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <span
                                                    class="text-danger">{{ $error['error'] ?? 'Errore sconosciuto' }}</span>
                                            </div>
                                        </div>
                                        <div class="collapse" id="errorDetails{{ $index }}">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6>Dettagli Errore:</h6>
                                                        <div class="bg-danger bg-opacity-10 rounded p-3">
                                                            <code>{{ $error['error'] ?? 'N/D' }}</code>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Dati del Record:</h6>
                                                        <div class="bg-light rounded p-3">
                                                            <pre class="mb-0" style="font-size: 0.8rem; max-height: 200px; overflow-y: auto;"><code>{{ json_encode($error['record'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if (empty($importStats['errors']))
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>Nessun errore trovato!</h5>
                                <p class="text-muted">L'importazione è stata completata senza errori.</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            onclick="closeErrorsModal()">Chiudi</button>
                        <button type="button" class="btn btn-primary" onclick="exportErrors()">
                            <i class="fas fa-download me-1"></i> Esporta Errori
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading Overlay -->
    <div class="loading-overlay d-none" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <p>Caricamento dati...</p>
        </div>
    </div>
    </div>
    </div>
    </div>
    </div>
    </div>
    </div>

    <!-- Modal Errori -->
    @if (!empty($importStats['errors']))
        <div class="modal fade" id="errorsModal" tabindex="-1" role="dialog" aria-labelledby="errorsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="errorsModalLabel">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Errori durante l'importazione ({{ count($importStats['errors']) }})
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close" onclick="closeErrorsModal()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 600px; overflow-y: auto;">

                        <!-- Ricerca negli errori -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="errorSearch"
                                    placeholder="Cerca negli errori...">
                            </div>
                        </div>

                        <!-- Lista errori -->
                        <div class="errors-list">
                            @foreach ($importStats['errors'] as $index => $error)
                                <div class="error-item mb-3" data-error-text="{{ strtolower($error['error'] ?? '') }}">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong>Errore #{{ $index + 1 }}</strong>
                                                <div>
                                                    @if (isset($error['line']))
                                                        <span class="badge bg-secondary">Riga {{ $error['line'] }}</span>
                                                    @endif
                                                    <button class="btn btn-sm btn-outline-primary" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#errorDetails{{ $index }}"
                                                        aria-expanded="false">
                                                        <i class="fas fa-chevron-down"></i> Dettagli
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <span
                                                    class="text-danger">{{ $error['error'] ?? 'Errore sconosciuto' }}</span>
                                            </div>
                                        </div>
                                        <div class="collapse" id="errorDetails{{ $index }}">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6>Dettagli Errore:</h6>
                                                        <div class="bg-danger bg-opacity-10 rounded p-3">
                                                            <code>{{ $error['error'] ?? 'N/D' }}</code>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Dati del Record:</h6>
                                                        <div class="bg-light rounded p-3">
                                                            <pre class="mb-0" style="font-size: 0.8rem; max-height: 200px; overflow-y: auto;"><code>{{ json_encode($error['record'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if (empty($importStats['errors']))
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>Nessun errore trovato!</h5>
                                <p class="text-muted">L'importazione è stata completata senza errori.</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            onclick="closeErrorsModal()">Chiudi</button>
                        <button type="button" class="btn btn-primary" onclick="exportErrors()">
                            <i class="fas fa-download me-1"></i> Esporta Errori
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@include('admin.assets.css_import_results')

@section('scripts')
    @parent
    
    <!-- Inizializzazione sicura dei dati -->
    <script>
        window.importResultsData = {
            hasResults: {{ ($importStats['total_imported'] ?? 0) > 0 || ($importStats['total_updated'] ?? 0) > 0 ? 'true' : 'false' }},
            importDataUrl: '{{ route('cruises.import.data') }}',
            csrf: '{{ csrf_token() }}',
            errors: []
        };
        
        @php
            $errors = [];
            if (isset($importStats['errors']) && is_array($importStats['errors'])) {
                $errors = $importStats['errors'];
            }
            $errorsJson = json_encode($errors);
        @endphp
        
        @if(!empty($errorsJson) && $errorsJson !== 'null' && $errorsJson !== 'false')
            window.importResultsData.errors = {!! $errorsJson !!};
        @endif
        
        // Debug per verificare i dati
        console.log('ImportResultsData initialized:', window.importResultsData);
        console.log('Errors count:', window.importResultsData.errors.length);
    </script>
    
    @include('admin.assets.js_import_results')
@endsection
