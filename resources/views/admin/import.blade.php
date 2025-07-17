@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-ship me-2"></i>
                        Risultati Importazione Crociere
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('cruises.import.form') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuova Importazione
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistiche Importazione -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-file-csv"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Record Elaborati</span>
                                    <span class="info-box-number">{{ $importStats['total_processed'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-check"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Importati</span>
                                    <span class="info-box-number">{{ $importStats['total_imported'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Saltati (Duplicati)</span>
                                    <span class="info-box-number">{{ $importStats['total_skipped'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-times"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Errori</span>
                                    <span class="info-box-number">{{ count($importStats['errors'] ?? []) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pulsanti Azione -->
                    <div class="row mb-3">
                        <div class="col-12">
                            @if(!empty($importStats['skipped_records']))
                                <a href="{{ route('cruises.import.download-skipped') }}" class="btn btn-warning me-2">
                                    <i class="fas fa-download"></i> Scarica Record Saltati
                                </a>
                            @endif
                            
                            @if(!empty($importStats['errors']))
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#errorsModal">
                                    <i class="fas fa-exclamation-circle"></i> Visualizza Errori
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- DataTable Crociere Importate -->
                    @if($importStats['total_imported'] > 0)
                        <div class="table-responsive">
                            <table id="cruisesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nave</th>
                                        <th>Crociera</th>
                                        <th>Linea</th>
                                        <th>Durata</th>
                                        <th>Partenza</th>
                                        <th>Arrivo</th>
                                        <th>Interior</th>
                                        <th>Oceanview</th>
                                        <th>Balcony</th>
                                        <th>Mini Suite</th>
                                        <th>Suite</th>
                                        <th>Importato il</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- I dati verranno caricati via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Nessuna crociera è stata importata.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Errori -->
@if(!empty($importStats['errors']))
<div class="modal fade" id="errorsModal" tabindex="-1" aria-labelledby="errorsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorsModalLabel">Errori durante l'importazione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="accordion" id="errorsAccordion">
                    @foreach($importStats['errors'] as $index => $error)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                                    Errore #{{ $index + 1 }}: {{ $error['error'] }}
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $index }}" data-bs-parent="#errorsAccordion">
                                <div class="accordion-body">
                                    <strong>Dati del record:</strong>
                                    <pre>{{ json_encode($error['record'], JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .info-box {
        display: block;
        min-height: 90px;
        background: #fff;
        width: 100%;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        border-radius: 2px;
        margin-bottom: 15px;
    }
    .info-box-icon {
        border-top-left-radius: 2px;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 2px;
        display: block;
        float: left;
        height: 90px;
        width: 90px;
        text-align: center;
        font-size: 45px;
        line-height: 90px;
        background: rgba(0,0,0,0.2);
        color: rgba(255,255,255,0.8);
    }
    .info-box-content {
        padding: 5px 10px;
        margin-left: 90px;
    }
    .info-box-text {
        text-transform: uppercase;
        font-weight: bold;
        font-size: 14px;
    }
    .info-box-number {
        display: block;
        font-weight: bold;
        font-size: 18px;
    }
    .bg-info { background-color: #17a2b8 !important; }
    .bg-success { background-color: #28a745 !important; }
    .bg-warning { background-color: #ffc107 !important; }
    .bg-danger { background-color: #dc3545 !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    @if($importStats['total_imported'] > 0)
    $('#cruisesTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("cruises.import.data") }}',
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'ship', name: 'ship' },
            { data: 'cruise', name: 'cruise' },
            { data: 'line', name: 'line' },
            { data: 'duration', name: 'duration' },
            { data: 'partenza', name: 'partenza' },
            { data: 'arrivo', name: 'arrivo' },
            { data: 'interior', name: 'interior' },
            { data: 'oceanview', name: 'oceanview' },
            { data: 'balcony', name: 'balcony' },
            { data: 'minisuite', name: 'minisuite' },
            { data: 'suite', name: 'suite' },
            { data: 'created_at', name: 'created_at' }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/Italian.json'
        },
        pageLength: 25,
        responsive: true,
        order: [[0, 'desc']]
    });
    @endif
});
</script>
@endpush