@extends('layouts.app')

@section('content')
    <div class="cruises-page-wrapper">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12">

                    <!-- Header della pagina -->
                    <div class="page-header">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="fas fa-ship"></i>
                            </div>
                            <div class="header-text">
                                <h1>Gestione Crociere</h1>
                                <p>Amministra e gestisci tutte le crociere del sistema</p>
                            </div>
                        </div>
                        <div class="header-actions">
                            <a href="{{ route('cruises.create') }}" class="btn btn-action" style="color: white">
                                <i class="fas fa-plus me-2"></i>Nuova Crociera
                            </a>
                            <a href="{{ route('cruises.import.form') }}" class="btn btn-action" style="color: white">
                                <i class="fas fa-upload me-2"></i>Importa Excel
                            </a>
                            <button class="btn btn-action" onclick="refreshTable()" style="color: white">
                                <i class="fas fa-sync-alt me-2"></i>Aggiorna
                            </button>
                            <button class="btn btn-action" onclick="exportCruises()" style="color: white">
                                <i class="fas fa-download me-2"></i>Esporta
                            </button>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <div id="alert-container">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
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
                    </div>

                    <!-- Card principale -->
                    <div class="cruises-card">
                        <div class="card-body">
                            <!-- Statistics Row -->
                            <div class="stats-row">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-ship"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="totalCruises">-</div>
                                        <div class="stat-label">Totale Crociere</div>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon available">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="availableCruises">-</div>
                                        <div class="stat-label">Disponibili</div>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon future">
                                        <i class="fas fa-calendar-plus"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="futureCruises">-</div>
                                        <div class="stat-label">Future</div>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon companies">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="totalCompanies">-</div>
                                        <div class="stat-label">Compagnie</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filters Section -->
                            <div class="filters-section">
                                <div class="search-container">
                                    <div class="search-box">
                                        <i class="fas fa-search"></i>
                                        <input type="text" class="search-input" id="globalSearch"
                                            placeholder="Cerca nave, crociera, compagnia...">
                                    </div>
                                </div>
                                <div class="filters-container">
                                    <div class="filter-group">
                                        <label>Compagnia</label>
                                        <select class="filter-select" id="companyFilter">
                                            <option value="">Tutte</option>
                                            <option value="MSC">MSC Cruises</option>
                                            <option value="Costa">Costa Crociere</option>
                                            <option value="Royal">Royal Caribbean</option>
                                            <option value="Norwegian">Norwegian</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label>Disponibilità</label>
                                        <select class="filter-select" id="availabilityFilter">
                                            <option value="">Tutte</option>
                                            <option value="available">Disponibili</option>
                                            <option value="future">Future</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label>Azioni Multiple</label>
                                        <button class="btn btn-sm btn-danger" id="bulkDeleteBtn" onclick="bulkDelete()"
                                            disabled>
                                            <i class="fas fa-trash me-1"></i>Elimina Selezionate
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Table Container -->
                            <div class="table-container">
                                <table class="table cruises-table" id="cruises-table">
                                    <thead>
                                        <tr>
                                            <!-- ✅ Checkbox Selezione -->
                                            <th width="3%">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>

                                            <!-- ✅ Nave -->
                                            <th width="18%">
                                                <i class="fas fa-ship me-2"></i>Nave
                                            </th>

                                            <!-- ✅ Crociera -->
                                            <th width="25%">
                                                <i class="fas fa-route me-2"></i>Crociera
                                            </th>

                                            <!-- ✅ Compagnia -->
                                            <th width="15%">
                                                <i class="fas fa-building me-2"></i>Compagnia
                                            </th>

                                            <!-- ✅ Durata -->
                                            <th width="8%">
                                                <i class="fas fa-clock me-2"></i>Durata
                                            </th>

                                            <!-- ✅ Data Partenza (ex Itinerario) -->
                                            <th width="12%">
                                                <i class="fas fa-calendar me-2"></i>Partenza
                                            </th>

                                            <!-- ✅ Prezzo Interior -->
                                            <th width="10%">
                                                <i class="fas fa-bed me-2"></i>Prezzo Min.
                                            </th>

                                            <!-- ✅ Azioni -->
                                            <th width="9%">
                                                <i class="fas fa-cogs me-2"></i>Azioni
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- I dati verranno caricati via AJAX da DataTables -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Loading Overlay -->
                            <div class="loading-overlay d-none" id="loadingOverlay">
                                <div class="loading-content">
                                    <div class="spinner"></div>
                                    <p>Caricamento crociere...</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Conferma Eliminazione -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Conferma Eliminazione
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Sei sicuro di voler eliminare la crociera:</p>
                    <div class="cruise-info">
                        <strong id="deleteShipName"></strong> - <span id="deleteCruiseName"></span>
                    </div>
                    <p class="text-danger mt-3 mb-0">
                        <i class="fas fa-warning me-2"></i>Questa azione non può essere annullata.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annulla
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash me-2"></i>Elimina
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('admin.cruises.assets.css_cruises_index')

@section('scripts')
    @parent
    @include('admin.cruises.assets.js_cruises_index')
@endsection
