<script>
    // Controllo sicurezza per verificare se i dati sono disponibili
    if (typeof window.importResultsData === 'undefined') {
        window.importResultsData = {
            hasResults: false,
            importDataUrl: '',
            csrf: '',
            errors: []
        };
        console.warn('importResultsData non inizializzato, usando valori di default');
    }

    $(document).ready(function() {
        // Inizializza la DataTable se ci sono risultati
        if (window.importResultsData.hasResults) {
            initializeDataTable();
        }

        // Inizializza i filtri
        initializeFilters();

        // Inizializza ricerca errori quando il modal si apre
        $('#errorsModal').on('shown.bs.modal shown.modal', function() {
            initializeErrorSearch();
        });

        // Mostra loading iniziale
        showLoading(true);

        // Nasconde loading dopo che tutto è caricato
        setTimeout(function() {
            showLoading(false);
        }, 1000);

        // Animazioni di entrata
        animateElements();
    });

    function initializeDataTable() {
        const table = $('#cruisesTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: window.importResultsData.importDataUrl,
                type: 'GET',
                beforeSend: function() {
                    showLoading(true);
                },
                complete: function() {
                    showLoading(false);
                },
                error: function(xhr, error, code) {
                    console.error('Errore caricamento dati:', error);
                    showToast('Errore nel caricamento dei dati. Riprova più tardi.', 'error');
                    showLoading(false);
                }
            },
            columns: [{
                    data: 'id',
                    name: 'id',
                    render: function(data, type, row) {
                        return '<span class="badge bg-primary">' + data + '</span>';
                    }
                },
                {
                    data: 'ship',
                    name: 'ship',
                    render: function(data, type, row) {
                        return '<strong>' + (data || 'N/D') + '</strong>';
                    }
                },
                {
                    data: 'cruise',
                    name: 'cruise',
                    render: function(data, type, row) {
                        if (data && data.length > 25) {
                            return '<span title="' + data + '">' + data.substring(0, 25) + '...</span>';
                        }
                        return data || 'N/D';
                    }
                },
                {
                    data: 'line',
                    name: 'line',
                    render: function(data, type, row) {
                        var badgeClass = 'bg-secondary';
                        if (data && data.toLowerCase().includes('msc')) badgeClass = 'bg-info';
                        if (data && data.toLowerCase().includes('costa')) badgeClass = 'bg-success';
                        if (data && data.toLowerCase().includes('royal')) badgeClass = 'bg-warning';

                        return '<span class="badge ' + badgeClass + '">' + (data || 'N/D') + '</span>';
                    }
                },
                {
                    data: 'duration',
                    name: 'duration',
                    render: function(data, type, row) {
                        return data || 'N/D';
                    }
                },
                {
                    data: 'partenza',
                    name: 'partenza',
                    render: function(data, type, row) {
                        return data || 'N/D';
                    }
                },
                {
                    data: 'arrivo',
                    name: 'arrivo',
                    render: function(data, type, row) {
                        return data || 'N/D';
                    }
                },
                {
                    data: 'interior',
                    name: 'interior',
                    render: function(data, type, row) {
                        if (data && data !== '-') {
                            return '<span class="text-success fw-bold">' + data + '</span>';
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },
                {
                    data: 'oceanview',
                    name: 'oceanview',
                    render: function(data, type, row) {
                        if (data && data !== '-') {
                            return '<span class="text-info fw-bold">' + data + '</span>';
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },
                {
                    data: 'balcony',
                    name: 'balcony',
                    render: function(data, type, row) {
                        if (data && data !== '-') {
                            return '<span class="text-primary fw-bold">' + data + '</span>';
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },
                {
                    data: 'details',
                    name: 'details',
                    render: function(data, type, row) {
                        if (!data || data === '-') {
                            return '<span class="text-muted">-</span>';
                        }

                        // Aggiungi badge per tipo di pacchetto
                        var badgeClass = 'bg-secondary';
                        var displayText = data;

                        if (data.toLowerCase().includes('drinks') && data.toLowerCase().includes(
                            'wifi')) {
                            badgeClass = 'bg-success';
                            displayText = 'Premium (Drinks + WiFi)';
                        } else if (data.toLowerCase().includes('escape to sea')) {
                            badgeClass = 'bg-warning';
                            displayText = 'Basic (Escape to Sea)';
                        } else if (data.toLowerCase().includes('brochure')) {
                            badgeClass = 'bg-info';
                            displayText = 'Standard (Brochure)';
                        }

                        if (data.length > 30) {
                            return '<span class="badge ' + badgeClass + '" title="' + data + '">' +
                                displayText + '</span>';
                        }
                        return '<span class="badge ' + badgeClass + '">' + displayText + '</span>';
                    }
                }
            ],
            language: {
                processing: "Elaborazione...",
                search: "",
                searchPlaceholder: "Cerca...",
                lengthMenu: "Mostra _MENU_ elementi",
                info: "Visualizzazione da _START_ a _END_ di _TOTAL_ elementi",
                infoEmpty: "Nessun elemento da visualizzare",
                infoFiltered: "(filtrati da _MAX_ elementi totali)",
                loadingRecords: "Caricamento...",
                zeroRecords: "Nessun elemento trovato",
                emptyTable: "Nessun dato presente nella tabella",
                paginate: {
                    first: "Primo",
                    previous: "Precedente",
                    next: "Successivo",
                    last: "Ultimo"
                }
            },
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "Tutti"]
            ],
            responsive: true,
            order: [
                [0, 'desc']
            ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            drawCallback: function() {
                $('[title]').tooltip();
            }
        });

        // Salva riferimento alla tabella per i filtri
        window.cruisesTable = table;
    }

    function initializeFilters() {
        // Filtro ricerca rapida
        $('#quickSearch').on('keyup', function() {
            if (window.cruisesTable) {
                window.cruisesTable.search(this.value).draw();
            }
        });

        // Filtro per compagnia
        $('#lineFilter').on('change', function() {
            if (window.cruisesTable) {
                const value = this.value;
                window.cruisesTable.column(3).search(value).draw();
            }
        });

        // Filtro per mese
        $('#monthFilter').on('change', function() {
            if (window.cruisesTable) {
                const month = this.value;
                if (month) {
                    // Cerca il pattern del mese nelle date (formato italiano dd/mm/yyyy)
                    window.cruisesTable.column(5).search('\\/' + month + '\\/', true, false).draw();
                } else {
                    window.cruisesTable.column(5).search('').draw();
                }
            }
        });
    }

    function resetFilters() {
        // Reset dei filtri
        $('#quickSearch').val('');
        $('#lineFilter').val('');
        $('#monthFilter').val('');

        // Reset della tabella
        if (window.cruisesTable) {
            window.cruisesTable.search('').columns().search('').draw();
        }

        // Mostra notifica
        showToast('Filtri resettati', 'success');
    }

    function exportResults() {
        // Placeholder per funzionalità di export
        showToast('Funzionalità di export in sviluppo', 'info');

        // Qui potresti implementare l'export reale
        // window.open('/admin/export-results', '_blank');
    }

    function showLoading(show) {
        const overlay = $('#loadingOverlay');
        if (show) {
            overlay.removeClass('d-none');
        } else {
            overlay.addClass('d-none');
        }
    }

    function showToast(message, type) {
        type = type || 'info';

        const toastContainer = getOrCreateToastContainer();
        const toastId = 'toast-' + Date.now();

        var bgClass = 'bg-primary';
        var iconClass = 'fa-info-circle';

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

        const toastHtml = '<div id="' + toastId + '" class="toast align-items-center text-white ' + bgClass +
            ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
            '<div class="d-flex">' +
            '<div class="toast-body">' +
            '<i class="fas ' + iconClass + ' me-2"></i>' +
            message +
            '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
            '</div>' +
            '</div>';

        toastContainer.append(toastHtml);

        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
        });

        toast.show();

        // Rimuovi il toast dal DOM dopo che si nasconde
        toastElement.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }

    function getOrCreateToastContainer() {
        var container = $('#toast-container');
        if (container.length === 0) {
            container = $(
                '<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11;"></div>'
            );
            $('body').append(container);
        }
        return container;
    }

    // === FUNZIONI PER LA GESTIONE DEGLI ERRORI ===

    /**
     * Mostra il modal degli errori di importazione
     */
    function showErrorsModal() {
        try {
            // Prova con Bootstrap 5
            const modalElement = document.getElementById('errorsModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                console.error('Modal errorsModal non trovato nel DOM');
                showToast('Impossibile aprire la finestra degli errori', 'error');
            }
        } catch (e) {
            try {
                // Fallback per Bootstrap 4
                $('#errorsModal').modal('show');
            } catch (e2) {
                console.error('Errore nell\'apertura del modal:', e2);
                showToast('Errore nell\'apertura della finestra degli errori', 'error');
            }
        }
    }

    /**
     * Chiude il modal degli errori
     */
    function closeErrorsModal() {
        try {
            const modalElement = document.getElementById('errorsModal');
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                } else {
                    // Se non c'è un'istanza, nascondi manualmente
                    modalElement.style.display = 'none';
                    modalElement.classList.remove('show');
                    // Rimuovi backdrop se presente
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
            }
        } catch (e) {
            // Fallback jQuery
            $('#errorsModal').modal('hide');
        }
    }

    /**
     * Inizializza la ricerca negli errori
     */
    function initializeErrorSearch() {
        $('#errorSearch').off('keyup').on('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            $('.error-item').each(function() {
                const errorText = $(this).data('error-text') || '';
                const errorContent = $(this).text().toLowerCase();
                const isVisible = errorText.includes(searchTerm) ||
                    errorContent.includes(searchTerm) ||
                    searchTerm === '';
                $(this).toggle(isVisible);
            });

            // Mostra conteggio risultati
            const visibleErrors = $('.error-item:visible').length;
            const totalErrors = $('.error-item').length;

            if (searchTerm && visibleErrors !== totalErrors) {
                showToast(`Trovati ${visibleErrors} di ${totalErrors} errori`, 'info');
            }
        });
    }

    /**
     * Debug errori - visualizza errori in console
     */
    function debugErrors() {
        console.log('=== DEBUG ERRORI IMPORTAZIONE ===');
        const errors = window.importResultsData.errors || [];
        console.log('Numero errori:', errors.length);

        errors.forEach(function(error, index) {
            console.log('--- Errore #' + (index + 1) + ' ---');
            console.log('Riga:', error.line || 'N/D');
            console.log('Messaggio:', error.error || 'N/D');
            console.log('Record:', error.record || {});
            console.log('------------------------');
        });

        showToast('Controlla la console per i dettagli degli errori', 'info');
        return errors;
    }

    /**
     * Esporta gli errori in un file CSV
     */
    function exportErrors() {
        try {
            const errors = window.importResultsData.errors || [];

            if (errors.length === 0) {
                showToast('Nessun errore da esportare', 'warning');
                return;
            }

            // Crea CSV degli errori
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Errore #,Riga,Messaggio,Dati Record\n";

            errors.forEach(function(error, index) {
                const row = [
                    index + 1,
                    error.line || 'N/D',
                    '"' + (error.error || 'N/D').replace(/"/g, '""') + '"',
                    '"' + JSON.stringify(error.record || {}).replace(/"/g, '""') + '"'
                ].join(',');
                csvContent += row + "\n";
            });

            // Download del file
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "errori_importazione_" + new Date().toISOString().slice(0, 10) + ".csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            showToast('File errori scaricato con successo', 'success');
        } catch (e) {
            console.error('Errore durante export:', e);
            showToast('Errore durante l\'export degli errori', 'error');
        }
    }

    // === FUNZIONI DI SUPPORTO ===

    /**
     * Animazioni di entrata per gli elementi
     */
    function animateElements() {
        // Anima le statistiche
        $('.stat-card').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(20px)'
            });

            setTimeout(function(element) {
                return function() {
                    $(element).css({
                        'transition': 'all 0.6s ease',
                        'opacity': '1',
                        'transform': 'translateY(0)'
                    });
                };
            }(this), index * 100);
        });

        // Anima le sezioni
        $('.filters-section, .actions-section, .table-section').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(20px)'
            });

            setTimeout(function(element) {
                return function() {
                    $(element).css({
                        'transition': 'all 0.6s ease',
                        'opacity': '1',
                        'transform': 'translateY(0)'
                    });
                };
            }(this), (index + 6) * 100);
        });
    }

    // Gestione responsive per la tabella
    $(window).on('resize', function() {
        if (window.cruisesTable) {
            window.cruisesTable.columns.adjust().responsive.recalc();
        }
    });

    // Gestione eventi per i bottoni del modal
    $(document).on('click', '[data-bs-dismiss="modal"]', function() {
        const modalId = $(this).closest('.modal').attr('id');
        if (modalId === 'errorsModal') {
            closeErrorsModal();
        }
    });

    // Assicurati che le funzioni siano globalmente accessibili
    window.showErrorsModal = showErrorsModal;
    window.closeErrorsModal = closeErrorsModal;
    window.debugErrors = debugErrors;
    window.exportErrors = exportErrors;
    window.resetFilters = resetFilters;
    window.exportResults = exportResults;
</script>
