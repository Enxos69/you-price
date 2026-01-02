<script>
    // JavaScript per gestire i dettagli crociera (solo per utenti autenticati)
    @auth
    document.addEventListener('DOMContentLoaded', function() {

        // Inizializza la modale
        let cruiseModalInstance = null;

        /**
         * Funzione per mostrare i dettagli della crociera nella modale
         */
        window.showCruiseDetails = async function(cruiseId) {
            const modalElement = document.getElementById('cruiseDetailModal');
            const modalBody = document.getElementById('modal-cruise-body');
            const modalTitle = document.getElementById('modal-cruise-name');

            // Crea istanza modale se non esiste
            if (!cruiseModalInstance) {
                cruiseModalInstance = new bootstrap.Modal(modalElement);
            }

            // Mostra loading
            modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Caricamento...</span>
                </div>
                <p class="mt-3 text-muted">Caricamento dettagli...</p>
            </div>
        `;

            cruiseModalInstance.show();

            try {
                const response = await fetch(`/crociere/${cruiseId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content
                    }
                });

                if (!response.ok) {
                    throw new Error('Errore nel caricamento dei dettagli');
                }

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Errore sconosciuto');
                }

                renderCruiseDetails(data.cruise);

            } catch (error) {
                console.error('Errore caricamento dettagli:', error);
                modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${error.message || 'Errore nel caricamento dei dettagli'}
                </div>
            `;
            }
        };

        // Gestione eventi chiusura modale
        const modalElement = document.getElementById('cruiseDetailModal');
        if (modalElement) {
            // Pulsante close (X)
            const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"]');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (cruiseModalInstance) {
                        cruiseModalInstance.hide();
                    }
                });
            });

            // Click fuori dalla modale
            modalElement.addEventListener('click', function(event) {
                if (event.target === modalElement) {
                    if (cruiseModalInstance) {
                        cruiseModalInstance.hide();
                    }
                }
            });

            // ESC key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modalElement.classList.contains('show')) {
                    if (cruiseModalInstance) {
                        cruiseModalInstance.hide();
                    }
                }
            });
        }

        /**
         * Renderizza i dettagli della crociera nella modale
         */
        function renderCruiseDetails(cruise) {
            const modalTitle = document.getElementById('modal-cruise-name');
            const modalBody = document.getElementById('modal-cruise-body');

            modalTitle.textContent = cruise.ship || 'Dettaglio Crociera';

            // Formatta le date
            const formatDate = (dateString) => {
                if (!dateString) return 'N/D';
                const date = new Date(dateString);
                return date.toLocaleDateString('it-IT', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            };

            // Formatta il prezzo
            const formatPrice = (price) => {
                if (!price || price === '-' || price === 0) return '-';
                return '€' + parseFloat(price).toLocaleString('it-IT', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            };

            const html = `
            <!-- Informazioni Generali - Compatta -->
            <div class="card mb-2 border-0 shadow-sm">
                <div class="card-header bg-light border-0 py-2">
                    <h6 class="mb-0 text-dark fw-bold" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Informazioni Generali
                    </h6>
                </div>
                <div class="card-body py-2 px-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center py-1">
                                <i class="fas fa-ship me-2 text-muted" style="width: 18px;"></i>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Nave</small>
                                    <strong class="text-dark" style="font-size: 0.875rem;">${cruise.ship || 'N/D'}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center py-1">
                                <i class="fas fa-building me-2 text-muted" style="width: 18px;"></i>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Compagnia</small>
                                    <strong class="text-dark" style="font-size: 0.875rem;">${cruise.line || 'N/D'}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center py-1">
                                <i class="fas fa-moon me-2 text-muted" style="width: 18px;"></i>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Durata</small>
                                    <strong class="text-dark" style="font-size: 0.875rem;">${cruise.night || cruise.duration || 'N/D'} ${cruise.night ? 'notti' : 'giorni'}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center py-1">
                                <i class="fas fa-calendar-alt me-2 text-muted" style="width: 18px;"></i>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Partenza</small>
                                    <strong class="text-dark" style="font-size: 0.875rem;">${formatDate(cruise.partenza)}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center py-1">
                                <i class="fas fa-anchor me-2 text-muted" style="width: 18px;"></i>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Da</small>
                                    <strong class="text-dark" style="font-size: 0.875rem;">${cruise.from || 'N/D'}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center py-1">
                                <i class="fas fa-calendar-check me-2 text-muted" style="width: 18px;"></i>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Arrivo</small>
                                    <strong class="text-dark" style="font-size: 0.875rem;">${formatDate(cruise.arrivo)}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center py-1">
                                <i class="fas fa-map-marker-alt me-2 text-muted" style="width: 18px;"></i>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">A</small>
                                    <strong class="text-dark" style="font-size: 0.875rem;">${cruise.to || 'N/D'}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Descrizione Crociera - Compatta -->
            ${cruise.cruise ? `
            <div class="card mb-2 border-0 shadow-sm">
                <div class="card-header bg-light border-0 py-2">
                    <h6 class="mb-0 text-dark fw-bold" style="font-size: 0.9rem;">
                        <i class="fas fa-align-left me-2 text-primary"></i>Descrizione Pacchetto
                    </h6>
                </div>
                <div class="card-body py-2 px-3">
                    <p class="mb-0 text-dark" style="font-size: 0.875rem; line-height: 1.4;">${cruise.cruise}</p>
                </div>
            </div>
            ` : ''}
            
            <!-- Dettagli Aggiuntivi - Compatta -->
            ${cruise.details ? `
            <div class="card mb-2 border-0 shadow-sm">
                <div class="card-header bg-light border-0 py-2">
                    <h6 class="mb-0 text-dark fw-bold" style="font-size: 0.9rem;">
                        <i class="fas fa-clipboard-list me-2 text-primary"></i>Note e Dettagli
                    </h6>
                </div>
                <div class="card-body py-2 px-3">
                    <p class="mb-0 text-dark" style="font-size: 0.875rem; line-height: 1.4;">${cruise.details}</p>
                </div>
            </div>
            ` : ''}
            
            <!-- Prezzi Cabine - Compatta -->
            <div class="card mb-0 border-0 shadow-sm">
                <div class="card-header bg-light border-0 py-2">
                    <h6 class="mb-0 text-dark fw-bold" style="font-size: 0.9rem;">
                        <i class="fas fa-euro-sign me-2 text-primary"></i>Prezzi per Tipologia Cabina
                    </h6>
                </div>
                <div class="card-body py-2 px-3">
                    <div class="row g-2 text-center">
                        ${cruise.interior && cruise.interior !== '-' ? `
                        <div class="col-6 col-md-4">
                            <div class="cabin-price-card p-2 border rounded bg-light">
                                <i class="fas fa-bed mb-1 text-primary" style="font-size: 1.2rem;"></i>
                                <h6 class="mb-0 text-muted" style="font-size: 0.75rem;">Interna</h6>
                                <p class="mb-0 fw-bold text-success" style="font-size: 1.1rem;">${formatPrice(cruise.interior)}</p>
                                <small class="text-muted" style="font-size: 0.7rem;">a persona</small>
                            </div>
                        </div>
                        ` : ''}
                        
                        ${cruise.oceanview && cruise.oceanview !== '-' ? `
                        <div class="col-6 col-md-4">
                            <div class="cabin-price-card p-2 border rounded bg-light">
                                <i class="fas fa-window-maximize mb-1 text-info" style="font-size: 1.2rem;"></i>
                                <h6 class="mb-0 text-muted" style="font-size: 0.75rem;">Esterna</h6>
                                <p class="mb-0 fw-bold text-success" style="font-size: 1.1rem;">${formatPrice(cruise.oceanview)}</p>
                                <small class="text-muted" style="font-size: 0.7rem;">a persona</small>
                            </div>
                        </div>
                        ` : ''}
                        
                        ${cruise.balcony && cruise.balcony !== '-' ? `
                        <div class="col-6 col-md-4">
                            <div class="cabin-price-card p-2 border rounded bg-light">
                                <i class="fas fa-door-open mb-1 text-warning" style="font-size: 1.2rem;"></i>
                                <h6 class="mb-0 text-muted" style="font-size: 0.75rem;">Balcone</h6>
                                <p class="mb-0 fw-bold text-success" style="font-size: 1.1rem;">${formatPrice(cruise.balcony)}</p>
                                <small class="text-muted" style="font-size: 0.7rem;">a persona</small>
                            </div>
                        </div>
                        ` : ''}
                        
                        ${cruise.minisuite && cruise.minisuite !== '-' ? `
                        <div class="col-6 col-md-4">
                            <div class="cabin-price-card p-2 border rounded bg-light">
                                <i class="fas fa-gem mb-1 text-purple" style="font-size: 1.2rem;"></i>
                                <h6 class="mb-0 text-muted" style="font-size: 0.75rem;">Mini Suite</h6>
                                <p class="mb-0 fw-bold text-success" style="font-size: 1.1rem;">${formatPrice(cruise.minisuite)}</p>
                                <small class="text-muted" style="font-size: 0.7rem;">a persona</small>
                            </div>
                        </div>
                        ` : ''}
                        
                        ${cruise.suite && cruise.suite !== '-' ? `
                        <div class="col-6 col-md-4">
                            <div class="cabin-price-card p-2 border rounded bg-light">
                                <i class="fas fa-crown mb-1 text-danger" style="font-size: 1.2rem;"></i>
                                <h6 class="mb-0 text-muted" style="font-size: 0.75rem;">Suite</h6>
                                <p class="mb-0 fw-bold text-success" style="font-size: 1.1rem;">${formatPrice(cruise.suite)}</p>
                                <small class="text-muted" style="font-size: 0.7rem;">a persona</small>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                    
                    ${!cruise.interior && !cruise.oceanview && !cruise.balcony && !cruise.minisuite && !cruise.suite ? `
                    <div class="text-center text-muted py-2">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Nessun prezzo disponibile al momento</small>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;

            modalBody.innerHTML = html;
        }
    });
    @endauth
