<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    let priceChart           = null;
    let seasonalWeeklyChart  = null;
    let seasonalMonthlyChart = null;
    let currentDepartureId   = null;
    let allSeries            = [];
    let selectedCategory     = null;
    let seasonalGroups       = {};

    loadTopVariations();

    document.getElementById('refresh-btn')
        .addEventListener('click', loadTopVariations);

    document.getElementById('search-form')
        .addEventListener('submit', function (e) {
            e.preventDefault();
            loadSearch();
        });

    // ── Tab stagionale (Bootstrap native) ────────────────────────────────────
    document.getElementById('seasonal-tabs')
        .addEventListener('shown.bs.tab', function (e) {
            if (!currentDepartureId) return;
            const target = e.target.getAttribute('href');
            if (target === '#seasonal-panel-monthly' && !seasonalMonthlyChart) {
                loadSeasonalMonthly(currentDepartureId, getSeasonalCategory());
            }
        });

    // ── Selettori macro / sotto-cabina stagionali ─────────────────────────────
    document.getElementById('seasonal-macro-select')
        .addEventListener('change', function () {
            if (!currentDepartureId) return;
            populateSeasonalSubs(this.value);
            reloadSeasonalCharts(getSeasonalCategory());
        });

    document.getElementById('seasonal-sub-select')
        .addEventListener('change', function () {
            if (!currentDepartureId) return;
            reloadSeasonalCharts(this.value);
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
        document.getElementById('ph-table-note').textContent = '';
        document.getElementById('ph-cat-pills').classList.add('d-none');
        document.getElementById('ph-cat-pills').innerHTML = '';
        allSeries        = [];
        selectedCategory = null;

        // Stato stagionale — reset
        currentDepartureId = departureId;
        if (seasonalWeeklyChart)  { seasonalWeeklyChart.destroy();  seasonalWeeklyChart  = null; }
        if (seasonalMonthlyChart) { seasonalMonthlyChart.destroy(); seasonalMonthlyChart = null; }

        // Pannello stagionale — prepara UI
        document.getElementById('seasonal-section').classList.remove('d-none');
        document.getElementById('seasonal-itinerary-name').textContent = cruiseName;
        document.getElementById('seasonal-weekly-chart').innerHTML  = '';
        document.getElementById('seasonal-monthly-chart').innerHTML = '';
        document.getElementById('seasonal-weekly-msg').textContent  = 'Caricamento...';
        document.getElementById('seasonal-monthly-msg').textContent = '';
        document.getElementById('seasonal-note').textContent = '';
        document.getElementById('seasonal-cat-wrapper').classList.add('d-none');

        // Riporta il tab settimanale attivo
        const wPanel = document.getElementById('seasonal-panel-weekly');
        const mPanel = document.getElementById('seasonal-panel-monthly');
        wPanel.classList.add('show', 'active');
        mPanel.classList.remove('show', 'active');
        document.querySelector('#seasonal-tabs .nav-link[href="#seasonal-panel-weekly"]').classList.add('active');
        document.querySelector('#seasonal-tabs .nav-link[href="#seasonal-panel-monthly"]').classList.remove('active');

        let url = '/api/admin/price-history/' + encodeURIComponent(departureId);
        const params = [];
        if (from) params.push('from=' + from);
        if (to)   params.push('to=' + to);
        if (params.length) url += '?' + params.join('&');

        try {
            const res  = await fetch(url);
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Errore');
            allSeries = data.series || [];
            buildCategoryPills(allSeries);
            const firstMacro = MACRO_ORDER.find(function (m) {
                return allSeries.some(function (s) { return s.macro === m; });
            }) || (allSeries[0] ? allSeries[0].macro : null);
            if (firstMacro) applyMacro(firstMacro);

            // Popola selettori stagionali e carica il grafico settimanale
            const defaultSeasonalCat = populateSeasonalSelectors(allSeries);
            document.getElementById('detail-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
            if (defaultSeasonalCat) loadSeasonalWeekly(departureId, defaultSeasonalCat);
        } catch (e) {
            showError('price-chart', 'Errore nel caricamento dati, riprova.');
        }
    }

    const MACRO_LABELS = { IS: 'Interna', OS: 'Esterna', BK: 'Balcone', MS: 'Mini Suite', SU: 'Suite', ND: 'N/D' };
    const MACRO_ORDER  = ['IS', 'OS', 'BK', 'MS', 'SU', 'ND'];
    const CHART_COLORS = ['#1a7a8a', '#4caf50', '#e67e22', '#9b59b6', '#e74c3c', '#3498db', '#f39c12', '#1abc9c'];

    function buildCategoryPills(series) {
        const pills = document.getElementById('ph-cat-pills');
        if (!series.length) return;

        // Raggruppa per macro, mantenendo l'ordine MACRO_ORDER
        const macros = [];
        const seen   = new Set();
        MACRO_ORDER.forEach(function (m) {
            if (!seen.has(m) && series.some(function (s) { return s.macro === m; })) {
                macros.push(m);
                seen.add(m);
            }
        });
        // Qualsiasi macro non in MACRO_ORDER (fallback)
        series.forEach(function (s) {
            if (!seen.has(s.macro)) { macros.push(s.macro); seen.add(s.macro); }
        });

        const wrap = document.createElement('div');
        wrap.className = 'd-flex flex-wrap align-items-center';
        wrap.style.gap = '6px';

        const lbl = document.createElement('span');
        lbl.className = 'text-muted small mr-2 flex-shrink-0';
        lbl.textContent = 'Tipo cabina:';
        wrap.appendChild(lbl);

        macros.forEach(function (macro, i) {
            const count = series.filter(function (s) { return s.macro === macro; }).length;
            const btn   = document.createElement('button');
            btn.type    = 'button';
            btn.className = 'btn btn-sm ' + (i === 0 ? 'btn-primary' : 'btn-outline-secondary');
            btn.dataset.macro = macro;
            btn.innerHTML = (MACRO_LABELS[macro] || macro) +
                ' <span class="badge badge-' + (i === 0 ? 'light text-primary' : 'secondary') +
                ' ml-1">' + count + '</span>';
            btn.addEventListener('click', function () {
                wrap.querySelectorAll('button').forEach(function (b) {
                    b.className = 'btn btn-sm btn-outline-secondary';
                    b.querySelector('.badge').className = 'badge badge-secondary ml-1';
                });
                this.className = 'btn btn-sm btn-primary';
                this.querySelector('.badge').className = 'badge badge-light text-primary ml-1';
                applyMacro(this.dataset.macro);
            });
            wrap.appendChild(btn);
        });

        pills.innerHTML = '';
        pills.appendChild(wrap);
        pills.classList.remove('d-none');
    }

    function applyMacro(macro) {
        selectedCategory = macro;
        const filtered = allSeries.filter(function (s) { return s.macro === macro; });
        renderChart(filtered);
        renderDetailTable(filtered, macro);
    }

    function renderChart(series) {
        const el = document.getElementById('price-chart');
        if (priceChart) { priceChart.destroy(); priceChart = null; }
        el.innerHTML = '';

        if (!series.length) {
            el.innerHTML = '<p class="text-muted">Nessun dato disponibile per il grafico.</p>';
            return;
        }

        const multiLine = series.length > 1;
        priceChart = new ApexCharts(el, {
            chart:  { type: 'line', height: 300, zoom: { enabled: true }, toolbar: { show: true } },
            series: series.map(function (s) {
                return {
                    name: s.name,
                    data: s.data.map(function (p) {
                        return { x: new Date(p.x).getTime(), y: p.y };
                    }),
                };
            }),
            xaxis:   { type: 'datetime', labels: { datetimeUTC: false, format: 'dd/MM/yy' } },
            yaxis:   { labels: { formatter: function (v) {
                return '€' + v.toLocaleString('it-IT', { minimumFractionDigits: 0 });
            }}},
            tooltip: { x: { format: 'dd/MM/yyyy' } },
            stroke:  { curve: 'stepline', width: 2 },
            legend:  { show: multiLine, position: 'top' },
            markers: { size: 4, hover: { size: 6 } },
            noData:  { text: 'Nessun dato disponibile' },
            colors:  CHART_COLORS,
        });
        priceChart.render();
    }

    function renderDetailTable(series, macro) {
        const tbody = document.getElementById('detail-table-body');
        const note  = document.getElementById('ph-table-note');

        if (!series.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nessun dato</td></tr>';
            note.textContent = '';
            return;
        }

        const rows = series.map(function (s) {
            const pts = s.data;
            if (!pts.length) return null;
            let minPt = pts[0], maxPt = pts[0];
            pts.forEach(function (p) {
                if (p.y < minPt.y) minPt = p;
                if (p.y > maxPt.y) maxPt = p;
            });
            const current  = pts[pts.length - 1];
            const first    = pts[0];
            const deltaEur = current.y - first.y;
            const deltaPct = first.y > 0 ? (deltaEur / first.y * 100) : 0;
            return { cabin: s.name, minPt, maxPt, current, deltaEur, deltaPct };
        }).filter(Boolean);

        note.textContent = (MACRO_LABELS[macro] || macro) + ' — ' + rows.length +
            ' cabin' + (rows.length === 1 ? 'a' : 'e') + '.';

        tbody.innerHTML = rows.map(function (r) {
            const cls   = r.deltaEur < 0 ? 'text-success' : (r.deltaEur > 0 ? 'text-danger' : 'text-muted');
            const arrow = r.deltaEur < 0 ? '▼' : (r.deltaEur > 0 ? '▲' : '—');
            const sign  = r.deltaEur > 0 ? '+' : '';
            const delta = r.deltaEur !== 0
                ? '<span class="' + cls + ' font-weight-bold">' + arrow + ' ' +
                  sign + '€' + Math.abs(r.deltaEur).toLocaleString('it-IT', { minimumFractionDigits: 2 }) +
                  ' (' + sign + r.deltaPct.toFixed(1) + '%)</span>'
                : '<span class="text-muted">—</span>';

            return '<tr>' +
                '<td><span class="badge badge-secondary">' + r.cabin + '</span></td>' +
                '<td>' + fmtEur(r.minPt.y) + '<br><small class="text-muted">' + fmtDate(r.minPt.x) + '</small></td>' +
                '<td>' + fmtEur(r.maxPt.y) + '<br><small class="text-muted">' + fmtDate(r.maxPt.x) + '</small></td>' +
                '<td class="font-weight-bold">' + fmtEur(r.current.y) + '<br><small class="text-muted">' + fmtDate(r.current.x) + '</small></td>' +
                '<td>' + delta + '</td>' +
                '</tr>';
        }).join('');
    }

    // ── Selettori stagionali ─────────────────────────────────────────────────

    function populateSeasonalSelectors(series) {
        seasonalGroups = {};
        series.forEach(function (s) {
            const m = s.macro || 'ND';
            if (!seasonalGroups[m]) seasonalGroups[m] = [];
            if (seasonalGroups[m].indexOf(s.name) === -1) seasonalGroups[m].push(s.name);
        });

        const macroSel = document.getElementById('seasonal-macro-select');
        const wrapper  = document.getElementById('seasonal-cat-wrapper');
        const availMacros = MACRO_ORDER.filter(function (m) { return seasonalGroups[m]; });

        if (!availMacros.length) { wrapper.classList.add('d-none'); return null; }

        macroSel.innerHTML = '';
        availMacros.forEach(function (m) {
            const opt = document.createElement('option');
            opt.value = m; opt.textContent = MACRO_LABELS[m] || m;
            macroSel.appendChild(opt);
        });

        const defaultCat = populateSeasonalSubs(availMacros[0]);
        wrapper.classList.remove('d-none');
        return defaultCat;
    }

    function populateSeasonalSubs(macro) {
        const subSel     = document.getElementById('seasonal-sub-select');
        const subWrapper = document.getElementById('seasonal-sub-wrapper');
        const subs = seasonalGroups[macro] || [];
        subSel.innerHTML = '';
        subs.forEach(function (cat) {
            const opt = document.createElement('option');
            opt.value = cat; opt.textContent = cat;
            subSel.appendChild(opt);
        });
        subWrapper.style.cssText = subs.length > 1 ? '' : 'display:none!important';
        return subs[0] || null;
    }

    function getSeasonalCategory() {
        return document.getElementById('seasonal-sub-select').value || null;
    }

    function reloadSeasonalCharts(cat) {
        if (!cat) return;
        if (seasonalMonthlyChart) { seasonalMonthlyChart.destroy(); seasonalMonthlyChart = null; }
        loadSeasonalWeekly(currentDepartureId, cat);
        if (document.getElementById('seasonal-panel-monthly').classList.contains('active')) {
            loadSeasonalMonthly(currentDepartureId, cat);
        }
    }

    // ── Analisi stagionale — settimanale ─────────────────────────────────────

    async function loadSeasonalWeekly(departureId, category) {
        const msg = document.getElementById('seasonal-weekly-msg');
        const el  = document.getElementById('seasonal-weekly-chart');
        msg.textContent = 'Caricamento...';
        el.innerHTML = '';
        if (seasonalWeeklyChart) { seasonalWeeklyChart.destroy(); seasonalWeeklyChart = null; }

        try {
            const res  = await fetch(
                '/api/admin/price-history/seasonal/weekly' +
                '?departure_id=' + encodeURIComponent(departureId) +
                '&category='     + encodeURIComponent(category)
            );
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Errore');
            msg.textContent = '';
            renderSeasonalWeeklyChart(data);
        } catch (e) {
            msg.textContent = '';
            el.innerHTML = '<div class="alert alert-danger mb-0">Errore nel caricamento dati stagionali.</div>';
        }
    }

    function renderSeasonalWeeklyChart(data) {
        const msg = document.getElementById('seasonal-weekly-msg');
        const el  = document.getElementById('seasonal-weekly-chart');

        if (data.insufficient_data || !data.series || !data.series.length) {
            el.innerHTML = '<div class="alert alert-info mb-0">Nessun dato disponibile per questo itinerario.</div>';
            document.getElementById('seasonal-note').textContent = '';
            return;
        }

        seasonalWeeklyChart = new ApexCharts(el, {
            chart:   { type: 'line', height: 320, toolbar: { show: false } },
            series:  data.series.map(function (s) {
                return { name: s.name, data: s.data.map(function (p) { return { x: -p.x, y: p.y }; }) };
            }),
            xaxis: {
                type: 'numeric', tickAmount: 8,
                title: { text: 'Settimane prima della partenza' },
                labels: { formatter: function (val) { return Math.abs(Math.round(val)) + ' sett.'; } },
            },
            yaxis: { labels: { formatter: function (v) {
                return '€' + parseFloat(v).toLocaleString('it-IT', { minimumFractionDigits: 0 });
            }}},
            tooltip: {
                x: { formatter: function (val) { return Math.abs(Math.round(val)) + ' settimane prima della partenza'; } },
                y: { formatter: function (val) { return '€' + parseFloat(val).toLocaleString('it-IT', { minimumFractionDigits: 2 }); }},
            },
            stroke: { curve: 'smooth', width: 2 }, markers: { size: 4, hover: { size: 6 } },
            legend: { position: 'top' }, colors: CHART_COLORS,
        });
        seasonalWeeklyChart.render();
        document.getElementById('seasonal-note').textContent =
            'Prezzi medi tra le partenze dello stesso itinerario alla stessa distanza temporale dalla data di imbarco.';
    }

    // ── Analisi stagionale — mensile ──────────────────────────────────────────

    async function loadSeasonalMonthly(departureId, category) {
        const msg = document.getElementById('seasonal-monthly-msg');
        const el  = document.getElementById('seasonal-monthly-chart');
        msg.textContent = 'Caricamento...';
        el.innerHTML = '';
        if (seasonalMonthlyChart) { seasonalMonthlyChart.destroy(); seasonalMonthlyChart = null; }

        try {
            const res  = await fetch(
                '/api/admin/price-history/seasonal/monthly' +
                '?departure_id=' + encodeURIComponent(departureId) +
                '&category='     + encodeURIComponent(category)
            );
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Errore');
            msg.textContent = '';
            renderSeasonalMonthlyChart(data);
        } catch (e) {
            msg.textContent = '';
            el.innerHTML = '<div class="alert alert-danger mb-0">Errore nel caricamento dati mensili.</div>';
        }
    }

    function renderSeasonalMonthlyChart(data) {
        const el = document.getElementById('seasonal-monthly-chart');

        if (!data.series || !data.series.length) {
            el.innerHTML = '<div class="alert alert-info mb-0">Nessun dato mensile disponibile per questo itinerario.</div>';
            return;
        }

        seasonalMonthlyChart = new ApexCharts(el, {
            chart:   { type: 'line', height: 320, toolbar: { show: false } },
            series:  data.series,
            xaxis: { categories: data.categories, title: { text: 'Mese di partenza' } },
            yaxis: { labels: { formatter: function (v) {
                return v != null ? '€' + parseFloat(v).toLocaleString('it-IT', { minimumFractionDigits: 0 }) : '';
            }}},
            tooltip: { y: { formatter: function (val) {
                return val != null ? '€' + parseFloat(val).toLocaleString('it-IT', { minimumFractionDigits: 2 }) : 'N/D';
            }}},
            stroke: { curve: 'smooth', width: 2 }, markers: { size: 4, hover: { size: 6 } },
            legend: { position: 'top' }, colors: CHART_COLORS,
        });
        seasonalMonthlyChart.render();
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
