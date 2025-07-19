<style>
/* ✅ CSS OTTIMIZZATO CON CHECKBOX ROTONDE VERDI */
/* MODALITÀ SEMPRE CHIARA - Dark mode disabilitata */

/* Variabili colore you-price - FISSE per modalità chiara */
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
    
    /* Nuove variabili per ottimizzazione */
    --table-row-height: 60px;
    --table-header-height: 50px;
    --border-radius-small: 6px;
    --border-radius-medium: 10px;
    --box-shadow-light: 0 2px 8px rgba(0, 0, 0, 0.06);
    --box-shadow-medium: 0 4px 16px rgba(0, 0, 0, 0.08);
    --transition-fast: 0.2s ease;
    --transition-medium: 0.3s ease;
    
    /* Forza sempre colori chiari */
    color-scheme: light only;
}

/* Forza modalità chiara su tutti gli elementi */
* {
    color-scheme: light only;
}

/* Override esplicito per prevenire dark mode */
html {
    color-scheme: light only;
}

body {
    background-color: white !important;
    color: #2C3E50 !important;
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
.stat-icon.expired { background: var(--youPrice-danger); }
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

/* ================== FILTRI OTTIMIZZATI ================== */
.filters-optimized {
    background: var(--youPrice-light);
    border-radius: var(--border-radius-medium);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--box-shadow-light);
}

.filters-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 1rem;
}

/* Ricerca Globale Ottimizzata */
.search-wrapper {
    flex: 1;
    max-width: 450px;
}

.search-input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 12px;
    color: var(--youPrice-accent);
    font-size: 0.9rem;
    z-index: 2;
}

.search-input-optimized {
    width: 100%;
    padding: 12px 40px 12px 35px;
    border: 2px solid transparent;
    border-radius: var(--border-radius-medium);
    background: white;
    font-size: 0.9rem;
    font-weight: 500;
    transition: var(--transition-medium);
    box-shadow: var(--box-shadow-light);
}

.search-input-optimized:focus {
    outline: none;
    border-color: var(--youPrice-primary);
    box-shadow: 0 0 0 3px rgba(132, 188, 0, 0.1);
}

.search-clear {
    position: absolute;
    right: 8px;
    background: none;
    border: none;
    color: #6c757d;
    padding: 4px;
    border-radius: 50%;
    cursor: pointer;
    transition: var(--transition-fast);
}

.search-clear:hover {
    background: rgba(108, 117, 125, 0.1);
    color: var(--youPrice-danger);
}

/* Filtri Rapidi */
.quick-filters {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.filter-group-optimized {
    position: relative;
}

.filter-select-optimized {
    padding: 10px 12px;
    border: 2px solid transparent;
    border-radius: var(--border-radius-small);
    background: white;
    font-size: 0.85rem;
    font-weight: 500;
    min-width: 140px;
    cursor: pointer;
    transition: var(--transition-medium);
    box-shadow: var(--box-shadow-light);
}

.filter-select-optimized:focus {
    outline: none;
    border-color: var(--youPrice-primary);
    box-shadow: 0 0 0 3px rgba(132, 188, 0, 0.1);
}

/* Azioni Multiple */
.bulk-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.7);
    border-radius: var(--border-radius-small);
    border: 2px dashed var(--youPrice-primary);
    animation: slideDown 0.3s ease;
}

.bulk-info {
    font-weight: 600;
    color: var(--youPrice-accent);
}

.selected-count {
    color: var(--youPrice-primary);
    font-size: 1.1rem;
}

.bulk-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-bulk {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: var(--border-radius-small);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition-fast);
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.btn-bulk-danger {
    background: var(--youPrice-danger);
    color: white;
}

.btn-bulk-danger:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.btn-bulk-secondary {
    background: #6c757d;
    color: white;
}

.btn-bulk-secondary:hover {
    background: #545b62;
    transform: translateY(-1px);
}