</script>

<style>
    /* Stili per la modale dettagli crociera - Versione Compatta */
    @auth .detail-item {
            padding: 0.25rem 0;
        }

        .cabin-price-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6 !important;
        }

        .cabin-price-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border-color: #0d6efd !important;
        }

        .text-purple {
            color: #8b5cf6;
        }

        .btn-action-detail {
            font-size: 0.875rem;
            padding: 0.25rem 0.75rem;
            white-space: nowrap;
        }

        .btn-action-detail i {
            font-size: 0.875rem;
        }

        /* Riduci spazi nella modale */
        #cruiseDetailModal .modal-header {
            padding: 0.75rem 1rem;
        }

        #cruiseDetailModal .modal-body {
            padding: 0.75rem 1rem;
        }

        #cruiseDetailModal .modal-footer {
            padding: 0.5rem 1rem;
        }

        /* Card compatte */
        #cruiseDetailModal .card {
            margin-bottom: 0.5rem !important;
        }

        #cruiseDetailModal .card-header {
            padding: 0.5rem 0.75rem !important;
        }

        #cruiseDetailModal .card-body {
            padding: 0.5rem 0.75rem !important;
        }

        /* Testi più compatti */
        #cruiseDetailModal h6 {
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        #cruiseDetailModal small {
            font-size: 0.75rem;
        }

        /* Righe info più compatte */
        #cruiseDetailModal .row {
            margin-left: -0.25rem;
            margin-right: -0.25rem;
        }

        #cruiseDetailModal .row>[class*='col'] {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }

        /* Modale più stretta e alta */
        #cruiseDetailModal .modal-dialog {
            max-width: 700px;
        }

        #cruiseDetailModal .modal-dialog-scrollable .modal-body {
            max-height: calc(100vh - 150px);
        }
        @endauth
    </style>
