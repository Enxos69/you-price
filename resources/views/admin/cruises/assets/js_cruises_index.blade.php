<script>
    // VERSIONE FINALE - Tutte le funzionalità complete e raffinate

    let cruisesTable;
    let selectedCruises = [];

    $(document).ready(function() {
        console.log('🚀 Inizializzazione Gestione Crociere - Versione Finale');

        // Inizializzazione con delay per stabilità
        setTimeout(function() {
            initializeCruisesTable();
            initializeFilters();
            initializeSweetAlert();
            loadCruiseStats();
        }, 500);
    });

    // ✅ CONFIGURAZIONE DATATABLE CON LARGHEZZE FORZATE E FISSE
    function initializeCruisesTable() {
        console.log('Inizializzazione DataTable con larghezze fisse...');

        // Distruggi DataTable esistente se presente
        if ($.fn.DataTable.isDataTable('#cruises-table')) {
            $('#cruises-table').DataTable().destroy();
        }

        const dataUrl = '{{ route('cruises.data') }}';

        cruisesTable = $('#cruises-table').DataTable({
            // ✅ DISABILITA RESPONSIVE E AUTO-WIDTH
            responsive: false,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferRender: true,

            // ✅ FORZA SCROLL ORIZZONTALE
            scrollX: true,
            scrollCollapse: true,

            ajax: {
                url: dataUrl,
                type: 'GET',
                beforeSend: function() {
                    showLoading(true);
                },
                complete: function() {
                    showLoading(false);
                },
                error: function(xhr, error, code) {
                    console.error('Errore AJAX DataTable:', error);
                    showToast('Errore nel caricamento dei dati. Riprova più tardi.', 'error');
                    showLoading(false);
                }
            },

            // ✅ LARGHEZZE FORZATE NELLE COLONNE
            columnDefs: [{
                    width: "3%",
                    targets: 0
                }, // Checkbox
                {
                    width: "18%",
                    targets: 1
                }, // Nave
                {
                    width: "25%",
                    targets: 2
                }, // Crociera
                {
                    width: "15%",
                    targets: 3
                }, // Compagnia
                {
                    width: "8%",
                    targets: 4
                }, // Durata
                {
                    width: "12%",
                    targets: 5
                }, // Partenza
                {
                    width: "10%",
                    targets: 6
                }, // Prezzo
                {
                    width: "9%",
                    targets: 7
                } // Azioni
            ],

            columns: [
                // ✅ CHECKBOX
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: "text-center",
                    render: function(data, type, row) {
                        return `<input type="checkbox" class="form-check-input cruise-checkbox" value="${row.id}">`;
                    }
                },
                // ✅ NAVE
                {
                    data: 'ship',
                    name: 'ship',
                    className: "text-left",
                    render: function(data, type, row) {
                        return `<div class="fw-bold">${data || 'N/D'}</div>`;
                    }
                },
                // ✅ CROCIERA
                {
                    data: 'cruise',
                    name: 'cruise',
                    className: "text-left",
                    render: function(data, type, row) {
                        if (data && data.length > 35) {
                            return `<span title="${data}" class="text-truncate d-block">${data.substring(0, 35)}...</span>`;
                        }
                        return data || 'N/D';
                    }
                },
                // ✅ COMPAGNIA
                {
                    data: 'line',
                    name: 'line',
                    className: "text-center"
                },
                // ✅ DURATA
                {
                    data: 'formatted_duration',
                    name: 'duration',
                    className: "text-center"
                },
                // ✅ DATA PARTENZA
                {
                    data: 'itinerary',
                    name: 'partenza',
                    className: "text-center"
                },
                // ✅ PREZZO INTERIOR
                {
                    data: 'interior',
                    name: 'interior',
                    className: "text-right"
                },
                // ✅ AZIONI
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: "text-center"
                }
            ],

            // ✅ IMPOSTAZIONI PERFORMANCE
            pageLength: 15,
            lengthMenu: [
                [10, 15, 25, 50],
                [10, 15, 25, 50]
            ],

            language: {
                processing: "⏳ Caricamento...",
                search: "",
                searchPlaceholder: "Cerca crociere...",
                lengthMenu: "Mostra _MENU_ crociere",
                info: "_START_-_END_ di _TOTAL_",
                infoEmpty: "Nessuna crociera",
                infoFiltered: "(filtrate da _MAX_ totali)",
                zeroRecords: "Nessun risultato trovato",
                emptyTable: "Nessuna crociera presente",
                paginate: {
                    previous: "‹",
                    next: "›"
                }
            },

            // ✅ DOM SEMPLIFICATO
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
            order: [
                [1, 'asc']
            ],

            // ✅ CALLBACK OTTIMIZZATO
            drawCallback: function() {
                setTimeout(() => {
                    // Forza larghezze dopo il rendering
                    fixColumnWidths();

                    // Inizializza tooltip
                    $('[title]').tooltip({
                        delay: {
                            show: 500,
                            hide: 100
                        }
                    });

                    // Attach eventi
                    $('.delete-cruise').off('click').on('click', function() {
                        const id = $(this).data('id');
                        const name = $(this).data('name');
                        confirmDelete(id, name);
                    });

                    updateSelectedCruises();
                }, 10);
            },

            // ✅ INIZIALIZZAZIONE COMPLETATA
            initComplete: function() {
                console.log('✅ DataTable inizializzata con larghezze fisse');
                fixColumnWidths();
            }
        });

        window.cruisesTable = cruisesTable;
    }

    // ✅ FUNZIONE PER FORZARE LE LARGHEZZE DOPO IL RENDERING
    function fixColumnWidths() {
        const table = $('#cruises-table');
        const ths = table.find('thead th');
        const widths = ['3%', '18%', '25%', '15%', '8%', '12%', '10%', '9%'];

        ths.each(function(index) {
            if (widths[index]) {
                $(this).css({
                    'width': widths[index],
                    'min-width': widths[index],
                    'max-width': widths[index]
                });
            }
        });

        // Forza anche le celle del body
        table.find('tbody td').each(function(index) {
            const colIndex = index % 8;
            if (widths[colIndex]) {
                $(this).css({
                    'width': widths[colIndex],
                    'min-width': widths[colIndex],
                    'max-width': widths[colIndex]
                });
            }
        });
    }

    // ✅ FUNZIONE PER CONFERMA ELIMINAZIONE
    function confirmDelete(cruiseId, cruiseName) {
        Swal.fire({
            title: 'Conferma eliminazione',
            text: `Sei sicuro di voler eliminare "${cruiseName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-2"></i>Elimina',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteCruise(cruiseId);
            }
        });
    }

    // ✅ FUNZIONE PER ELIMINAZIONE AJAX
    function deleteCruise(cruiseId) {
        $.ajax({
            url: `/admin/cruises/${cruiseId}`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.response) {
                    showToast(response.message || 'Crociera eliminata con successo', 'success');
                    cruisesTable.ajax.reload(null, false); // Ricarica senza resettare pagina
                } else {
                    showToast(response.message || 'Errore durante l\'eliminazione', 'error');
                }
            },
            error: function(xhr) {
                console.error('Errore eliminazione:', xhr);
                showToast('Errore durante l\'eliminazione', 'error');
            }
        });
    }

    // ✅ OTTIMIZZAZIONE CHECKBOX CON THROTTLING
    function updateSelectedCruises() {
        clearTimeout(window.checkboxTimeout);
        window.checkboxTimeout = setTimeout(() => {
            selectedCruises = $('.cruise-checkbox:checked').map(function() {
                return this.value;
            }).get();

            const count = selectedCruises.length;
            $('#selected-count').text(count);
            $('#bulk-actions').toggle(count > 0);

            // Aggiorna checkbox "Seleziona tutto"
            const totalCheckboxes = $('.cruise-checkbox').length;
            const selectAllCheckbox = $('#selectAll');

            if (count === 0) {
                selectAllCheckbox.prop('indeterminate', false).prop('checked', false);
            } else if (count === totalCheckboxes) {
                selectAllCheckbox.prop('indeterminate', false).prop('checked', true);
            } else {
                selectAllCheckbox.prop('indeterminate', true).prop('checked', false);
            }
        }, 100);
    }

    function initializeFilters() {
        console.log('Inizializzazione filtri...');

        // Search globale personalizzata
        $('#globalSearch').on('keyup', debounce(function() {
            if (cruisesTable) {
                cruisesTable.search(this.value).draw();
            }
        }, 300));

        // Filtro per compagnia
        $('#companyFilter').on('change', function() {
            if (cruisesTable) {
                const value = this.value;
                cruisesTable.column(3).search(value).draw();
            }
        });

        // Filtro per disponibilità
        $('#availabilityFilter').on('change', function() {
            if (cruisesTable) {
                const value = this.value;
                if (value === 'available') {
                    cruisesTable.columns([6, 7, 8]).search('€', true, false).draw();
                } else if (value === 'future') {
                    cruisesTable.search('').draw();
                } else {
                    cruisesTable.search('').columns().search('').draw();
                }
            }
        });

        // Checkbox "Seleziona tutto"
        $('#selectAll').on('change', function() {
            const isChecked = this.checked;
            $('.cruise-checkbox:visible').prop('checked', isChecked);
            updateSelectedCruises();
        });

        // Gestione checkbox individuali
        $(document).on('change', '.cruise-checkbox', function() {
            updateSelectedCruises();
        });

        console.log('✅ Filtri inizializzati');
    }

    function updateCheckboxes() {
        const totalCheckboxes = $('.cruise-checkbox:visible').length;
        const checkedCheckboxes = $('.cruise-checkbox:visible:checked').length;

        if ($('#selectAll').length > 0) {
            $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
            $('#selectAll').prop('checked', checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0);
        }
    }

    function updateSelectedCruises() {
        selectedCruises = [];
        $('.cruise-checkbox:checked').each(function() {
            selectedCruises.push({
                id: $(this).val(),
                name: $(this).data('cruise')
            });
        });

        if ($('#bulkDeleteBtn').length > 0) {
            $('#bulkDeleteBtn').prop('disabled', selectedCruises.length === 0);
        }

        updateCheckboxes();
    }

    function attachActionEvents() {
        // Rimuovi event listeners esistenti per evitare duplicati
        $('.deleteButton').off('click');

        // Eventi per i pulsanti "Elimina"
        $(document).on('click', '.deleteButton', function() {
            const cruiseId = $(this).data('id');
            const shipName = $(this).data('ship');
            const cruiseName = $(this).data('cruise');

            showDeleteConfirmation(cruiseId, shipName, cruiseName);
        });
    }

    function showDeleteConfirmation(cruiseId, shipName, cruiseName) {
        Swal.fire({
            title: 'Conferma Eliminazione',
            html: `Sei sicuro di voler eliminare la crociera:<br><br>
               <strong>${shipName}</strong><br>
               <em>${cruiseName}</em>`,
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
                deleteCruise(cruiseId, shipName);
            }
        });
    }


    function bulkDelete() {
        if (selectedCruises.length === 0) {
            showToast('Nessuna crociera selezionata', 'warning');
            return;
        }

        const cruiseNames = selectedCruises.map(c => c.name).slice(0, 3).join('<br>');
        const moreText = selectedCruises.length > 3 ? `<br><small>...e altre ${selectedCruises.length - 3}</small>` :
            '';

        Swal.fire({
            title: 'Conferma Eliminazione Multipla',
            html: `Sei sicuro di voler eliminare ${selectedCruises.length} crociere?<br><br>
               <small>${cruiseNames}${moreText}</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-2"></i>Elimina Tutte',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                performBulkDelete();
            }
        });
    }

    function performBulkDelete() {
        const ids = selectedCruises.map(c => c.id);

        Swal.fire({
            title: 'Eliminazione in corso...',
            text: `Eliminando ${ids.length} crociere...`,
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route('cruises.bulk-delete') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ids: ids
            },
            success: function(response) {
                if (response.response) {
                    Swal.fire({
                        title: 'Eliminazione Completata!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#84bc00'
                    });

                    // Reset selezioni
                    selectedCruises = [];
                    $('#selectAll').prop('checked', false);
                    $('#bulkDeleteBtn').prop('disabled', true);

                    // Ricarica tabella e statistiche
                    cruisesTable.ajax.reload(null, false);
                    loadCruiseStats();
                } else {
                    Swal.fire({
                        title: 'Errore!',
                        text: response.message ||
                            'Si è verificato un errore durante l\'eliminazione.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Errore!',
                    text: 'Si è verificato un errore durante l\'eliminazione multipla.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    }

    function loadCruiseStats() {
        $.ajax({
            url: '{{ route('cruises.stats') }}',
            type: 'GET',
            success: function(stats) {
                updateStatsDisplay(stats);
            },
            error: function() {
                console.log('Errore caricamento statistiche, uso dati tabella');
                updateCruiseStats();
            }
        });
    }

    function updateCruiseStats() {
        setTimeout(() => {
            if (cruisesTable && cruisesTable.ajax.json()) {
                const tableData = cruisesTable.ajax.json();
                updateStatsDisplay({
                    total_cruises: tableData.recordsTotal || 0,
                    available_cruises: calculateAvailableCruises(),
                    future_cruises: calculateFutureCruises(),
                    companies: calculateUniqueCompanies()
                });
            }
        }, 500);
    }

    function calculateAvailableCruises() {
        let count = 0;
        if (cruisesTable) {
            cruisesTable.rows({
                search: 'applied'
            }).every(function() {
                const data = this.data();
                if ((data.interior && data.interior.includes('€')) ||
                    (data.oceanview && data.oceanview.includes('€')) ||
                    (data.balcony && data.balcony.includes('€'))) {
                    count++;
                }
            });
        }
        return count;
    }

    function calculateFutureCruises() {
        let count = 0;
        if (cruisesTable) {
            cruisesTable.rows({
                search: 'applied'
            }).every(function() {
                const data = this.data();
                if (data.itinerary && (data.itinerary.includes('2024') || data.itinerary.includes('2025'))) {
                    count++;
                }
            });
        }
        return count;
    }

    function calculateUniqueCompanies() {
        const companies = new Set();
        if (cruisesTable) {
            cruisesTable.rows({
                search: 'applied'
            }).every(function() {
                const data = this.data();
                if (data.line) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.line;
                    const companyText = tempDiv.textContent || tempDiv.innerText || '';
                    if (companyText.trim()) {
                        companies.add(companyText.trim());
                    }
                }
            });
        }
        return companies.size;
    }

    function updateStatsDisplay(stats) {
        $('#totalCruises').text(formatNumber(stats.total_cruises || 0));
        $('#availableCruises').text(formatNumber(stats.available_cruises || 0));
        $('#futureCruises').text(formatNumber(stats.future_cruises || 0));
        $('#totalCompanies').text(formatNumber(stats.companies || 0));

        // Animazione sui numeri
        $('.stat-number').addClass('animate__animated animate__pulse');
        setTimeout(() => {
            $('.stat-number').removeClass('animate__animated animate__pulse');
        }, 1000);
    }

    function initializeTooltips() {
        $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
    }

    function initializeSweetAlert() {
        // Personalizza SweetAlert2
        const swalCustom = document.createElement('style');
        swalCustom.innerHTML = `
        .swal-custom {
            border-radius: 15px !important;
        }
        .swal2-popup.swal-custom .swal2-title {
            color: var(--youPrice-dark, #2C3E50) !important;
        }
        .swal2-popup.swal-custom .swal2-content {
            color: #6c757d !important;
        }
    `;
        document.head.appendChild(swalCustom);
    }

    function showLoading(show) {
        const overlay = $('#loadingOverlay');
        if (show && overlay.length > 0) {
            overlay.removeClass('d-none');
        } else if (overlay.length > 0) {
            overlay.addClass('d-none');
        }
    }

    function showToast(message, type) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: type,
            title: message
        });
    }

    function refreshTable() {
        if (cruisesTable) {
            showLoading(true);
            cruisesTable.ajax.reload(function() {
                showLoading(false);
                loadCruiseStats();

                // Reset selezioni
                selectedCruises = [];
                $('#selectAll').prop('checked', false);
                $('#bulkDeleteBtn').prop('disabled', true);

                showToast('Tabella aggiornata!', 'success');
            });
        }
    }

    function exportCruises() {
        Swal.fire({
            title: 'Esporta Crociere',
            text: 'Vuoi esportare tutte le crociere in un file CSV?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#84bc00',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-download me-2"></i>Esporta',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route('cruises.export') }}';
                showToast('Export avviato! Il download inizierà a breve.', 'success');
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
        if (cruisesTable) {
            cruisesTable.columns.adjust().responsive.recalc();
        }
    }, 250));

    // Gestione tasti di scelta rapida
    $(document).on('keydown', function(e) {
        // Ctrl + N per nuova crociera
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            window.location.href = '{{ route('cruises.create') }}';
        }

        // Ctrl + R per aggiornare tabella
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            refreshTable();
        }

        // ESC per deselezionare tutto
        if (e.key === 'Escape') {
            $('.cruise-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false);
            updateSelectedCruises();
        }
    });

    // Gestione errori AJAX globali
    $(document).ajaxError(function(event, xhr, settings, thrownError) {
        if (xhr.status === 419) {
            showToast('Sessione scaduta. Ricarica la pagina.', 'warning');
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else if (xhr.status === 403) {
            showToast('Accesso negato. Controlla i tuoi permessi.', 'error');
        } else if (xhr.status >= 500) {
            showToast('Errore del server. Riprova più tardi.', 'error');
        }
    });

    // Esporta funzioni globalmente
    window.refreshTable = refreshTable;
    window.exportCruises = exportCruises;
    window.bulkDelete = bulkDelete;
    window.cruisesTable = cruisesTable;

    console.log('✅ Script gestione crociere caricato completamente');
</script>
