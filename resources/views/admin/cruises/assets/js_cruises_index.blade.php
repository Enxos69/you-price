<script>
$(document).ready(function() {
    // Inizializza il form
    initializeForm();
    
    // Inizializza validazioni
    initializeValidation();
    
    // Inizializza gestori eventi
    initializeEventHandlers();
    
    // Inizializza SweetAlert
    initializeSweetAlert();
});

function initializeForm() {
    // Auto-calcolo durata da notti
    $('#night').on('input', function() {
        const nights = parseInt($(this).val());
        if (nights && nights > 0) {
            $('#duration').val(nights + 1);
        }
    });
    
    // Auto-calcolo notti da durata
    $('#duration').on('input', function() {
        const days = parseInt($(this).val());
        if (days && days > 1) {
            $('#night').val(days - 1);
        }
    });
    
    // Validazione date
    $('#partenza').on('change', function() {
        const departureDate = $(this).val();
        const arrivalInput = $('#arrivo');
        
        if (departureDate) {
            // Imposta data minima per arrivo
            arrivalInput.attr('min', departureDate);
            
            // Se c'è una durata, calcola automaticamente l'arrivo
            const duration = parseInt($('#duration').val());
            if (duration && duration > 0) {
                const departure = new Date(departureDate);
                const arrival = new Date(departure);
                arrival.setDate(departure.getDate() + duration - 1);
                arrivalInput.val(arrival.toISOString().split('T')[0]);
            }
        }
    });
    
    // Auto-suggerimenti per compagnie personalizzate
    $('#line').on('change', function() {
        if ($(this).val() === 'Altra') {
            showCustomCompanyInput();
        }
    });
    
    // Formattazione prezzi in tempo reale
    $('.form-control[type="number"]').on('input', function() {
        const value = $(this).val();
        if (value && value > 0) {
            formatPriceInput($(this));
        }
    });
}

function showCustomCompanyInput() {
    Swal.fire({
        title: 'Compagnia Personalizzata',
        input: 'text',
        inputLabel: 'Nome della compagnia',
        inputPlaceholder: 'Inserisci il nome della compagnia...',
        showCancelButton: true,
        confirmButtonColor: '#84bc00',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Aggiungi',
        cancelButtonText: 'Annulla',
        inputValidator: (value) => {
            if (!value) {
                return 'Devi inserire il nome della compagnia!';
            }
            if (value.length < 2) {
                return 'Il nome deve avere almeno 2 caratteri!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const customCompany = result.value;
            
            // Aggiungi l'opzione al select
            const option = new Option(customCompany, customCompany, true, true);
            $('#line').append(option);
            
            showToast(`Compagnia "${customCompany}" aggiunta con successo!`, 'success');
        } else {
            // Ripristina selezione precedente
            $('#line').val('').trigger('change');
        }
    });
}

function formatPriceInput(input) {
    // Formattazione visiva dei prezzi (opzionale)
    const value = parseFloat(input.val());
    if (!isNaN(value) && value > 0) {
        input.addClass('is-valid').removeClass('is-invalid');
    }
}

function initializeValidation() {
    // Validazione personalizzata
    $('#cruiseForm').on('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            submitForm();
        }
    });
    
    // Validazione in tempo reale
    $('.form-control[required]').on('blur', function() {
        validateField($(this));
    });
    
    // Validazione prezzi
    $('.form-control[type="number"]').on('blur', function() {
        validatePriceField($(this));
    });
}

