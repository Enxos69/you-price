<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    let priceChart = null;

    loadTopVariations();

    document.getElementById('refresh-btn')
        .addEventListener('click', loadTopVariations);

    document.getElementById('search-form')
        .addEventListener('submit', function (e) {
            e.preventDefault();
            loadSearch();
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
