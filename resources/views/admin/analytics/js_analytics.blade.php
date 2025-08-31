    <script>
        // Analytics Dashboard JavaScript
        $(document).ready(function() {
            let trendsChart, devicesChart, budgetChart, participantsChart;
            let currentPage = 1;

            // Inizializzazione
            loadGeneralStats();
            loadCharts();
            loadGeographicData();
            loadSearchParameters();
            loadPerformanceMetrics();
            loadSearchLogs();

            // Event Listeners
            $('#refresh-btn').click(function() {
                refreshAllData();
            });

            $('#export-btn').click(function() {
                $('#exportModal').modal('show');
            });

            $('#confirm-export').click(function() {
                exportData();
            });

            // Filtri tabella
            $('#filter-user-type, #filter-device, #filter-success').change(function() {
                currentPage = 1;
                loadSearchLogs();
            });

            // Period buttons per trend chart
            $('.btn-group button[data-period]').click(function() {
                $('.btn-group button').removeClass('active');
                $(this).addClass('active');
                const period = $(this).data('period');
                loadTrendsChart(period);
            });

            // Funzioni principali
            async function loadGeneralStats() {
                try {
                    const response = await fetch('/api/analytics/general-stats');
                    const data = await response.json();

                    if (response.ok) {
                        updateGeneralStatsUI(data);
                    } else {
                        showError('Errore caricamento statistiche generali');
                    }
                } catch (error) {
                    showError('Errore di connessione: ' + error.message);
                }
            }

            function updateGeneralStatsUI(data) {
                animateCounter('#total-searches', data.total_searches || 0);
                animateCounter('#successful-searches', data.successful_searches || 0);
                animateCounter('#total-searches-small', data.total_searches || 0);

                // Se stai usando la struttura alternativa, aggiungi anche questi:
                if (document.getElementById('today-searches')) {
                    animateCounter('#today-searches', data.today_searches || 0);
                    animateCounter('#yesterday-searches', data.yesterday_searches || 0);
                }

                // Calcola tasso di successo
                const successRate = data.total_searches > 0 ?
                    Math.round((data.successful_searches / data.total_searches) * 100) : 0;
                animateCounter('#success-rate', successRate, '%');

                // Calcola percentuale utenti registrati
                const registeredPct = data.total_searches > 0 ?
                    Math.round((data.registered_users_searches / data.total_searches) * 100) : 0;
                animateCounter('#registered-users-pct', registeredPct, '%');

                // Durata media
                $('#avg-duration').text(Math.round(data.avg_search_duration || 0));
                $('#avg-satisfaction').text(Math.round(data.avg_satisfaction || 0));

                // Variazione giornaliera con testo più chiaro
                const changePercent = data.daily_change_percent || 0;
                const changeClass = changePercent >= 0 ? 'text-success' : 'text-danger';
                const changeIcon = changePercent >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                const changePrefix = changePercent > 0 ? '+' : '';

                $('#daily-change')
                    .html(`<i class="fas ${changeIcon}"></i> ${changePrefix}${changePercent}%`)
                    .removeClass('text-success text-danger text-muted')
                    .addClass(changeClass);

                // Rapporto registrati vs ospiti
                $('#registered-vs-guest').text(`${data.registered_users_searches} vs ${data.guest_searches}`);
            }

            // Funzione aggiuntiva per mostrare tooltip esplicativo
            function addDailyChangeTooltip() {
                const dailyChangeElement = document.getElementById('daily-change');
                if (dailyChangeElement && typeof bootstrap !== 'undefined') {
                    new bootstrap.Tooltip(dailyChangeElement, {
                        title: 'Variazione percentuale delle ricerche di oggi rispetto a ieri',
                        placement: 'top',
                        trigger: 'hover focus'
                    });
                }
            }

            async function loadCharts() {
                await loadTrendsChart(30);
                await loadDevicesChart();
            }

            async function loadTrendsChart(days = 30) {
                try {
                    const response = await fetch(`/api/analytics/search-trends?days=${days}`);
                    const data = await response.json();

                    if (response.ok) {
                        updateTrendsChart(data);
                    }
                } catch (error) {
                    console.error('Errore caricamento trend:', error);
                }
            }

            function updateTrendsChart(data) {
                const ctx = document.getElementById('trends-chart').getContext('2d');

                if (trendsChart) {
                    trendsChart.destroy();
                }

                const labels = data.map(item => moment(item.date).format('DD/MM'));
                const searches = data.map(item => item.total_searches);
                const successful = data.map(item => item.successful_searches);
                const satisfaction = data.map(item => Math.round(item.avg_satisfaction || 0));

                trendsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ricerche Totali',
                            data: searches,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y'
                        }, {
                            label: 'Ricerche Riuscite',
                            data: successful,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y'
                        }, {
                            label: 'Soddisfazione Media (%)',
                            data: satisfaction,
                            borderColor: '#ffc107',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Numero Ricerche'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Soddisfazione (%)'
                                },
                                grid: {
                                    drawOnChartArea: false,
                                },
                                max: 100
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        }
                    }
                });
            }

            async function loadDevicesChart() {
                try {
                    const response = await fetch('/api/analytics/device-stats');
                    const data = await response.json();

                    if (response.ok) {
                        updateDevicesChart(data.devices);
                    }
                } catch (error) {
                    console.error('Errore caricamento dispositivi:', error);
                }
            }

            function updateDevicesChart(devices) {
                const ctx = document.getElementById('devices-chart').getContext('2d');

                if (devicesChart) {
                    devicesChart.destroy();
                }

                const labels = devices.map(d => {
                    console.log(d.device_type);

                    switch (d.device_type) {
                        case 'mobile':
                            return 'Mobile';
                        case 'tablet':
                            return 'Tablet';
                        case 'desktop':
                            return 'Desktop';
                        default:
                            return 'Altro';
                    }
                });
                const counts = devices.map(d => d.count);
                const colors = ['#007bff', '#28a745', '#ffc107', '#dc3545'];

                devicesChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: counts,
                            backgroundColor: colors.slice(0, labels.length),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            async function loadGeographicData() {
                try {
                    const response = await fetch('/api/analytics/geographic-stats');
                    const data = await response.json();

                    if (response.ok) {
                        updateCountriesList(data.countries);
                    }
                } catch (error) {
                    console.error('Errore caricamento dati geografici:', error);
                }
            }

            function updateCountriesList(countries) {
                console.log(countries);

                const container = $('#countries-list');
                container.empty();

                if (countries.length === 0) {
                    container.html('<div class="text-center py-3 text-muted">Nessun dato disponibile</div>');
                    return;
                }

                countries.forEach(country => {
                    const flag = getCountryFlag(country.country);
                    container.append(`
                <div class="list-group-item">
                    <div class="country-item">
                        <span class="country-name">${flag} ${country.country}</span>
                        <span class="country-count">${country.searches}</span>
                    </div>
                </div>
            `);
                });
            }


            async function loadSearchParameters() {
                try {
                    const response = await fetch('/api/analytics/search-parameters');
                    const data = await response.json();

                    if (response.ok) {
                        // Aggiorna contatore porti
                        const portsCount = (data.popular_ports || []).length;
                        document.getElementById('ports-count').textContent = portsCount;

                        // Aggiorna lista porti con il nuovo design
                        updatePortsList(data.popular_ports || []);

                        // Aggiorna altri grafici
                        updateBudgetChart(data.budget_ranges);
                        updateParticipantsChart(data.participants);
                    }
                } catch (error) {
                    console.error('Errore caricamento parametri ricerca:', error);

                    // Mostra stato di errore per i porti
                    const container = $('#ports-list');
                    container.html(`
                                    <div class="ports-empty text-center py-4">
                                        <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                                        <h6 class="text-danger">Errore di Caricamento</h6>
                                        <p class="text-muted mb-0 small">Impossibile caricare i dati dei porti.</p>
                                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadSearchParameters()">
                                            <i class="fas fa-redo me-1"></i>Riprova
                                        </button>
                                    </div>
                                `);

                    // Reset contatore
                    document.getElementById('ports-count').textContent = '0';
                }
            }

            function updatePortsList(ports) {
                const container = $('#ports-list');

                container.empty();

                if (ports.length === 0) {
                    container.html(`
                        <div class="ports-empty text-center py-4">
                            <i class="fas fa-anchor fa-3x text-muted mb-3" style="opacity: 0.5;"></i>
                            <h6 class="text-muted">Nessun porto trovato</h6>
                            <p class="text-muted mb-0 small">Non ci sono dati sui porti disponibili al momento.</p>
                        </div>
                    `);
                    return;
                }

                const maxVisible = 8; // Mostra solo 8 porti inizialmente
                const shouldShowViewMore = ports.length > maxVisible;
                let isExpanded = false;

                function renderPorts(showAll = false) {
                    const portsToShow = showAll ? ports : ports.slice(0, maxVisible);
                    container.empty();

                    portsToShow.forEach((port, index) => {
                        const isTop3 = index < 3;
                        const successRate = port.searches > 0 ?
                            Math.round((port.searches_with_results / port.searches) * 100) : 0;

                        const portIcon = getPortIcon(port.port_start);

                        const portElement = $(`
                                <div class="pt-4 pb-4 port-pill ${isTop3 ? 'top-port' : ''}" style="opacity: 0; transform: translateX(-20px);">
                                    <div class="port-info">
                                        <div class="port-icon">
                                            ${portIcon}
                                        </div>
                                        <div class="port-details">
                                            <div class="port-name" title="${port.port_start}">
                                                ${port.port_start}
                                            </div>
                                            <div class="port-stats">
                                                <div class="port-stat">
                                                    <i class="fas fa-star"></i>
                                                    <span>${Math.round(port.avg_satisfaction || 0)}%</span>
                                                </div>
                                                <div class="port-stat">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span>${successRate}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mr-2 port-count">${port.searches}</div>
                                    ${isTop3 ? `<div class="mr-3 rank-badge">${index + 1}</div>` : ''}
                                </div>
            `);

                        // Hover effect con dettagli aggiuntivi
                        portElement.hover(
                            function() {
                                $(this).attr('title',
                                    `${port.port_start}\nRicerche: ${port.searches}\nSoddisfazione: ${Math.round(port.avg_satisfaction || 0)}%\nSuccessi: ${port.searches_with_results}/${port.searches}`
                                );
                            }
                        );

                        container.append(portElement);

                        // Animazione di entrata
                        setTimeout(() => {
                            portElement.css({
                                'opacity': '1',
                                'transform': 'translateX(0)',
                                'transition': 'all 0.4s ease'
                            });
                        }, index * 100);
                    });

                    // Aggiungi bottone "Visualizza tutti" se necessario
                    if (shouldShowViewMore) {
                        const viewMoreBtn = $(`
                <div class="view-more-btn" style="cursor: pointer;">
                    <i class="fas fa-chevron-${isExpanded ? 'up' : 'down'} me-2"></i>
                    ${isExpanded ? 'Mostra meno porti' : 'Visualizza tutti i porti'}
                </div>
            `);

                        viewMoreBtn.click(function() {
                            isExpanded = !isExpanded;
                            renderPorts(isExpanded);
                        });

                        container.append(viewMoreBtn);
                    }
                }

                // Inizializza con vista limitata
                renderPorts(false);
            }

            function getPortIcon(portName) {
                const name = portName.toLowerCase();
                if (name.includes('roma') || name.includes('civitavecchia')) return '🏛️';
                if (name.includes('barcellona')) return '🏰';
                if (name.includes('venezia')) return '🚤';
                if (name.includes('genova')) return '⚓';
                if (name.includes('napoli')) return '🌋';
                if (name.includes('palermo')) return '🍊';
                if (name.includes('marsiglia')) return '🇫🇷';
                if (name.includes('savona')) return '🌊';
                if (name.includes('bari')) return '🏖️';
                if (name.includes('trieste')) return '🏰';
                if (name.includes('cagliari') || name.includes('olbia')) return '🏝️';
                return '🏖️';
            }

            function updateBudgetChart(budgetRanges) {
                const ctx = document.getElementById('budget-chart').getContext('2d');

                if (budgetChart) {
                    budgetChart.destroy();
                }

                console.log('📊 Dati budget ricevuti:', budgetRanges);

                // Controllo se i dati sono una Collection Laravel (hanno la proprietà *items)
                let budgetData = budgetRanges;
                if (budgetRanges && typeof budgetRanges === 'object' && budgetRanges['*items']) {
                    budgetData = budgetRanges['*items'];
                    console.log('🔄 Convertiti dati Collection Laravel:', budgetData);
                }

                // Controlla se abbiamo dati validi
                if (!budgetData || typeof budgetData !== 'object' || Object.keys(budgetData).length === 0) {
                    console.warn('⚠️ Nessun dato budget valido:', budgetData);
                    showBudgetChartError();
                    return;
                }

                // Prepara i dati per Chart.js
                const labels = [];
                const values = [];
                const satisfactionData = [];
                const backgroundColors = [
                    'rgba(40, 167, 69, 0.8)', // Verde per budget bassi
                    'rgba(0, 123, 255, 0.8)', // Blu per budget medi
                    'rgba(255, 193, 7, 0.8)', // Giallo per budget medio-alti
                    'rgba(255, 99, 132, 0.8)', // Rosa per budget alti
                    'rgba(153, 102, 255, 0.8)' // Viola per budget molto alti
                ];
                const borderColors = [
                    'rgba(40, 167, 69, 1)',
                    'rgba(0, 123, 255, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(153, 102, 255, 1)'
                ];

                // Ordine predefinito per i range
                const rangeOrder = ['0-999', '1000-1999', '2000-2999', '3000-4999', '5000+'];

                rangeOrder.forEach((range, index) => {
                    const rangeData = budgetData[range];
                    if (rangeData && rangeData.count > 0) {
                        labels.push(`€${range}`);
                        values.push(rangeData.count || 0);
                        satisfactionData.push(rangeData.avg_satisfaction || 0);
                    }
                });

                console.log('📈 Dati elaborati per grafico budget:', {
                    labels,
                    values,
                    satisfactionData
                });

                // Se non abbiamo dati da visualizzare
                if (labels.length === 0 || values.every(v => v === 0)) {
                    console.warn('⚠️ Nessun dato budget da visualizzare');
                    showBudgetChartEmpty();
                    return;
                }

                // Crea il grafico con i dati reali
                budgetChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Numero Ricerche',
                            data: values,
                            backgroundColor: backgroundColors.slice(0, labels.length),
                            borderColor: borderColors.slice(0, labels.length),
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Numero di Ricerche',
                                    font: {
                                        weight: 600,
                                        size: 12
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    },
                                    stepSize: 1 // Per valori piccoli
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Range Budget',
                                    font: {
                                        weight: 600,
                                        size: 12
                                    }
                                },
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: 'white',
                                bodyColor: 'white',
                                borderColor: 'rgba(255, 255, 255, 0.2)',
                                borderWidth: 1,
                                cornerRadius: 8,
                                titleFont: {
                                    weight: 600
                                },
                                callbacks: {
                                    title: function(context) {
                                        return `Budget ${context[0].label}`;
                                    },
                                    label: function(context) {
                                        return `Ricerche: ${context.raw}`;
                                    },
                                    afterBody: function(context) {
                                        const index = context[0].dataIndex;
                                        const satisfaction = satisfactionData[index];
                                        return [
                                            '',
                                            `💯 Soddisfazione media: ${satisfaction}%`,
                                            satisfaction > 70 ? '✨ Range ad alta soddisfazione' :
                                            '📈 Potenziale di miglioramento'
                                        ];
                                    }
                                }
                            }
                        },
                        animation: {
                            duration: 1500,
                            easing: 'easeOutQuart'
                        },
                        onHover: (event, activeElements) => {
                            event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' :
                                'default';
                        }
                    }
                });

                console.log('✅ Grafico budget creato con successo con', labels.length, 'range');
            }


            // Funzioni per gestire stati vuoti
            function showBudgetChartEmpty() {
                const ctx = document.getElementById('budget-chart').getContext('2d');

                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                ctx.font = '16px Arial';
                ctx.fillStyle = '#6c757d';
                ctx.textAlign = 'center';

                // Disegna icona e testo
                ctx.fillText('📊', ctx.canvas.width / 2, ctx.canvas.height / 2 - 20);
                ctx.font = '14px Arial';
                ctx.fillText('Nessun dato budget disponibile', ctx.canvas.width / 2, ctx.canvas.height / 2 + 5);
                ctx.font = '12px Arial';
                ctx.fillStyle = '#007bff';
                ctx.fillText('I dati verranno mostrati dopo le prime ricerche', ctx.canvas.width / 2, ctx.canvas
                    .height / 2 + 25);
            }

            function showParticipantsChartEmpty() {
                const ctx = document.getElementById('participants-chart').getContext('2d');

                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                ctx.font = '16px Arial';
                ctx.fillStyle = '#6c757d';
                ctx.textAlign = 'center';

                ctx.fillText('👥', ctx.canvas.width / 2, ctx.canvas.height / 2 - 20);
                ctx.font = '14px Arial';
                ctx.fillText('Nessun dato partecipanti disponibile', ctx.canvas.width / 2, ctx.canvas.height / 2 +
                    5);
                ctx.font = '12px Arial';
                ctx.fillStyle = '#007bff';
                ctx.fillText('I dati verranno mostrati dopo le prime ricerche', ctx.canvas.width / 2, ctx.canvas
                    .height / 2 + 25);
            }

            function updateParticipantsChart(participants) {
                const ctx = document.getElementById('participants-chart').getContext('2d');

                if (participantsChart) {
                    participantsChart.destroy();
                }

                console.log('👥 Dati partecipanti ricevuti:', participants);

                // Controllo se i dati sono una Collection Laravel
                let participantsData = participants;
                if (participants && typeof participants === 'object' && participants['*items']) {
                    participantsData = participants['*items'];
                    console.log('🔄 Convertiti dati Collection partecipanti:', participantsData);
                }

                // Se è un array normale, usalo direttamente
                if (Array.isArray(participantsData)) {
                    console.log('📊 Usando array normale per partecipanti');
                } else if (participantsData && typeof participantsData === 'object') {
                    // Se è un oggetto, convertilo in array
                    participantsData = Object.values(participantsData);
                    console.log('🔄 Convertito oggetto in array per partecipanti:', participantsData);
                } else {
                    console.warn('⚠️ Dati partecipanti non validi:', participantsData);
                    showParticipantsChartEmpty();
                    return;
                }

                if (!participantsData || participantsData.length === 0) {
                    console.warn('⚠️ Nessun dato partecipanti disponibile');
                    showParticipantsChartEmpty();
                    return;
                }

                // Prepara dati per Chart.js
                const labels = participantsData.map(p =>
                    `${p.participants} ${p.participants === 1 ? 'persona' : 'persone'}`
                );
                const values = participantsData.map(p => p.count || 0);
                const budgetData = participantsData.map(p => p.avg_budget || 0);

                console.log('📈 Dati elaborati per grafico partecipanti:', {
                    labels,
                    values,
                    budgetData
                });

                participantsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Numero Ricerche',
                            data: values,
                            backgroundColor: 'rgba(40, 167, 69, 0.8)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Numero di Ricerche',
                                    font: {
                                        weight: 600,
                                        size: 12
                                    }
                                },
                                ticks: {
                                    stepSize: 1
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Numero Partecipanti',
                                    font: {
                                        weight: 600,
                                        size: 12
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: 'white',
                                bodyColor: 'white',
                                callbacks: {
                                    afterBody: function(context) {
                                        const index = context[0].dataIndex;
                                        const avgBudget = budgetData[index];
                                        const satisfaction = participantsData[index].avg_satisfaction;
                                        return [
                                            '',
                                            `💰 Budget medio: €${Math.round(avgBudget)}`,
                                            `😊 Soddisfazione: ${satisfaction}%`
                                        ];
                                    }
                                }
                            }
                        }
                    }
                });

                console.log('✅ Grafico partecipanti creato con successo');
            }


            async function loadPerformanceMetrics() {
                try {
                    const response = await fetch('/api/analytics/performance-metrics');
                    const data = await response.json();

                    if (response.ok) {
                        updatePerformanceMetrics(data);
                    }
                } catch (error) {
                    console.error('Errore caricamento metriche performance:', error);
                }
            }

            function updatePerformanceMetrics(data) {
                const metrics = data.overall_metrics;
                const devicePerformance = data.device_performance;

                $('#avg-duration-detailed').text(Math.round(metrics.avg_duration || 0) + 'ms');
                $('#p95-duration').text(Math.round(metrics.p95_duration || 0) + 'ms');
                $('#slow-searches').text(metrics.slow_searches || 0);
                $('#failed-searches').text(metrics.failed_searches || 0);

                // Performance per dispositivo
                const mobilePerf = devicePerformance.find(d => d.device_type === 'mobile');
                const desktopPerf = devicePerformance.find(d => d.device_type === 'desktop');

                $('#mobile-performance').text(Math.round(mobilePerf?.avg_duration || 0) + 'ms');
                $('#desktop-performance').text(Math.round(desktopPerf?.avg_duration || 0) + 'ms');
            }

            async function loadSearchLogs() {
                try {
                    console.log('📋 Caricamento log ricerche...');

                    const params = new URLSearchParams({
                        page: currentPage,
                        per_page: 10,
                        user_type: $('#filter-user-type').val() || '',
                        device_type: $('#filter-device').val() || '',
                        successful_only: $('#filter-success').val() || ''
                    });

                    const response = await fetch(`/api/analytics/search-logs?${params}`);
                    const data = await response.json();

                    console.log('📊 Dati log ricevuti:', data);

                    if (response.ok && data.data) {
                        updateSearchLogsTable(data.data);
                        updatePagination(data);
                    } else {
                        throw new Error(data.error || 'Errore caricamento log');
                    }
                } catch (error) {
                    console.error('❌ Errore caricamento log:', error);
                    showSearchLogsError(error.message);
                }
            }

            function updateSearchLogsTable(logs) {
                const tbody = $('#search-logs-table tbody');
                tbody.empty();

                console.log('🔍 Aggiornamento tabella con', logs.length, 'log');

                if (!logs || logs.length === 0) {
                    tbody.html(`
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-search fa-2x text-muted mb-3"></i><br>
                                            <h6 class="text-muted">Nessun log trovato</h6>
                                            <p class="text-muted mb-0">Prova a modificare i filtri o controlla se ci sono ricerche registrate.</p>
                                        </td>
                                    </tr>
                                `);
                    return;
                }

                logs.forEach((log, index) => {
                    console.log(`📝 Elaborando log ${index + 1}:`, log);

                    // Gestione utente con fallback sicuri
                    const userName = log.user_name || 'Ospite';
                    const userType = log.user_type || 'Ospite';
                    const userBadge = log.is_guest === false ?
                        '<span class="user-type-badge">Registrato</span>' :
                        '<span class="user-type-badge" style="background: #fff3cd; color: #856404;">Ospite</span>';

                    // Gestione device con fallback
                    const deviceType = log.device_type || 'N/D';
                    const deviceIcon = getDeviceIcon(deviceType);
                    const operatingSystem = log.operating_system || '';
                    const browser = log.browser || '';

                    // Gestione parametri ricerca
                    const dateRange = log.date_range || 'N/D';
                    const budget = log.budget || 0;
                    const participants = log.participants || 0;
                    const portStart = log.port_start || '';

                    const parameters = `
                                            <div class="search-params">
                                                <div><strong>${dateRange}</strong></div>
                                                <div class="text-muted small">€${budget.toLocaleString('it-IT')} (${participants} pers.)</div>
                                                ${portStart ? `<div class="text-info small">📍 ${portStart}</div>` : ''}
                                            </div>
                                        `;

                    // Gestione risultati
                    const totalMatches = log.total_matches || 0;
                    const totalAlternatives = log.total_alternatives || 0;
                    const satisfactionScore = log.satisfaction_score || 0;

                    const results = `
                                        <div class="search-results">
                                            <div><strong>${totalMatches}</strong> match</div>
                                            <div><strong>${totalAlternatives}</strong> alternative</div>
                                        </div>
                                    `;

                    // Gestione performance
                    const searchDuration = log.search_duration_ms || 0;
                    const performance = `
                                            <div class="search-performance">
                                                <div><strong>${searchDuration}ms</strong></div>
                                                <div class="text-muted small">Soddisf: ${Math.round(satisfactionScore)}%</div>
                                            </div>
                                        `;

                    // Gestione location
                    const country = log.country || 'N/D';
                    const city = log.city || '';
                    const location = `
                                        <div class="search-location">
                                            <div>${getCountryFlag(country)} <strong>${country}</strong></div>
                                            ${city ? `<div class="text-muted small">${city}</div>` : ''}
                                        </div>
                                    `;

                    // Gestione stato
                    const searchSuccessful = log.search_successful;
                    const statusBadge = searchSuccessful ?
                        '<span class="status-badge status-success"><i class="fas fa-check-circle"></i> Successo</span>' :
                        '<span class="status-badge status-error"><i class="fas fa-times-circle"></i> Errore</span>';

                    // Gestione device display
                    const deviceDisplay = `
                                            <div class="device-info">
                                                <div>${deviceIcon} <span class="device-badge">${deviceType}</span></div>
                                                ${operatingSystem ? `<div class="text-muted small">${operatingSystem}</div>` : ''}
                                                ${browser ? `<div class="text-muted small">${browser}</div>` : ''}
                                            </div>
                                            `;

                    // Creazione riga tabella
                    const row = $(`
                                    <tr class="log-row" data-log-id="${log.id}">
                                        <td class="text-nowrap">
                                            <div class="log-date">${log.search_date}</div>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <div><strong>${userName}</strong></div>
                                                <div>${userBadge}</div>
                                                ${log.user_email ? `<div class="text-muted small">${log.user_email}</div>` : ''}
                                            </div>
                                        </td>
                                        <td>${deviceDisplay}</td>
                                        <td>${parameters}</td>
                                        <td>${results}</td>
                                        <td>${performance}</td>
                                        <td>${location}</td>
                                        <td>${statusBadge}</td>
                                    </tr>
                                `);

                    // Hover effect
                    row.hover(
                        function() {
                            $(this).addClass('table-row-hover');
                            if (log.error_message) {
                                $(this).attr('title', 'Errore: ' + log.error_message);
                            }
                        },
                        function() {
                            $(this).removeClass('table-row-hover');
                        }
                    );

                    tbody.append(row);

                    // Animazione di entrata
                    setTimeout(() => {
                        row.addClass('fade-in-row');
                    }, index * 50);
                });

                console.log('✅ Tabella log aggiornata con', logs.length, 'righe');
            }

            function showSearchLogsError(errorMessage) {
                const tbody = $('#search-logs-table tbody');
                tbody.html(`
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i><br>
                                        <h6 class="text-danger">Errore Caricamento Log</h6>
                                        <p class="text-muted mb-2">${errorMessage}</p>
                                        <button class="btn btn-sm btn-outline-primary" onclick="loadSearchLogs()">
                                            <i class="fas fa-redo me-1"></i>Riprova
                                        </button>
                                    </td>
                                </tr>
                            `);
            }

            function updatePagination(data) {
                const pagination = $('#pagination');
                pagination.empty();

                const totalPages = data.last_page;
                const currentPageNum = data.current_page;

                // Previous button
                if (currentPageNum > 1) {
                    pagination.append(`
                                        <li class="page-item">
                                            <a class="page-link" href="#" data-page="${currentPageNum - 1}">Precedente</a>
                                        </li>
                                    `);
                }

                // Page numbers
                for (let i = Math.max(1, currentPageNum - 2); i <= Math.min(totalPages, currentPageNum + 2); i++) {
                    const activeClass = i === currentPageNum ? 'active' : '';
                    pagination.append(`
                                        <li class="page-item ${activeClass}">
                                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                                        </li>
                                    `);
                }

                // Next button
                if (currentPageNum < totalPages) {
                    pagination.append(`
                                        <li class="page-item">
                                            <a class="page-link" href="#" data-page="${currentPageNum + 1}">Successiva</a>
                                        </li>
                                    `);
                }

                // Event listeners for pagination
                pagination.find('a').click(function(e) {
                    e.preventDefault();
                    currentPage = parseInt($(this).data('page'));
                    loadSearchLogs();
                });
            }

            function exportData() {
                const formData = new FormData(document.getElementById('export-form'));
                const params = new URLSearchParams(formData);

                window.location.href = `/admin/analytics/export?${params}`;
                $('#exportModal').modal('hide');
            }

            function refreshAllData() {
                const btn = $('#refresh-btn');
                const originalHtml = btn.html();

                btn.html('<i class="fas fa-sync-alt fa-spin"></i> Aggiornamento...')
                    .prop('disabled', true);

                Promise.all([
                    loadGeneralStats(),
                    loadCharts(),
                    loadGeographicData(),
                    loadSearchParameters(),
                    loadPerformanceMetrics(),
                    loadSearchLogs()
                ]).finally(() => {
                    btn.html(originalHtml).prop('disabled', false);
                    showSuccess('Dati aggiornati con successo!');
                });
            }

            // Utility functions
            function animateCounter(selector, target, suffix = '') {
                const element = $(selector);
                const start = 0;
                const duration = 1500;
                const startTime = performance.now();

                function updateCounter(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);

                    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                    const current = Math.floor(start + (target - start) * easeOutQuart);

                    element.text(current + suffix);

                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    }
                }

                requestAnimationFrame(updateCounter);
            }

            // Utility functions aggiornate
            function getCountryFlag(country) {
                const flags = {
                    'Italy': '🇮🇹',
                    'Italia': '🇮🇹',
                    'United States': '🇺🇸',
                    'Germany': '🇩🇪',
                    'Germania': '🇩🇪',
                    'France': '🇫🇷',
                    'Francia': '🇫🇷',
                    'Spain': '🇪🇸',
                    'Spagna': '🇪🇸',
                    'United Kingdom': '🇬🇧',
                    'Netherlands': '🇳🇱',
                    'Paesi Bassi': '🇳🇱',
                    'Austria': '🇦🇹',
                    'Switzerland': '🇨🇭',
                    'Svizzera': '🇨🇭',
                    'Local': '🏠',
                    'N/D': '🌍'
                };
                return flags[country] || '🌍';
            }

            function getDeviceIcon(deviceType) {
                const icons = {
                    'mobile': '📱',
                    'tablet': '📲',
                    'desktop': '💻',
                    'N/D': '❓'
                };
                return icons[deviceType] || '❓';
            }

            function showSuccess(message) {
                // Implementa toast di successo
                console.log('Success:', message);
            }

            function showError(message) {
                // Implementa toast di errore
                console.error('Error:', message);
            }
        });
    </script>
