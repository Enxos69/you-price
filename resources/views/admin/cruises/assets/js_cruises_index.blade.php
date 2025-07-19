<script>
    // ================== GESTIONE CROCIERE OTTIMIZZATA ==================
    // Versione ottimizzata per grosse moli di dati con performance migliorate

    class CruiseManager {
        constructor() {
            // Configurazioni performance
            this.config = {
                pageSize: 15,
                maxPageSize: 100,
                debounceDelay: 300,
                apiDelay: 100,
                virtualScrollThreshold: 1000,
                batchSize: 50
            };

            // Stato dell'applicazione
            this.state = {
                currentPage: 1,
                pageSize: this.config.pageSize,
                totalRecords: 0,
                filteredRecords: 0,
                sortColumn: 'ship',
                sortDirection: 'asc',
                searchTerm: '',
                filters: {
                    company: '',
                    price: '',
                    status: ''
                },
                selectedIds: new Set(),
                isLoading: false,
                data: [],
                isInitialized: false
            };

            // Cache e throttling
            this.cache = new Map();
            this.searchTimeout = null;
            this.filterTimeout = null;
            this.requestController = null;

            // Elementi DOM cachati
            this.elements = {};

            // Bind dei metodi
            this.handleSearch = this.debounce(this.handleSearch.bind(this), this.config.debounceDelay);
            this.handleFilter = this.debounce(this.handleFilter.bind(this), this.config.debounceDelay);
        }

        // ================== INIZIALIZZAZIONE ==================
        async init() {
            console.log('🚀 Inizializzazione CruiseManager ottimizzato...');

            try {
                this.cacheElements();
                this.attachEventListeners();
                this.initializeSearch();
                this.initializeFilters();
                this.initializePagination();
                this.initializeSorting();
                this.initializeCheckboxes();

                await this.loadData();
                await this.loadStats();

                this.state.isInitialized = true;
                console.log('✅ CruiseManager inizializzato con successo');
            } catch (error) {
                console.error('❌ Errore inizializzazione:', error);
                this.showError('Errore durante l\'inizializzazione');
            }
        }

        // Cachatura elementi DOM per performance
        cacheElements() {
            this.elements = {
                // Tabella
                table: document.getElementById('cruisesTable'),
                tableBody: document.getElementById('tableBody'),
                tableContainer: document.getElementById('tableContainer'),
                tableLoading: document.getElementById('tableLoading'),
                tableEmpty: document.getElementById('tableEmpty'),

                // Ricerca e filtri
                globalSearch: document.getElementById('globalSearch'),
                clearSearch: document.getElementById('clearSearch'),
                companyFilter: document.getElementById('companyFilter'),
                priceFilter: document.getElementById('priceFilter'),
                statusFilter: document.getElementById('statusFilter'),

                // Checkbox e azioni
                selectAll: document.getElementById('selectAll'),
                bulkActions: document.getElementById('bulkActions'),

                // Paginazione
                paginationInfo: document.getElementById('paginationInfo'),
                paginationButtons: document.getElementById('paginationButtons'),
                pageSize: document.getElementById('pageSize'),

                // Statistiche
                totalCruises: document.getElementById('totalCruises'),
                availableCruises: document.getElementById('availableCruises'),
                expiredCruises: document.getElementById('expiredCruises'),
                totalCompanies: document.getElementById('totalCompanies')
            };
        }

        // ================== EVENT LISTENERS ==================
        attachEventListeners() {
            // Ricerca globale
            if (this.elements.globalSearch) {
                this.elements.globalSearch.addEventListener('input', (e) => {
                    this.state.searchTerm = e.target.value.trim();
                    this.handleSearch();
                    this.toggleClearButton();
                });
            }

            // Pulsante clear ricerca
            if (this.elements.clearSearch) {
                this.elements.clearSearch.addEventListener('click', () => {
                    this.clearSearch();
                });
            }

            // Filtri
            ['companyFilter', 'priceFilter', 'statusFilter'].forEach(filterId => {
                const element = this.elements[filterId];
                if (element) {
                    element.addEventListener('change', (e) => {
                        const filterName = filterId.replace('Filter', '');
                        this.state.filters[filterName] = e.target.value;
                        this.state.currentPage = 1; // Reset alla prima pagina
                        this.handleFilter();
                    });
                }
            });

            // Page size
            if (this.elements.pageSize) {
                this.elements.pageSize.addEventListener('change', (e) => {
                    this.state.pageSize = parseInt(e.target.value);
                    this.state.currentPage = 1;
                    this.loadData();
                });
            }

            // Select all checkbox
            if (this.elements.selectAll) {
                this.elements.selectAll.addEventListener('change', (e) => {
                    this.handleSelectAll(e.target.checked);
                });
            }

            // Gestione tasti scorciatoia
            document.addEventListener('keydown', (e) => {
                this.handleKeyboardShortcuts(e);
            });

            // Resize window per responsive
            window.addEventListener('resize', this.debounce(() => {
                this.handleResize();
            }, 250));
        }

        // ================== CARICAMENTO DATI ==================
        async loadData() {
            if (this.state.isLoading) return;

            this.state.isLoading = true;
            this.showLoading(true);

            // Cancella richiesta precedente se in corso
            if (this.requestController) {
                this.requestController.abort();
            }
            this.requestController = new AbortController();

            try {
                const cacheKey = this.generateCacheKey();

                // Controlla cache
                if (this.cache.has(cacheKey)) {
                    const cachedData = this.cache.get(cacheKey);
                    this.renderData(cachedData);
                    this.state.isLoading = false;
                    this.showLoading(false);
                    return;
                }

                const params = new URLSearchParams({
                    draw: Date.now(), // Timestamp per evitare cache browser
                    start: (this.state.currentPage - 1) * this.state.pageSize,
                    length: this.state.pageSize,
                    'search[value]': this.state.searchTerm,
                    'order[0][column]': this.getColumnIndex(this.state.sortColumn),
                    'order[0][dir]': this.state.sortDirection,
                    company: this.state.filters.company,
                    price: this.state.filters.price,
                    status: this.state.filters.status
                });

                const response = await fetch(`{{ route('cruises.data') }}?${params}`, {
                    signal: this.requestController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();

                // Salva in cache (mantieni solo ultime 10 richieste)
                if (this.cache.size >= 10) {
                    const firstKey = this.cache.keys().next().value;
                    this.cache.delete(firstKey);
                }
                this.cache.set(cacheKey, data);

                this.renderData(data);

            } catch (error) {
                if (error.name === 'AbortError') {
                    console.log('Richiesta annullata');
                    return;
                }

                console.error('Errore caricamento dati:', error);
                this.showError('Errore nel caricamento dei dati');
                this.clearTable(); // Pulisci la tabella anche in caso di errore
                this.showEmptyState();
            } finally {
                this.state.isLoading = false;
                this.showLoading(false);
                this.requestController = null;
            }
        }

        // ================== RENDERING DATI ==================
        renderData(data) {
            if (!data || !data.data) {
                this.showEmptyState();
                this.clearTable(); // Pulisci la tabella
                return;
            }

            this.state.data = data.data;
            this.state.totalRecords = data.recordsTotal || 0;
            this.state.filteredRecords = data.recordsFiltered || 0;

            // Nascondi stati di loading/empty
            this.showLoading(false);
            this.showEmptyState(false);

            // Se non ci sono risultati, pulisci la tabella e mostra empty state
            if (data.data.length === 0) {
                this.clearTable();
                this.showEmptyState();
                this.updatePagination();
                return;
            }

            // Rendering ottimizzato con DocumentFragment
            const fragment = document.createDocumentFragment();

            data.data.forEach((cruise, index) => {
                const row = this.createTableRow(cruise, index);
                fragment.appendChild(row);
            });

            // Svuota e riempie tabella in una sola operazione
            this.elements.tableBody.innerHTML = '';
            this.elements.tableBody.appendChild(fragment);

            // Aggiorna componenti UI
            this.updatePagination();
            this.updateSelectAllState();
            this.attachRowEventListeners();

            // Performance: triggera layout una sola volta
            requestAnimationFrame(() => {
                this.elements.tableContainer.scrollTop = 0;
            });
        }

        // Nuovo metodo per pulire completamente la tabella
        clearTable() {
            if (this.elements.tableBody) {
                this.elements.tableBody.innerHTML = '';
            }

            // Reset selezioni
            this.state.selectedIds.clear();
            this.updateSelectAllState();
            this.updateBulkActions();

            console.log('📋 Tabella pulita - nessun risultato da mostrare');
        }

        // Creazione ottimizzata riga tabella
        createTableRow(cruise, index) {
            const row = document.createElement('tr');
            row.className = 'table-row-optimized';
            row.dataset.cruiseId = cruise.id;

            const isSelected = this.state.selectedIds.has(cruise.id.toString());

            row.innerHTML = `
            <td class="col-checkbox">
                <div class="checkbox-wrapper">
                    <input type="checkbox" 
                           class="checkbox-optimized cruise-checkbox" 
                           value="${cruise.id}"
                           ${isSelected ? 'checked' : ''}
                           id="cruise-${cruise.id}">
                    <label for="cruise-${cruise.id}" class="checkbox-label"></label>
                </div>
            </td>
            <td class="col-ship">
                <div class="ship-name" title="${this.escapeHtml(cruise.ship || '')}">${cruise.ship || 'N/D'}</div>
            </td>
            <td class="col-cruise">
                <div class="cruise-title ${(cruise.cruise || '').length > 40 ? 'truncated' : ''}" 
                     title="${this.escapeHtml(cruise.cruise || '')}">${this.truncateText(cruise.cruise || 'N/D', 40)}</div>
            </td>
            <td class="col-company text-center">
                ${this.renderCompanyBadge(cruise.line)}
            </td>
            <td class="col-duration text-center">
                <span class="duration-text">${cruise.formatted_duration || 'N/D'}</span>
            </td>
            <td class="col-price text-right">
                ${this.renderPrice(cruise.interior)}
            </td>
            <td class="col-actions text-center">
                ${this.renderActionButtons(cruise)}
            </td>
        `;

            return row;
        }

        // ================== HELPERS RENDERING ==================
        renderCompanyBadge(company) {
            if (!company) {
                return '<span class="company-badge company-default">N/D</span>';
            }

            const companyText = this.escapeHtml(company);
            const companyLower = company.toLowerCase();
            let badgeClass = 'company-default';

            if (companyLower.includes('msc')) badgeClass = 'company-msc';
            else if (companyLower.includes('costa')) badgeClass = 'company-costa';
            else if (companyLower.includes('royal')) badgeClass = 'company-royal';
            else if (companyLower.includes('norwegian')) badgeClass = 'company-norwegian';

            return `<span class="company-badge ${badgeClass}">${companyText}</span>`;
        }

        renderPrice(price) {
            if (!price || price === 'N/D') {
                return '<span class="price-na">N/D</span>';
            }
            return `<span class="price-value">${price}</span>`;
        }

        renderActionButtons(cruise) {
            return `
            <div class="actions-group">
                <a href="/admin/cruises/${cruise.id}" 
                   class="action-btn action-btn-view" 
                   title="Visualizza">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="/admin/cruises/${cruise.id}/edit" 
                   class="action-btn action-btn-edit" 
                   title="Modifica">
                    <i class="fas fa-edit"></i>
                </a>
                <button type="button" 
                        class="action-btn action-btn-delete delete-cruise-btn" 
                        data-cruise-id="${cruise.id}"
                        data-cruise-name="${this.escapeHtml(cruise.ship + ' - ' + cruise.cruise)}"
                        title="Elimina">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        }

        // Mappa colonne per ordinamento
        getColumnIndex(columnName) {
            const columnMap = {
                'ship': 1,
                'cruise': 2,
                'line': 3,
                'duration': 4,
                'interior': 5
            };
            return columnMap[columnName] || 1;
        }

        // ================== EVENT HANDLERS ==================
        attachRowEventListeners() {
            // Checkbox delle righe
            const checkboxes = this.elements.tableBody.querySelectorAll('.cruise-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', (e) => {
                    this.handleRowSelection(e.target.value, e.target.checked);
                });
            });

            // Pulsanti elimina
            const deleteButtons = this.elements.tableBody.querySelectorAll('.delete-cruise-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const cruiseId = button.dataset.cruiseId;
                    const cruiseName = button.dataset.cruiseName;
                    this.confirmDelete(cruiseId, cruiseName);
                });
            });
        }

        handleSearch() {
            this.state.currentPage = 1;
            this.cache.clear(); // Pulisci cache per ricerca
            this.loadData();
        }

        handleFilter() {
            this.state.currentPage = 1;
            this.cache.clear(); // Pulisci cache per filtri
            this.loadData();
        }

        handleRowSelection(cruiseId, isSelected) {
            if (isSelected) {
                this.state.selectedIds.add(cruiseId);
            } else {
                this.state.selectedIds.delete(cruiseId);
            }

            this.updateSelectAllState();
            this.updateBulkActions();
        }

        handleSelectAll(isSelected) {
            const checkboxes = this.elements.tableBody.querySelectorAll('.cruise-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = isSelected;
                const cruiseId = checkbox.value;

                if (isSelected) {
                    this.state.selectedIds.add(cruiseId);
                } else {
                    this.state.selectedIds.delete(cruiseId);
                }
            });

            this.updateBulkActions();
        }

        handleKeyboardShortcuts(e) {
            // Ctrl/Cmd + F: Focus su ricerca
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                this.elements.globalSearch?.focus();
            }

            // Escape: Deseleziona tutto
            if (e.key === 'Escape') {
                this.deselectAll();
                this.elements.globalSearch?.blur();
            }

            // Ctrl/Cmd + R: Refresh
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                this.refreshData();
            }
        }

        handleResize() {
            // Aggiorna layout responsive se necessario
            this.updateTableLayout();
        }

        // ================== SORTING ==================
        initializeSorting() {
            const sortableHeaders = this.elements.table.querySelectorAll('.sortable');

            sortableHeaders.forEach(header => {
                header.addEventListener('click', () => {
                    const column = header.dataset.column;
                    this.handleSort(column);
                });
            });
        }

        handleSort(column) {
            if (this.state.sortColumn === column) {
                this.state.sortDirection = this.state.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.state.sortColumn = column;
                this.state.sortDirection = 'asc';
            }

            this.updateSortIndicators();
            this.state.currentPage = 1;
            this.loadData();
        }

        updateSortIndicators() {
            // Rimuovi indicatori esistenti
            const headers = this.elements.table.querySelectorAll('.sortable');
            headers.forEach(header => {
                header.classList.remove('asc', 'desc');
            });

            // Aggiungi indicatore corrente
            const currentHeader = this.elements.table.querySelector(`[data-column="${this.state.sortColumn}"]`);
            if (currentHeader) {
                currentHeader.classList.add(this.state.sortDirection);
            }
        }

        // ================== PAGINAZIONE ==================
        initializePagination() {
            // Event listener già aggiunto in attachEventListeners
        }

        updatePagination() {
            this.updatePaginationInfo();
            this.updatePaginationButtons();
        }

        updatePaginationInfo() {
            if (!this.elements.paginationInfo) return;

            // Se non ci sono risultati filtrati, mostra messaggio appropriato
            if (this.state.filteredRecords === 0) {
                this.elements.paginationInfo.textContent = 'Nessun risultato trovato';
                return;
            }

            const start = Math.min((this.state.currentPage - 1) * this.state.pageSize + 1, this.state
                .filteredRecords);
            const end = Math.min(this.state.currentPage * this.state.pageSize, this.state.filteredRecords);

            this.elements.paginationInfo.textContent =
                `Mostrando ${start}-${end} di ${this.state.filteredRecords} risultati`;
        }

        updatePaginationButtons() {
            if (!this.elements.paginationButtons) return;

            const totalPages = Math.ceil(this.state.filteredRecords / this.state.pageSize);
            const currentPage = this.state.currentPage;

            let buttonsHtml = '';

            // Pulsante Previous
            const prevDisabled = currentPage <= 1 ? 'disabled' : '';
            buttonsHtml += `
            <button class="page-btn page-btn-prev ${prevDisabled}" 
                    onclick="cruiseManager.goToPage(${currentPage - 1})"
                    ${prevDisabled ? 'disabled' : ''}>
                <i class="fas fa-chevron-left"></i> Prec
            </button>
        `;

            // Calcola range pagine da mostrare
            const range = this.calculatePageRange(currentPage, totalPages);

            // Prima pagina
            if (range.start > 1) {
                buttonsHtml += `<button class="page-btn" onclick="cruiseManager.goToPage(1)">1</button>`;
                if (range.start > 2) {
                    buttonsHtml += `<span class="page-ellipsis">...</span>`;
                }
            }

            // Pagine nel range
            for (let i = range.start; i <= range.end; i++) {
                const activeClass = i === currentPage ? 'active' : '';
                buttonsHtml += `
                <button class="page-btn ${activeClass}" 
                        onclick="cruiseManager.goToPage(${i})">${i}</button>
            `;
            }

            // Ultima pagina
            if (range.end < totalPages) {
                if (range.end < totalPages - 1) {
                    buttonsHtml += `<span class="page-ellipsis">...</span>`;
                }
                buttonsHtml +=
                    `<button class="page-btn" onclick="cruiseManager.goToPage(${totalPages})">${totalPages}</button>`;
            }

            // Pulsante Next
            const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
            buttonsHtml += `
            <button class="page-btn page-btn-next ${nextDisabled}" 
                    onclick="cruiseManager.goToPage(${currentPage + 1})"
                    ${nextDisabled ? 'disabled' : ''}>
                Succ <i class="fas fa-chevron-right"></i>
            </button>
        `;

            this.elements.paginationButtons.innerHTML = buttonsHtml;
        }

        calculatePageRange(currentPage, totalPages, maxButtons = 5) {
            const half = Math.floor(maxButtons / 2);
            let start = Math.max(1, currentPage - half);
            let end = Math.min(totalPages, start + maxButtons - 1);

            if (end - start + 1 < maxButtons) {
                start = Math.max(1, end - maxButtons + 1);
            }

            return {
                start,
                end
            };
        }

        goToPage(page) {
            const totalPages = Math.ceil(this.state.filteredRecords / this.state.pageSize);

            if (page < 1 || page > totalPages || page === this.state.currentPage) {
                return;
            }

            this.state.currentPage = page;
            this.loadData();
        }

        // ================== CHECKBOX E SELEZIONI ==================
        initializeCheckboxes() {
            // Event listeners già aggiunti in attachEventListeners

            // Debug: aggiungi classe per test se necessario
            this.setupCheckboxDebug();
        }

        setupCheckboxDebug() {
            // Funzione per testare la visibilità delle checkbox
            console.log('🔲 Inizializzazione checkbox con spunta visibile');

            // Aggiungi event listener per debug
            document.addEventListener('change', (e) => {
                if (e.target.classList.contains('checkbox-optimized')) {
                    console.log('Checkbox cambiata:', e.target.checked, e.target);

                    // Forza la visibilità della spunta
                    if (e.target.checked) {
                        e.target.style.setProperty('--checkmark-visible', '1');
                    } else {
                        e.target.style.removeProperty('--checkmark-visible');
                    }
                }
            });
        }

        updateSelectAllState() {
            if (!this.elements.selectAll) return;

            const visibleCheckboxes = this.elements.tableBody.querySelectorAll('.cruise-checkbox');
            const checkedCheckboxes = this.elements.tableBody.querySelectorAll('.cruise-checkbox:checked');

            if (visibleCheckboxes.length === 0) {
                this.elements.selectAll.indeterminate = false;
                this.elements.selectAll.checked = false;
            } else if (checkedCheckboxes.length === 0) {
                this.elements.selectAll.indeterminate = false;
                this.elements.selectAll.checked = false;
            } else if (checkedCheckboxes.length === visibleCheckboxes.length) {
                this.elements.selectAll.indeterminate = false;
                this.elements.selectAll.checked = true;
            } else {
                this.elements.selectAll.indeterminate = true;
                this.elements.selectAll.checked = false;
            }

            // Debug: log dello stato per verificare funzionamento
            console.log('SelectAll state:', {
                indeterminate: this.elements.selectAll.indeterminate,
                checked: this.elements.selectAll.checked,
                visibleCount: visibleCheckboxes.length,
                checkedCount: checkedCheckboxes.length
            });
        }

        updateBulkActions() {
            const selectedCount = this.state.selectedIds.size;

            if (this.elements.bulkActions) {
                if (selectedCount > 0) {
                    this.elements.bulkActions.style.display = 'flex';
                    const countElement = this.elements.bulkActions.querySelector('.selected-count');
                    if (countElement) {
                        countElement.textContent = selectedCount;
                    }
                } else {
                    this.elements.bulkActions.style.display = 'none';
                }
            }
        }

        deselectAll() {
            this.state.selectedIds.clear();

            const checkboxes = this.elements.tableBody.querySelectorAll('.cruise-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            if (this.elements.selectAll) {
                this.elements.selectAll.checked = false;
                this.elements.selectAll.indeterminate = false;
            }

            this.updateBulkActions();
        }

        // ================== RICERCA E FILTRI ==================
        initializeSearch() {
            this.toggleClearButton();
        }

        initializeFilters() {
            // Carica opzioni dinamiche se necessario
            this.loadFilterOptions();
        }

        toggleClearButton() {
            if (this.elements.clearSearch) {
                this.elements.clearSearch.style.display = this.state.searchTerm ? 'block' : 'none';
            }
        }

        clearSearch() {
            this.state.searchTerm = '';
            if (this.elements.globalSearch) {
                this.elements.globalSearch.value = '';
            }
            this.toggleClearButton();
            this.handleSearch();
        }

        async loadFilterOptions() {
            // Implementa se necessario caricare opzioni dinamicamente
        }

        // ================== STATISTICHE ==================
        async loadStats() {
            try {
                const response = await fetch('{{ route('cruises.stats') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const stats = await response.json();
                    this.updateStats(stats);
                }
            } catch (error) {
                console.error('Errore caricamento statistiche:', error);
            }
        }

        updateStats(stats) {
            const updates = [{
                    element: this.elements.totalCruises,
                    value: stats.total_cruises || 0
                },
                {
                    element: this.elements.availableCruises,
                    value: stats.available_cruises || 0
                },
                {
                    element: this.elements.expiredCruises,
                    value: stats.expired_cruises || 0
                },
                {
                    element: this.elements.totalCompanies,
                    value: stats.companies || 0
                }
            ];

            updates.forEach(({
                element,
                value
            }) => {
                if (element) {
                    this.animateNumber(element, parseInt(element.textContent) || 0, value);
                }
            });
        }

        animateNumber(element, start, end, duration = 1000) {
            const startTime = performance.now();
            const difference = end - start;

            const step = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                const currentValue = Math.floor(start + (difference * this.easeOutQuart(progress)));
                element.textContent = this.formatNumber(currentValue);

                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            };

            requestAnimationFrame(step);
        }

        easeOutQuart(t) {
            return 1 - Math.pow(1 - t, 4);
        }

        // ================== AZIONI CRUD ==================
        async confirmDelete(cruiseId, cruiseName) {
            const result = await this.showConfirmDialog({
                title: 'Conferma eliminazione',
                text: `Sei sicuro di voler eliminare "${cruiseName}"?`,
                icon: 'warning',
                confirmButtonText: '<i class="fas fa-trash me-2"></i>Elimina',
                confirmButtonColor: '#dc3545'
            });

            if (result.isConfirmed) {
                await this.deleteCruise(cruiseId);
            }
        }

        async deleteCruise(cruiseId) {
            try {
                const response = await fetch(`/admin/cruises/${cruiseId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.response) {
                    this.showSuccess(result.message || 'Crociera eliminata con successo');

                    // Rimuovi dalla selezione
                    this.state.selectedIds.delete(cruiseId.toString());

                    // IMPORTANTE: Forza il reload della tabella
                    this.cache.clear(); // Pulisci cache per forzare ricaricamento
                    await this.loadData(); // Ricarica dati
                    await this.loadStats(); // Aggiorna statistiche

                    console.log('✅ Crociera eliminata e tabella aggiornata:', cruiseId);
                } else {
                    this.showError(result.message || 'Errore durante l\'eliminazione');
                }
            } catch (error) {
                console.error('Errore eliminazione:', error);
                this.showError('Errore durante l\'eliminazione');
            }
        }

        async bulkDelete() {
            if (this.state.selectedIds.size === 0) {
                this.showWarning('Nessuna crociera selezionata');
                return;
            }

            const selectedArray = Array.from(this.state.selectedIds);
            const result = await this.showConfirmDialog({
                title: 'Conferma eliminazione multipla',
                text: `Sei sicuro di voler eliminare ${selectedArray.length} crociere?`,
                icon: 'warning',
                confirmButtonText: '<i class="fas fa-trash me-2"></i>Elimina tutte',
                confirmButtonColor: '#dc3545'
            });

            if (result.isConfirmed) {
                await this.performBulkDelete(selectedArray);
            }
        }

        async performBulkDelete(ids) {
            try {
                this.showLoading(true, 'Eliminazione in corso...');

                const response = await fetch('{{ route('cruises.bulk-delete') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        ids
                    })
                });

                const result = await response.json();

                if (result.response) {
                    this.showSuccess(result.message);

                    // Reset selezioni
                    this.state.selectedIds.clear();
                    this.updateSelectAllState();
                    this.updateBulkActions();

                    // IMPORTANTE: Forza il reload completo
                    this.cache.clear(); // Pulisci cache
                    await this.loadData(); // Ricarica dati
                    await this.loadStats(); // Aggiorna statistiche

                    console.log('✅ Eliminazione multipla completata e tabella aggiornata');
                } else {
                    this.showError(result.message || 'Errore durante l\'eliminazione multipla');
                }
            } catch (error) {
                console.error('Errore eliminazione multipla:', error);
                this.showError('Errore durante l\'eliminazione multipla');
            } finally {
                this.showLoading(false);
            }
        }

        // ================== UI STATES ==================
        showLoading(show, message = 'Caricamento...') {
            if (this.elements.tableLoading) {
                if (show) {
                    this.elements.tableLoading.style.display = 'flex';
                    const loadingText = this.elements.tableLoading.querySelector('.loading-text');
                    if (loadingText) {
                        loadingText.textContent = message;
                    }
                } else {
                    this.elements.tableLoading.style.display = 'none';
                }
            }
        }

        showEmptyState(show = true) {
            if (this.elements.tableEmpty) {
                this.elements.tableEmpty.style.display = show ? 'block' : 'none';
            }

            // Se mostriamo empty state, nascondi la tabella
            if (show && this.elements.tableContainer) {
                this.elements.tableContainer.style.display = 'none';
            } else if (!show && this.elements.tableContainer) {
                this.elements.tableContainer.style.display = 'block';
            }
        }

        updateTableLayout() {
            // Aggiorna layout per responsive se necessario
            if (window.innerWidth < 768) {
                this.elements.tableContainer?.classList.add('mobile-layout');
            } else {
                this.elements.tableContainer?.classList.remove('mobile-layout');
            }
        }

        // ================== UTILITY FUNCTIONS ==================
        debounce(func, wait) {
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

        throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }

        generateCacheKey() {
            return JSON.stringify({
                page: this.state.currentPage,
                pageSize: this.state.pageSize,
                search: this.state.searchTerm,
                sortColumn: this.state.sortColumn,
                sortDirection: this.state.sortDirection,
                filters: this.state.filters,
                timestamp: Math.floor(Date.now() / 60000) // Cache per 1 minuto
            });
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        truncateText(text, maxLength) {
            if (!text || text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        }

        formatNumber(num) {
            return new Intl.NumberFormat('it-IT').format(num);
        }

        // ================== NOTIFICHE ==================
        async showConfirmDialog(options) {
            // Implementazione con SweetAlert2 se disponibile, altrimenti confirm browser
            if (typeof Swal !== 'undefined') {
                return await Swal.fire({
                    title: options.title,
                    text: options.text,
                    icon: options.icon || 'question',
                    showCancelButton: true,
                    confirmButtonColor: options.confirmButtonColor || '#007bff',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: options.confirmButtonText || 'Conferma',
                    cancelButtonText: 'Annulla',
                    customClass: {
                        popup: 'swal-optimized'
                    }
                });
            } else {
                return {
                    isConfirmed: confirm(`${options.title}\n\n${options.text}`)
                };
            }
        }

        showSuccess(message) {
            this.showToast(message, 'success');
        }

        showError(message) {
            this.showToast(message, 'error');
        }

        showWarning(message) {
            this.showToast(message, 'warning');
        }

        showToast(message, type = 'info') {
            if (typeof Swal !== 'undefined') {
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
            } else {
                alert(message);
            }
        }

        // ================== API PUBBLICHE ==================
        async refreshData() {
            this.cache.clear(); // Pulisci sempre la cache
            this.clearTable(); // Pulisci la tabella prima del reload
            await this.loadData();
            await this.loadStats();
            this.showSuccess('Dati aggiornati!');
        }

        async exportData() {
            const result = await this.showConfirmDialog({
                title: 'Esporta Crociere',
                text: 'Vuoi esportare tutte le crociere in un file CSV?',
                icon: 'question',
                confirmButtonText: '<i class="fas fa-download me-2"></i>Esporta',
                confirmButtonColor: '#28a745'
            });

            if (result.isConfirmed) {
                window.location.href = '{{ route('cruises.export') }}';
                this.showSuccess('Export avviato! Il download inizierà a breve.');
            }
        }

        // Getter per stato corrente
        getState() {
            return {
                ...this.state
            };
        }

        // Forza l'aggiornamento della tabella senza cache
        async forceRefresh() {
            console.log('🔄 Forzando refresh completo della tabella...');

            // Pulisci tutto
            this.cache.clear();
            this.clearTable();
            this.state.selectedIds.clear();

            // Mostra loading
            this.showLoading(true, 'Aggiornamento in corso...');
            this.showEmptyState(false);

            try {
                // Ricarica tutto
                await this.loadData();
                await this.loadStats();

                console.log('✅ Refresh completo completato');
            } catch (error) {
                console.error('❌ Errore durante il refresh:', error);
                this.showError('Errore durante l\'aggiornamento');
            }
        }
        setFilter(filterName, value) {
            if (this.state.filters.hasOwnProperty(filterName)) {
                this.state.filters[filterName] = value;
                const filterElement = this.elements[filterName + 'Filter'];
                if (filterElement) {
                    filterElement.value = value;
                }
                this.handleFilter();
            }
        }

        setSearch(searchTerm) {
            this.state.searchTerm = searchTerm;
            if (this.elements.globalSearch) {
                this.elements.globalSearch.value = searchTerm;
            }
            this.toggleClearButton();
            this.handleSearch();
        }
    }

    // ================== INIZIALIZZAZIONE GLOBALE ==================
    let cruiseManager;

    // Inizializzazione quando DOM è pronto
    document.addEventListener('DOMContentLoaded', async function() {
        console.log('🚀 Inizializzazione gestione crociere ottimizzata...');

        try {
            cruiseManager = new CruiseManager();
            await cruiseManager.init();

            // Esponi funzioni globali per compatibilità
            window.cruiseManager = cruiseManager;
            window.refreshTable = () => cruiseManager.refreshData();
            window.exportCruises = () => cruiseManager.exportData();
            window.bulkDelete = () => cruiseManager.bulkDelete();
            window.deselectAll = () => cruiseManager.deselectAll();

            console.log('✅ Gestione crociere inizializzata con successo');
        } catch (error) {
            console.error('❌ Errore inizializzazione gestione crociere:', error);
        }
    });

    // Gestione errori globali AJAX
    document.addEventListener('DOMContentLoaded', function() {
        // Intercetta errori di rete
        window.addEventListener('unhandledrejection', function(event) {
            if (event.reason && event.reason.name === 'TypeError' && event.reason.message.includes(
                    'fetch')) {
                console.error('Errore di connessione:', event.reason);
                if (cruiseManager) {
                    cruiseManager.showError(
                        'Errore di connessione. Controlla la tua connessione internet.');
                }
            }
        });
    });

    // ================== PERFORMANCE MONITORING ==================
    if (typeof performance !== 'undefined' && performance.mark) {
        performance.mark('cruise-manager-script-loaded');
    }

    console.log('✅ Script CruiseManager ottimizzato caricato completamente');
</script>
