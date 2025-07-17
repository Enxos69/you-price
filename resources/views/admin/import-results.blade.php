@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title mb-0">
                                <i class="fas fa-ship me-2"></i>
                                Risultati Importazione Crociere
                            </h3>
                            <p class="mb-0 mt-1 opacity-75">
                                Riepilogo dell'ultima importazione effettuata
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('cruises.import.form') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Nuova Importazione
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <!-- Statistiche Importazione -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stats-card bg-info">
                                <div class="stats-icon">
                                    <i class="fas fa-file-csv"></i>
                                </div>
                                <div class="stats-content">
                                    <h3 class="stats-number">{{ $importStats['total_processed'] ?? 0 }}</h3>
                                    <p class="stats-label">Record Elaborati</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stats-card bg-success">
                                <div class="stats-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="stats-content">
                                    <h3 class="stats-number">{{ $importStats['total_imported'] ?? 0 }}</h3>
                                    <p class="stats-label">Importati</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stats-card bg-warning">
                                <div class="stats-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stats-content">
                                    <h3 class="stats-number">{{ $importStats['total_skipped'] ?? 0 }}</h3>
                                    <p class="stats-label">Saltati (Duplicati)</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stats-card bg-danger">
                                <div class="stats-icon">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="stats-content">
                                    <h3 class="stats-number">{{ count($importStats['errors'] ?? []) }}</h3>
                                    <p class="stats-label">Errori</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pulsanti Azione -->
                    @if(!empty($importStats['skipped_records']) || !empty($importStats['errors']))
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="bg-light rounded-3 p-3">
                                <h6 class="text-dark mb-3">
                                    <i class="fas fa-tools me-2"></i>Azioni Disponibili
                                </h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @if(!empty($importStats['skipped_records']))
                                        <a href="{{ route('cruises.import.download-skipped') }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-download me-1"></i> Scarica Record Saltati
                                        </a>
                                    @endif
                                    
                                    @if(!empty($importStats['errors']))
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#errorsModal">
                                            <i class="fas fa-exclamation-circle me-1"></i> Visualizza Errori ({{ count($importStats['errors']) }})
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- DataTable Crociere Importate -->
                    @if(($importStats['total_imported'] ?? 0) > 0)
                        <div class="table-responsive">
                            <table id="cruisesTable" class="table table-striped table-hover">
                                <thead class="table-dark">
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
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i>
                            <div>
                                <strong>Nessuna crociera importata</strong><br>
                                Non sono state importate nuove crociere durante questa operazione.
                                @if(($importStats['total_skipped'] ?? 0) > 0)
                                    Tutti i {{ $importStats['total_skipped'] }} record erano duplicati.
                                @endif
                            </div>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorsModalLabel">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Errori durante l'importazione ({{ count($importStats['errors']) }})
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="accordion" id="errorsAccordion">
                    @foreach($importStats['errors'] as $index => $error)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                                    <strong>Errore #{{ $index + 1 }}</strong>
                                    @if(isset($error['line']))
                                        <span class="badge bg-secondary ms-2">Riga {{ $error['line'] }}</span>
                                    @endif
                                    <span class="ms-auto me-3 text-danger">{{ $error['error'] }}</span>
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $index }}" data-bs-parent="#errorsAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Dettagli Errore:</h6>
                                            <div class="bg-danger bg-opacity-10 rounded p-3">
                                                <code>{{ $error['error'] }}</code>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Dati del Record:</h6>
                                            <div class="bg-light rounded p-3">
                                                <pre class="mb-0"><code>{{ json_encode($error['record'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                            </div>
                                        </div>
                                    </div>
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

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stats-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid;
    position: relative;
    overflow: hidden;
}

.stats-card.bg-info { border-left-color: #17a2b8; }
.stats-card.bg-success { border-left-color: #28a745; }
.stats-card.bg-warning { border-left-color: #ffc107; }
.stats-card.bg-danger { border-left-color: #dc3545; }

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    opacity: 0.1;
    transform: translate(30px, -30px);
}

.stats-card.bg-info::before { background: #17a2b8; }
.stats-card.bg-success::before { background: #28a745; }
.stats-card.bg-warning::before { background: #ffc107; }
.stats-card.bg-danger::before { background: #dc3545; }

.stats-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stats-card.bg-info .stats-icon { background: #17a2b8; }
.stats-card.bg-success .stats-icon { background: #28a745; }
.stats-card.bg-warning .stats-icon { background: #ffc107; }
.stats-card.bg-danger .stats-icon { background: #dc3545; }

.stats-content {
    position: relative;
    z-index: 1;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.stats-label {
    margin: 0;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}

.card {
    border-radius: 15px;
    overflow: hidden;
}

.accordion-button {
    font-weight: 500;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #495057;
}

pre code {
    font-size: 0.8rem;
    max-height: 200px;
    overflow-y: auto;
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    @if(($importStats['total_imported'] ?? 0) > 0)
    $('#cruisesTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("cruises.import.data") }}',
            type: 'GET',
            error: function(xhr, error, code) {
                console.error('Errore caricamento dati:', error);
                alert('Errore nel caricamento dei dati. Riprova più tardi.');
            }
        },
        columns: [
            { data: 'id', name: 'id', width: '50px' },
            { data: 'ship', name: 'ship' },
            { data: 'cruise', name: 'cruise' },
            { data: 'line', name: 'line' },
            { data: 'duration', name: 'duration', width: '80px' },
            { data: 'partenza', name: 'partenza', width: '100px' },
            { data: 'arrivo', name: 'arrivo', width: '100px' },
            { data: 'interior', name: 'interior', width: '100px' },
            { data: 'oceanview', name: 'oceanview', width: '100px' },
            { data: 'balcony', name: 'balcony', width: '100px' },
            { data: 'minisuite', name: 'minisuite', width: '100px' },
            { data: 'suite', name: 'suite', width: '100px' },
            { data: 'created_at', name: 'created_at', width: '130px' }
        ],
        language: {
            processing: "Caricamento...",
            search: "Cerca:",
            lengthMenu: "Mostra _MENU_ elementi",
            info: "Elementi da _START_ a _END_ di _TOTAL_ totali",
            infoEmpty: "Nessun elemento da visualizzare",
            infoFiltered: "(filtrati da _MAX_ elementi totali)",
            infoPostFix: "",
            loadingRecords: "Caricamento...",
            zeroRecords: "Nessun elemento trovato",
            emptyTable: "Nessun dato presente nella tabella",
            paginate: {
                first: "Primo",
                previous: "Precedente",
                next: "Prossimo",
                last: "Ultimo"
            }
        },
        pageLength: 25,
        responsive: true,
        order: [[0, 'desc']],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        drawCallback: function() {
            // Aggiungi tooltip per le celle con contenuto lungo
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });
    @endif
});
</script>
@endsection