function validateForm() {
    let isValid = true;
    const errors = {};
    
    // Validazione campi obbligatori
    const requiredFields = ['ship', 'cruise', 'line'];
    requiredFields.forEach(field => {
        const input = $(`#${field}`);
        if (!input.val().trim()) {
            errors[field] = 'Questo campo è obbligatorio';
            isValid = false;
        }
    });
    
    // Validazione date
    const departureDate = $('#partenza').val();
    const arrivalDate = $('#arrivo').val();
    
    if (departureDate && arrivalDate) {
        if (new Date(departureDate) > new Date(arrivalDate)) {
            errors['arrivo'] = 'La data di arrivo deve essere uguale o successiva alla partenza';
            isValid = false;
        }
    }
    
    // Validazione durata/notti
    const duration = parseInt($('#duration').val());
    const nights = parseInt($('#night').val());
    
    if (duration && nights && Math.abs(duration - nights) > 1) {
        errors['duration'] = 'Durata e numero di notti non sono coerenti';
        isValid = false;
    }
    
    // Validazione prezzi
    const priceFields = ['interior', 'oceanview', 'balcony', 'minisuite', 'suite'];
    let hasPrices = false;
    
    priceFields.forEach(field => {
        const value = parseFloat($(`#${field}`).val());
        if (value && value > 0) {
            hasPrices = true;
            if (value > 50000) {
                errors[field] = 'Il prezzo sembra troppo alto (max €50.000)';
                isValid = false;
            }
        }
    });
    
    if (!hasPrices) {
        showToast('Inserisci almeno un prezzo per una tipologia di cabina', 'warning');
    }
    
    // Mostra errori
    displayValidationErrors(errors);
    
    return isValid;
}

function validateField(input) {
    const field = input.attr('name');
    const value = input.val().trim();
    
    clearFieldError(input);
    
    if (input.prop('required') && !value) {
        showFieldError(input, 'Questo campo è obbligatorio');
        return false;
    }
    
    // Validazioni specifiche per campo
    switch (field) {
        case 'ship':
        case 'cruise':
            if (value.length < 2) {
                showFieldError(input, 'Deve contenere almeno 2 caratteri');
                return false;
            }
            break;
            
        case 'duration':
        case 'night':
            const num = parseInt(value);
            if (value && (isNaN(num) || num < 1 || num > 365)) {
                showFieldError(input, 'Inserisci un numero valido tra 1 e 365');
                return false;
            }
            break;
    }
    
    input.addClass('is-valid');
    return true;
}

function validatePriceField(input) {
    const value = parseFloat(input.val());
    
    clearFieldError(input);
    
    if (input.val() && (isNaN(value) || value < 0)) {
        showFieldError(input, 'Inserisci un prezzo valido');
        return false;
    }
    
    if (value > 50000) {
        showFieldError(input, 'Il prezzo sembra troppo alto (max €50.000)');
        return false;
    }
    
    if (value > 0) {
        input.addClass('is-valid');
    }
    
    return true;
}

function showFieldError(input, message) {
    input.addClass('is-invalid').removeClass('is-valid');
    input.siblings('.invalid-feedback').text(message);
}

function clearFieldError(input) {
    input.removeClass('is-invalid is-valid');
    input.siblings('.invalid-feedback').text('');
}

function displayValidationErrors(errors) {
    // Pulisci errori precedenti
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    // Mostra nuovi errori
    Object.keys(errors).forEach(field => {
        const input = $(`#${field}`);
        showFieldError(input, errors[field]);
    });
    
    // Scrolla al primo errore
    if (Object.keys(errors).length > 0) {
        const firstError = $(`#${Object.keys(errors)[0]}`);
        $('html, body').animate({
            scrollTop: firstError.offset().top - 100
        }, 500);
    }
}