/* ================== CHECKBOX ROTONDE COME NELL'IMMAGINE ================== */

.checkbox-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
}

/* CHECKBOX ROTONDE COMPLETAMENTE PERSONALIZZATE */
.checkbox-optimized {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    width: 22px !important;
    height: 22px !important;
    border: 2px solid #d1d5db !important;
    border-radius: 50% !important; /* ROTONDE */
    background: white !important;
    cursor: pointer !important;
    position: relative !important;
    transition: all 0.2s ease !important;
    margin: 0 !important;
    vertical-align: middle !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
}

.checkbox-optimized:hover {
    border-color: #84bc00 !important;
    box-shadow: 0 0 0 3px rgba(132, 188, 0, 0.1) !important;
    transform: scale(1.05) !important;
}

.checkbox-optimized:focus {
    outline: none !important;
    box-shadow: 0 0 0 4px rgba(132, 188, 0, 0.2) !important;
}

/* STATO SELEZIONATO - ESATTO COME L'IMMAGINE */
.checkbox-optimized:checked {
    background: #84bc00 !important; /* Verde youPrice */
    border-color: #84bc00 !important;
    box-shadow: 0 2px 8px rgba(132, 188, 0, 0.3) !important;
}

.checkbox-optimized:checked::after {
    content: '✓' !important;
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    color: white !important;
    font-weight: 900 !important;
    font-size: 14px !important;
    line-height: 1 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
    animation: checkmarkBounce 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important;
}

/* Animazione spunta con bounce */
@keyframes checkmarkBounce {
    0% {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.3) rotate(-15deg);
    }
    50% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1.2) rotate(5deg);
    }
    100% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1) rotate(0deg);
    }
}

/* Checkbox nelle righe della tabella */
.table-optimized .checkbox-optimized {
    width: 20px !important;
    height: 20px !important;
}

.table-optimized .checkbox-optimized:checked::after {
    font-size: 12px !important;
}

/* Checkbox nel header */
.table-head-optimized .checkbox-optimized {
    border-color: rgba(255, 255, 255, 0.6) !important;
    background: rgba(255, 255, 255, 0.9) !important;
}

.table-head-optimized .checkbox-optimized:checked {
    background: white !important;
    border-color: white !important;
}

.table-head-optimized .checkbox-optimized:checked::after {
    color: #84bc00 !important; /* Spunta verde su sfondo bianco */
}

/* Stato indeterminato */
.checkbox-optimized:indeterminate {
    background: #fbbf24 !important;
    border-color: #fbbf24 !important;
}

.checkbox-optimized:indeterminate::after {
    content: '−' !important;
    color: white !important;
    font-weight: 900 !important;
    font-size: 18px !important;
}

.table-head-optimized .checkbox-optimized:indeterminate {
    background: white !important;
    border-color: white !important;
}

.table-head-optimized .checkbox-optimized:indeterminate::after {
    color: #fbbf24 !important;
}

.checkbox-label {
    margin: 0;
    cursor: pointer;
}

