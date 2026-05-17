@extends('layouts.app')

@section('content')
<div class="alerts-log-wrapper">
    <div class="container-fluid">

        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0"><i class="fas fa-bell mr-2 text-primary"></i>Log Esecuzioni — Verifica Alert Prezzi</h2>
                        <p class="text-muted mb-0">Monitoraggio cron <code>alerts:check</code></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI cards --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card kpi-card kpi-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="kpi-icon"><i class="fas fa-play-circle"></i></div>
                            <div>
                                <div class="kpi-number">{{ number_format($kpi->total_runs) }}</div>
                                <div class="kpi-label">ESECUZIONI TOTALI</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card kpi-card kpi-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="kpi-icon"><i class="fas fa-paper-plane"></i></div>
                            <div>
                                <div class="kpi-number">{{ number_format($kpi->total_triggered) }}</div>
                                <div class="kpi-label">NOTIFICHE INVIATE</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card kpi-card kpi-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <div>
                                <div class="kpi-number">{{ number_format($kpi->total_failed) }}</div>
                                <div class="kpi-label">ERRORI EMAIL (TOT)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card kpi-card kpi-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="kpi-icon"><i class="fas fa-bell"></i></div>
                            <div>
                                <div class="kpi-number">{{ $activeAlerts }}</div>
                                <div class="kpi-label">ALERT ATTIVI ORA</div>
                                @if($lastRun)
                                    <div class="kpi-sub mt-1">
                                        Ultima: {{ $lastRun->started_at->format('d/m H:i') }}
                                        @php
                                            $statusClass = match($lastRun->status) {
                                                'completed' => 'success',
                                                'failed'    => 'danger',
                                                default     => 'warning',
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $statusClass }} ml-1">{{ $lastRun->status }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabella log --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
                <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Storico esecuzioni</h5>
                <form method="GET" action="{{ route('admin.alerts-log.index') }}" class="d-flex align-items-center" style="gap:.5rem;">
                    <select name="status" class="form-control form-control-sm" style="width:130px;" onchange="this.form.submit()">
                        <option value="">Tutti</option>
                        <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed"    {{ $statusFilter === 'failed'    ? 'selected' : '' }}>Failed</option>
                        <option value="running"   {{ $statusFilter === 'running'   ? 'selected' : '' }}>Running</option>
                    </select>
                </form>
            </div>
            <div class="card-body p-0">
                @if($logs->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">Nessuna esecuzione registrata.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 log-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="pl-3">#</th>
                                    <th>Data/Ora</th>
                                    <th>Durata</th>
                                    <th class="text-center">Controllati</th>
                                    <th class="text-center">Notificati</th>
                                    <th class="text-center">Saltati</th>
                                    <th class="text-center">Errori</th>
                                    <th class="text-center">Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    @php
                                        $statusClass = match($log->status) {
                                            'completed' => 'success',
                                            'failed'    => 'danger',
                                            default     => 'warning',
                                        };
                                        $rowClass = $log->emails_failed > 0 ? 'table-danger' : '';
                                    @endphp
                                    <tr class="log-row {{ $rowClass }}" data-id="{{ $log->id }}">
                                        <td class="pl-3 text-muted small align-middle">{{ $log->id }}</td>
                                        <td class="align-middle">
                                            <strong>{{ $log->started_at->format('d/m/Y') }}</strong>
                                            <span class="text-muted">{{ $log->started_at->format('H:i:s') }}</span>
                                        </td>
                                        <td class="align-middle text-muted small">
                                            {{ $log->duration ?? '—' }}
                                        </td>
                                        <td class="text-center align-middle">{{ $log->alerts_checked }}</td>
                                        <td class="text-center align-middle">
                                            @if($log->alerts_triggered > 0)
                                                <span class="badge badge-success">{{ $log->alerts_triggered }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle text-muted">{{ $log->alerts_skipped }}</td>
                                        <td class="text-center align-middle">
                                            @if($log->emails_failed > 0)
                                                <span class="badge badge-danger">{{ $log->emails_failed }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-{{ $statusClass }}">{{ $log->status }}</span>
                                        </td>
                                        <td class="text-right align-middle pr-2">
                                            @if($log->notes)
                                                <button class="btn btn-link btn-sm p-0 toggle-detail" data-target="detail-{{ $log->id }}" title="Mostra note">
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($log->notes)
                                        <tr class="detail-row d-none" id="detail-{{ $log->id }}">
                                            <td colspan="9" class="bg-light px-4 py-2">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-info-circle text-muted mr-2 mt-1"></i>
                                                    <div>
                                                        <strong class="small text-muted">Note:</strong>
                                                        <span class="small">{{ $log->notes }}</span>
                                                        @if($log->finished_at)
                                                            <span class="text-muted small ml-3">
                                                                Fine: {{ $log->finished_at->format('d/m/Y H:i:s') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginazione --}}
                    @if($logs->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">
                                Mostrando {{ $logs->firstItem() }}–{{ $logs->lastItem() }} di {{ $logs->total() }} esecuzioni
                            </small>
                            {{ $logs->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

<style>
.alerts-log-wrapper {
    padding: 2rem 1rem 3rem;
}

.kpi-card {
    border: none;
    border-radius: .5rem;
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
    position: relative;
    overflow: hidden;
}

.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 4px; height: 100%;
}

.kpi-primary::before  { background: linear-gradient(135deg,#007bff,#0056b3); }
.kpi-success::before  { background: linear-gradient(135deg,#28a745,#1e7e34); }
.kpi-danger::before   { background: linear-gradient(135deg,#dc3545,#b21f2d); }
.kpi-info::before     { background: linear-gradient(135deg,#17a2b8,#117a8b); }

.kpi-icon {
    width: 2.75rem; height: 2.75rem;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 1.1rem;
    margin-right: 1rem;
    flex-shrink: 0;
}

.kpi-primary .kpi-icon  { background: linear-gradient(135deg,#007bff,#0056b3); }
.kpi-success .kpi-icon  { background: linear-gradient(135deg,#28a745,#1e7e34); }
.kpi-danger .kpi-icon   { background: linear-gradient(135deg,#dc3545,#b21f2d); }
.kpi-info .kpi-icon     { background: linear-gradient(135deg,#17a2b8,#117a8b); }

.kpi-number { font-size: 1.75rem; font-weight: 700; line-height: 1.1; }
.kpi-label  { font-size: .65rem; font-weight: 600; color: #6c757d; letter-spacing: .06em; }
.kpi-sub    { font-size: .75rem; }

.log-table th { font-size: .75rem; font-weight: 600; letter-spacing: .04em; }
.log-table td { font-size: .875rem; }

.log-row { cursor: default; }
.detail-row td { border-top: none !important; }

.toggle-detail { color: #6c757d; }
.toggle-detail:hover { color: #343a40; }
.toggle-detail.open i { transform: rotate(180deg); }
.toggle-detail i { transition: transform .2s; display: inline-block; }
</style>

@section('scripts')
@parent
<script>
document.querySelectorAll('.toggle-detail').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var targetId = this.getAttribute('data-target');
        var row = document.getElementById(targetId);
        if (row) {
            row.classList.toggle('d-none');
            this.classList.toggle('open');
        }
    });
});
</script>
@endsection
