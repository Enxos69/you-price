/**
 * DASHBOARD.JS - JavaScript per la Dashboard Utente
 * You Price Cruises
 */

(function() {
    'use strict';

    // Configurazione
    const CONFIG = {
        csrf: document.querySelector('meta[name="csrf-token"]')?.content,
        autoRefresh: false, // Auto-refresh stats ogni 5 minuti
        refreshInterval: 300000 // 5 minuti
    };

    // Utility Functions
    const Utils = {
        /**
         * Mostra toast di notifica
         */
        showToast(message, type = 'success') {
            // Se Bootstrap Toast è disponibile
            if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                const toastContainer = document.getElementById('toastContainer');
                if (!toastContainer) {
                    this.createToastContainer();
                }
                
                const toastHtml = `
                    <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `;
                
                const toastElement = document.createElement('div');
                toastElement.innerHTML = toastHtml;
                document.getElementById('toastContainer').appendChild(toastElement.firstElementChild);
                
                const toast = new bootstrap.Toast(toastElement.querySelector('.toast'));
                toast.show();
                
                // Rimuovi dopo che scompare
                setTimeout(() => toastElement.remove(), 5000);
            } else {
                // Fallback ad alert
                alert(message);
            }
        },

        /**
         * Crea container per i toast se non esiste
         */
        createToastContainer() {
            if (!document.getElementById('toastContainer')) {
                const container = document.createElement('div');
                container.id = 'toastContainer';
                container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                container.style.zIndex = '9999';
                document.body.appendChild(container);
            }
        },

        /**
         * Fetch helper con gestione errori
         */
        async fetchJSON(url, options = {}) {
            try {
                const defaultOptions = {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrf,
                        'Accept': 'application/json'
                    }
                };

                const response = await fetch(url, { ...defaultOptions, ...options });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return await response.json();
            } catch (error) {
                console.error('Fetch error:', error);
                this.showToast('Si è verificato un errore. Riprova.', 'danger');
                throw error;
            }
        },

        /**
         * Aggiorna un contatore nel DOM
         */
        updateCounter(selector, value) {
            const element = document.querySelector(selector);
            if (element) {
                element.textContent = value;
            }
        },

        /**
         * Mostra/nascondi spinner di loading
         */
        toggleLoading(element, show = true) {
            if (show) {
                element.classList.add('loading');
                element.style.pointerEvents = 'none';
            } else {
                element.classList.remove('loading');
                element.style.pointerEvents = '';
            }
        }
    };

    // Dashboard Manager
    const Dashboard = {
        /**
         * Inizializza la dashboard
         */
        init() {
            console.log('Dashboard initialized');
            this.setupEventListeners();
            
            if (CONFIG.autoRefresh) {
                this.startAutoRefresh();
            }
        },

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            // Quick actions potrebbero avere eventi custom
            document.querySelectorAll('.quick-action-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (!e.target.closest('a')) {
                        const link = this.querySelector('a');
                        if (link) link.click();
                    }
                });
            });
        },

        /**
         * Auto-refresh delle statistiche
         */
        startAutoRefresh() {
            setInterval(async () => {
                await this.refreshStats();
            }, CONFIG.refreshInterval);
        },

        /**
         * Ricarica le statistiche via AJAX
         */
        async refreshStats() {
            try {
                const data = await Utils.fetchJSON('/api/dashboard/stats');
                
                if (data && data.total_searches !== undefined) {
                    Utils.updateCounter('.stat-number:nth-child(1)', data.total_searches);
                    Utils.updateCounter('.stat-number:nth-child(2)', data.cruises_viewed);
                    Utils.updateCounter('.stat-number:nth-child(3)', data.favorites_count);
                    Utils.updateCounter('.stat-number:nth-child(4)', data.active_alerts);
                }
            } catch (error) {
                console.error('Error refreshing stats:', error);
            }
        }
    };

    // Favorites Manager
    const Favorites = {
        /**
         * Toggle favorite (aggiungi/rimuovi)
         */
        async toggle(cruiseId, element) {
            Utils.toggleLoading(element);
            
            try {
                const data = await Utils.fetchJSON(`/cruises/${cruiseId}/favorite/toggle`, {
                    method: 'POST'
                });

                if (data.success) {
                    Utils.showToast(data.message, 'success');
                    
                    // Aggiorna icona
                    const icon = element.querySelector('i') || element;
                    if (data.is_favorite) {
                        icon.classList.remove('far');
                        icon.classList.add('fas', 'text-danger');
                    } else {
                        icon.classList.remove('fas', 'text-danger');
                        icon.classList.add('far');
                    }
                    
                    // Aggiorna contatori
                    if (data.favorites_count !== undefined) {
                        Utils.updateCounter('#favorites-count', data.favorites_count);
                        document.querySelectorAll('.favorites-count').forEach(el => {
                            el.textContent = data.favorites_count;
                        });
                    }
                }
            } catch (error) {
                console.error('Error toggling favorite:', error);
            } finally {
                Utils.toggleLoading(element, false);
            }
        },

        /**
         * Verifica se una crociera è nei preferiti
         */
        async check(cruiseId) {
            try {
                const data = await Utils.fetchJSON(`/api/favorites/check/${cruiseId}`);
                return data.success && data.is_favorite;
            } catch (error) {
                console.error('Error checking favorite:', error);
                return false;
            }
        },

        /**
         * Aggiorna la nota di un preferito
         */
        async updateNote(cruiseId, note) {
            try {
                const data = await Utils.fetchJSON(`/cruises/${cruiseId}/favorite/note`, {
                    method: 'PATCH',
                    body: JSON.stringify({ note: note })
                });

                if (data.success) {
                    Utils.showToast(data.message, 'success');
                    return true;
                }
            } catch (error) {
                console.error('Error updating note:', error);
                return false;
            }
        }
    };

    // Alerts Manager  
    const Alerts = {
        /**
         * Crea un nuovo alert
         */
        async create(alertData) {
            try {
                const data = await Utils.fetchJSON('/alert-prezzi', {
                    method: 'POST',
                    body: JSON.stringify(alertData)
                });

                if (data.success) {
                    Utils.showToast(data.message, 'success');
                    return data.alert;
                }
            } catch (error) {
                console.error('Error creating alert:', error);
                return null;
            }
        },

        /**
         * Elimina un alert
         */
        async delete(alertId, element) {
            if (!confirm('Sei sicuro di voler eliminare questo alert?')) {
                return;
            }

            Utils.toggleLoading(element);
            
            try {
                const data = await Utils.fetchJSON(`/alert-prezzi/${alertId}`, {
                    method: 'DELETE'
                });

                if (data.success) {
                    Utils.showToast(data.message, 'success');
                    
                    // Rimuovi l'elemento dal DOM
                    const card = element.closest('.alert-card, .card');
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        setTimeout(() => card.remove(), 300);
                    }
                    
                    return true;
                }
            } catch (error) {
                console.error('Error deleting alert:', error);
                return false;
            } finally {
                Utils.toggleLoading(element, false);
            }
        },

        /**
         * Toggle attivo/disattivo
         */
        async toggleActive(alertId, element) {
            Utils.toggleLoading(element);
            
            try {
                const data = await Utils.fetchJSON(`/alert-prezzi/${alertId}/toggle`, {
                    method: 'POST'
                });

                if (data.success) {
                    Utils.showToast(data.message, 'success');
                    
                    // Aggiorna UI
                    const card = element.closest('.alert-card, .card');
                    if (card) {
                        const badge = card.querySelector('.badge');
                        if (data.is_active) {
                            card.classList.remove('opacity-75');
                            badge.textContent = 'In Monitoraggio';
                            badge.classList.remove('bg-secondary');
                            badge.classList.add('bg-info');
                        } else {
                            card.classList.add('opacity-75');
                            badge.textContent = 'Disattivato';
                            badge.classList.remove('bg-info');
                            badge.classList.add('bg-secondary');
                        }
                    }
                    
                    return data.is_active;
                }
            } catch (error) {
                console.error('Error toggling alert:', error);
                return null;
            } finally {
                Utils.toggleLoading(element, false);
            }
        }
    };

    // Expose API globalmente
    window.DashboardAPI = {
        Utils,
        Dashboard,
        Favorites,
        Alerts
    };

    // Auto-inizializzazione quando il DOM è pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => Dashboard.init());
    } else {
        Dashboard.init();
    }
})();