/* ================== TABELLA OTTIMIZZATA ================== */
.table-wrapper-optimized {
    position: relative;
    background: white;
    border-radius: var(--border-radius-medium);
    overflow: hidden;
    box-shadow: var(--box-shadow-medium);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.table-container-optimized {
    position: relative;
    overflow-x: auto;
    max-height: 70vh;
    overflow-y: auto;
}

/* Scrollbar personalizzata */
.table-container-optimized::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-container-optimized::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.table-container-optimized::-webkit-scrollbar-thumb {
    background: var(--youPrice-secondary);
    border-radius: 4px;
}

.table-container-optimized::-webkit-scrollbar-thumb:hover {
    background: var(--youPrice-primary);
}

.table-optimized {
    width: 100%;
    margin-bottom: 0;
    table-layout: fixed;
    border-spacing: 0;
    border-collapse: separate;
}

/* Header Ottimizzato */
.table-head-optimized {
    position: sticky;
    top: 0;
    z-index: 10;
    background: var(--youPrice-gradient);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.table-head-optimized th {
    height: var(--table-header-height);
    padding: 0.75rem 0.5rem;
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
    vertical-align: middle;
    user-select: none;
}

/* Larghezze colonne ottimizzate */
.col-checkbox { width: 50px; min-width: 50px; }
.col-ship { 
    width: 20%; 
    min-width: 150px; 
}
.col-cruise { width: 30%; min-width: 200px; }
.col-company { width: 15%; min-width: 120px; }
.col-duration { width: 10%; min-width: 80px; }
.col-price { width: 12%; min-width: 100px; }
.col-actions { width: 13%; min-width: 120px; }

/* Override per colonna nave - no truncate */
.col-ship .ship-name {
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: unset !important;
    word-wrap: break-word !important;
    line-height: 1.3 !important;
}

/* Contenuto intestazione ordinabile */
.th-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: var(--transition-fast);
}

.sortable:hover .th-content {
    color: rgba(255, 255, 255, 0.8);
}

.sort-icon {
    font-size: 0.7rem;
    opacity: 0.6;
    transition: var(--transition-fast);
}

.sortable.asc .sort-icon:before {
    content: "\f0de"; /* fa-sort-up */
    opacity: 1;
}

.sortable.desc .sort-icon:before {
    content: "\f0dd"; /* fa-sort-down */
    opacity: 1;
}

/* Righe tabella */
.table-optimized tbody tr {
    height: var(--table-row-height);
    border-bottom: 1px solid #f1f3f4;
    transition: var(--transition-fast);
    cursor: pointer;
}

.table-optimized tbody tr:hover {
    background-color: rgba(132, 188, 0, 0.05);
    transform: scale(1.001);
}

.table-optimized tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border: none;
    font-size: 0.85rem;
    font-weight: 500;
    /* Rimosso: overflow: hidden; text-overflow: ellipsis; white-space: nowrap; */
}

