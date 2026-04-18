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
                        <div id="price-chart" class="mb-4"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Data rilevazione</th>
                                        <th>Categoria</th>
                                        <th>Prezzo</th>
                                        <th>Δ vs precedente</th>
                                        <th>Fonte</th>
                                    </tr>
                                </thead>
                                <tbody id="detail-table-body"></tbody>
                            </table>
                        </div>
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