function submitForm() {
    const submitBtn = $('#submitBtn');
    const btnText = $('#btnText');
    const loadingSpinner = $('#loadingSpinner');
    
    // Mostra loading
    submitBtn.prop('disabled', true);
    loadingSpinner.removeClass('d-none');
    btnText.html('<i class="fas fa-spinner fa-spin me-2"></i>Salvataggio...');
    
    // Prepara dati
    const formData = new FormData(document.getElementById('cruiseForm'));
    
    // Determina URL e metodo
    const form = $('#cruiseForm');
    const url = form.attr('action');
    const method = form.find('input[name="_method"]').val() || 'POST';
    
    $.ajax({
        url: url,
        method: method === 'PUT' ? 'POST' : method,
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.response) {
                // Successo
                Swal.fire({
                    title: 'Successo!',
                    text: response.message || 'Crociera salvata con successo',
                    icon: 'success',
                    confirmButtonColor: '#84bc00',
                    confirmButtonText: 'OK'
                }).then(() => {
                    if (response.cruise_id) {
                        // Redirect alla pagina di visualizzazione
                        window.location.href = `/admin/cruises/${response.cruise_id}`;
                    } else {
                        // Redirect alla lista
                        window.location.href = '{{ route("cruises.index") }}';
                    }
                });
            } else {
                // Errori di validazione
                handleValidationErrors(response.errors);
                resetSubmitButton();
            }
        },
        error: function(xhr) {
            resetSubmitButton();
            
            if (xhr.status === 422) {
                // Errori di validazione
                const response = xhr.responseJSON;
                if (response && response.errors) {
                    handleValidationErrors(response.errors);
                } else {
                    showToast('Errori di validazione. Controlla i dati inseriti.', 'error');
                }
            } else if (xhr.status === 419) {
                // Token CSRF scaduto
                showToast('Sessione scaduta. Ricarica la pagina.', 'warning');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                // Altri errori
                const message = xhr.responseJSON?.message || 'Si è verificato un errore. Riprova più tardi.';
                showToast(message, 'error');
            }
        }
    });
}

function resetSubmitButton() {
    const submitBtn = $('#submitBtn');
    const btnText = $('#btnText');
    const loadingSpinner = $('#loadingSpinner');
    const isEdit = $('input[name="_method"]').val() === 'PUT';
    
    submitBtn.prop('disabled', false);
    loadingSpinner.addClass('d-none');
    btnText.html(isEdit ? 
        '<i class="fas fa-save me-2"></i>Aggiorna Crociera' : 
        '<i class="fas fa-save me-2"></i>Salva Crociera'
    );
}

function handleValidationErrors(errors) {
    displayValidationErrors(errors);
    
    // Mostra toast con riepilogo errori
    const errorCount = Object.keys(errors).length;
    showToast(`Trovati ${errorCount} errori. Correggi i campi evidenziati.`, 'error');
}

function resetForm() {
    Swal.fire({
        title: 'Conferma Reset',
        text: 'Sei sicuro di voler resettare tutti i campi?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sì, resetta',
        cancelButtonText: 'Annulla'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('cruiseForm').reset();
            $('.form-control').removeClass('is-invalid is-valid');
            $('.invalid-feedback').text('');
            showToast('Form resettato', 'success');
        }
    });
}

function initializeEventHandlers() {
    // Gestione tasti di scelta rapida
    $(document).on('keydown', function(e) {
        // Ctrl + S per salvare
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            $('#cruiseForm').submit();
        }
        
        // Ctrl + R per resettare
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            resetForm();
        }
        
        // ESC per annullare
        if (e.key === 'Escape') {
            window.history.back();
        }
    });
    
    // Auto-save draft (opzionale)
    let autoSaveTimeout;
    $('.form-control').on('input', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(saveDraft, 30000); // Salva dopo 30 secondi di inattività
    });
    
    // Avviso prima di uscire con modifiche non salvate
    let formChanged = false;
    $('.form-control').on('input change', function() {
        formChanged = true;
    });
    
    $(window).on('beforeunload', function(e) {
        if (formChanged) {
            const message = 'Hai modifiche non salvate. Sei sicuro di voler uscire?';
            e.returnValue = message;
            return message;
        }
    });
    
    // Rimuovi avviso dopo submit
    $('#cruiseForm').on('submit', function() {
        formChanged = false;
    });
}

function saveDraft() {
    // Implementazione opzionale per salvare bozze
    const formData = $('#cruiseForm').serialize();
    localStorage.setItem('cruise_draft', formData);
    showToast('Bozza salvata automaticamente', 'info');
}

function loadDraft() {
    // Implementazione opzionale per caricare bozze
    const draft = localStorage.getItem('cruise_draft');
    if (draft) {
        // Popola il form con i dati della bozza
        showToast('Bozza caricata', 'info');
    }
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
    `;
    document.head.appendChild(swalCustom);
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

// Esporta funzioni globalmente
window.resetForm = resetForm;
window.saveDraft = saveDraft;
window.loadDraft = loadDraft;
</script>