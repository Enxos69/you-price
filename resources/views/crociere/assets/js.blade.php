<script>
    // Crea/aggiorna singolo gauge con grafica dal rosso al verde

    $(function() {
        // Configurazione DateRangePicker
        $('#date-range').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: "Applica",
                cancelLabel: "Annulla",
                fromLabel: "Da",
                toLabel: "A",
                customRangeLabel: "Personalizzato",
                daysOfWeek: ["D", "L", "M", "M", "G", "V", "S"],
                monthNames: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu",
                    "Lug", "Ago", "Set", "Ott", "Nov", "Dic"
                ],
                firstDay: 1
            },
            ranges: {
                'Prossimo mese': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month')
                    .endOf('month')
                ],
                'Estate 2025': [moment('2025-06-21'), moment('2025-09-21')],
                'Autunno 2025': [moment('2025-09-21'), moment('2025-12-21')],
                'Inverno 2025/26': [moment('2025-12-21'), moment('2026-03-20')],
                'Primavera 2026': [moment('2026-03-20'), moment('2026-06-21')]
            },
            startDate: moment().add(1, 'month'),
            endDate: moment().add(1, 'month').add(7, 'days'),
            minDate: moment(),
            maxDate: moment().add(2, 'years'),
            opens: 'left',
            drops: 'down'
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Elementi DOM
        const form = document.querySelector('#search-form');
        const resultsSection = document.querySelector('#results-section');
        const loadingSpinner = document.getElementById('loading-spinner');

        // Variabili per i grafici
        let satisfactionChart, optimizationChart;
        let isSearching = false;

        // Inizializzazione
        loadInitialStats();
        setupFormValidation();
        setupBudgetCalculator();

        // Event listeners
        form.addEventListener('submit', handleSearch);
        form.addEventListener('reset', handleReset);

        // Funzione principale di ricerca
        async function handleSearch(e) {
            e.preventDefault();

            if (isSearching) return;
            isSearching = true;

            // Validazione form
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                isSearching = false;
                return;
            }

            // UI Loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            setLoadingState(submitBtn, true);

            try {
                // Prepara dati
                const formData = new FormData(form);

                // Aggiungi CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    formData.append('_token', csrfToken.getAttribute('content'));
                }

                // Chiamata API
                const response = await fetch('/crociere/search', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : '',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Errore nella ricerca');
                }

                if (data.success === false) {
                    throw new Error(data.error || 'Ricerca fallita');
                }

                // Mostra risultati
                await displayResults(data);
                showToast('Ricerca completata con successo!', 'success');

            } catch (error) {
                console.error('Errore ricerca:', error);
                showToast(error.message || 'Si è verificato un errore durante la ricerca', 'error');
                displayErrorState();
            } finally {
                setLoadingState(submitBtn, false, originalContent);
                isSearching = false;
            }
        }

        // Reset form
        function handleReset() {
            form.classList.remove('was-validated');
            resultsSection.classList.add('d-none');
            document.getElementById('budget-per-person').textContent = '';
            showToast('Form resettato', 'info');
        }

        // Gestione stato di caricamento
        function setLoadingState(button, loading, originalContent = null) {
            if (loading) {
                button.disabled = true;
                loadingSpinner.classList.remove('d-none');
                button.querySelector('.fas').className = 'fas fa-spinner fa-spin me-2';
            } else {
                button.disabled = false;
                loadingSpinner.classList.add('d-none');
                button.querySelector('.fas').className = 'fas fa-search me-2';
            }
        }

        // Visualizzazione risultati
        async function displayResults(data) {
            // Mostra sezione risultati
            resultsSection.classList.remove('d-none');
            resultsSection.classList.add('fade-in');

            // Scroll ai risultati
            setTimeout(() => {
                resultsSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 300);

            // Aggiorna gauge e statistiche
            updateGauges(data);
            updateSearchStats(data.statistiche || {});

            // Aggiorna tabelle
            await updateTables(data);

            // Aggiorna consigli AI
            updateAISuggestions(data.consigli || []);
        }

        // Aggiornamento gauge
        function updateGauges(data) {
            const currentSatisfaction = data.soddisfazione_attuale || 0;
            const optimalSatisfaction = data.soddisfazione_ottimale || 0;

            // Aggiorna gauge soddisfazione
            updateGauge('satisfaction-gauge', currentSatisfaction, 'satisfaction-score');

            // Aggiorna gauge ottimizzazione
            updateGauge('optimization-gauge', optimalSatisfaction, 'optimization-score');

            // Aggiorna contatori
            document.getElementById('total-matches').textContent = (data.matches || []).length;
            document.getElementById('total-alternatives').textContent = (data.alternative || []).length;
        }

        // Crea/aggiorna singolo gauge con grafica dal rosso al verde
        function updateGauge(containerId, value, scoreElementId) {
            const container = document.getElementById(containerId);

            // Colori graduali dal rosso al verde
            const getGaugeColors = (val) => {
                if (val >= 80) return {
                    color: '#28a745',
                    gradient: ['#28a745', '#20c997'],
                    rating: 'excellent',
                    text: 'Eccellente'
                };
                if (val >= 60) return {
                    color: '#20c997',
                    gradient: ['#20c997', '#ffc107'],
                    rating: 'good',
                    text: 'Buono'
                };
                if (val >= 40) return {
                    color: '#ffc107',
                    gradient: ['#ffc107', '#fd7e14'],
                    rating: 'average',
                    text: 'Medio'
                };
                return {
                    color: '#dc3545',
                    gradient: ['#fd7e14', '#dc3545'],
                    rating: 'poor',
                    text: 'Scarso'
                };
            };

            const gaugeData = getGaugeColors(value);

            // Aggiorna classe della card container
            const card = container.closest('.card');
            if (card) {
                card.classList.remove('excellent', 'good', 'average', 'poor');
                card.classList.add(gaugeData.rating);
            }

            // Aggiorna rating text
            const ratingElement = containerId === 'satisfaction-gauge' ?
                document.getElementById('satisfaction-rating') :
                document.getElementById('optimization-suggestion');

            if (ratingElement) {
                ratingElement.textContent = gaugeData.text;
                ratingElement.classList.remove('excellent', 'good', 'average', 'poor');
                ratingElement.classList.add(gaugeData.rating);
            }

            const options = {
                series: [value],
                chart: {
                    height: 120,
                    type: 'radialBar',
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -135,
                        endAngle: 135,
                        hollow: {
                            size: '60%',
                            background: 'transparent',
                        },
                        track: {
                            background: '#f1f3f4',
                            strokeWidth: '100%',
                            margin: 5,
                        },
                        dataLabels: {
                            name: {
                                show: false,
                            },
                            value: {
                                show: false,
                            }
                        }
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'horizontal',
                        shadeIntensity: 0.5,
                        gradientToColors: [gaugeData.gradient[1]],
                        inverseColors: false,
                        opacityFrom: 1,
                        opacityTo: 1,
                        stops: [0, 100]
                    }
                },
                colors: [gaugeData.gradient[0]],
                stroke: {
                    lineCap: 'round'
                }
            };

            // Distruggi grafico esistente se presente
            if (container._chart) {
                container._chart.destroy();
            }

            // Crea nuovo grafico
            const chart = new ApexCharts(container, options);
            container._chart = chart;
            chart.render();

            // Anima contatore e aggiungi classe per colore
            animateValue(scoreElementId, 0, value, 1500);

            // Aggiungi classe per colore del gauge overlay
            const overlay = container.nextElementSibling;
            if (overlay && overlay.classList.contains('gauge-overlay')) {
                overlay.classList.remove('gauge-excellent', 'gauge-good', 'gauge-average', 'gauge-poor');
                overlay.classList.add('gauge-' + gaugeData.rating);
            }
        }

        // Aggiornamento statistiche ricerca
        function updateSearchStats(stats) {
            const statElements = {
                'avg-savings': Math.round(stats.risparmio_medio || 0),
                'companies-found': stats.compagnie_diverse || 0,
                'avg-duration': Math.round(stats.durata_media || 0),
                'avg-price-found': Math.round(stats.prezzo_medio_trovato || 0)
            };

            Object.entries(statElements).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) {
                    animateCounterValue(element, value);
                }
            });
        }

        // Aggiornamento tabelle
        async function updateTables(data) {
            const matches = data.matches || [];
            const alternatives = data.alternative || [];

            // Aggiorna contatori badge
            document.getElementById('matches-count').textContent = matches.length;
            document.getElementById('alternatives-count').textContent = alternatives.length;

            // Riempi tabelle
            await fillTable('matches-table', matches, 'matches');
            await fillTable('alternatives-table', alternatives, 'alternatives');
        }

        // Riempimento tabella con animazioni
        async function fillTable(tableId, items, type) {
            const table = document.getElementById(tableId);
            const tbody = table.querySelector('tbody');
            const noResultsDiv = document.getElementById(type === 'matches' ? 'no-matches' :
                'no-alternatives');

            // Pulisci tabella
            tbody.innerHTML = '';

            if (items.length === 0) {
                if (noResultsDiv) {
                    noResultsDiv.classList.remove('d-none');
                    noResultsDiv.classList.add('fade-in');
                }
                return;
            }

            if (noResultsDiv) {
                noResultsDiv.classList.add('d-none');
            }

            // Aggiungi righe con animazione
            items.forEach((item, index) => {
                const tr = createTableRow(item, type);
                tr.style.opacity = '0';
                tr.style.transform = 'translateY(15px)';
                tbody.appendChild(tr);

                // Anima entrata
                setTimeout(() => {
                    tr.style.transition = 'all 0.4s ease';
                    tr.style.opacity = '1';
                    tr.style.transform = 'translateY(0)';
                    tr.classList.add(index % 2 === 0 ? 'slide-in-left' : 'slide-in-right');
                }, index * 80);
            });
        }

        // Creazione riga tabella senza azioni per matches - Style coordinato
        function createTableRow(item, type) {
            const tr = document.createElement('tr');
            tr.className = 'cruise-row';

            // Calcola costo giornaliero
            const totalPrice = parseFloat(String(item.interior || item.prezzo_persona || 0).replace(/[€,]/g,
                '')) || 0;
            const nights = parseInt(item.night) || 1;
            const participants = getParticipantsCount();
            const totalCostForGroup = totalPrice * participants;
            const dailyCostForGroup = Math.round(totalCostForGroup / Math.max(nights, 1));

            // Formattazione valori
            const formatPrice = (price) => {
                return price ? `€${Math.round(price).toLocaleString('it-IT')}` : 'N/D';
            };

            const formatDate = (date) => {
                if (!date || date === 'N/D') return 'N/D';
                try {
                    return moment(date).format('DD/MM/YY');
                } catch {
                    return date;
                }
            };

            const getCompanyClass = (company) => {
                const companyLower = (company || '').toLowerCase();
                if (companyLower.includes('royal caribbean')) return 'company-royal';
                if (companyLower.includes('msc')) return 'company-msc';
                if (companyLower.includes('costa')) return 'company-costa';
                if (companyLower.includes('norwegian')) return 'company-norwegian';
                if (companyLower.includes('carnival')) return 'company-carnival';
                if (companyLower.includes('celebrity')) return 'company-celebrity';
                return 'badge bg-secondary';
            };

            if (type === 'matches') {
                // Tabella matches SENZA colonna azioni
                tr.innerHTML = `
                <td>
                    <div class="ship-name">${item.ship || 'N/D'}</div>
                </td>
                <td>
                    <div class="cruise-details">${(item.cruise || 'N/D').substring(0, 25)}${(item.cruise || '').length > 25 ? '...' : ''}</div>
                </td>
                <td>
                    <span class="${getCompanyClass(item.line)}">
                        ${item.line || 'N/D'}
                    </span>
                </td>
                <td>
                    <span class="badge duration-badge">
                        ${nights}g
                    </span>
                </td>
                <td>
                    <div class="price-total">${formatPrice(totalCostForGroup)}</div>
                    ${item.savings > 0 ? `<div class="price-savings">-${item.savings}%</div>` : ''}
                </td>
                <td>
                    <div class="price-daily">${formatPrice(dailyCostForGroup)}</div>
                </td>
            `;
            } else {
                // Tabella alternatives CON benefit
                tr.innerHTML = `
                <td>
                    <div class="ship-name">${item.ship || 'N/D'}</div>
                </td>
                <td>
                    <div class="cruise-details">${(item.cruise || 'N/D').substring(0, 25)}${(item.cruise || '').length > 25 ? '...' : ''}</div>
                </td>
                <td>
                    <span class="${getCompanyClass(item.line)}">
                        ${item.line || 'N/D'}
                    </span>
                </td>
                <td>
                    <span class="badge duration-badge">
                        ${nights}g
                    </span>
                </td>
                <td>
                    <div class="price-total">${formatPrice(totalCostForGroup)}</div>
                </td>
                <td>
                    <div class="price-daily">${formatPrice(dailyCostForGroup)}</div>
                </td>
                <td>
                    <span class="benefit-tag">
                        ${(item.benefit || item.recommendation_reason || 'Buona opzione').substring(0, 15)}${(item.benefit || item.recommendation_reason || '').length > 15 ? '...' : ''}
                    </span>
                </td>
            `;
            }

            // Tooltip con info complete
            tr.setAttribute('title',
                `${item.ship} - ${item.cruise}\n${item.from || ''} → ${item.to || ''}\nPartenza: ${formatDate(item.partenza)}`
                );
            tr.setAttribute('data-bs-toggle', 'tooltip');
            tr.setAttribute('data-bs-placement', 'top');

            return tr;
        }

        // Ottieni numero partecipanti dal form
        function getParticipantsCount() {
            const participantsInput = document.getElementById('participants');
            return parseInt(participantsInput.value) || 2;
        }

        // Aggiornamento consigli AI
        function updateAISuggestions(suggestions) {
            const container = document.getElementById('ai-suggestions');

            if (suggestions.length === 0) {
                container.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-lightbulb text-warning me-3" style="font-size: 1.5rem;"></i>
                    <div>
                        <h6 class="mb-1 text-dark">Suggerimento Base</h6>
                        <p class="mb-0 text-muted">
                            Prova ad ampliare le date di ricerca o modificare i parametri per trovare più opzioni.
                        </p>
                    </div>
                </div>
            `;
                return;
            }

            const suggestionsHtml = suggestions.map((suggestion, index) => `
            <div class="d-flex align-items-start mb-2 suggestion-item" style="animation-delay: ${index * 0.2}s">
                <i class="fas fa-arrow-right text-success me-2 mt-1"></i>
                <span class="text-dark">${suggestion}</span>
            </div>
        `).join('');

            container.innerHTML = `
            <div class="suggestions-container">
                ${suggestionsHtml}
            </div>
        `;

            // Anima suggerimenti
            setTimeout(() => {
                const items = container.querySelectorAll('.suggestion-item');
                items.forEach((item, index) => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-15px)';

                    setTimeout(() => {
                        item.style.transition = 'all 0.4s ease';
                        item.style.opacity = '1';
                        item.style.transform = 'translateX(0)';
                    }, index * 150);
                });
            }, 100);
        }

        // Caricamento statistiche iniziali - SENZA expired cruises
        async function loadInitialStats() {
            try {
                const response = await fetch('/crociere/stats');
                const stats = await response.json();

                if (response.ok && !stats.error) {
                    // Anima contatori con i colori coordinati (solo 3 statistiche)
                    animateCounterValue(document.getElementById('total-cruises'), stats.total_cruises || 0);
                    animateCounterValue(document.getElementById('available-cruises'), stats
                        .available_cruises || 0);
                    animateCounterValue(document.getElementById('total-companies'), stats.companies || 0);
                    // RIMOSSO: expired-cruises
                }
            } catch (error) {
                console.warn('Impossibile caricare statistiche:', error);
            }
        }

        // Animazione contatori con effetto coordinato
        function animateCounterValue(element, targetValue) {
            if (!element) return;

            element.classList.add('counter-updating');

            const startValue = 0;
            const duration = 1800;
            const startTime = performance.now();

            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Easing function per smoothness
                const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                const current = Math.floor(startValue + (targetValue - startValue) * easeOutQuart);

                element.textContent = current.toLocaleString('it-IT');

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    element.classList.remove('counter-updating');
                }
            }

            requestAnimationFrame(updateCounter);
        }

        // Animazione valori semplici
        function animateValue(elementId, start, end, duration) {
            const element = document.getElementById(elementId);
            if (!element) return;

            const startTime = performance.now();
            const difference = end - start;

            function updateValue(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                const easeOutCubic = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(start + (difference * easeOutCubic));

                element.textContent = current;

                if (progress < 1) {
                    requestAnimationFrame(updateValue);
                }
            }

            requestAnimationFrame(updateValue);
        }

        // Configurazione validazione form
        function setupFormValidation() {
            const inputs = form.querySelectorAll('input[required]');

            inputs.forEach(input => {
                input.addEventListener('blur', validateInput);
                input.addEventListener('input', clearValidation);
            });

            function validateInput() {
                const input = this;
                const isValid = input.checkValidity();

                input.classList.remove('is-valid', 'is-invalid');

                if (input.value.trim() !== '') {
                    input.classList.add(isValid ? 'is-valid' : 'is-invalid');
                }
            }

            function clearValidation() {
                this.classList.remove('is-valid', 'is-invalid');
            }
        }

        // Setup calcolatore budget
        function setupBudgetCalculator() {
            const budgetInput = document.getElementById('budget');
            const participantsInput = document.getElementById('participants');
            const budgetPerPersonEl = document.getElementById('budget-per-person');

            function updateBudgetCalculation() {
                const budget = parseFloat(budgetInput.value) || 0;
                const participants = parseInt(participantsInput.value) || 1;

                if (budget > 0 && participants > 0) {
                    const perPerson = budget / participants;
                    budgetPerPersonEl.innerHTML = `
                    <i class="fas fa-calculator me-1"></i>
                    €${perPerson.toLocaleString('it-IT')} per persona
                `;
                } else {
                    budgetPerPersonEl.textContent = '';
                }
            }

            budgetInput.addEventListener('input', updateBudgetCalculation);
            participantsInput.addEventListener('input', updateBudgetCalculation);

            // Calcolo iniziale
            updateBudgetCalculation();
        }

        // Gestione stato di errore
        function displayErrorState() {
            resultsSection.classList.remove('d-none');
            resultsSection.innerHTML = `
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-4"></i>
                            <h4 class="text-dark">Oops! Qualcosa è andato storto</h4>
                            <p class="text-muted mb-4">
                                Non siamo riusciti a completare la ricerca. 
                                Controlla i parametri e riprova.
                            </p>
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="fas fa-redo me-2"></i>Riprova
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

            resultsSection.scrollIntoView({
                behavior: 'smooth'
            });
        }

        // Sistema di notifiche toast coordinato con il design
        function showToast(message, type = 'info', duration = 3000) {
            const toastContainer = document.getElementById('toast-container');
            const toastId = 'toast-' + Date.now();

            const iconMap = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle'
            };

            const colorMap = {
                success: 'toast-success',
                error: 'toast-error',
                warning: 'bg-warning text-dark',
                info: 'toast-info'
            };

            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = `toast ${colorMap[type] || colorMap.info} show`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
            <div class="toast-body d-flex align-items-center">
                <i class="${iconMap[type] || iconMap.info} me-2"></i>
                <span class="flex-grow-1">${message}</span>
                <button type="button" class="btn-close ms-2" onclick="this.closest('.toast').remove()"></button>
            </div>
        `;

            toastContainer.appendChild(toast);

            // Auto-remove con animazione
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 300);
                }
            }, duration);
        }

        // Gestione eventi speciali - RIMUOVI gestione action buttons
        function setupSpecialFeatures() {
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + Enter per submit
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    if (!isSearching) {
                        form.dispatchEvent(new Event('submit'));
                    }
                }

                // Escape per nascondere risultati
                if (e.key === 'Escape' && !resultsSection.classList.contains('d-none')) {
                    resultsSection.classList.add('d-none');
                    showToast('Risultati nascosti', 'info', 2000);
                }
            });

            // Gestione click su righe tabella per info aggiuntive
            document.addEventListener('click', function(e) {
                if (e.target.closest('.cruise-row')) {
                    const row = e.target.closest('.cruise-row');
                    const shipName = row.querySelector('.ship-name')?.textContent || 'N/D';

                    // Highlight temporaneo della riga con colore verde
                    row.style.backgroundColor = 'rgba(45, 80, 22, 0.15)';
                    setTimeout(() => {
                        row.style.backgroundColor = '';
                    }, 1000);

                    showToast(`Selezionata: ${shipName}`, 'info', 2000);
                }
            });
        }

        // Performance monitoring
        function setupPerformanceMonitoring() {
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                const startTime = performance.now();
                return originalFetch.apply(this, args).then(response => {
                    const endTime = performance.now();
                    const duration = endTime - startTime;

                    if (args[0].includes('/crociere/search')) {
                        console.log(`🚢 Ricerca completata in ${duration.toFixed(0)}ms`);

                        if (duration > 3000) {
                            showToast('Ricerca completata (connessione lenta)', 'warning', 4000);
                        }
                    }
                    return response;
                });
            };
        }

        // Gestione responsive coordinata
        function setupResponsiveHandling() {
            const mediaQuery = window.matchMedia('(max-width: 768px)');

            function handleResponsiveChange(e) {
                if (e.matches) {
                    // Mobile optimizations
                    document.body.classList.add('mobile-view');

                    // Adatta display delle tabelle
                    const tables = document.querySelectorAll('.table-responsive');
                    tables.forEach(table => {
                        table.style.fontSize = '0.8rem';
                    });

                    // Riduci padding delle card su mobile
                    const cards = document.querySelectorAll('.card-body');
                    cards.forEach(card => {
                        card.style.padding = '1rem';
                    });
                } else {
                    document.body.classList.remove('mobile-view');
                }
            }

            handleResponsiveChange(mediaQuery);
            mediaQuery.addEventListener('change', handleResponsiveChange);
        }

        // Inizializzazione tooltips
        function initializeTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover focus',
                    delay: {
                        show: 500,
                        hide: 100
                    }
                });
            });
        }

        // Funzioni di utilità coordinate con il design
        const Utils = {
            formatPrice: (price) => {
                const numericPrice = parseFloat(String(price).replace(/[€,]/g, '')) || 0;
                return `€${numericPrice.toLocaleString('it-IT')}`;
            },

            formatDate: (date) => {
                if (!date) return 'N/D';
                try {
                    return moment(date).format('DD/MM/YYYY');
                } catch {
                    return date;
                }
            },

            calculateDailyCost: (totalPrice, nights, participants = 1) => {
                const total = parseFloat(String(totalPrice).replace(/[€,]/g, '')) || 0;
                const nightsNum = parseInt(nights) || 1;
                return Math.round((total * participants) / nightsNum);
            },

            getCompanyColor: (company) => {
                const companyLower = (company || '').toLowerCase();
                if (companyLower.includes('royal')) return '#FFA000';
                if (companyLower.includes('msc')) return '#1565C0';
                if (companyLower.includes('costa')) return '#FFD600';
                if (companyLower.includes('norwegian')) return '#0277BD';
                if (companyLower.includes('carnival')) return '#D32F2F';
                if (companyLower.includes('celebrity')) return '#512DA8';
                return '#6c757d';
            }
        };

        // Inizializzazione completa
        function initialize() {
            setupSpecialFeatures();
            setupPerformanceMonitoring();
            setupResponsiveHandling();

            // Inizializza tooltips dopo un delay
            setTimeout(initializeTooltips, 1000);

            console.log('🚢 Sistema di ricerca crociere (Design Coordinato) inizializzato!');
        }

        // Cleanup per performance
        window.addEventListener('beforeunload', function() {
            // Cleanup grafici
            document.querySelectorAll('[id*="gauge"]').forEach(element => {
                if (element._chart) {
                    element._chart.destroy();
                }
            });

            // Cleanup tooltips
            const tooltips = document.querySelectorAll('.tooltip');
            tooltips.forEach(tooltip => tooltip.remove());
        });

        // Avvia inizializzazione
        initialize();

        // Gestione errori globali
        window.addEventListener('error', function(e) {
            console.error('Errore globale:', e.error);
            showToast('Si è verificato un errore imprevisto', 'error');
        });

        // Esporta utilità globali
        window.CruiseSearchUtils = Utils;
    });
</script>
