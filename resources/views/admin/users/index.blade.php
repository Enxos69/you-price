@extends('layouts.app')

@section('content')
    <div class="users-page-wrapper">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12">
                    
                    <div class="page-header">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="header-text">
                                <h1>Gestione Utenti</h1>
                                <p>Amministra e gestisci tutti gli utenti del sistema</p>
                            </div>
                        </div>
                        <div class="header-actions">
                            <button class="btn btn-action" onclick="refreshTable()" style="color: white">
                                <i class="fas fa-sync-alt me-2"></i>Aggiorna
                            </button>
                            <button class="btn btn-action" onclick="exportUsers()" style="color: white">
                                <i class="fas fa-download me-2"></i>Esporta
                            </button>
                        </div>
                    </div>

                    <div class="users-card">
                        <div class="card-body">                            
                            <div class="stats-row">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="totalUsers">-</div>
                                        <div class="stat-label">Totale Utenti</div>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon active">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="activeUsers">-</div>
                                        <div class="stat-label">Utenti Attivi</div>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon disabled">
                                        <i class="fas fa-user-times"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="disabledUsers">-</div>
                                        <div class="stat-label">Utenti Disabilitati</div>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon admin">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="adminUsers">-</div>
                                        <div class="stat-label">Amministratori</div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-container">
                                <table class="table users-table" id="users-table">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-user me-2"></i>Nome</th>
                                            <th><i class="fas fa-user me-2"></i>Cognome</th>
                                            <th><i class="fas fa-envelope me-2"></i>Email</th>
                                            <th><i class="fas fa-user-tag me-2"></i>Ruolo</th>
                                            <th><i class="fas fa-check-circle me-2"></i>Email Verificata</th>
                                            <th><i class="fas fa-toggle-on me-2"></i>Stato</th>
                                            <th><i class="fas fa-cogs me-2"></i>Azioni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                            <div class="loading-overlay d-none" id="loadingOverlay">
                                <div class="loading-content">
                                    <div class="spinner"></div>
                                    <p>Caricamento utenti...</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('admin.users.assets.css_gestione_utenti')

@section('scripts')
    @parent
    @include('admin.users.assets.js_gestione_utenti')
@endsection