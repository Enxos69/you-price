<style>
/* ✅ CSS OTTIMIZZATO PER CROCIERE - SOLO CLASSI NECESSARIE */

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
    --youPrice-info: #17a2b8;
}

/* Wrapper principale */
.cruises-page-wrapper {
    padding-top: 50px;
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
    flex-wrap: wrap;
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
    text-decoration: none;
}

.btn-action:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    transform: translateY(-1px);
    text-decoration: none;
}

/* Card principale */
.cruises-card {
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
    animation: fadeInUp 0.6s ease forwards;
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

.stat-card:nth-child(2) { animation-delay: 0.1s; }
.stat-card:nth-child(3) { animation-delay: 0.2s; }
.stat-card:nth-child(4) { animation-delay: 0.3s; }

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

.stat-icon.available { background: var(--youPrice-success); }
.stat-icon.future { background: var(--youPrice-info); }
.stat-icon.companies { background: var(--youPrice-accent); }

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
    align-items: end;
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

/* ✅ TABELLA OTTIMIZZATA - 8 COLONNE */
.table-container {
    position: relative;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.cruises-table {
    margin-bottom: 0;
    width: 100%;
    table-layout: fixed;
}

/* Larghezze colonne specifiche */
.cruises-table th:nth-child(1), .cruises-table td:nth-child(1) { width: 3%; min-width: 40px; text-align: center; }
.cruises-table th:nth-child(2), .cruises-table td:nth-child(2) { width: 18%; min-width: 140px; text-align: left; }
.cruises-table th:nth-child(3), .cruises-table td:nth-child(3) { width: 25%; min-width: 180px; text-align: left; }
.cruises-table th:nth-child(4), .cruises-table td:nth-child(4) { width: 15%; min-width: 120px; text-align: center; }
.cruises-table th:nth-child(5), .cruises-table td:nth-child(5) { width: 8%; min-width: 80px; text-align: center; }
.cruises-table th:nth-child(6), .cruises-table td:nth-child(6) { width: 12%; min-width: 100px; text-align: center; }
.cruises-table th:nth-child(7), .cruises-table td:nth-child(7) { width: 10%; min-width: 90px; text-align: right; }
.cruises-table th:nth-child(8), .cruises-table td:nth-child(8) { width: 9%; min-width: 85px; text-align: center; }

.cruises-table thead {
    background: var(--youPrice-gradient);
}

.cruises-table thead th {
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 0.75rem 0.5rem;
    border: none;
    vertical-align: middle;
    white-space: nowrap;
}

.cruises-table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f1f3f4;
}

.cruises-table tbody tr:hover {
    background-color: rgba(132, 188, 0, 0.05);
    transform: scale(1.002);
}

.cruises-table tbody td {
    padding: 0.5rem 0.5rem;
    vertical-align: middle;
    border: none;
    font-weight: 500;
    font-size: 0.85rem;
    word-wrap: break-word;
}

/* Form Controls */
.form-check-input {
    border: 2px solid var(--youPrice-accent);
    border-radius: 4px;
    transform: scale(0.9);
}

.form-check-input:checked {
    background-color: var(--youPrice-primary);
    border-color: var(--youPrice-primary);
}

/* Badge Compagnie */
.badge {
    font-size: 0.7rem;
    padding: 0.4rem 0.6rem;
    border-radius: 6px;
    font-weight: 600;
}

.badge.bg-info { background-color: var(--youPrice-info) !important; }
.badge.bg-success { background-color: var(--youPrice-success) !important; }
.badge.bg-warning { background-color: var(--youPrice-warning) !important; color: var(--youPrice-dark) !important; }
.badge.bg-primary { background-color: var(--youPrice-primary) !important; }
.badge.bg-secondary { background-color: #6c757d !important; }

/* Pulsanti */
.btn-group-sm .btn {
    padding: 0.25rem 0.4rem;
    font-size: 0.75rem;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-group-sm .btn i {
    font-size: 0.8rem;
}

.btn-outline-primary {
    color: var(--youPrice-primary);
    border: 1px solid var(--youPrice-primary);
}

.btn-outline-primary:hover {
    background: var(--youPrice-primary);
    color: white;
    transform: translateY(-1px);
}

.btn-outline-warning {
    color: var(--youPrice-warning);
    border: 1px solid var(--youPrice-warning);
}

.btn-outline-warning:hover {
    background: var(--youPrice-warning);
    color: var(--youPrice-dark);
    transform: translateY(-1px);
}

.btn-outline-danger {
    color: var(--youPrice-danger);
    border: 1px solid var(--youPrice-danger);
}

.btn-outline-danger:hover {
    background: var(--youPrice-danger);
    color: white;
    transform: translateY(-1px);
}

/* Testo specifico */
.fw-bold {
    font-weight: 700 !important;
    color: var(--youPrice-dark);
}

.text-truncate {
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cruises-table td:nth-child(7) {
    font-weight: 600;
    color: var(--youPrice-primary);
}

.cruises-table td:nth-child(6) small {
    font-size: 0.7rem;
    display: block;
    margin-bottom: 2px;
}

/* Loading e Stati */
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

.dataTables_processing {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    margin-left: -100px;
    margin-top: -20px;
    text-align: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    font-weight: 600;
    color: var(--youPrice-primary);
}

/* DataTables personalizzazione */
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

/* Animazioni */
@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 1200px) {
    .cruises-table th:nth-child(3), .cruises-table td:nth-child(3) { width: 20%; min-width: 150px; }
    .cruises-table th:nth-child(2), .cruises-table td:nth-child(2) { width: 15%; min-width: 120px; }
}

@media (max-width: 992px) {
    .cruises-table { font-size: 0.8rem; }
    .cruises-table th, .cruises-table td { padding: 0.4rem 0.3rem; }
}

@media (max-width: 768px) {
    .cruises-page-wrapper { padding-top: 70px; }
    .page-header { flex-direction: column; gap: 1rem; text-align: center; }
    .header-actions { width: 100%; justify-content: center; flex-wrap: wrap; }
    .filters-section { flex-direction: column; align-items: stretch; }
    .filters-container { justify-content: center; flex-wrap: wrap; }
    .card-body { padding: 1.5rem; }
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .table-container { overflow-x: auto; }
    .cruises-table { min-width: 900px; }
}

@media (max-width: 480px) {
    .stats-row { grid-template-columns: 1fr; }
    .cruises-table { min-width: 800px; font-size: 0.75rem; }
    .header-actions { gap: 0.5rem; }
    .btn-action { padding: 0.4rem 0.8rem; font-size: 0.85rem; }
}
/* ✅ CSS AGGIUNTIVO PER FORZARE LARGHEZZE COLONNE DATATABLE */

/* Forza layout fisso e disabilita auto-resize */
#cruises-table {
    table-layout: fixed !important;
    width: 100% !important;
}

/* Larghezze forzate con !important per sovrascrivere DataTables */
#cruises-table th:nth-child(1),
#cruises-table td:nth-child(1) {
    width: 3% !important;
    min-width: 40px !important;
    max-width: 3% !important;
}

#cruises-table th:nth-child(2),
#cruises-table td:nth-child(2) {
    width: 18% !important;
    min-width: 140px !important;
    max-width: 18% !important;
}

