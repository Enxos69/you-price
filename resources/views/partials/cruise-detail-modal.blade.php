{{-- Modale Dettaglio Crociera - Condivisa tra Dashboard e Ricerca --}}
@auth
<div class="modal fade" id="cruiseDetailModal" tabindex="-1" aria-labelledby="cruiseDetailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="cruiseDetailModalLabel">
                    <i class="fas fa-ship me-2"></i>
                    <span id="modal-cruise-name">Dettaglio Crociera</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-cruise-body">
                {{-- Contenuto dinamico caricato via JavaScript --}}
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Caricamento...</span>
                    </div>
                    <p class="mt-3 text-muted">Caricamento dettagli...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="favorite-btn" class="btn btn-outline-danger">
                    <i class="fas fa-heart me-2" id="favorite-icon"></i>
                    <span id="favorite-text">Aggiungi ai Preferiti</span>
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Chiudi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    {{-- Toast inseriti dinamicamente via JavaScript --}}
</div>
@endauth