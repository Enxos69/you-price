/**
 * FAVORITES.JS - JavaScript per la pagina Preferiti
 * You Price Cruises
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // Ottieni l'API dalla dashboard
    const { Utils, Favorites } = window.DashboardAPI || {};

    if (!Utils || !Favorites) {
        console.error('Dashboard API not loaded');
        return;
    }

    // ========================================================================
    // RIMOZIONE PREFERITI
    // ========================================================================

    // Gestione rimozione singolo preferito
    document.querySelectorAll('.remove-favorite').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const cruiseId = this.dataset.cruiseId;
            
            if (!confirm('Sei sicuro di voler rimuovere questa crociera dai preferiti?')) {
                return;
            }

            const card = this.closest('.favorite-card');
            Utils.toggleLoading(card);

            const success = await Favorites.toggle(cruiseId, this);
            
            if (success !== undefined) {
                // Rimuovi la card con animazione
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                
                setTimeout(() => {
                    card.remove();
                    
                    // Controlla se non ci sono più preferiti
                    const remainingCards = document.querySelectorAll('.favorite-card');
                    if (remainingCards.length === 0) {
                        location.reload(); // Ricarica per mostrare empty state
                    }
                }, 300);
            } else {
                Utils.toggleLoading(card, false);
            }
        });
    });

    // Rimuovi tutti i preferiti
    const removeAllBtn = document.getElementById('removeAllFavorites');
    if (removeAllBtn) {
        removeAllBtn.addEventListener('click', async function() {
            if (!confirm('Sei sicuro di voler rimuovere TUTTI i preferiti? Questa azione non può essere annullata.')) {
                return;
            }

            Utils.toggleLoading(this);

            try {
                const response = await Utils.fetchJSON('/api/favorites/all', {
                    method: 'DELETE'
                });

                if (response.success) {
                    Utils.showToast(response.message, 'success');
                    
                    // Ricarica la pagina dopo 1 secondo
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            } catch (error) {
                console.error('Error removing all favorites:', error);
                Utils.toggleLoading(this, false);
            }
        });
    }

    // ========================================================================
    // AGGIORNAMENTO NOTE
    // ========================================================================

    document.querySelectorAll('.update-note-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const cruiseId = this.dataset.cruiseId;
            const noteTextarea = this.querySelector('textarea[name="note"]');
            const note = noteTextarea.value.trim();
            const submitBtn = this.querySelector('button[type="submit"]');

            Utils.toggleLoading(submitBtn);

            const success = await Favorites.updateNote(cruiseId, note);

            if (success) {
                // Chiudi il modal
                const modal = bootstrap.Modal.getInstance(this.closest('.modal'));
                if (modal) {
                    modal.hide();
                }

                // Aggiorna la nota nella card se visibile
                const card = document.querySelector(`.favorite-card[data-cruise-id="${cruiseId}"]`);
                if (card) {
                    let noteAlert = card.querySelector('.alert-light');
                    
                    if (note) {
                        if (!noteAlert) {
                            // Crea il box della nota
                            noteAlert = document.createElement('div');
                            noteAlert.className = 'alert alert-light border mb-3';
                            noteAlert.innerHTML = `
                                <small class="text-muted d-block mb-1">
                                    <i class="fas fa-sticky-note me-1"></i>Nota personale:
                                </small>
                                <small>${note}</small>
                            `;
                            card.querySelector('.card-body .row').after(noteAlert);
                        } else {
                            // Aggiorna la nota esistente
                            noteAlert.querySelector('small:last-child').textContent = note;
                        }
                    } else {
                        // Rimuovi la nota se vuota
                        if (noteAlert) {
                            noteAlert.remove();
                        }
                    }
                }
            }

            Utils.toggleLoading(submitBtn, false);
        });
    });

    // ========================================================================
    // CHARACTER COUNTER PER NOTE
    // ========================================================================

    document.querySelectorAll('textarea[name="note"]').forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength') || 500;
        
        // Crea il counter
        const counter = document.createElement('small');
        counter.className = 'text-muted float-end';
        counter.textContent = `${textarea.value.length}/${maxLength}`;
        
        const formText = textarea.nextElementSibling;
        if (formText && formText.classList.contains('form-text')) {
            formText.appendChild(counter);
        }

        // Aggiorna il counter
        textarea.addEventListener('input', function() {
            counter.textContent = `${this.value.length}/${maxLength}`;
            
            if (this.value.length >= maxLength * 0.9) {
                counter.classList.add('text-warning');
            } else {
                counter.classList.remove('text-warning');
            }
        });
    });

    // ========================================================================
    // FILTRI E ORDINAMENTO (opzionale)
    // ========================================================================

    // Aggiungi funzionalità di ricerca locale se necessario
    const searchInput = document.getElementById('searchFavorites');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            document.querySelectorAll('.favorite-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                const shouldShow = text.includes(searchTerm);
                
                card.closest('.col-md-6, .col-lg-4').style.display = 
                    shouldShow ? '' : 'none';
            });
        });
    }

    // ========================================================================
    // ANIMAZIONI
    // ========================================================================

    // Aggiungi animazione fade-in alle card
    const cards = document.querySelectorAll('.favorite-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 50);
    });
});
