@extends('layouts.app')

@section('content')
<div class="price-history-wrapper">
    <div class="container-fluid">

        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Variazioni Prezzi Crociere</h2>
                        <p class="text-muted mb-0">Monitoraggio storico prezzi per partenza e categoria cabina</p>
                    </div>
                    <button id="refresh-btn" class="btn btn-outline-primary">
                        <i class="fas fa-sync-alt"></i> Aggiorna
                    </button>
                </div>
            </div>
        </div>

        {{-- Top 10 Variazioni --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line mr-2"></i>Top 10 Variazioni (ultimi 30 giorni)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="top10-container">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Caricamento...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ricerca --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-search mr-2"></i>Cerca Crociera</h5>
                    </div>
                    <div class="card-body">
                        <form id="search-form">
                            <div class="form-row align-items-end">
                                <div class="col-md-4 mb-2">
                                    <label for="search-input">Nome crociera</label>
                                    <input type="text" id="search-input" class="form-control"
                                           placeholder="Es. Mediterraneo..." minlength="2">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label for="season-select">Stagione</label>
                                    <select id="season-select" class="form-control">
                                        <option value="">— Stagione —</option>
                                        <option value="spring">Primavera (mar–mag)</option>
                                        <option value="summer">Estate (giu–ago)</option>
                                        <option value="autumn">Autunno (set–nov)</option>
                                        <option value="winter">Inverno (dic–feb)</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label for="from-date">Da</label>
                                    <input type="date" id="from-date" class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label for="to-date">A</label>
                                    <input type="date" id="to-date" class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Cerca
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div id="search-results"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dettaglio Partenza --}}
        <div id="detail-section" class="row mb-4 d-none">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history mr-2"></i>Andamento prezzi:
                            <span id="detail-title" class="text-primary"></span>
                        </h5>
                    </div>
                    <div class="card-body" id="detail-container">
                        <div id="ph-cat-pills" class="d-none mb-3"></div>
                        <div id="price-chart" class="mb-4"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Cabina</th>
                                        <th>Prezzo min</th>
                                        <th>Prezzo max</th>
                                        <th>Prezzo attuale</th>
                                        <th>Δ primo rilievo → attuale</th>
                                    </tr>
                                </thead>
                                <tbody id="detail-table-body"></tbody>
                            </table>
                        </div>
                        <p id="ph-table-note" class="text-muted small mt-1 mb-0"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Analisi Stagionale --}}
        <div id="seasonal-section" class="row mb-4 d-none">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar mr-2"></i>Analisi Stagionale —
                            <span id="seasonal-itinerary-name" class="text-primary"></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="seasonal-cat-wrapper" class="d-none d-flex align-items-center flex-wrap mb-3" style="gap:12px;">
                            <div class="d-flex align-items-center mr-3">
                                <label class="mr-2 mb-0 text-muted small" style="white-space:nowrap;">Tipo cabina:</label>
                                <select id="seasonal-macro-select" class="form-control form-control-sm" style="max-width:180px;"></select>
                            </div>
                            <div id="seasonal-sub-wrapper" class="d-flex align-items-center" style="display:none!important;">
                                <label class="mr-2 mb-0 text-muted small">Cabina:</label>
                                <select id="seasonal-sub-select" class="form-control form-control-sm" style="max-width:120px;"></select>
                            </div>
                        </div>
                        <ul class="nav nav-tabs mb-3" id="seasonal-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#seasonal-panel-weekly" role="tab">
                                    <i class="fas fa-chart-line mr-1"></i>Evoluzione settimanale
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#seasonal-panel-monthly" role="tab">
                                    <i class="fas fa-calendar-alt mr-1"></i>Stagionalità mensile
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="seasonal-panel-weekly" role="tabpanel">
                                <p id="seasonal-weekly-msg" class="text-center text-muted py-3 mb-0" style="font-size:13px;"></p>
                                <div id="seasonal-weekly-chart"></div>
                            </div>
                            <div class="tab-pane fade" id="seasonal-panel-monthly" role="tabpanel">
                                <p id="seasonal-monthly-msg" class="text-center text-muted py-3 mb-0" style="font-size:13px;"></p>
                                <div id="seasonal-monthly-chart"></div>
                            </div>
                        </div>
                        <p id="seasonal-note" class="text-muted small mt-2 mb-0"></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
@include('admin.price-history.assets.css')
@section('scripts')
    @parent
    @include('admin.price-history.assets.js')
@endsection