/* Applica truncate solo alle colonne specifiche */
.table-optimized tbody td:not(.col-ship):not(.col-actions) {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Contenuto celle specifiche */
.ship-name {
    font-weight: 700;
    color: var(--youPrice-dark);
    white-space: normal !important;
    word-wrap: break-word;
    overflow: visible !important;
    text-overflow: unset !important;
}

.cruise-title {
    color: var(--youPrice-dark);
    line-height: 1.3;
}

.cruise-title.truncated {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.company-badge {
    display: inline-block;
    padding: 0.3rem 0.7rem;
    border-radius: var(--border-radius-small);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.company-msc { background: #007bff; color: white; }
.company-costa { background: #28a745; color: white; }
.company-royal { background: #ffc107; color: #212529; }
.company-norwegian { background: #6f42c1; color: white; }
.company-default { background: #6c757d; color: white; }

.duration-text {
    font-weight: 600;
    color: var(--youPrice-accent);
}

.price-value {
    font-weight: 700;
    color: var(--youPrice-primary);
    font-size: 0.9rem;
}

.price-na {
    color: #6c757d;
    font-style: italic;
}

/* Pulsanti azioni */
.actions-group {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
}

.action-btn {
    padding: 0.3rem 0.5rem;
    border: none;
    border-radius: var(--border-radius-small);
    font-size: 0.75rem;
    cursor: pointer;
    transition: var(--transition-fast);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.action-btn-view {
    background: var(--youPrice-info);
    color: white;
}

.action-btn-edit {
    background: var(--youPrice-warning);
    color: var(--youPrice-dark);
}

.action-btn-delete {
    background: var(--youPrice-danger);
    color: white;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* ================== STATI TABELLA ================== */
.table-loading {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 20;
    backdrop-filter: blur(1px);
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(132, 188, 0, 0.2);
    border-top: 3px solid var(--youPrice-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

.loading-text {
    color: var(--youPrice-accent);
    font-weight: 600;
    font-size: 0.9rem;
}

.table-empty {
    padding: 4rem 2rem;
    text-align: center;
    background: white;
}

.empty-icon {
    font-size: 4rem;
    color: #e9ecef;
    margin-bottom: 1rem;
}

.empty-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--youPrice-dark);
    margin-bottom: 0.5rem;
}

.empty-subtitle {
    color: #6c757d;
    font-size: 0.9rem;
}

/* ================== PAGINAZIONE OTTIMIZZATA ================== */
.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.pagination-info {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-size-select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: var(--border-radius-small);
    background: white;
    font-size: 0.85rem;
    cursor: pointer;
    transition: var(--transition-fast);
}

.page-size-select:focus {
    outline: none;
    border-color: var(--youPrice-primary);
    box-shadow: 0 0 0 2px rgba(132, 188, 0, 0.1);
}

.pagination-buttons {
    display: flex;
    gap: 0.25rem;
}

.page-btn {
    padding: 0.5rem 0.75rem;
    border: 1px solid #ced4da;
    background: white;
    color: var(--youPrice-dark);
    border-radius: var(--border-radius-small);
    cursor: pointer;
    transition: var(--transition-fast);
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
}

.page-btn:hover:not(.disabled) {
    background: var(--youPrice-primary);
    color: white;
    border-color: var(--youPrice-primary);
    text-decoration: none;
}

.page-btn.active {
    background: var(--youPrice-accent);
    color: white;
    border-color: var(--youPrice-accent);
}

.page-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-btn-prev,
.page-btn-next {
    padding: 0.5rem 1rem;
}

.page-ellipsis {
    padding: 0.5rem 0.25rem;
    color: #6c757d;
    display: inline-flex;
    align-items: center;
}

/* ================== FORZATURA MODALITÀ CHIARA ================== */
/* Assicura che tutti gli elementi mantengano sempre l'aspetto chiaro */

/* Input e form sempre chiari */
input, select, textarea, button {
    color-scheme: light only !important;
    background-color: white !important;
    color: #2C3E50 !important;
}

/* Tabella sempre chiara */
.table-optimized,
.table-optimized * {
    color-scheme: light only !important;
}

.table-optimized tbody tr {
    background-color: white !important;
}

.table-optimized tbody tr:hover {
    background-color: rgba(132, 188, 0, 0.05) !important;
}

/* Card sempre chiara */
.cruises-card {
    background: white !important;
    color: #2C3E50 !important;
}

/* Filtri sempre chiari */
.search-input-optimized,
.filter-select-optimized,
.page-size-select {
    background: white !important;
    color: #2C3E50 !important;
    border-color: #ced4da !important;
}

/* Paginazione sempre chiara */
.pagination-wrapper {
    background: #f8f9fa !important;
    color: #2C3E50 !important;
}

.page-btn {
    background: white !important;
    color: #2C3E50 !important;
    border-color: #ced4da !important;
}

/* ================== ANIMAZIONI ================== */
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

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.animate-pulse {
    animation: pulse 1s ease-in-out;
}

/* ================== RESPONSIVE OTTIMIZZATO ================== */
@media (max-width: 1200px) {
    .col-cruise { width: 25%; min-width: 180px; }
@media (max-width: 1200px) {
    .col-cruise { width: 25%; min-width: 180px; }
    .col-ship { width: 18%; min-width: 130px; }
}

@media (max-width: 992px) {
    .filters-top-row {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .search-wrapper {
        max-width: none;
    }
    
    .quick-filters {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .table-optimized {
        font-size: 0.8rem;
    }
    
    .table-head-optimized th,
    .table-optimized tbody td {
        padding: 0.5rem 0.3rem;
    }
    
    :root {
        --table-row-height: 55px;
        --table-header-height: 45px;
    }
    
    /* Checkbox responsive */
    .checkbox-optimized {
        width: 20px !important;
        height: 20px !important;
    }
    
    .checkbox-optimized:checked::after {
        font-size: 12px !important;
    }
}

@media (max-width: 768px) {
    .cruises-page-wrapper {
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
        flex-wrap: wrap;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .bulk-actions {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .pagination-wrapper {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .table-container-optimized {
        max-height: 60vh;
    }
    
    /* Scroll orizzontale forzato su mobile */
    .table-optimized {
        min-width: 800px;
    }
    
    .table-container-optimized.mobile-layout {
        overflow-x: scroll;
    }
    
    /* Checkbox mobile */
    .checkbox-optimized {
        width: 18px !important;
        height: 18px !important;
    }
    
    .checkbox-optimized:checked::after {
        font-size: 11px !important;
    }
}

@media (max-width: 480px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
    
    .quick-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group-optimized {
        width: 100%;
    }
    
    .filter-select-optimized {
        width: 100%;
        min-width: auto;
    }
    
    .table-optimized {
        min-width: 700px;
        font-size: 0.75rem;
    }
    
    .header-actions {
        gap: 0.5rem;
    }
    
    .btn-action {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
    }
    
    :root {
        --table-row-height: 50px;
        --table-header-height: 40px;
    }
    
    /* Checkbox molto piccole su mobile */
    .checkbox-optimized {
        width: 16px !important;
        height: 16px !important;
    }
    
    .checkbox-optimized:checked::after {
        font-size: 10px !important;
    }
}

/* ================== PERFORMANCE OTTIMIZZAZIONI ================== */
/* Riduce il repaint delle animazioni */
.table-optimized tbody tr,
.action-btn,
.page-btn,
.btn-bulk,
.checkbox-optimized {
    will-change: transform;
}

/* Disabilita animazioni se l'utente preferisce ridurre il movimento */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
    
    .checkbox-optimized:checked::after {
        animation: none !important;
    }
}

/* ================== ACCESSIBILITÀ ================== */
/* Focus visibili per navigazione da tastiera */
.search-input-optimized:focus,
.filter-select-optimized:focus,
.page-size-select:focus,
.checkbox-optimized:focus,
.action-btn:focus,
.page-btn:focus {
    outline: 2px solid var(--youPrice-primary);
    outline-offset: 2px;
}

/* Contrasto migliorato */
.table-head-optimized th {
    color: white;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Indicatori di stato per screen reader */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* ================== UTILITIES ================== */
.text-center { text-align: center !important; }
.text-left { text-align: left !important; }
.text-right { text-align: right !important; }

.d-none { display: none !important; }
.d-block { display: block !important; }
.d-flex { display: flex !important; }

.me-1 { margin-right: 0.25rem !important; }
.me-2 { margin-right: 0.5rem !important; }
.mb-0 { margin-bottom: 0 !important; }

.fw-bold { font-weight: 700 !important; }

/* ================== SWEETALERT CUSTOMIZATIONS ================== */
.swal-optimized {
    border-radius: var(--border-radius-medium) !important;
    font-family: inherit !important;
}

.swal2-popup.swal-optimized .swal2-title {
    color: var(--youPrice-dark) !important;
    font-weight: 600 !important;
}

.swal2-popup.swal-optimized .swal2-content {
    color: #6c757d !important;
}

.swal2-popup.swal-optimized .swal2-confirm {
    background: var(--youPrice-primary) !important;
    border-radius: var(--border-radius-small) !important;
}

.swal2-popup.swal-optimized .swal2-cancel {
    background: #6c757d !important;
    border-radius: var(--border-radius-small) !important;
}

.swal2-toast {
    border-radius: var(--border-radius-small) !important;
}

/* ================== PRINT STYLES ================== */
@media print {
    .cruises-page-wrapper {
        background: white !important;
        padding: 0 !important;
    }
    
    .page-header,
    .filters-optimized,
    .bulk-actions,
    .pagination-wrapper,
    .action-btn {
        display: none !important;
    }
    
    .table-wrapper-optimized {
        box-shadow: none !important;
        border: 1px solid #000 !important;
    }
    
    .table-optimized {
        font-size: 10px !important;
    }
    
    .table-head-optimized {
        background: #f8f9fa !important;
        color: #000 !important;
    }
    
    .company-badge {
        background: transparent !important;
        color: #000 !important;
        border: 1px solid #000 !important;
    }
    
    .checkbox-optimized {
        width: 12px !important;
        height: 12px !important;
        print-color-adjust: exact !important;
    }
}

/* ================== HIGH CONTRAST MODE ================== */
@media (prefers-contrast: high) {
    :root {
        --youPrice-primary: #000080;
        --youPrice-accent: #000000;
        --youPrice-danger: #800000;
        --youPrice-success: #008000;
        --youPrice-warning: #808000;
        --youPrice-info: #008080;
    }
    
    .table-optimized tbody tr {
        border-bottom: 2px solid #000;
    }
    
    .action-btn {
        border: 2px solid #000 !important;
    }
    
    .company-badge {
        border: 2px solid #000 !important;
    }
    
    .checkbox-optimized {
        border-width: 3px !important;
    }
    
    .checkbox-optimized:checked {
        background: #000080 !important;
        border-color: #000080 !important;
    }
}

/* ================== CUSTOM SCROLLBARS ================== */
/* Firefox */
* {
    scrollbar-width: thin;
    scrollbar-color: var(--youPrice-secondary) #f1f1f1;
}

/* Webkit browsers */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: var(--youPrice-secondary);
    border-radius: 4px;
    border: 1px solid #f1f1f1;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--youPrice-primary);
}

::-webkit-scrollbar-corner {
    background: #f1f1f1;
}

/* ================== SELECTION STYLES ================== */
::selection {
    background: rgba(132, 188, 0, 0.3);
    color: var(--youPrice-dark);
}

::-moz-selection {
    background: rgba(132, 188, 0, 0.3);
    color: var(--youPrice-dark);
}

/* ================== LOADING STATES ================== */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.skeleton-row {
    height: var(--table-row-height);
    margin-bottom: 1px;
}

.skeleton-text {
    height: 1rem;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.skeleton-text.short { width: 60%; }
.skeleton-text.medium { width: 80%; }
.skeleton-text.long { width: 100%; }

/* ================== ERROR STATES ================== */
.error-state {
    padding: 2rem;
    text-align: center;
    color: var(--youPrice-danger);
}

.error-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.error-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.error-message {
    color: #6c757d;
    margin-bottom: 1rem;
}

.error-retry {
    background: var(--youPrice-primary);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius-small);
    cursor: pointer;
    transition: var(--transition-fast);
}

.error-retry:hover {
    background: #6ba300;
    transform: translateY(-1px);
}

/* ================== PERFORMANCE OPTIMIZATIONS FINALI ================== */
/* GPU acceleration per elementi animati */
.table-optimized tbody tr,
.action-btn,
.page-btn,
.btn-bulk,
.loading-spinner,
.checkbox-optimized {
    transform: translateZ(0);
    backface-visibility: hidden;
    perspective: 1000;
}

/* Ottimizzazione rendering per large lists */
.table-optimized tbody {
    contain: layout style paint;
}

/* Prevenzione layout thrashing */
.table-optimized th,
.table-optimized td {
    contain: layout style;
}

/* ================== DEBUG UTILITIES (rimuovere in produzione) ================== */
.debug-performance * {
    outline: 1px solid red !important;
}

.debug-performance .table-optimized {
    background: rgba(255, 0, 0, 0.1) !important;
}

/* ================== FINE CSS ================== */
</style>