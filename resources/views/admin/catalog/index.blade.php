@extends('layouts.app')

@section('title', 'Catalogo CruiseHost — Admin')

@section('content')
<div class="container-fluid py-4" style="max-width:1400px;">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold" style="color:#1a7a8a;">
                <i class="fas fa-ship mr-2"></i>Gestione Catalogo CruiseHost
            </h2>
            <small class="text-muted">
                @if($lastSync)
                    Ultima sincronizzazione completata:
                    <strong>{{ \Carbon\Carbon::parse($lastSync->finished_at)->locale('it')->isoFormat('D MMM YYYY [alle] HH:mm') }}</strong>
                @else
                    Nessuna sincronizzazione completata
                @endif
            </small>
        </div>
        <a href="{{ route('admin.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Admin
        </a>
    </div>

    {{-- Stat cards --}}
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #1a7a8a !important;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 fw-bold mb-0" style="color:#1a7a8a;">{{ number_format($stats['departures']) }}</div>
                            <small class="text-muted">Partenze totali</small>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x" style="color:#1a7a8a;opacity:.2;"></i>
                    </div>
                    <div class="mt-1">
                        <small class="text-success"><i class="fas fa-circle mr-1" style="font-size:.5rem;"></i>{{ number_format($stats['future']) }} future con prezzo</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #4caf50 !important;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 fw-bold mb-0" style="color:#4caf50;">{{ number_format($stats['products']) }}</div>
                            <small class="text-muted">Prodotti / Itinerari</small>
                        </div>
                        <i class="fas fa-route fa-2x" style="color:#4caf50;opacity:.2;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #ff9800 !important;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 fw-bold mb-0" style="color:#ff9800;">{{ $stats['cruise_lines'] }}</div>
                            <small class="text-muted">Compagnie attive</small>
                        </div>
                        <i class="fas fa-building fa-2x" style="color:#ff9800;opacity:.2;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #9c27b0 !important;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 fw-bold mb-0" style="color:#9c27b0;">{{ $stats['ships'] }}</div>
                            <small class="text-muted">Navi in catalogo</small>
                        </div>
                        <i class="fas fa-anchor fa-2x" style="color:#9c27b0;opacity:.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sync panel --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h5 class="fw-bold mb-1"><i class="fas fa-sync-alt mr-2" style="color:#1a7a8a;"></i>Sincronizzazione Catalogo</h5>
                    <p class="text-muted mb-3 small">
                        Scarica il catalogo aggiornato da CruiseHost (ZIP → JSON) e importa partenze, prezzi e dati nave.
                        Massimo <strong>4 sync</strong> al giorno.
                    </p>

                    {{-- Progress bar (nascosta di default) --}}
                    <div id="sync-progress-wrap" style="display:none;" class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small id="sync-status-text" class="text-muted">Avvio in corso...</small>
                            <small id="sync-elapsed" class="text-muted"></small>
                        </div>
                        <div class="progress" style="height:8px;border-radius:4px;">
                            <div id="sync-progress-bar"
                                 class="progress-bar progress-bar-striped progress-bar-animated"
                                 style="width:100%;background:#1a7a8a;"></div>
                        </div>
                    </div>

                    {{-- Output processo (debug) --}}
                    <pre id="sync-process-output"
                         style="display:none;background:#1a1a2e;color:#a8ff78;font-size:.72rem;padding:10px;border-radius:6px;max-height:140px;overflow-y:auto;margin-bottom:.75rem;"></pre>

                    {{-- Alert risultato --}}
                    <div id="sync-result-alert" class="alert d-none mb-3" role="alert"></div>

                    <div class="d-flex align-items-center">
                        <button id="btn-sync"
                                class="btn btn-lg mr-2"
                                style="background:#1a7a8a;color:#fff;min-width:200px;"
                                {{ $todaySyncs >= 4 ? 'disabled' : '' }}>
                            <i class="fas fa-cloud-download-alt mr-2"></i>
                            <span id="btn-sync-label">Sincronizza Catalogo</span>
                        </button>
                        <button id="btn-stop" class="btn btn-danger mr-3" style="display:none;">
                            <i class="fas fa-stop mr-1"></i>Forza Stop
                        </button>
                        <div>
                            <div class="d-flex align-items-center">
                                <div class="sync-quota-dots mr-2">
                                    @for($i = 1; $i <= 4; $i++)
                                        <span class="sync-dot {{ $i <= $todaySyncs ? 'used' : 'free' }}"></span>
                                    @endfor
                                </div>
                                <small class="text-muted">
                                    <strong>{{ $todaySyncs }}/4</strong> sync oggi
                                    @if($todaySyncs >= 4)
                                        <span class="badge badge-danger ml-1">Limite raggiunto</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-center d-none d-md-block">
                    <div style="font-size:5rem;opacity:.08;color:#1a7a8a;line-height:1;">
                        <i class="fas fa-database"></i>
                    </div>
                    @if($lastSync && $lastSync->finished_at)
                    <div class="text-muted small mt-n3">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        {{ \Carbon\Carbon::parse($lastSync->finished_at)->diffForHumans() }}
                        &bull; {{ number_format($lastSync->products_imported) }} prodotti
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Storico sincronizzazioni --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-history mr-2" style="color:#1a7a8a;"></i>Storico Sincronizzazioni
            </h6>
            <span class="badge badge-pill" style="background:#1a7a8a;color:#fff;font-size:.8rem;">
                {{ $todaySyncs }} oggi
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 small" id="history-table">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th class="px-3 py-2 text-muted" style="width:50px;">#</th>
                            <th class="py-2 text-muted">Avvio</th>
                            <th class="py-2 text-muted">Fine</th>
                            <th class="py-2 text-muted">Origine</th>
                            <th class="py-2 text-muted">Durata</th>
                            <th class="py-2 text-muted text-right">Prodotti</th>
                            <th class="py-2 text-muted text-right">Prezzi</th>
                            <th class="py-2 text-muted">Stato</th>
                            <th class="py-2 text-muted">Note</th>
                        </tr>
                    </thead>
                    <tbody id="history-tbody">
                        @forelse($history as $row)
                        <tr>
                            <td class="px-3 text-muted">{{ $row['id'] }}</td>
                            <td>{{ $row['started_at'] }}</td>
                            <td class="text-muted">{{ $row['finished_at'] }}</td>
                            <td>
                                @if($row['triggered_by'] === 'manual')
                                    <span class="badge badge-info">
                                        <i class="fas fa-hand-pointer mr-1"></i>Manuale
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-robot mr-1"></i>Cron
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $row['duration'] }}</td>
                            <td class="text-right">
                                @if($row['products_imported'] > 0)
                                    <strong>{{ number_format($row['products_imported']) }}</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right text-muted">{{ $row['prices_recorded'] > 0 ? number_format($row['prices_recorded']) : '—' }}</td>
                            <td>
                                @if($row['status'] === 'completed')
                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Completato</span>
                                @elseif($row['status'] === 'running')
                                    <span class="badge badge-warning text-dark"><i class="fas fa-spinner fa-spin mr-1"></i>In corso</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Fallito</span>
                                @endif
                            </td>
                            <td class="text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                title="{{ $row['notes'] }}">
                                {{ $row['notes'] ?? '' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Nessuna sincronizzazione eseguita
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<style>
.sync-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 3px;
}
.sync-dot.used  { background: #1a7a8a; }
.sync-dot.free  { background: #dee2e6; }
</style>
<script>
(function () {
    'use strict';

    var btnSync        = document.getElementById('btn-sync');
    var btnLabel       = document.getElementById('btn-sync-label');
    var progressWrap   = document.getElementById('sync-progress-wrap');
    var progressBar    = document.getElementById('sync-progress-bar');
    var statusText     = document.getElementById('sync-status-text');
    var elapsedEl      = document.getElementById('sync-elapsed');
    var resultAlert    = document.getElementById('sync-result-alert');
    var processOutput  = document.getElementById('sync-process-output');

    var SYNC_URL       = '{{ route('admin.catalog.sync') }}';
    var STATUS_BASE    = '{{ url('admin/catalog/sync') }}/';
    var STOP_BASE      = '{{ url('admin/catalog/sync') }}/';
    var CSRF           = '{{ csrf_token() }}';
    var btnStop        = document.getElementById('btn-stop');

    var pollTimer      = null;
    var elapsedTimer   = null;
    var elapsedSecs    = 0;
    var activeLogId    = null;

    // Controlla se c'è un sync in corso al caricamento della pagina
    @foreach($history as $row)
        @if($row['status'] === 'running')
            activeLogId = {{ $row['id'] }};
        @break
        @endif
    @endforeach

    if (activeLogId) {
        startPolling(activeLogId);
        setRunningUI();
    }

    btnSync.addEventListener('click', function () {
        hideAlert();
        setRunningUI();

        fetch(SYNC_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(function (r) { return r.json().then(function(d){ return {ok: r.ok, data: d}; }); })
        .then(function (res) {
            if (!res.ok) {
                setIdleUI();
                showAlert('danger', '<i class="fas fa-exclamation-triangle mr-1"></i>' + (res.data.error || 'Errore avvio sync.'));
                return;
            }
            activeLogId = res.data.log_id;
            startPolling(activeLogId);
        })
        .catch(function () {
            setIdleUI();
            showAlert('danger', 'Errore di connessione.');
        });
    });

    function startPolling(logId) {
        clearInterval(pollTimer);
        pollTimer = setInterval(function () {
            fetch(STATUS_BASE + logId + '/status', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (row) {
                statusText.textContent = row.notes && row.status === 'running'
                    ? row.notes
                    : statusLabel(row.status, row.products_imported);

                if (row.process_output) {
                    processOutput.style.display = 'block';
                    processOutput.textContent = row.process_output;
                    processOutput.scrollTop = processOutput.scrollHeight;
                }

                if (row.status === 'completed') {
                    stopPolling();
                    setIdleUI();
                    showAlert('success',
                        '<i class="fas fa-check-circle mr-1"></i>Sincronizzazione completata in <strong>' + row.duration + '</strong>. ' +
                        '<strong>' + row.products_imported.toLocaleString('it') + '</strong> prodotti, ' +
                        '<strong>' + row.prices_recorded.toLocaleString('it') + '</strong> prezzi importati.'
                    );
                    refreshHistory();

                } else if (row.status === 'failed') {
                    stopPolling();
                    setIdleUI();
                    showAlert('danger', '<i class="fas fa-times-circle mr-1"></i>Sincronizzazione fallita. ' + (row.notes || ''));
                    refreshHistory();
                }
            });
        }, 3000);
    }

    function stopPolling() {
        clearInterval(pollTimer);
        clearInterval(elapsedTimer);
    }

    // Pulsante stop
    btnStop.addEventListener('click', function () {
        if (!activeLogId) return;
        if (!confirm('Vuoi forzare la terminazione del sync in corso?')) return;
        fetch(STOP_BASE + activeLogId + '/stop', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            stopPolling();
            setIdleUI();
            showAlert('warning', '<i class="fas fa-stop-circle mr-1"></i>' + d.message);
            refreshHistory();
        });
    });

    function setRunningUI() {
        btnSync.disabled = true;
        btnStop.style.display = 'inline-block';
        btnLabel.textContent = 'Sincronizzazione in corso...';
        progressWrap.style.display = 'block';
        elapsedSecs = 0;
        clearInterval(elapsedTimer);
        elapsedTimer = setInterval(function () {
            elapsedSecs++;
            var m = Math.floor(elapsedSecs / 60);
            var s = elapsedSecs % 60;
            elapsedEl.textContent = (m > 0 ? m + 'm ' : '') + s + 's';
        }, 1000);
    }

    function setIdleUI() {
        btnSync.disabled = false;
        btnStop.style.display = 'none';
        btnLabel.textContent = 'Sincronizza Catalogo';
        progressWrap.style.display = 'none';
        clearInterval(elapsedTimer);
        processOutput.style.display = 'none';
    }

    function statusLabel(status, products) {
        if (status === 'running') return products > 0 ? 'Importazione in corso (' + products + ' prodotti)...' : 'Download catalogo...';
        if (status === 'completed') return 'Completato.';
        return 'Errore.';
    }

    function showAlert(type, html) {
        resultAlert.className = 'alert alert-' + type;
        resultAlert.innerHTML = html;
        resultAlert.classList.remove('d-none');
    }

    function hideAlert() {
        resultAlert.classList.add('d-none');
    }

    function refreshHistory() {
        fetch('{{ route('admin.catalog.history') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r){ return r.json(); })
        .then(function(rows){
            var tbody = document.getElementById('history-tbody');
            if (!rows.length) return;
            tbody.innerHTML = rows.map(function(row){
                return '<tr>' +
                    '<td class="px-3 text-muted">' + row.id + '</td>' +
                    '<td>' + row.started_at + '</td>' +
                    '<td class="text-muted">' + row.finished_at + '</td>' +
                    '<td>' + triggerBadge(row.triggered_by) + '</td>' +
                    '<td class="text-muted">' + row.duration + '</td>' +
                    '<td class="text-right">' + (row.products_imported > 0 ? '<strong>' + row.products_imported.toLocaleString('it') + '</strong>' : '—') + '</td>' +
                    '<td class="text-right text-muted">' + (row.prices_recorded > 0 ? row.prices_recorded.toLocaleString('it') : '—') + '</td>' +
                    '<td>' + statusBadge(row.status) + '</td>' +
                    '<td class="text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + (row.notes || '') + '</td>' +
                    '</tr>';
            }).join('');

            // Aggiorna contatore oggi
            var todayCount = rows.filter(function(r){ return r.started_at.startsWith('{{ now()->format('d/m/Y') }}'); }).length;
            document.querySelector('.badge.badge-pill').textContent = todayCount + ' oggi';
        });
    }

    function triggerBadge(trigger) {
        return trigger === 'manual'
            ? '<span class="badge badge-info"><i class="fas fa-hand-pointer mr-1"></i>Manuale</span>'
            : '<span class="badge badge-secondary"><i class="fas fa-robot mr-1"></i>Cron</span>';
    }

    function statusBadge(status) {
        if (status === 'completed') return '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Completato</span>';
        if (status === 'running')   return '<span class="badge badge-warning text-dark"><i class="fas fa-spinner fa-spin mr-1"></i>In corso</span>';
        return '<span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Fallito</span>';
    }

})();
</script>
@endsection
