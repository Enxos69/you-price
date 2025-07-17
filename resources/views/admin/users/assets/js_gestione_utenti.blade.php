<script>
let usersTable;

$(document).ready(function() {
    // Inizializza la DataTable
    initializeUsersTable();
    
    // Inizializza i filtri
    initializeFilters();
    
    // Inizializza i tooltip
    initializeTooltips();
    
    // Carica le statistiche dalla tabella esistente
    loadUserStatsFromTable();
});

function initializeUsersTable() {
    usersTable = $('#users-table').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("users.data") }}',
            beforeSend: function() {
                showLoading(true);
            },
            complete: function() {
                showLoading(false);
                updateUserStats();
            }
        },
        columns: [
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    return `<div class="fw-bold text-dark">${data || 'N/D'}</div>`;
                }
            },
            {
                data: 'surname',
                name: 'surname',
                render: function(data, type, row) {
                    return `<div class="fw-bold text-dark">${data || 'N/D'}</div>`;
                }
            },
            {
                data: 'email',
                name: 'email',
                render: function(data, type, row) {
                    return `<div class="text-muted">${data}</div>`;
                }
            },
            {
                data: 'roles',
                name: 'roles',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    // Fix: gestisce i ruoli dal tuo sistema
                    const isAdmin = data && (
                        data.toLowerCase().includes('amministratore') || 
                        data.toLowerCase().includes('admin')
                    );
                    const badgeClass = isAdmin ? 'admin' : 'user';
                    const roleText = isAdmin ? 'Amministratore' : 'Utente';
                    
                    return `<span class="role-badge ${badgeClass}">
                        <i class="fas fa-${isAdmin ? 'user-shield' : 'user'} me-1"></i>
                        ${roleText}
                    </span>`;
                }
            },
            {
                data: 'abilitato',
                name: 'abilitato',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    // Il controller ora restituisce già l'HTML formattato
                    return data;
                }
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return data; // Il controller già fornisce l'HTML formattato
                }
            }
        ],
        language: {
            processing: "Elaborazione...",
            search: "",
            searchPlaceholder: "Cerca utenti...",
            lengthMenu: "Mostra _MENU_ utenti",
            info: "Visualizzazione da _START_ a _END_ di _TOTAL_ utenti",
            infoEmpty: "Nessun utente trovato",
            infoFiltered: "(filtrati da _MAX_ utenti totali)",
            infoPostFix: "",
            loadingRecords: "Caricamento...",
            zeroRecords: "Nessun utente corrisponde ai criteri di ricerca",
            emptyTable: "Nessun utente presente nella tabella",
            paginate: {
                first: "Primo",
                previous: "Precedente",
                next: "Successivo",
                last: "Ultimo"
            }
        },
        pageLength: 10,
        lengthMenu: [
            [5, 10, 25, 50, -1],
            [5, 10, 25, 50, "Tutti"]
        ],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        order: [[0, 'asc']],
        drawCallback: function() {
            initializeTooltips();
            updateActionButtons();
        }
    });
}

function initializeFilters() {
    // Search globale personalizzata
    $('#globalSearch').on('keyup', function() {
        usersTable.search(this.value).draw();
    });
    
    // Filtro per ruolo - Fix per i tuoi ruoli
    $('#roleFilter').on('change', function() {
        const value = this.value;
        if (value === 'admin') {
            usersTable.column(3).search('Amministratore').draw();
        } else if (value === 'user') {
            usersTable.column(3).search('Utente').draw();
        } else {
            usersTable.column(3).search('').draw();
        }
    });
    
    // Filtro per stato - Fix per i tuoi stati
    $('#statusFilter').on('change', function() {
        const value = this.value;
        if (value === '') {
            usersTable.column(4).search('').draw();
        } else if (value === '1') {
            usersTable.column(4).search('Attivo').draw();
        } else if (value === '0') {
            usersTable.column(4).search('Disabilitato').draw();
        }
    });
}

function initializeTooltips() {
    $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
}

function updateActionButtons() {
    // Riattacca gli event listeners per i pulsanti di azione
    $('.lockButton, .unlockButton').off('click');
    attachActionEvents();
}

function attachActionEvents() {
    // Eventi per i pulsanti "Lock"
    $(document).on('click', '.lockButton', function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name') || 'questo utente';
        
        Swal.fire({
            title: 'Conferma Disabilitazione',
            text: `Sei sicuro di voler disabilitare ${userName}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-user-times me-2"></i>Disabilita',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Annulla',
            customClass: {
                popup: 'swal-custom',
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                performUserAction('lock', userId, userName);
            }
        });
    });

    // Eventi per i pulsanti "Unlock"
    $(document).on('click', '.unlockButton', function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name') || 'questo utente';
        
        Swal.fire({
            title: 'Conferma Abilitazione',
            text: `Sei sicuro di voler abilitare ${userName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-user-check me-2"></i>Abilita',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Annulla',
            customClass: {
                popup: 'swal-custom',
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                performUserAction('unlock', userId, userName);
            }
        });
    });
}

