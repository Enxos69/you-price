<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    let priceChart           = null;
    let seasonalChart        = null;
    let seasonalMonthlyData  = null;
    let currentDepartureId   = null;
    let activeSeasonalTab    = 'weekly';

    loadTopVariations();

    document.getElementById('refresh-btn')
        .addEventListener('click', loadTopVariations);

    document.getElementById('search-form')
        .addEventListener('submit', function (e) {
            e.preventDefault();
            loadSearch();
        });

    // ── Tab stagionale ────────────────────────────────────────────────────────
    document.getElementById('seasonal-tabs')
        .addEventListener('click', function (e) {
            e.preventDefault();
            const link = e.target.closest('[data-tab]');
            if (!link || !currentDepartureId) return;

            document.querySelectorAll('#seasonal-tabs .nav-link')
                .forEach(function (l) { l.classList.remove('active'); });
            link.classList.add('active');
            activeSeasonalTab = link.dataset.tab;

            const cat = document.getElementById('seasonal-category').value;
            if (activeSeasonalTab === 'monthly') {
                if (seasonalMonthlyData) {
                    renderSeasonalMonthlyChart(seasonalMonthlyData);
                } else {
                    loadSeasonalMonthly(currentDepartureId, cat);
                }
            } else {
                loadSeasonalWeekly(currentDepartureId, cat);
            }
        });

    // ── Selettore categoria stagionale ────────────────────────────────────────
    document.getElementById('seasonal-category')
        .addEventListener('change', function () {
            if (!currentDepartureId) return;
            seasonalMonthlyData = null;
            if (activeSeasonalTab === 'monthly') {
                loadSeasonalMonthly(currentDepartureId, this.value);
            } else {
                loadSeasonalWeekly(currentDepartureId, this.value);
            }
        });

    document.getElementById('season-select')
        .addEventListener('change', function () {
            const year = new Date().getFullYear();
            const ranges = {
                spring: [year + '-03-01', year + '-05-31'],
                summer: [year + '-06-01', year + '-08-31'],
                autumn: [year + '-09-01', year + '-11-30'],
                winter: [year + '-12-01', (year + 1) + '-02-28'],
            };
            const sel = ranges[this.value];
            document.getElementById('from-date').value = sel ? sel[0] : '';
            document.getElementById('to-date').value   = sel ? sel[1] : '';
        });

    // ── Top 10 ───────────────────────────────────────────────────────────────

    async function loadTopVariations() {
        showSpinner('top10-container');
        try {
            const res  = await fetch('/api/admin/price-history/top-variations');
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Errore');
            renderTop10(data);
        } catch (e) {
            showError('top10-container', 'Errore nel caricamento dati, riprova.');
        }
    }

    function renderTop10(data) {
        const container = document.getElementById('top10-container');

        if (data.insufficient_data || !data.data.length) {
            container.innerHTML =
                '<div class="alert alert-info mb-0">Dati insufficienti — ' +
                'lo storico si arricchirà con le prossime importazioni del catalogo.</div>';
            return;
        }

        let html = '<div class="table-responsive">' +
            '<table class="table table-hover table-sm mb-0"><thead><tr>' +
            '<th>Crociera</th><th>Partenza</th><th>Categoria</th>' +
            '<th class="text-right">Prezzo attuale</th>' +
            '<th class="text-right">Prezzo 30gg fa</th>' +
            '<th class="text-right">Δ €</th>' +
            '<th class="text-right">Δ %</th>' +
            '</tr></thead><tbody>';

        data.data.forEach(function (row) {
            const cls  = parseFloat(row.delta_eur) < 0 ? 'text-success' : 'text-danger';
            const sign = parseFloat(row.delta_eur) > 0 ? '+' : '';
            html += '<tr class="top10-row"' +
                ' data-departure-id="' + row.departure_id + '"' +
                ' data-cruise-name="' + escHtml(row.cruise_name) + '">' +
                '<td>' + escHtml(row.cruise_name) + '</td>' +
                '<td>' + fmtDate(row.dep_date) + '</td>' +
                '<td><span class="badge badge-secondary">' + row.category_code + '</span></td>' +
                '<td class="text-right">' + fmtEur(row.current_price) + '</td>' +
                '<td class="text-right">' + fmtEur(row.ref_price) + '</td>' +
                '<td class="text-right ' + cls + ' font-weight-bold">' +
                    sign + '€' + Math.abs(parseFloat(row.delta_eur)).toLocaleString('it-IT', { minimumFractionDigits: 2 }) +
                '</td>' +
                '<td class="text-right ' + cls + '">' + sign + row.delta_pct + '%</td>' +
                '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;

        container.querySelectorAll('.top10-row').forEach(function (tr) {
            tr.addEventListener('click', function () {
                loadDepartureDetail(this.dataset.departureId, this.dataset.cruiseName);
            });
        });
    }

    // ── Ricerca ───────────────────────────────────────────────────────────────

    async function loadSearch() {
        const q    = document.getElementById('search-input').value.trim();
        const from = document.getElementById('from-date').value;
        const to   = document.getElementById('to-date').value;

        if (q.length < 2) return;

        showSpinner('search-results');

        try {
            const res  = await fetch('/api/admin/price-history/search?q=' + encodeURIComponent(q));
            const data = await res.json();
            if (!res.ok) throw new Error('Errore');
            renderSearchResults(data, from, to);
        } catch (e) {
            showError('search-results', 'Errore nel caricamento dati, riprova.');
        }
    }

    function renderSearchResults(data, from, to) {
        const container = document.getElementById('search-results');

        if (!data.data.length) {
            container.innerHTML = '<p class="text-muted mt-2 mb-0">Nessuna crociera trovata</p>';
            return;
        }

        let html = '<ul class="list-group mt-2">';
        data.data.forEach(function (item) {
            const price = item.min_price
                ? '€' + parseFloat(item.min_price).toLocaleString('it-IT', { minimumFractionDigits: 0 })
                : '-';
            html += '<li class="list-group-item list-group-item-action"' +
                ' data-departure-id="' + item.id + '"' +
                ' data-cruise-name="' + escHtml(item.cruise_name) + '"' +
                ' data-from="' + from + '"' +
                ' data-to="' + to + '">' +
                '<strong>' + escHtml(item.cruise_name) + '</strong>' +
                '<span class="text-muted ml-2">Partenza: ' + fmtDate(item.dep_date) + '</span>' +
                '<span class="badge badge-primary float-right">' + price + '</span>' +
                '</li>';
        });
        html += '</ul>';
        container.innerHTML = html;

        container.querySelectorAll('.list-group-item').forEach(function (li) {
            li.addEventListener('click', function () {
                loadDepartureDetail(
                    this.dataset.departureId,
                    this.dataset.cruiseName,
                    this.dataset.from,
                    this.dataset.to
                );
            });
        });
    }

    // ── Dettaglio partenza ────────────────────────────────────────────────────

    async function loadDepartureDetail(departureId, cruiseName, from, to) {
        document.getElementById('detail-section').classList.remove('d-none');
        document.getElementById('detail-title').textContent = cruiseName;
        document.getElementById('price-chart').innerHTML =
            '<div class="text-center py-3"><div class="spinner-border text-primary" role="status">' +
            '<span class="sr-only">Caricamento...</span></div></div>';
        document.getElementById('detail-table-body').innerHTML = '';

        // Stato stagionale — reset
        currentDepartureId  = departureId;
        seasonalMonthlyData = null;
        activeSeasonalTab   = 'weekly';

        // Pannello stagionale — prepara UI
        document.getElementById('seasonal-section').classList.remove('d-none');
        document.getElementById('seasonal-itinerary-name').textContent = cruiseName;
        document.getElementById('seasonal-chart').innerHTML =
            '<div class="text-center py-3">' +
            '<div class="spinner-border text-primary" role="status">' +
            '<span class="sr-only">Caricamento...</span></div></div>';
        document.getElementById('seasonal-note').textContent = '';

        // Reset tab UI
        document.querySelectorAll('#seasonal-tabs .nav-link')
            .forEach(function (l) { l.classList.remove('active'); });
        document.querySelector('#seasonal-tabs [data-tab="weekly"]').classList.add('active');

        let url = '/api/admin/price-history/' + encodeURIComponent(departureId);
        const params = [];
        if (from) params.push('from=' + from);
        if (to)   params.push('to=' + to);
        if (params.length) url += '?' + params.join('&');

        try {
            const res  = await fetch(url);
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Errore');
            renderChart(data.series);
            renderDetailTable(data.series);
            document.getElementById('detail-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
            loadSeasonalWeekly(departureId, document.getElementById('seasonal-category').value);
        } catch (e) {
            showError('price-chart', 'Errore nel caricamento dati, riprova.');
        }
    }

    function renderChart(series) {
        const el = document.getElementById('price-chart');
        if (priceChart) { priceChart.destroy(); priceChart = null; }
        el.innerHTML = '';

        if (!series.length) {
            el.innerHTML = '<p class="text-muted">Nessun dato disponibile per il grafico.</p>';
            return;
        }

        priceChart = new ApexCharts(el, {
            chart:  { type: 'line', height: 350, zoom: { enabled: true }, toolbar: { show: true } },
            series: series.map(function (s) {
                return {
                    name: s.name,
                    data: s.data.map(function (p) {
                        return { x: new Date(p.x).getTime(), y: p.y };
                    }),
                };
            }),
            xaxis:  { type: 'datetime', labels: { datetimeUTC: false, format: 'dd/MM/yy' } },
            yaxis:  { labels: { formatter: function (v) {
                return '€' + v.toLocaleString('it-IT', { minimumFractionDigits: 0 });
            }}},
            tooltip: { x: { format: 'dd/MM/yyyy HH:mm' } },
            stroke:  { curve: 'smooth', width: 2 },
            legend:  { position: 'top' },
            markers: { size: 4 },
            noData:  { text: 'Nessun dato disponibile' },
            colors:  ['#1a7a8a', '#4caf50', '#ff9800', '#e91e63', '#9c27b0'],
        });
        priceChart.render();
    }

    function renderDetailTable(series) {
        const tbody = document.getElementById('detail-table-body');

        const rows = [];
        series.forEach(function (s) {
            s.data.forEach(function (p) { rows.push(Object.assign({}, p, { category: s.name })); });
        });
        rows.sort(function (a, b) { return new Date(a.x) - new Date(b.x); });

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nessun dato</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(function (p) {
            let delta = '<span class="text-muted">—</span>';
            if (p.delta_eur !== null) {
                const cls  = p.delta_eur < 0 ? 'text-success' : 'text-danger';
                const sign = p.delta_eur > 0 ? '+' : '';
                delta = '<span class="' + cls + '">' +
                    sign + '€' + Math.abs(p.delta_eur).toLocaleString('it-IT', { minimumFractionDigits: 2 }) +
                    ' (' + sign + p.delta_pct + '%)' +
                    '</span>';
            }
            return '<tr>' +
                '<td>' + fmtDate(p.x) + '</td>' +
                '<td><span class="badge badge-secondary">' + p.category + '</span></td>' +
                '<td>' + fmtEur(p.y) + '</td>' +
                '<td>' + delta + '</td>' +
                '<td><span class="badge badge-' + (p.source === 'api' ? 'info' : 'light') + '">' + p.source + '</span></td>' +
                '</tr>';
        }).join('');
    }

    // ── Analisi stagionale — settimanale ────────────────────────────────────────

    async function loadSeasonalWeekly(departureId, category) {
        document.getElementById('seasonal-chart').innerHTML =
            '<div class="text-center py-3">' +
            '<div class="spinner-border text-primary" role="status">' +
            '<span class="sr-only">Caricamento...</span></div></div>';
        document.getElementById('seasonal-note').textContent = '';

        try {
            const res  = await fetch(
                '/api/admin/price-history/seasonal/weekly' +
                '?departure_id=' + encodeURIComponent(departureId) +
                '&category='     + encodeURIComponent(category)
            );
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Errore');
            updateSeasonalCategorySelect(data.available_categories);
            renderSeasonalWeeklyChart(data);
        } catch (e) {
            document.getElementById('seasonal-chart').innerHTML =
                '<div class="alert alert-danger mb-0">Errore nel caricamento dati stagionali.</div>';
        }
    }

    function renderSeasonalWeeklyChart(data) {
        var el = document.getElementById('seasonal-chart');
        if (seasonalChart) { seasonalChart.destroy(); seasonalChart = null; }
        el.innerHTML = '';

        if (data.insufficient_data || !data.series || !data.series.length) {
            el.innerHTML =
                '<div class="alert alert-info mb-0">Dati insufficienti per il confronto — ' +
                'meno di 2 stagioni disponibili per questo itinerario.</div>';
            document.getElementById('seasonal-note').textContent = '';
            return;
        }

        var weeklySeries = data.series.map(function (s) {
            return {
                name: s.name,
                data: s.data.map(function (p) { return { x: -p.x, y: p.y }; }),
            };
        });

        seasonalChart = new ApexCharts(el, {
            chart:   { type: 'line', height: 320, toolbar: { show: false } },
            series:  weeklySeries,
            xaxis: {
                type: 'numeric',
                tickAmount: 8,
                title: { text: 'Settimane prima della partenza' },
                labels: { formatter: function (val) { return Math.abs(Math.round(val)) + ' sett.'; } },
            },
            yaxis: {
                labels: { formatter: function (v) {
                    return '€' + parseFloat(v).toLocaleString('it-IT', { minimumFractionDigits: 0 });
                }},
            },
            tooltip: {
                x: { formatter: function (val) { return Math.abs(Math.round(val)) + ' settimane prima della partenza'; } },
                y: { formatter: function (val) {
                    return '€' + parseFloat(val).toLocaleString('it-IT', { minimumFractionDigits: 2 });
                }},
            },
            stroke:  { curve: 'smooth', width: 2 },
            markers: { size: 4, hover: { size: 6 } },
            legend:  { position: 'top' },
            colors:  ['#1a7a8a', '#4caf50', '#ff9800', '#e91e63', '#9c27b0'],
        });
        seasonalChart.render();
        document.getElementById('seasonal-note').textContent =
            'Prezzi medi tra le partenze dello stesso itinerario alla stessa distanza temporale dalla data di imbarco.';
    }

    function updateSeasonalCategorySelect(availableCategories) {
        var sel = document.getElementById('seasonal-category');
        var currentVal = sel.value;
        Array.from(sel.options).forEach(function (opt) {
            opt.disabled = availableCategories.indexOf(opt.value) === -1;
        });
        if (availableCategories.indexOf(currentVal) === -1 && availableCategories.length) {
            sel.value = availableCategories[0];
        }
    }

    // ── Analisi stagionale — mensile ────────────────────────────────────────────

    async function loadSeasonalMonthly(departureId, category) {
        document.getElementById('seasonal-chart').innerHTML =
            '<div class="text-center py-3">' +
            '<div class="spinner-border text-primary" role="status">' +
            '<span class="sr-only">Caricamento...</span></div></div>';
        document.getElementById('seasonal-note').textContent = '';

        try {
            const res  = await fetch(
                '/api/admin/price-history/seasonal/monthly' +
                '?departure_id=' + encodeURIComponent(departureId) +
                '&category='     + encodeURIComponent(category)
            );
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Errore');
            seasonalMonthlyData = data;
            renderSeasonalMonthlyChart(data);
        } catch (e) {
            document.getElementById('seasonal-chart').innerHTML =
                '<div class="alert alert-danger mb-0">Errore nel caricamento dati mensili.</div>';
        }
    }

    function renderSeasonalMonthlyChart(data) {
        var el = document.getElementById('seasonal-chart');
        if (seasonalChart) { seasonalChart.destroy(); seasonalChart = null; }
        el.innerHTML = '';

        if (!data.series || !data.series.length) {
            el.innerHTML =
                '<div class="alert alert-info mb-0">Nessun dato mensile disponibile per questo itinerario.</div>';
            document.getElementById('seasonal-note').textContent = '';
            return;
        }

        seasonalChart = new ApexCharts(el, {
            chart:   { type: 'line', height: 320, toolbar: { show: false } },
            series:  data.series,
            xaxis: {
                categories: data.categories,
                title: { text: 'Mese di partenza' },
            },
            yaxis: {
                labels: { formatter: function (v) {
                    return v != null
                        ? '€' + parseFloat(v).toLocaleString('it-IT', { minimumFractionDigits: 0 })
                        : '';
                }},
            },
            tooltip: {
                y: { formatter: function (val) {
                    return val != null
                        ? '€' + parseFloat(val).toLocaleString('it-IT', { minimumFractionDigits: 2 })
                        : 'N/D';
                }},
            },
            stroke:  { curve: 'smooth', width: 2 },
            markers: { size: 4, hover: { size: 6 } },
            legend:  { position: 'top' },
            colors:  ['#1a7a8a', '#4caf50', '#ff9800', '#e91e63', '#9c27b0'],
        });
        seasonalChart.render();
        document.getElementById('seasonal-note').textContent =
            "Prezzo all'ultimo snapshot disponibile per ciascuna partenza. I mesi senza partenze appaiono vuoti.";
    }

    // ── Utility ───────────────────────────────────────────────────────────────

    function showSpinner(id) {
        document.getElementById(id).innerHTML =
            '<div class="text-center py-3">' +
            '<div class="spinner-border text-primary" role="status">' +
            '<span class="sr-only">Caricamento...</span></div></div>';
    }

    function showError(id, msg) {
        document.getElementById(id).innerHTML =
            '<div class="alert alert-danger mb-0">' + msg + '</div>';
    }

    function fmtDate(str) {
        if (!str) return '—';
        return new Date(str).toLocaleDateString('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function fmtEur(val) {
        return '€' + parseFloat(val).toLocaleString('it-IT', { minimumFractionDigits: 2 });
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

});
</script>
