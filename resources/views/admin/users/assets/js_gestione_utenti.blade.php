<script>
    let usersTable;

    $(document).ready(function() {
        initializeUsersTable();
        initializeFilters();
        initializeTooltips();
        loadUserStatsFromTable();
    });

    function initializeUsersTable() {
        usersTable = $('#users-table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('users.data') }}',
                beforeSend: function() {
                    showLoading(true);
                },
                complete: function() {
                    showLoading(false);
                    updateUserStats();
                }
            },
            columns: [{
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
                    data: 'email_verified',
                    name: 'email_verified',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    data: 'abilitato',
                    name: 'abilitato',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return data;
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
            order: [
                [0, 'asc']
            ],
            drawCallback: function() {
                initializeTooltips();
                updateActionButtons();
            }
        });
    }

    function initializeFilters() {
        $('#globalSearch').on('keyup', function() {
            usersTable.search(this.value).draw();
        });

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

        $('#statusFilter').on('change', function() {
            const value = this.value;
            if (value === '') {
                usersTable.column(5).search('').draw();
            } else if (value === '1') {
                usersTable.column(5).search('Attivo').draw();
            } else if (value === '0') {
                usersTable.column(5).search('Disabilitato').draw();
            }
        });
    }

    function initializeTooltips() {
        $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
    }

    function updateActionButtons() {
        $('.lockButton, .unlockButton, .resendVerificationButton, .forceVerifyButton').off('click');
        attachActionEvents();
    }

    function attachActionEvents() {
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
                cancelButtonText: '<i class="fas fa-times me-2"></i>Annulla'
            }).then((result) => {
                if (result.isConfirmed) {
                    performUserAction('lock', userId, userName);
                }
            });
        });

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
                cancelButtonText: '<i class="fas fa-times me-2"></i>Annulla'
            }).then((result) => {
                if (result.isConfirmed) {
                    performUserAction('unlock', userId, userName);
                }
            });
        });

        $(document).on('click', '.resendVerificationButton', function() {
            const userId = $(this).data('id');
            const userName = $(this).data('name') || 'questo utente';

            Swal.fire({
                title: 'Reinvia Email di Verifica',
                text: `Vuoi reinviare l'email di verifica a ${userName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-envelope me-2"></i>Invia',
                cancelButtonText: '<i class="fas fa-times me-2"></i>Annulla'
            }).then((result) => {
                if (result.isConfirmed) {
                    performUserAction('resend-verification', userId, userName);
                }
            });
        });

        $(document).on('click', '.forceVerifyButton', function() {
            const userId = $(this).data('id');
            const userName = $(this).data('name') || 'questo utente';

            Swal.fire({
                title: 'Forza Verifica Email',
                text: `Vuoi forzare la verifica email per ${userName}? L'utente sarà marcato come verificato.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#84bc00',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check-double me-2"></i>Verifica',
                cancelButtonText: '<i class="fas fa-times me-2"></i>Annulla'
            }).then((result) => {
                if (result.isConfirmed) {
                    performUserAction('force-verify', userId, userName);
                }
            });
        });
    }

    function performUserAction(action, userId, userName) {
        const routes = {
            'lock': '{{ route('users.lock') }}',
            'unlock': '{{ route('users.unlock') }}',
            'resend-verification': '{{ route('users.resend-verification') }}',
            'force-verify': '{{ route('users.force-verify') }}'
        };

        const messages = {
            'lock': {
                action: 'disabilitazione',
                success: 'disabilitato'
            },
            'unlock': {
                action: 'abilitazione',
                success: 'abilitato'
            },
            'resend-verification': {
                action: 'invio email',
                success: 'inviata l\'email di verifica'
            },
            'force-verify': {
                action: 'verifica email',
                success: 'verificata l\'email'
            }
        };

        const actionUrl = routes[action];
        const msg = messages[action];

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
                    text: `${userName}: ${msg.success} con successo.`,
                    icon: 'success',
                    confirmButtonColor: '#84bc00',
                    confirmButtonText: '<i class="fas fa-check me-2"></i>OK'
                });

                usersTable.ajax.reload(null, false);
                loadUserStatsFromTable();
            },
            error: function(xhr) {
                showLoading(false);

                let errorMessage = 'Si è verificato un errore durante la ' + msg.action + '.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.info) {
                    errorMessage = xhr.responseJSON.info;
                }

                Swal.fire({
                    title: 'Errore!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: '<i class="fas fa-times me-2"></i>Chiudi'
                });
            }
        });
    }

    function loadUserStatsFromTable() {
        if (usersTable && usersTable.ajax.json()) {
            const tableData = usersTable.ajax.json();
            updateStatsDisplay({
                total: tableData.recordsTotal || 0,
                active: calculateActiveUsers(),
                disabled: calculateDisabledUsers(),
                admin: calculateAdminUsers()
            });
        } else {
            updateStatsDisplay({
                total: '0',
                active: '0',
                disabled: '0',
                admin: '0'
            });
        }
    }

    function calculateActiveUsers() {
        let activeCount = 0;
        usersTable.rows({
            search: 'applied'
        }).every(function() {
            const data = this.data();
            if (data.abilitato && data.abilitato.includes('Attivo')) {
                activeCount++;
            }
        });
        return activeCount;
    }

    function calculateDisabledUsers() {
        let disabledCount = 0;
        usersTable.rows({
            search: 'applied'
        }).every(function() {
            const data = this.data();
            if (data.abilitato && data.abilitato.includes('Disabilitato')) {
                disabledCount++;
            }
        });
        return disabledCount;
    }

    function calculateAdminUsers() {
        let adminCount = 0;
        usersTable.rows({
            search: 'applied'
        }).every(function() {
            const data = this.data();
            if (data.roles && (data.roles.includes('Amministratore') || data.roles.includes('admin'))) {
                adminCount++;
            }
        });
        return adminCount;
    }

    function updateUserStats() {
        setTimeout(() => {
            loadUserStatsFromTable();
        }, 500);
    }

    function updateStatsDisplay(stats) {
        $('#totalUsers').text(stats.total);
        $('#activeUsers').text(stats.active);
        $('#disabledUsers').text(stats.disabled);
        $('#adminUsers').text(stats.admin);

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

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });

            Toast.fire({
                icon: 'success',
                title: 'Tabella aggiornata!'
            });
        });
    }

    function exportUsers() {
        Swal.fire({
            title: 'Esporta Utenti',
            text: 'Funzionalità di export in sviluppo',
            icon: 'info',
            confirmButtonColor: '#84bc00',
            confirmButtonText: '<i class="fas fa-check me-2"></i>OK'
        });
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

    $(window).on('resize', debounce(function() {
        if (usersTable) {
            usersTable.columns.adjust().responsive.recalc();
        }
    }, 250));
</script>