function performUserAction(action, userId, userName) {
    const actionUrl = action === 'lock' ? '{{ route("users.lock") }}' : '{{ route("users.unlock") }}';
    const actionText = action === 'lock' ? 'disabilitazione' : 'abilitazione';
    const successText = action === 'lock' ? 'disabilitato' : 'abilitato';
    
    // Mostra loading
    showLoading(true);
    
    $.ajax({
        url: actionUrl,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            userId: userId
        },
        success: function(response) {
            showLoading(false);
            
            Swal.fire({
                title: 'Operazione Completata!',
                text: `${userName} è stato ${successText} con successo.`,
                icon: 'success',
                confirmButtonColor: '#84bc00',
                confirmButtonText: '<i class="fas fa-check me-2"></i>OK',
                customClass: {
                    popup: 'swal-custom',
                    confirmButton: 'btn btn-primary'
                }
            });
            
            // Ricarica la tabella e le statistiche
            usersTable.ajax.reload(null, false);
            loadUserStatsFromTable();
        },
        error: function(xhr) {
            showLoading(false);
            
            let errorMessage = 'Si è verificato un errore durante la ' + actionText + '.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            Swal.fire({
                title: 'Errore!',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: '<i class="fas fa-times me-2"></i>Chiudi',
                customClass: {
                    popup: 'swal-custom',
                    confirmButton: 'btn btn-danger'
                }
            });
        }
    });
}

function loadUserStatsFromTable() {
    // Carica le statistiche basandosi sui dati della DataTable
    if (usersTable && usersTable.ajax.json()) {
        const tableData = usersTable.ajax.json();
        updateStatsDisplay({
            total: tableData.recordsTotal || 0,
            active: calculateActiveUsers(),
            disabled: calculateDisabledUsers(),
            admin: calculateAdminUsers()
        });
    } else {
        // Fallback con dati di default
        updateStatsDisplay({
            total: '0',
            active: '0',
            disabled: '0',
            admin: '0'
        });
    }
}

function calculateActiveUsers() {
    // Fix: calcola utenti attivi dal HTML della colonna
    let activeCount = 0;
    usersTable.rows({search: 'applied'}).every(function() {
        const data = this.data();
        // Controlla se il badge contiene "Attivo"
        if (data.abilitato && data.abilitato.includes('Attivo')) {
            activeCount++;
        }
    });
    return activeCount;
}

function calculateDisabledUsers() {
    // Fix: calcola utenti disabilitati dal HTML della colonna
    let disabledCount = 0;
    usersTable.rows({search: 'applied'}).every(function() {
        const data = this.data();
        // Controlla se il badge contiene "Disabilitato"
        if (data.abilitato && data.abilitato.includes('Disabilitato')) {
            disabledCount++;
        }
    });
    return disabledCount;
}

function calculateAdminUsers() {
    // Fix: calcola amministratori dal tuo sistema di ruoli
    let adminCount = 0;
    usersTable.rows({search: 'applied'}).every(function() {
        const data = this.data();
        // Controlla se il ruolo contiene "Amministratore"
        if (data.roles && (
            data.roles.includes('Amministratore') || 
            data.roles.includes('admin')
        )) {
            adminCount++;
        }
    });
    return adminCount;
}

function updateUserStats() {
    // Aggiorna le statistiche basandosi sui dati visibili nella tabella
    setTimeout(() => {
        loadUserStatsFromTable();
    }, 500);
}

function updateStatsDisplay(stats) {
    $('#totalUsers').text(stats.total);
    $('#activeUsers').text(stats.active);
    $('#disabledUsers').text(stats.disabled);
    $('#adminUsers').text(stats.admin);
    
    // Animazione sui numeri
    $('.stat-number').each(function() {
        $(this).addClass('animate__animated animate__pulse');
        setTimeout(() => {
            $(this).removeClass('animate__animated animate__pulse');
        }, 1000);
    });
}

function showLoading(show) {
    if (show) {
        $('#loadingOverlay').removeClass('d-none');
    } else {
        $('#loadingOverlay').addClass('d-none');
    }
}

function refreshTable() {
    showLoading(true);
    usersTable.ajax.reload(function() {
        showLoading(false);
        loadUserStatsFromTable();
        
        // Notifica di aggiornamento
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {
                popup: 'swal-toast'
            }
        });
        
        Toast.fire({
            icon: 'success',
            title: 'Tabella aggiornata!'
        });
    });
}

function exportUsers() {
    // Implementa l'esportazione degli utenti
    Swal.fire({
        title: 'Esporta Utenti',
        text: 'Funzionalità di export in sviluppo',
        icon: 'info',
        confirmButtonColor: '#84bc00',
        confirmButtonText: '<i class="fas fa-check me-2"></i>OK',
        customClass: {
            popup: 'swal-custom',
            confirmButton: 'btn btn-primary'
        }
    });
}

// Utility functions
function formatNumber(num) {
    return new Intl.NumberFormat('it-IT').format(num);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Event listeners globali
$(window).on('resize', debounce(function() {
    if (usersTable) {
        usersTable.columns.adjust().responsive.recalc();
    }
}, 250));

// Inizializza tutto quando il documento è pronto
$(document).ready(function() {
    // Personalizza SweetAlert2
    const swalCustom = document.createElement('style');
    swalCustom.innerHTML = `
        .swal-custom {
            border-radius: 15px !important;
        }
        .swal-toast {
            border-radius: 10px !important;
        }
        .swal2-popup.swal-custom .swal2-title {
            color: var(--youPrice-dark) !important;
        }
        .swal2-popup.swal-custom .swal2-content {
            color: #6c757d !important;
        }
    `;
    document.head.appendChild(swalCustom);
});
</script>