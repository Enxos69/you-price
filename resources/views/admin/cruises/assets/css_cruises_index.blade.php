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

.stat-icon.available {
    background: var(--youPrice-success);
}

.stat-icon.future {
    background: var(--youPrice-info);
}

.stat-icon.companies {
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

/* Table Container */
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
}

.cruises-table thead {
    background: var(--youPrice-gradient);
}

.cruises-table thead th {
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    padding: 1rem 0.75rem;
    border: none;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
}

.cruises-table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f1f3f4;
}

.cruises-table tbody tr:hover {
    background-color: rgba(132, 188, 0, 0.05);
    transform: scale(1.005);
}

.cruises-table tbody td {
    padding: 0.75rem;
    vertical-align: middle;
    text-align: center;
    border: none;
    font-weight: 500;
    font-size: 0.9rem;
    word-wrap: break-word;
}

/* Checkbox Styling */
.form-check-input {
    border: 2px solid var(--youPrice-accent);
    border-radius: 4px;
}

.form-check-input:checked {
    background-color: var(--youPrice-primary);
    border-color: var(--youPrice-primary);
}

/* Badge Styling per Compagnie */
.badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
}

.badge.bg-info {
    background-color: var(--youPrice-info) !important;
}

.badge.bg-success {
    background-color: var(--youPrice-success) !important;
}

.badge.bg-warning {
    background-color: var(--youPrice-warning) !important;
    color: var(--youPrice-dark) !important;
}

.badge.bg-primary {
    background-color: var(--youPrice-primary) !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
    flex-wrap: nowrap;
}

.btn-sm {
    padding: 0.35rem 0.7rem;
    font-size: 0.8rem;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.btn-info {
    background: var(--youPrice-info);
    color: white;
}

.btn-info:hover {
    background: #138496;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
    color: white;
}

.btn-primary {
    background: var(--youPrice-primary);
    color: white;
}

.btn-primary:hover {
    background: #6a9c00;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(132, 188, 0, 0.3);
    color: white;
}

.btn-danger {
    background: var(--youPrice-danger);
    color: white;
}

.btn-danger:hover {
    background: #c82333;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    color: white;
}

/* Bulk Actions */
#bulkDeleteBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

#bulkDeleteBtn:not(:disabled):hover {
    background: #c82333;
    transform: translateY(-1px);
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

/* Modal Styling */
.modal-content {
    border-radius: 15px;
    border: none;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.modal-header.bg-danger {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}

.cruise-info {
    background: rgba(220, 53, 69, 0.1);
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #dc3545;
    margin: 1rem 0;
}

/* Alert Styling */
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

.alert-danger {
    background: rgba(220, 53, 69, 0.1);
    color: #721c24;
    border-left: 4px solid #dc3545;
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

/* Responsive */
@media (max-width: 1200px) {
    .cruises-table {
        font-size: 0.85rem;
    }
    
    .cruises-table th,
    .cruises-table td {
        padding: 0.5rem 0.25rem;
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
    
    .filters-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filters-container {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .cruises-table {
        min-width: 900px;
    }
}

@media (max-width: 480px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
    
    .cruises-table {
        min-width: 800px;
        font-size: 0.75rem;
    }
    
    .header-actions {
        gap: 0.5rem;
    }
    
    .btn-action {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
    }
}

/* Tooltip Styling */
[data-bs-toggle="tooltip"] {
    cursor: help;
}

.tooltip {
    font-size: 0.8rem;
}

.tooltip-inner {
    background-color: var(--youPrice-dark);
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
}

/* Animations */
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

.stat-card {
    animation: fadeInUp 0.6s ease forwards;
}

.stat-card:nth-child(2) {
    animation-delay: 0.1s;
}

.stat-card:nth-child(3) {
    animation-delay: 0.2s;
}

.stat-card:nth-child(4) {
    animation-delay: 0.3s;
}

/* Print Styles */
@media print {
    .cruises-page-wrapper {
        padding: 0;
        background: white;
    }
    
    .page-header,
    .filters-section,
    .header-actions,
    .action-buttons {
        display: none !important;
    }
    
    .cruises-card {
        box-shadow: none;
        border: 1px solid #ccc;
    }
    
    .cruises-table {
        font-size: 0.8rem;
    }
}
</style>