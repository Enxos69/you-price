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
                                    <div class="stat-icon expired">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="expiredCruises">-</div>
                                        <div class="stat-label">Non più disponibili</div>
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

                            <!-- Filtri Ottimizzati -->
                            <div class="filters-optimized">
                                <div class="filters-top-row">
                                    <!-- Ricerca Globale -->
                                    <div class="search-wrapper">
                                        <div class="search-input-group">
                                            <i class="fas fa-search search-icon"></i>
                                            <input type="text" id="globalSearch" class="search-input-optimized"
                                                placeholder="Cerca nave, crociera, compagnia..." autocomplete="off">
                                            <button type="button" class="search-clear" id="clearSearch"
                                                style="display: none;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Filtri Rapidi -->
                                    <div class="quick-filters">
                                        <div class="filter-group-optimized">
                                            <select id="companyFilter" class="filter-select-optimized">
                                                <option value="">Tutte le compagnie</option>
                                                <option value="msc">MSC Cruises</option>
                                                <option value="costa">Costa Crociere</option>
                                                <option value="royal">Royal Caribbean</option>
                                                <option value="carnival">Carnival</option>
                                            </select>
                                        </div>

                                        <div class="filter-group-optimized">
                                            <select id="priceFilter" class="filter-select-optimized">
                                                <option value="">Tutti i prezzi</option>
                                                <option value="0-500">€0 - €500</option>
                                                <option value="500-1000">€500 - €1.000</option>
                                                <option value="1000-2000">€1.000 - €2.000</option>
                                                <option value="2000+">€2.000+</option>
                                            </select>
                                        </div>

                                        <div class="filter-group-optimized">
                                            <select id="statusFilter" class="filter-select-optimized">
                                                <option value="">Tutti gli stati</option>
                                                <option value="available">Disponibili (non scadute)</option>
                                                <option value="expired">Non più disponibili (scadute)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Azioni Multiple -->
                                <div class="bulk-actions" id="bulkActions" style="display: none;">
                                    <div class="bulk-info">
                                        <span class="selected-count">0</span> crociere selezionate
                                    </div>
                                    <div class="bulk-buttons">
                                        <button type="button" class="btn-bulk btn-bulk-danger" onclick="bulkDelete()">
                                            <i class="fas fa-trash me-1"></i>Elimina
                                        </button>
                                        <button type="button" class="btn-bulk btn-bulk-secondary"
                                            onclick="deselectAll()">
                                            <i class="fas fa-times me-1"></i>Deseleziona
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabella Ottimizzata -->
                            <div class="table-wrapper-optimized">
                                <div class="table-container-optimized" id="tableContainer">
                                    <table class="table table-optimized" id="cruisesTable">
                                        <thead class="table-head-optimized">
                                            <tr>
                                                <th class="col-checkbox">
                                                    <div class="checkbox-wrapper">
                                                        <input type="checkbox" id="selectAll" class="checkbox-optimized">
                                                        <label for="selectAll" class="checkbox-label"></label>
                                                    </div>
                                                </th>
                                                <th class="col-ship sortable" data-column="ship">
                                                    <div class="th-content">
                                                        <span>Nave</span>
                                                        <i class="fas fa-sort sort-icon"></i>
                                                    </div>
                                                </th>
                                                <th class="col-cruise sortable" data-column="cruise">
                                                    <div class="th-content">
                                                        <span>Crociera</span>
                                                        <i class="fas fa-sort sort-icon"></i>
                                                    </div>
                                                </th>
                                                <th class="col-company sortable" data-column="line">
                                                    <div class="th-content">
                                                        <span>Compagnia</span>
                                                        <i class="fas fa-sort sort-icon"></i>
                                                    </div>
                                                </th>
                                                <th class="col-duration sortable" data-column="duration">
                                                    <div class="th-content">
                                                        <span>Durata</span>
                                                        <i class="fas fa-sort sort-icon"></i>
                                                    </div>
                                                </th>
                                                <th class="col-price sortable" data-column="interior">
                                                    <div class="th-content">
                                                        <span>Prezzo</span>
                                                        <i class="fas fa-sort sort-icon"></i>
                                                    </div>
                                                </th>
                                                <th class="col-actions">
                                                    <span>Azioni</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">
                                            <!-- I dati verranno inseriti via JavaScript -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Loading State -->
                                <div class="table-loading" id="tableLoading">
                                    <div class="loading-spinner"></div>
                                    <div class="loading-text">Caricamento crociere...</div>
                                </div>

                                <!-- Empty State -->
                                <div class="table-empty" id="tableEmpty" style="display: none;">
                                    <div class="empty-icon">
                                        <i class="fas fa-ship"></i>
                                    </div>
                                    <div class="empty-title">Nessuna crociera trovata</div>
                                    <div class="empty-subtitle">Prova a modificare i filtri di ricerca</div>
                                </div>
                            </div>

                            <!-- Paginazione Ottimizzata -->
                            <div class="pagination-wrapper" id="paginationWrapper">
                                <div class="pagination-info">
                                    <span class="pagination-text" id="paginationInfo">
                                        Mostrando 0 di 0 risultati
                                    </span>
                                </div>
                                <div class="pagination-controls">
                                    <select id="pageSize" class="page-size-select">
                                        <option value="15">15 per pagina</option>
                                        <option value="25">25 per pagina</option>
                                        <option value="50">50 per pagina</option>
                                        <option value="100">100 per pagina</option>
                                    </select>
                                    <div class="pagination-buttons" id="paginationButtons">
                                        <!-- I pulsanti verranno generati via JavaScript -->
                                    </div>
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
