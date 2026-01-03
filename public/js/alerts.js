/**
 * ALERTS.JS - JavaScript per la pagina Alert Prezzi
 * You Price Cruises
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // Ottieni l'API dalla dashboard
    const { Utils, Alerts } = window.DashboardAPI || {};

    if (!Utils || !Alerts) {
        console.error('Dashboard API not loaded');
        return;
    }

    // ========================================================================
    // ELIMINAZIONE ALERT
    // ========================================================================

    // Elimina singolo alert
    document.querySelectorAll('.delete-alert').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const alertId = this.dataset.alertId;
            await Alerts.delete(alertId, this);
        });
    });

    // Elimina tutti gli alert inattivi
    const deleteInactiveBtn = document.getElementById('deleteInactiveAlerts');
    if (deleteInactiveBtn) {
        deleteInactiveBtn.addEventListener('click', async function() {
            if (!confirm('Sei sicuro di voler eliminare tutti gli alert inattivi?')) {
                return;
            }

            Utils.toggleLoading(this);

            try {
                const response = await Utils.fetchJSON('/api/alerts/inactive', {
                    method: 'DELETE'
                });

                if (response.success) {
                    Utils.showToast(response.message, 'success');
                    
                    // Rimuovi le card inattive dal DOM
                    document.querySelectorAll('.alert-card.opacity-75').forEach(card => {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        setTimeout(() => card.remove(), 300);
                    });
                    
                    // Ricarica dopo 1 secondo se non ci sono più card
                    setTimeout(() => {
                        const remainingCards = document.querySelectorAll('.alert-card');
                        if (remainingCards.length === 0) {
                            location.reload();
                        }
                    }, 1000);
                }
            } catch (error) {
                console.error('Error deleting inactive alerts:', error);
            } finally {
                Utils.toggleLoading(this, false);
            }
        });
    }

    // ========================================================================
    // TOGGLE ATTIVO/DISATTIVO
    // ========================================================================

    document.querySelectorAll('.toggle-alert').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const alertId = this.dataset.alertId;
            const isActive = await Alerts.toggleActive(alertId, this);
            
            if (isActive !== null) {
                // Aggiorna l'icona del pulsante
                const icon = this.querySelector('i');
                if (icon) {
                    this.title = isActive ? 'Disattiva' : 'Attiva';
                    this.classList.toggle('text-warning', isActive);
                    this.classList.toggle('text-success', !isActive);
                }
            }
        });
    });

    // ========================================================================
    // RESET NOTIFICA
    // ========================================================================

    document.querySelectorAll('.reset-notification').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const alertId = this.dataset.alertId;
            Utils.toggleLoading(this);

            try {
                const response = await Utils.fetchJSON(`/alert-prezzi/${alertId}/reset-notification`, {
                    method: 'POST'
                });

                if (response.success) {
                    Utils.showToast(response.message, 'success');
                    
                    // Nascondi il pulsante
                    this.style.display = 'none';
                }
            } catch (error) {
                console.error('Error resetting notification:', error);
            } finally {
                Utils.toggleLoading(this, false);
            }
        });
    });

    // ========================================================================
    // FORM CREAZIONE ALERT (se presente nella pagina)
    // ========================================================================

    const createAlertForm = document.getElementById('createAlertForm');
    if (createAlertForm) {
        createAlertForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const alertData = {
                cruise_id: formData.get('cruise_id'),
                target_price: parseFloat(formData.get('target_price')),
                cabin_type: formData.get('cabin_type'),
                alert_type: formData.get('alert_type') || 'fixed_price',
                percentage_threshold: formData.get('percentage_threshold') ? 
                    parseFloat(formData.get('percentage_threshold')) : null
            };

            const submitBtn = this.querySelector('button[type="submit"]');
            Utils.toggleLoading(submitBtn);

            const alert = await Alerts.create(alertData);

            if (alert) {
                // Resetta il form
                this.reset();
                
                // Chiudi il modal se presente
                const modal = bootstrap.Modal.getInstance(this.closest('.modal'));
                if (modal) {
                    modal.hide();
                }
                
                // Ricarica la pagina dopo 1 secondo
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }

            Utils.toggleLoading(submitBtn, false);
        });
    }

    // ========================================================================
    // AGGIORNAMENTO PROGRESS BAR
    // ========================================================================

    // Anima le progress bar al caricamento
    document.querySelectorAll('.progress-bar').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        
        setTimeout(() => {
            bar.style.transition = 'width 1s ease';
            bar.style.width = width;
        }, 100);
    });

    // ========================================================================
    // FILTRI (opzionale)
    // ========================================================================

    // Filtro per stato alert
    const filterButtons = document.querySelectorAll('[data-filter]');
    if (filterButtons.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.dataset.filter;
                
                // Aggiorna pulsanti attivi
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filtra le card
                document.querySelectorAll('.alert-card').forEach(card => {
                    const badge = card.querySelector('.badge');
                    const badgeText = badge ? badge.textContent.toLowerCase() : '';
                    
                    let shouldShow = true;
                    
                    if (filter === 'active') {
                        shouldShow = !card.classList.contains('opacity-75');
                    } else if (filter === 'inactive') {
                        shouldShow = card.classList.contains('opacity-75');
                    } else if (filter === 'reached') {
                        shouldShow = badgeText.includes('raggiunto');
                    }
                    
                    card.closest('.col-md-6, .col-lg-4').style.display = 
                        shouldShow ? '' : 'none';
                });
            });
        });
    }

    // ========================================================================
    // ORDINAMENTO
    // ========================================================================

    const sortSelect = document.getElementById('sortAlerts');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortBy = this.value;
            const container = document.querySelector('.row');
            const cards = Array.from(container.querySelectorAll('.col-md-6, .col-lg-4'));
            
            cards.sort((a, b) => {
                const cardA = a.querySelector('.alert-card');
                const cardB = b.querySelector('.alert-card');
                
                if (sortBy === 'date') {
                    // Ordina per data creazione (più recenti prima)
                    return 0; // Già ordinati dal backend
                } else if (sortBy === 'progress') {
                    // Ordina per percentuale di progresso
                    const progressA = parseInt(cardA.querySelector('.progress-bar').style.width) || 0;
                    const progressB = parseInt(cardB.querySelector('.progress-bar').style.width) || 0;
                    return progressB - progressA;
                } else if (sortBy === 'price') {
                    // Ordina per prezzo target
                    const priceA = parseFloat(cardA.querySelector('.text-primary').textContent.replace(/[^\d]/g, '')) || 0;
                    const priceB = parseFloat(cardB.querySelector('.text-primary').textContent.replace(/[^\d]/g, '')) || 0;
                    return priceA - priceB;
                }
                return 0;
            });
            
            // Riordina nel DOM
            cards.forEach(card => container.appendChild(card));
        });
    }

    // ========================================================================
    // ANIMAZIONI
    // ========================================================================

    // Aggiungi animazione fade-in alle card
    const cards = document.querySelectorAll('.alert-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 50);
    });

    // ========================================================================
    // TOOLTIP INITIALIZATION
    // ========================================================================

    // Inizializza i tooltip di Bootstrap se presenti
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
