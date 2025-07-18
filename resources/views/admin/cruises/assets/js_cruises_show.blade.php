<script>
$(document).ready(function() {
    // Inizializza la pagina
    initializeShowPage();
    
    // Inizializza SweetAlert
    initializeSweetAlert();
    
    // Carica animazioni
    setTimeout(() => {
        animateElements();
    }, 300);
});

function initializeShowPage() {
    // Inizializza tooltip
    $('[title]').tooltip();
    
    // Gestione tasti di scelta rapida
    $(document).on('keydown', function(e) {
        // E per modifica
        if (e.key === 'e' || e.key === 'E') {
            const editBtn = $('a[href*="edit"]').first();
            if (editBtn.length) {
                window.location.href = editBtn.attr('href');
            }
        }
        
        // Ctrl + P per stampare
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            printCruise();
        }
        
        // Ctrl + Backspace per tornare indietro
        if (e.ctrlKey && e.key === 'Backspace') {
            e.preventDefault();
            window.history.back();
        }
        
        // ESC per tornare alla lista
        if (e.key === 'Escape') {
            window.location.href = '{{ route("cruises.index") }}';
        }
    });
    
    // Click su prezzi per maggiori dettagli
    $('.price-card.available').on('click', function() {
        const cabinType = $(this).find('h6').text();
        const price = $(this).find('.price').text();
        
        showPriceDetails(cabinType, price);
    });
    
    // Hover effects su crociere simili
    $('.similar-cruise-item').hover(
        function() {
            $(this).find('.similar-actions').addClass('show');
        },
        function() {
            $(this).find('.similar-actions').removeClass('show');
        }
    );
}

