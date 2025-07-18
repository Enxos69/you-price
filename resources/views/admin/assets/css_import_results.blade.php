<style>
/* Variabili colore you-price */
:root {
    --youPrice-primary: #84bc00;     
    --youPrice-secondary: #96CEB4;   
    --youPrice-accent: #006170;      
    --youPrice-light: #e4f4f3;       
    --youPrice-dark: #2C3E50;        
    --youPrice-gradient: linear-gradient(135deg, #006170 0%, #84bc00 100%);
}

/* Wrapper principale */
.results-page-wrapper {
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
.results-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(78, 205, 196, 0.1);
    overflow: hidden;
}

.card-body {
    padding: 2rem;
}

/* Titoli sezioni */
.section-title {
    color: var(--youPrice-dark);
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 1.1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--youPrice-light);
}

/* Sezione Statistiche */
.stats-section {
    background: rgba(132, 188, 0, 0.05);
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid rgba(132, 188, 0, 0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.25rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    border-left: 4px solid;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-card.bg-info { border-left-color: #17a2b8; }
.stat-card.bg-success { border-left-color: #28a745; }
.stat-card.bg-primary { border-left-color: #007bff; }
.stat-card.bg-warning { border-left-color: #ffc107; }
.stat-card.bg-danger { border-left-color: #dc3545; }
.stat-card.bg-secondary { border-left-color: #6c757d; }

.stat-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
}

.stat-card.bg-info .stat-icon { background: #17a2b8; }
.stat-card.bg-success .stat-icon { background: #28a745; }
.stat-card.bg-primary .stat-icon { background: #007bff; }
.stat-card.bg-warning .stat-icon { background: #ffc107; }
.stat-card.bg-danger .stat-icon { background: #dc3545; }
.stat-card.bg-secondary .stat-icon { background: #6c757d; }

.stat-content {
    position: relative;
    z-index: 1;
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: var(--youPrice-dark);
}

.stat-label {
    margin: 0;
    color: #fff;
    font-weight: 500;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

/* Sezione Filtri */
.filters-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.filters-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--youPrice-accent);
    text-transform: uppercase;
    letter-spacing: 0.5px;
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

.filter-select {
    padding: 0.75rem;
    border: 2px solid transparent;
    border-radius: 8px;
    background: white;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: var(--youPrice-primary);
    box-shadow: 0 0 0 3px rgba(132, 188, 0, 0.1);
}

.filter-reset-btn {
    background: var(--youPrice-accent);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
}

.filter-reset-btn:hover {
    background: #004d5c;
    transform: translateY(-1px);
}

/* Sezione Azioni */
.actions-section {
    background: rgba(150, 206, 180, 0.1);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.action-btn {
    background: white;
    border: 2px solid transparent;
    border-radius: 10px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: inherit;
}

.action-btn.warning {
    border-color: #ffc107;
}

.action-btn.warning:hover {
    background: rgba(255, 193, 7, 0.1);
}

.action-btn.danger {
    border-color: #dc3545;
}

.action-btn.danger:hover {
    background: rgba(220, 53, 69, 0.1);
}

.action-btn.success {
    border-color: #28a745;
}

.action-btn.success:hover {
    background: rgba(40, 167, 69, 0.1);
}

.action-btn.info {
    border-color: #17a2b8;
}

.action-btn.info:hover {
    background: rgba(23, 162, 184, 0.1);
}

.action-btn i {
    font-size: 1.5rem;
}

.action-content {
    flex: 1;
}

.action-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.action-subtitle {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Sezione Tabella */
.table-section {
    margin-bottom: 1.5rem;
}

.table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.table {
    margin-bottom: 0;
}

.table thead {
    background: var(--youPrice-gradient);
}

.table thead th {
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    padding: 1rem 0.75rem;
    border: none;
    text-align: center;
    vertical-align: middle;
}

.table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f1f3f4;
}

.table tbody tr:hover {
    background-color: rgba(132, 188, 0, 0.05);
    transform: scale(1.005);
}

.table tbody td {
    padding: 0.75rem;
    vertical-align: middle;
    text-align: center;
    border: none;
    font-weight: 500;
    font-size: 0.9rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    background: #f8f9fa;
    border-radius: 12px;
}

.empty-icon {
    font-size: 3rem;
    color: var(--youPrice-accent);
    margin-bottom: 1rem;
}

.empty-state h5 {
    color: var(--youPrice-dark);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6c757d;
    margin-bottom: 1rem;
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

/* Alerts */
.alert {
    border-radius: 10px;
    border: none;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: rgba(40, 167, 69, 0.1);
    color: #155724;
    border-left: 4px solid #28a745;
}

/* Responsive */
@media (max-width: 768px) {
    .results-page-wrapper {
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
    
    .card-body {
        padding: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .table {
        min-width: 800px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>