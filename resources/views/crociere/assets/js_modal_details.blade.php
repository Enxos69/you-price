<script>
    // JavaScript per gestire i dettagli crociera (solo per utenti autenticati)
    @auth
    document.addEventListener('DOMContentLoaded', function() {

        // Inizializza la modale
        let cruiseModalInstance = null;
        let currentCruiseId = null;
        let isFavorite = false;

        // Apri modale automaticamente se cruise_id è nell'URL (da dashboard)
        const urlParams = new URLSearchParams(window.location.search);
        const cruiseIdFromUrl = urlParams.get('cruise_id');
        if (cruiseIdFromUrl) {
            // Rimuovi il parametro dall'URL senza ricaricare la pagina
            const newUrl = window.location.pathname;
            window.history.replaceState({}, '', newUrl);
            
            // Apri la modale dopo un breve delay per permettere il caricamento della pagina
            setTimeout(() => {
                window.showCruiseDetails(cruiseIdFromUrl);
            }, 300);
        }

        /**
         * Funzione per mostrare i dettagli della crociera nella modale
         */
        window.showCruiseDetails = async function(cruiseId) {
            const modalElement = document.getElementById('cruiseDetailModal');
            const modalBody = document.getElementById('modal-cruise-body');
            const modalTitle = document.getElementById('modal-cruise-name');

            // Salva l'ID della crociera corrente
            currentCruiseId = cruiseId;

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
                
                // Carica lo stato preferiti
                await loadFavoriteStatus(cruiseId);

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

        /**
         * Carica lo stato preferiti della crociera
         */
        async function loadFavoriteStatus(cruiseId) {
            try {
                const response = await fetch(`/cruises/${cruiseId}/favorite/check`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                if (data.success) {
                    isFavorite = data.is_favorite;
                    updateFavoriteButton();
                }
            } catch (error) {
                console.error('Errore caricamento stato preferiti:', error);
            }
        }

        /**
         * Aggiorna l'aspetto del pulsante preferiti
         */
        function updateFavoriteButton() {
            const btn = document.getElementById('favorite-btn');
            const icon = document.getElementById('favorite-icon');
            const text = document.getElementById('favorite-text');
            const headerBtn = document.getElementById('favorite-icon-header');
            const headerIcon = document.getElementById('favorite-icon-header-heart');

            if (isFavorite) {
                // Footer button
                if (btn) {
                    btn.classList.remove('btn-outline-danger');
                    btn.classList.add('btn-danger');
                }
                if (icon) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                }
                if (text) {
                    text.textContent = 'Rimuovi dai Preferiti';
                }
                
                // Header icon
                if (headerBtn) {
                    headerBtn.classList.remove('btn-light');
                    headerBtn.classList.add('btn-danger');
                    headerBtn.title = 'Rimuovi dai preferiti';
                }
                if (headerIcon) {
                    headerIcon.classList.remove('far');
                    headerIcon.classList.add('fas');
                    headerIcon.classList.add('text-white');
                }
            } else {
                // Footer button
                if (btn) {
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-outline-danger');
                }
                if (icon) {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
                if (text) {
                    text.textContent = 'Aggiungi ai Preferiti';
                }
                
                // Header icon
                if (headerBtn) {
                    headerBtn.classList.remove('btn-danger');
                    headerBtn.classList.add('btn-light');
                    headerBtn.title = 'Aggiungi ai preferiti';
                }
                if (headerIcon) {
                    headerIcon.classList.remove('fas');
                    headerIcon.classList.add('far');
                    headerIcon.classList.remove('text-white');
                }
            }
        }

        /**
         * Toggle preferiti (aggiungi/rimuovi)
         */
        window.toggleFavorite = async function() {
            if (!currentCruiseId) return;

            const btn = document.getElementById('favorite-btn');
            const icon = document.getElementById('favorite-icon');
            
            // Disabilita pulsante durante la richiesta
            btn.disabled = true;
            icon.classList.add('fa-spin');

            try {
                const response = await fetch(`/cruises/${currentCruiseId}/favorite/toggle`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    isFavorite = data.is_favorite;
                    updateFavoriteButton();
                    
                    // Mostra toast di conferma
                    showFavoriteToast(data.message, isFavorite ? 'success' : 'info');
                    
                    // Animazione cuore
                    animateHeart(icon);
                } else {
                    throw new Error(data.message || 'Errore durante l\'operazione');
                }
            } catch (error) {
                console.error('Errore toggle preferiti:', error);
                showFavoriteToast('Errore durante l\'operazione', 'error');
            } finally {
                btn.disabled = false;
                icon.classList.remove('fa-spin');
            }
        };

        /**
         * Animazione cuore
         */
        function animateHeart(icon) {
            // Anima icona footer
            if (icon) {
                icon.classList.add('heart-beat');
                setTimeout(() => {
                    icon.classList.remove('heart-beat');
                }, 600);
            }
            
            // Anima anche icona header
            const headerIcon = document.getElementById('favorite-icon-header-heart');
            if (headerIcon) {
                headerIcon.classList.add('heart-beat');
                setTimeout(() => {
                    headerIcon.classList.remove('heart-beat');
                }, 600);
            }
        }

        /**
         * Mostra toast per feedback preferiti
         */
        function showFavoriteToast(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            if (!toastContainer) return;

            const toastId = 'favorite-toast-' + Date.now();
            const iconMap = {
                success: 'fas fa-heart text-danger',
                info: 'fas fa-heart-broken text-muted',
                error: 'fas fa-exclamation-circle text-danger'
            };

            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = 'toast show';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="toast-body d-flex align-items-center">
                    <i class="${iconMap[type] || iconMap.success} me-2"></i>
                    <span class="flex-grow-1">${message}</span>
                    <button type="button" class="btn-close ms-2" onclick="this.closest('.toast').remove()"></button>
                </div>
            `;

            toastContainer.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 3000);
        }
    });
    @endauth
</script>

<style>
    /* Stili per la modale dettagli crociera - Versione Compatta */
    @auth
        .detail-item {
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

        /* Animazione cuore */
        @keyframes heartBeat {
            0%, 100% {
                transform: scale(1);
            }
            25% {
                transform: scale(1.3);
            }
            50% {
                transform: scale(1.1);
            }
            75% {
                transform: scale(1.2);
            }
        }

        .heart-beat {
            animation: heartBeat 0.6s ease-in-out;
        }

        /* Stile pulsante preferiti footer */
        #favorite-btn {
            transition: all 0.3s ease;
        }

        #favorite-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        #favorite-btn.btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        #favorite-btn.btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        /* Stile icona preferiti header */
        #favorite-icon-header {
            transition: all 0.3s ease;
            border: none;
        }

        #favorite-icon-header:hover {
            transform: scale(1.1);
        }

        #favorite-icon-header.btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        #favorite-icon-header.btn-danger:hover {
            background-color: #c82333;
        }

        #favorite-icon-header-heart {
            font-size: 1rem;
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