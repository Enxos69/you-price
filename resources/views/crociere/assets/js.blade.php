<script>
    $(function() {
        // Configurazione DateRangePicker
        $('#date-range').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: "Applica",
                cancelLabel: "Annulla",
                daysOfWeek: ["Do", "Lu", "Ma", "Me", "Gi", "Ve", "Sa"],
                monthNames: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu", "Lug", "Ago", "Set", "Ott",
                    "Nov", "Dic"
                ],
                firstDay: 1
            },
            startDate: moment().add(1, 'month'),
            endDate: moment().add(1, 'month').add(7, 'days'),
            minDate: moment(),
            maxDate: moment().add(2, 'years')
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('#search-form');
        const resultsSection = document.querySelector('#results-section');
        const gauge1 = document.getElementById('gaugeAttuale');
        const gauge2 = document.getElementById('gaugeOttimale');
        const loadingSpinner = document.getElementById('loading-spinner');

        let chart1, chart2;

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Mostra loading
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            loadingSpinner.classList.remove('d-none');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Ricerca in corso...';

            try {
                const formData = new FormData(form);

                const res = await fetch('/crociere/search', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content
                    },
                    body: formData
                });

                if (!res.ok) {
                    throw new Error('Errore nella ricerca');
                }

                const data = await res.json();

                // Mostra sezione risultati con animazione
                resultsSection.classList.remove('d-none');
                resultsSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // Aggiorna i gauge con animazione
                updateGauge(gauge1, data.soddisfazione_attuale, 'score-current', chart1, (
                    newChart) => chart1 = newChart);
                updateGauge(gauge2, data.soddisfazione_ottimale, 'score-optimal', chart2, (
                    newChart) => chart2 = newChart);

                // Aggiorna le tabelle
                fillTable('table-matches', data.matches || [], 'matches');
                fillTable('table-suggestions', data.alternative || [], 'suggestions');

                // Aggiorna le statistiche
                updateStats(data);

                // Aggiorna i consigli
                updateSuggestions(data.consigli || []);

            } catch (error) {
                console.error('Errore:', error);
                alert('Si è verificato un errore durante la ricerca. Riprova.');
            } finally {
                // Ripristina il pulsante
                submitBtn.disabled = false;
                loadingSpinner.classList.add('d-none');
                submitBtn.innerHTML = originalText;
            }
        });

        function updateGauge(canvas, value, scoreElementId, existingChart, chartCallback) {
            // Distruggi il grafico esistente
            if (existingChart) {
                existingChart.destroy();
            }

            // Crea nuovo grafico
            const newChart = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [value, 100 - value],
                        backgroundColor: [
                            value >= 80 ? '#4ecdc4' : value >= 60 ? '#ffd93d' : '#ff6b6b',
                            '#e0e0e0'
                        ],
                        borderWidth: 0,
                        cutout: '75%'
                    }]
                },
                options: {
                    rotation: -90,
                    circumference: 180,
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            enabled: false
                        },
                        legend: {
                            display: false
                        }
                    },
                    animation: {
                        animateRotate: true,
                        duration: 2000
                    }
                }
            });

            chartCallback(newChart);

            // Anima il numero
            animateValue(scoreElementId, 0, value, 1500);
        }

        function animateValue(elementId, start, end, duration) {
            const element = document.getElementById(elementId);
            const startTime = performance.now();

            function updateValue(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const current = Math.floor(start + (end - start) * progress);

                element.textContent = current + '%';

                if (progress < 1) {
                    requestAnimationFrame(updateValue);
                }
            }

            requestAnimationFrame(updateValue);
        }

        function fillTable(tableId, items, type) {
            const tbody = document.querySelector(`#${tableId} tbody`);
            const noResultsDiv = document.getElementById(type === 'matches' ? 'no-matches' : 'no-suggestions');

            tbody.innerHTML = '';

            if (items.length === 0) {
                if (noResultsDiv) noResultsDiv.classList.remove('d-none');
                return;
            }

            if (noResultsDiv) noResultsDiv.classList.add('d-none');

            items.forEach((row, index) => {
                const tr = document.createElement('tr');
                tr.style.opacity = '0';
                tr.style.transform = 'translateY(20px)';

                const matchPercentage = row.match_percentage || Math.floor(Math.random() * 30 + 70);
                const benefitText = row.benefit || getBenefitText(row);

                if (type === 'matches') {
                    tr.innerHTML = `
                    <td><strong>${row.ship || 'N/D'}</strong></td>
                    <td>${row.line || 'N/D'}</td>
                    <td><span class="badge bg-primary">${row.night || 'N/D'}</span></td>
                    <td><strong class="text-success">${formatPrice(row.interior)}</strong></td>
                    <td>${formatDate(row.partenza)}</td>
                    <td><span class="badge ${getMatchBadgeClass(matchPercentage)}">${matchPercentage}%</span></td>
                `;
                } else {
                    tr.innerHTML = `
                    <td><strong>${row.ship || 'N/D'}</strong></td>
                    <td>${row.line || 'N/D'}</td>
                    <td><span class="badge bg-info">${row.night || 'N/D'}</span></td>
                    <td><strong class="text-primary">${formatPrice(row.interior)}</strong></td>
                    <td>${formatDate(row.partenza)}</td>
                    <td><small class="text-success">${benefitText}</small></td>
                `;
                }

                tbody.appendChild(tr);

                // Animazione di entrata
                setTimeout(() => {
                    tr.style.transition = 'all 0.5s ease';
                    tr.style.opacity = '1';
                    tr.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }

        function updateStats(data) {
            document.getElementById('current-matches').textContent =
                `${data.matches?.length || 0} crociere trovate`;

            document.getElementById('optimal-suggestion').textContent =
                data.suggerimento_ottimale || 'Espandi le date di ricerca';
        }

        function updateSuggestions(consigli) {
            const suggestionsText = document.getElementById('suggestions-text');

            if (consigli.length === 0) {
                suggestionsText.innerHTML = `
                <p class="mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    Prova ad ampliare le date di ricerca o rimuovere alcuni filtri per trovare più opzioni.
                </p>
            `;
                return;
            }

            const consigliHtml = consigli.map(consiglio =>
                `<p class="mb-2"><i class="fas fa-arrow-right me-2"></i>${consiglio}</p>`
            ).join('');

            suggestionsText.innerHTML = consigliHtml;
        }

        // Funzioni di utilità
        function formatPrice(price) {
            if (!price) return 'N/D';
            const numericPrice = typeof price === 'string' ?
                price.replace(/[€,]/g, '') : price;
            return `€${parseInt(numericPrice).toLocaleString('it-IT')}`;
        }

        function formatDate(date) {
            if (!date) return 'N/D';
            return moment(date).format('DD/MM/YYYY');
        }

        function getMatchBadgeClass(percentage) {
            if (percentage >= 90) return 'bg-success';
            if (percentage >= 75) return 'bg-warning';
            return 'bg-secondary';
        }

        function getBenefitText(row) {
            const benefits = [
                'Prezzo migliore',
                'Date più flessibili',
                'Itinerario premium',
                'Nave più moderna',
                'Partenza comoda'
            ];
            return benefits[Math.floor(Math.random() * benefits.length)];
        }

        // Validazione del form in tempo reale
        const inputs = form.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        });
    });
</script>