#cruises-table th:nth-child(3),
#cruises-table td:nth-child(3) {
    width: 25% !important;
    min-width: 180px !important;
    max-width: 25% !important;
}

#cruises-table th:nth-child(4),
#cruises-table td:nth-child(4) {
    width: 15% !important;
    min-width: 120px !important;
    max-width: 15% !important;
}

#cruises-table th:nth-child(5),
#cruises-table td:nth-child(5) {
    width: 8% !important;
    min-width: 80px !important;
    max-width: 8% !important;
}

#cruises-table th:nth-child(6),
#cruises-table td:nth-child(6) {
    width: 12% !important;
    min-width: 100px !important;
    max-width: 12% !important;
}

#cruises-table th:nth-child(7),
#cruises-table td:nth-child(7) {
    width: 10% !important;
    min-width: 90px !important;
    max-width: 10% !important;
}

#cruises-table th:nth-child(8),
#cruises-table td:nth-child(8) {
    width: 9% !important;
    min-width: 85px !important;
    max-width: 9% !important;
}

/* Disabilita il resize automatico di DataTables */
.dataTables_wrapper .dataTables_scroll_head table,
.dataTables_wrapper .dataTables_scroll_body table {
    table-layout: fixed !important;
}

/* Forza il contenimento del testo */
#cruises-table th,
#cruises-table td {
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    padding: 0.5rem 0.25rem !important;
}

/* Eccezioni per colonne che possono andare a capo */
#cruises-table th:nth-child(2),
#cruises-table td:nth-child(2),
#cruises-table th:nth-child(3),
#cruises-table td:nth-child(3) {
    white-space: normal !important;
}

/* Scroll orizzontale se necessario */
.table-container {
    overflow-x: auto !important;
}

.dataTables_scrollBody {
    overflow-x: auto !important;
}

/* Impedisci a DataTables di cambiare le larghezze */
.dataTables_wrapper table {
    margin: 0 !important;
}

/* Responsive fix - mantieni larghezze anche su mobile */
@media (max-width: 768px) {
    .table-container {
        overflow-x: scroll !important;
    }
    
    #cruises-table {
        min-width: 900px !important;
    }
    
    /* Mantieni le larghezze anche su mobile */
    #cruises-table th,
    #cruises-table td {
        font-size: 0.75rem !important;
        padding: 0.3rem 0.2rem !important;
    }
}


</style>