function deleteCruise(cruiseId, shipName, cruiseName) {
    Swal.fire({
        title: 'Conferma Eliminazione',
        html: `Sei sicuro di voler eliminare la crociera:<br><br><strong>${shipName}</strong><br><em>${cruiseName}</em>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Elimina',
        cancelButtonText: '<i class="fas fa-times me-2"></i>Annulla',
        customClass: {
            popup: 'swal-custom'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            performDelete(cruiseId, shipName);
        }
    });
}

function performDelete(cruiseId, shipName) {
    // Mostra loading
    Swal.fire({
        title: 'Eliminazione in corso...',
        text: 'Attendere prego',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: `/admin/cruises/${cruiseId}`,
        type: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.response) {
                Swal.fire({
                    title: 'Eliminazione Completata!',
                    text: `La crociera "${shipName}" è stata eliminata con successo.`,
                    icon: 'success',
                    confirmButtonColor: '#84bc00',
                    confirmButtonText: '<i class="fas fa-check me-2"></i>OK'
                }).then(() => {
                    // Redirect alla lista
                    window.location.href = '{{ route("cruises.index") }}';
                });
            } else {
                Swal.fire({
                    title: 'Errore!',
                    text: response.message || 'Si è verificato un errore durante l\'eliminazione.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
        },
        error: function(xhr) {
            let errorMessage = 'Si è verificato un errore durante l\'eliminazione.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            Swal.fire({
                title: 'Errore!',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}

function printCruise() {
    // Prepara la pagina per la stampa
    const originalTitle = document.title;
    const cruiseTitle = $('.header-text h1').text();
    const cruiseSubtitle = $('.header-text p').text();
    
    document.title = `${cruiseTitle} - ${cruiseSubtitle}`;
    
    // Nascondi elementi non necessari per la stampa
    $('.header-actions, .actions-section, .similar-cruises-card').hide();
    
    // Mostra dialog di stampa
    window.print();
    
    // Ripristina elementi dopo stampa
    setTimeout(() => {
        $('.header-actions, .actions-section, .similar-cruises-card').show();
        document.title = originalTitle;
    }, 1000);
}

function showPriceDetails(cabinType, price) {
    // Simula dettagli aggiuntivi per i prezzi
    const details = generatePriceDetails(cabinType, price);
    
    Swal.fire({
        title: `Dettagli ${cabinType}`,
        html: details,
        icon: 'info',
        confirmButtonColor: '#84bc00',
        confirmButtonText: '<i class="fas fa-check me-2"></i>Chiudi',
        customClass: {
            popup: 'swal-custom'
        }
    });
}

function generatePriceDetails(cabinType, price) {
    const cleanPrice = price.replace('€', '').replace('.', '').replace(',', '');
    const numericPrice = parseInt(cleanPrice);
    
    let details = `<div class="text-start">
        <p><strong>Prezzo:</strong> ${price}</p>
        <p><strong>Tipologia:</strong> ${cabinType}</p>
    `;
    
    // Aggiungi dettagli specifici per tipologia
    switch (cabinType.toLowerCase()) {
        case 'cabina interna':
            details += `
                <p><strong>Caratteristiche:</strong></p>
                <ul class="text-start">
                    <li>Senza finestre esterne</li>
                    <li>Più economica</li>
                    <li>Stesso comfort delle altre cabine</li>
                </ul>
            `;
            break;
        case 'vista mare':
            details += `
                <p><strong>Caratteristiche:</strong></p>
                <ul class="text-start">
                    <li>Finestra con vista mare</li>
                    <li>Luce naturale</li>
                    <li>Vista panoramica</li>
                </ul>
            `;
            break;
        case 'balcone':
            details += `
                <p><strong>Caratteristiche:</strong></p>
                <ul class="text-start">
                    <li>Balcone privato</li>
                    <li>Spazio esterno</li>
                    <li>Vista panoramica</li>
                    <li>Maggiore privacy</li>
                </ul>
            `;
            break;
        case 'mini suite':
            details += `
                <p><strong>Caratteristiche:</strong></p>
                <ul class="text-start">
                    <li>Spazio maggiorato</li>
                    <li>Area living separata</li>
                    <li>Servizi premium</li>
                    <li>Balcone ampio</li>
                </ul>
            `;
            break;
        case 'suite':
            details += `
                <p><strong>Caratteristiche:</strong></p>
                <ul class="text-start">
                    <li>Massimo lusso</li>
                    <li>Più stanze</li>
                    <li>Servizio maggiordomo</li>
                    <li>Accesso aree esclusive</li>
                </ul>
            `;
            break;
    }
    
    // Calcola prezzo per notte (se disponibile)
    const nightsElement = $('[data-nights]');
    if (nightsElement.length) {
        const nights = parseInt(nightsElement.data('nights'));
        if (nights > 0) {
            const pricePerNight = Math.round(numericPrice / nights);
            details += `<p><strong>Prezzo per notte:</strong> €${pricePerNight.toLocaleString('it-IT')}</p>`;
        }
    }
    
    details += `</div>`;
    return details;
}

function animateElements() {
    // Anima le sezioni info
    $('.info-section').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(30px)'
        });

        setTimeout(() => {
            $(this).css({
                'transition': 'all 0.8s ease',
                'opacity': '1',
                'transform': 'translateY(0)'
            });
        }, index * 150);
    });
    
    // Anima le price cards
    $('.price-card').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'scale(0.9)'
        });

        setTimeout(() => {
            $(this).css({
                'transition': 'all 0.6s ease',
                'opacity': '1',
                'transform': 'scale(1)'
            });
        }, (index * 100) + 500);
    });
    
    // Anima i punti dell'itinerario
    $('.itinerary-point').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateX(' + (index === 0 ? '-50px' : '50px') + ')'
        });

        setTimeout(() => {
            $(this).css({
                'transition': 'all 0.7s ease',
                'opacity': '1',
                'transform': 'translateX(0)'
            });
        }, (index * 200) + 300);
    });
    
    // Anima le action buttons
    $('.action-btn').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(20px)'
        });

        setTimeout(() => {
            $(this).css({
                'transition': 'all 0.5s ease',
                'opacity': '1',
                'transform': 'translateY(0)'
            });
        }, (index * 100) + 800);
    });
}

function initializeSweetAlert() {
    // Personalizza SweetAlert2
    const swalCustom = document.createElement('style');
    swalCustom.innerHTML = `
        .swal-custom {
            border-radius: 15px !important;
        }
        .swal2-popup.swal-custom .swal2-title {
            color: var(--youPrice-dark) !important;
        }
        .swal2-popup.swal-custom .swal2-content {
            color: #6c757d !important;
        }
        .swal2-popup .text-start {
            text-align: left !important;
        }
        .swal2-popup ul {
            margin: 0.5rem 0;
            padding-left: 1.5rem;
        }
        .swal2-popup li {
            margin-bottom: 0.25rem;
        }
    `;
    document.head.appendChild(swalCustom);
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('it-IT', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}

function shareCruise() {
    // Implementazione per condividere la crociera
    if (navigator.share) {
        navigator.share({
            title: document.title,
            text: 'Guarda questa crociera interessante!',
            url: window.location.href
        }).catch(err => {
            console.log('Errore nella condivisione:', err);
            copyToClipboard();
        });
    } else {
        copyToClipboard();
    }
}

function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        showToast('Link copiato negli appunti!', 'success');
    }).catch(() => {
        // Fallback per browser più vecchi
        const textArea = document.createElement('textarea');
        textArea.value = window.location.href;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showToast('Link copiato negli appunti!', 'success');
        } catch (err) {
            showToast('Impossibile copiare il link', 'error');
        }
        document.body.removeChild(textArea);
    });
}

function showToast(message, type) {
    type = type || 'info';
    
    const toastContainer = getOrCreateToastContainer();
    const toastId = 'toast-' + Date.now();
    
    let bgClass = 'bg-primary';
    let iconClass = 'fa-info-circle';
    
    switch (type) {
        case 'success':
            bgClass = 'bg-success';
            iconClass = 'fa-check-circle';
            break;
        case 'error':
            bgClass = 'bg-danger';
            iconClass = 'fa-exclamation-circle';
            break;
        case 'warning':
            bgClass = 'bg-warning';
            iconClass = 'fa-exclamation-triangle';
            break;
    }
    
    const toastHtml = `<div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas ${iconClass} me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>`;
    
    toastContainer.append(toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 3000
    });
    
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

function getOrCreateToastContainer() {
    let container = $('#toast-container');
    if (container.length === 0) {
        container = $('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11;"></div>');
        $('body').append(container);
    }
    return container;
}

// Gestione eventi per il responsive
$(window).on('resize', function() {
    // Ricalcola layout per elementi responsive
    $('.prices-grid, .actions-grid, .similar-cruises-grid').each(function() {
        // Trigger di ricalcolo layout se necessario
    });
});

// Gestione stampa
$(window).on('beforeprint', function() {
    $('.header-actions, .actions-section').hide();
});

$(window).on('afterprint', function() {
    $('.header-actions, .actions-section').show();
});

// Esporta funzioni globalmente
window.deleteCruise = deleteCruise;
window.printCruise = printCruise;
window.shareCruise = shareCruise;