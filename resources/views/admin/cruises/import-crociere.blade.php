@extends('layouts.app')

@section('content')
    <div class="import-page-wrapper">
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
                                <h1>Importa Crociere</h1>
                                <p>Carica il tuo file CSV per importare le crociere nel sistema</p>
                            </div>
                        </div>
                        <!-- Link ai risultati se disponibili -->
                        @if(session('import_stats'))
                        <div class="header-actions">
                            <a href="{{ route('cruises.import.results') }}" class="btn btn-success">
                                <i class="fas fa-chart-bar me-2"></i>Vedi Ultima Importazione
                            </a>
                        </div>
                        @endif
                    </div>

                    <!-- Card principale -->
                    <div class="import-card">
                        <div class="card-body">
                            <!-- Alert Messages -->
                            <div id="alert-container">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        {{ session('success') }}
                                        @if(session('import_stats'))
                                        <div class="mt-2">
                                            <a href="{{ route('cruises.import.results') }}" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-eye me-1"></i>Visualizza Risultati Dettagliati
                                            </a>
                                        </div>
                                        @endif
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif
                            </div>

                            <!-- Upload Form -->
                            <form id="importForm" action="{{ route('cruises.import') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <!-- Upload Zone -->
                                <div class="upload-zone" id="uploadZone">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <h5 class="upload-title">Trascina il file qui o clicca per selezionare</h5>
                                    <p class="upload-subtitle">Formati supportati: CSV, TXT (max 10MB)</p>
                                    <input type="file" class="d-none" id="csvFile" name="csv_file" accept=".csv,.txt"
                                        required>
                                </div>

                                <!-- File Preview -->
                                <div class="file-preview d-none" id="filePreview">
                                    <div class="file-info">
                                        <div class="file-icon">
                                            <i class="fas fa-file-csv"></i>
                                        </div>
                                        <div class="file-details">
                                            <h6 id="fileName" class="mb-1"></h6>
                                            <small class="text-muted" id="fileSize"></small>
                                        </div>
                                        <button type="button" class="btn-remove" id="removeFile">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Stats Preview -->
                                <div class="stats-preview d-none" id="statsPreview">
                                    <h6 class="stats-title">
                                        <i class="fas fa-chart-bar me-2"></i>Anteprima File
                                    </h6>
                                    <div class="stats-grid">
                                        <div class="stat-item">
                                            <div class="stat-number" id="rowCount">0</div>
                                            <div class="stat-label">Righe</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-number" id="columnCount">0</div>
                                            <div class="stat-label">Colonne</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-number" id="fileFormat">CSV</div>
                                            <div class="stat-label">Formato</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sezione combinata: Options + Progress + Button -->
                                <div class="bottom-section">
                                    <!-- Import Options -->
                                    <div class="import-options">
                                        <h6 class="options-title">
                                            <i class="fas fa-cog me-2"></i>Opzioni di Importazione
                                        </h6>
                                        <div class="options-grid">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="skipDuplicates"
                                                    name="skip_duplicates" checked>
                                                <label class="form-check-label" for="skipDuplicates">
                                                    Aggiorna con prezzi migliori
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="validateData"
                                                    name="validate_data" checked>
                                                <label class="form-check-label" for="validateData">
                                                    Valida dati durante l'importazione
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="createBackup"
                                                    name="create_backup">
                                                <label class="form-check-label" for="createBackup">
                                                    Crea backup prima dell'importazione
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="progress-container d-none" id="progressContainer">
                                        <div class="progress-wrapper">
                                            <div class="progress">
                                                <div class="progress-bar" id="progressBar"></div>
                                            </div>
                                            <div class="progress-text">
                                                <small>Importazione in corso...</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <button type="submit" class="btn-import" id="submitBtn" disabled>
                                        <span class="spinner-border spinner-border-sm me-2 d-none"
                                            id="loadingSpinner"></span>
                                        <span id="btnText">Seleziona un file per continuare</span>
                                    </button>
                                </div>
                            </form>

                            <!-- Format Info - Compatta -->
                            <div class="format-info">
                                <h6 class="info-title">
                                    <i class="fas fa-info-circle me-2"></i>Formato CSV: separatore (;), UTF-8, colonne:
                                    ship, cruise, line
                                </h6>
                            </div>

                            <!-- Quick Stats se disponibili -->
                            @if(session('import_stats'))
                            <div class="quick-stats mt-4">
                                <h6 class="mb-3">
                                    <i class="fas fa-history me-2"></i>Ultima Importazione
                                </h6>
                                <div class="row">
                                    <div class="col-md-3 col-sm-6">
                                        <div class="quick-stat-item">
                                            <div class="quick-stat-number">{{ session('import_stats')['total_imported'] ?? 0 }}</div>
                                            <div class="quick-stat-label">Nuovi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="quick-stat-item">
                                            <div class="quick-stat-number">{{ session('import_stats')['total_updated'] ?? 0 }}</div>
                                            <div class="quick-stat-label">Aggiornati</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="quick-stat-item">
                                            <div class="quick-stat-number">{{ session('import_stats')['total_skipped'] ?? 0 }}</div>
                                            <div class="quick-stat-label">Saltati</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="quick-stat-item">
                                            <div class="quick-stat-number">{{ count(session('import_stats')['errors'] ?? []) }}</div>
                                            <div class="quick-stat-label">Errori</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('admin.cruises.assets.css_import_crociere')

@section('scripts')
    @parent    
    @include('admin.cruises.assets.js_import_crociere')
@endsection

