<style>
/* Variabili colore you-price */
:root {
    --youPrice-primary: #84bc00;     
    --youPrice-secondary: #96CEB4;   
    --youPrice-accent: #006170;      
    --youPrice-light: #e4f4f3;       
    --youPrice-dark: #2C3E50;        
    --youPrice-gradient: linear-gradient(135deg, #006170 0%, #84bc00 100%);
    --youPrice-danger: #dc3545;
    --youPrice-warning: #ffc107;
    --youPrice-success: #28a745;
}

/* Wrapper principale */
.users-page-wrapper {
    padding-top: 90px;
    padding-bottom: 20px;
    background: linear-gradient(135deg, #f8fdfc 0%, #e8f5f3 100%);
    min-height: 100vh;
}

/* Header della pagina */
.page-header {
    background: var(--youPrice-gradient);
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 8px 25px rgba(78, 205, 196, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    backdrop-filter: blur(10px);
}

.header-text h1 {
    color: white;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-text p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    margin-bottom: 0;
}

.header-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-action {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.btn-action:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    transform: translateY(-1px);
}

/* Card principale */
.users-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(78, 205, 196, 0.1);
    overflow: hidden;
}

.card-body {
    padding: 2rem;
}

/* Statistics Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 1px solid rgba(78, 205, 196, 0.1);
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--youPrice-primary);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    background: var(--youPrice-primary);
}

.stat-icon.active {
    background: var(--youPrice-success);
}

.stat-icon.disabled {
    background: var(--youPrice-danger);
}

.stat-icon.admin {
    background: var(--youPrice-accent);
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--youPrice-dark);
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

/* Filters Section */
.filters-section {
    background: var(--youPrice-light);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.search-container {
    flex: 1;
    max-width: 400px;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.search-box i {
    position: absolute;
    left: 1rem;
    color: var(--youPrice-accent);
    z-index: 2;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 2px solid transparent;
    border-radius: 10px;
    background: white;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: var(--youPrice-primary);
    box-shadow: 0 0 0 3px rgba(132, 188, 0, 0.1);
}

.filters-container {
    display: flex;
    gap: 1rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.filter-group label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--youPrice-accent);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-select {
    padding: 0.5rem 0.75rem;
    border: 2px solid transparent;
    border-radius: 8px;
    background: white;
    font-size: 0.9rem;
    min-width: 140px;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: var(--youPrice-primary);
    box-shadow: 0 0 0 3px rgba(132, 188, 0, 0.1);
}

/* Table Container - FIX RESPONSIVE */
.table-container {
    position: relative;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.users-table {
    margin-bottom: 0;
    width: 100%;
    table-layout: fixed; /* FIX: Layout fisso per controllare le larghezze */
}

/* FIX: Larghezze colonne ottimizzate */
.users-table th:nth-child(1), /* Nome */
.users-table td:nth-child(1) {
    width: 15%;
    min-width: 120px;
}

.users-table th:nth-child(2), /* Cognome */
.users-table td:nth-child(2) {
    width: 15%;
    min-width: 120px;
}

.users-table th:nth-child(3), /* Email */
.users-table td:nth-child(3) {
    width: 25%;
    min-width: 200px;
}

.users-table th:nth-child(4), /* Ruolo */
.users-table td:nth-child(4) {
    width: 15%;
    min-width: 130px; /* FIX: Larghezza minima per evitare wrapping */
}

.users-table th:nth-child(5), /* Stato */
.users-table td:nth-child(5) {
    width: 12%;
    min-width: 110px;
}

.users-table th:nth-child(6), /* Azioni */
.users-table td:nth-child(6) {
    width: 18%;
    min-width: 140px;
}

.users-table thead {
    background: var(--youPrice-gradient);
}

.users-table thead th {
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem; /* FIX: Font più piccolo per header */
    letter-spacing: 0.5px;
    padding: 0.75rem 0.5rem; /* FIX: Padding ridotto */
    border: none;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap; /* FIX: Evita wrapping negli header */
}

.users-table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f1f3f4;
}

.users-table tbody tr:hover {
    background-color: rgba(132, 188, 0, 0.05);
    transform: scale(1.005); /* FIX: Trasformazione ridotta */
}

.users-table tbody td {
    padding: 0.75rem 0.5rem; /* FIX: Padding ridotto */
    vertical-align: middle;
    text-align: center;
    border: none;
    font-weight: 500;
    font-size: 0.9rem; /* FIX: Font leggermente più piccolo */
    word-wrap: break-word; /* FIX: Gestisce testi lunghi */
    overflow: hidden;
}

/* FIX: Gestione testi lunghi nelle celle */
.users-table tbody td:nth-child(3) { /* Email */
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}

/* Status Badges - FIX COMPATTI */
.status-badge {
    padding: 0.3rem 0.6rem; /* FIX: Padding ridotto */
    border-radius: 15px; /* FIX: Border radius ridotto */
    font-size: 0.7rem; /* FIX: Font più piccolo */
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    white-space: nowrap; /* FIX: Evita wrapping */
}

.status-badge.active {
    background: rgba(40, 167, 69, 0.1);
    color: var(--youPrice-success);
    border: 1px solid rgba(40, 167, 69, 0.2);
}

.status-badge.disabled {
    background: rgba(220, 53, 69, 0.1);
    color: var(--youPrice-danger);
    border: 1px solid rgba(220, 53, 69, 0.2);
}

/* Role Badges - FIX COMPATTI */
.role-badge {
    padding: 0.3rem 0.6rem; /* FIX: Padding ridotto */
    border-radius: 15px;
    font-size: 0.7rem; /* FIX: Font più piccolo */
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap; /* FIX: Evita wrapping del testo */
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
}

.role-badge.admin {
    background: rgba(0, 97, 112, 0.1);
    color: var(--youPrice-accent);
    border: 1px solid rgba(0, 97, 112, 0.2);
}

.role-badge.user {
    background: rgba(132, 188, 0, 0.1);
    color: var(--youPrice-primary);
    border: 1px solid rgba(132, 188, 0, 0.2);
}

/* Action Buttons - FIX COMPATTI */
.action-buttons {
    display: flex;
    gap: 0.3rem; /* FIX: Gap ridotto */
    justify-content: center;
    flex-wrap: nowrap; /* FIX: Evita wrapping */
}

.btn-sm {
    padding: 0.3rem 0.6rem; /* FIX: Padding ridotto */
    font-size: 0.75rem; /* FIX: Font più piccolo */
    border-radius: 5px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    min-width: 32px; /* FIX: Larghezza minima */
    height: 28px; /* FIX: Altezza fissa */
}

.btn-primary {
    background: var(--youPrice-primary);
    color: white;
}

.btn-primary:hover {
    background: #6a9c00;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(132, 188, 0, 0.3);
}

.btn-danger {
    background: var(--youPrice-danger);
    color: white;
}

.btn-danger:hover {
    background: #c82333;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.btn-success {
    background: var(--youPrice-success);
    color: white;
}

.btn-success:hover {
    background: #218838;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-info {
    background: var(--youPrice-accent);
    color: white;
}

.btn-info:hover {
    background: #004d5c;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 97, 112, 0.3);
}

/* Loading Overlay */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    backdrop-filter: blur(2px);
}

.loading-content {
    text-align: center;
    color: var(--youPrice-accent);
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(0, 97, 112, 0.1);
    border-left-color: var(--youPrice-accent);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* DataTables Custom Styling */
.dataTables_wrapper {
    padding: 0;
}

.dataTables_length,
.dataTables_filter,
.dataTables_info,
.dataTables_paginate {
    margin: 1rem 0;
}

.dataTables_length select,
.dataTables_filter input {
    border: 2px solid transparent;
    border-radius: 6px;
    padding: 0.5rem;
    transition: all 0.3s ease;
}

.dataTables_length select:focus,
.dataTables_filter input:focus {
    outline: none;
    border-color: var(--youPrice-primary);
    box-shadow: 0 0 0 3px rgba(132, 188, 0, 0.1);
}

.paginate_button {
    padding: 0.5rem 0.75rem !important;
    margin: 0 0.25rem !important;
    border-radius: 6px !important;
    border: 1px solid transparent !important;
    transition: all 0.3s ease !important;
}

.paginate_button:hover {
    background: var(--youPrice-primary) !important;
    color: white !important;
    border-color: var(--youPrice-primary) !important;
}

.paginate_button.current {
    background: var(--youPrice-accent) !important;
    color: white !important;
    border-color: var(--youPrice-accent) !important;
}

/* FIX RESPONSIVE - Schermi piccoli */
@media (max-width: 1200px) {
    .users-table {
        font-size: 0.8rem;
    }
    
    .users-table th,
    .users-table td {
        padding: 0.5rem 0.25rem;
    }
    
    .role-badge,
    .status-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.4rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.4rem;
        font-size: 0.7rem;
        min-width: 28px;
        height: 24px;
    }
}

@media (max-width: 768px) {
    .users-page-wrapper {
        padding-top: 70px;
    }
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .header-actions {
        width: 100%;
        justify-content: center;
    }
    
    .filters-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filters-container {
        justify-content: center;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    /* FIX: Tabella mobile con scroll orizzontale */
    .table-container {
        overflow-x: auto;
    }
    
    .users-table {
        min-width: 700px; /* FIX: Larghezza minima per scroll orizzontale */
    }
}

@media (max-width: 480px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
    
    .users-table {
        min-width: 600px;
        font-size: 0.75rem;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 0.2rem;
    }
}
</style>