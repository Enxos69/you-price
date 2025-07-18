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
.cruise-form-wrapper {
    padding-top: 90px;
    padding-bottom: 40px;
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
.form-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(78, 205, 196, 0.1);
    overflow: hidden;
}

.card-body {
    padding: 2.5rem;
}

/* Form Sections */
.form-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid rgba(78, 205, 196, 0.1);
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 2rem;
}

.section-title {
    color: var(--youPrice-accent);
    font-weight: 600;
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--youPrice-light);
    display: flex;
    align-items: center;
}

/* Form Groups */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: var(--youPrice-dark);
    margin-bottom: 0.5rem;
    display: block;
    font-size: 0.95rem;
}

.form-label.required::after {
    content: ' *';
    color: var(--youPrice-danger);
    font-weight: bold;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #fff;
}

.form-control:focus {
    outline: none;
    border-color: var(--youPrice-primary);
    box-shadow: 0 0 0 3px rgba(132, 188, 0, 0.1);
    background: #fff;
}

.form-control.is-invalid {
    border-color: var(--youPrice-danger);
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

.form-control.is-valid {
    border-color: var(--youPrice-success);
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
}

/* Select Styling */
select.form-control {
    background-image: url("data:image/svg+xml;charset=utf-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
    padding-right: 2.5rem;
}

/* Textarea Styling */
textarea.form-control {
    resize: vertical;
    min-height: 120px;
}

/* Invalid Feedback */
.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: var(--youPrice-danger);
    font-weight: 500;
}

/* Info Items (per edit/show) */
.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-item:last-child {
    border-bottom: none;
}

.info-item label {
    font-weight: 600;
    color: var(--youPrice-accent);
    margin-bottom: 0;
    font-size: 0.9rem;
}

.info-item span {
    color: var(--youPrice-dark);
    font-weight: 500;
}

/* Form Actions */
.form-actions {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    align-items: center;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.form-actions .btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 140px;
    justify-content: center;
}

.btn-primary {
    background: var(--youPrice-primary);
    color: white;
}

.btn-primary:hover {
    background: #6a9c00;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(132, 188, 0, 0.3);
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    color: white;
}

.btn-outline-secondary {
    background: transparent;
    color: #6c757d;
    border: 2px solid #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-1px);
}

.btn-outline-info {
    background: transparent;
    color: var(--youPrice-accent);
    border: 2px solid var(--youPrice-accent);
}

.btn-outline-info:hover {
    background: var(--youPrice-accent);
    color: white;
    transform: translateY(-1px);
}

/* Loading State */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Alert Messages */
.alert {
    border-radius: 10px;
    border: none;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    animation: slideDown 0.3s ease;
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

/* Grid Layout */
.row {
    margin-left: -0.75rem;
    margin-right: -0.75rem;
}

.row > [class*="col-"] {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}

/* Responsive */
@media (max-width: 768px) {
    .cruise-form-wrapper {
        padding-top: 70px;
        padding-bottom: 20px;
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
    
    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
    }
    
    .section-title {
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-actions .btn {
        min-width: auto;
        width: 100%;
    }
}

@media (max-width: 480px) {
    .card-body {
        padding: 1rem;
    }
    
    .header-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .header-text h1 {
        font-size: 1.5rem;
    }
    
    .form-control {
        padding: 0.6rem 0.8rem;
        font-size: 0.9rem;
    }
}

/* Animations */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.form-section {
    animation: fadeIn 0.6s ease forwards;
}

.form-section:nth-child(2) {
    animation-delay: 0.1s;
}

.form-section:nth-child(3) {
    animation-delay: 0.2s;
}

.form-section:nth-child(4) {
    animation-delay: 0.3s;
}

/* Focus Management */
.form-control:focus,
.btn:focus {
    outline: none;
}

/* Custom Validation States */
.was-validated .form-control:valid {
    border-color: var(--youPrice-success);
    background-image: url("data:image/svg+xml;charset=utf-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.94-.94 2.94-2.94a.5.5 0 1 1 .7.7L3.7 6.73a.5.5 0 0 1-.7 0l-1.7-1.7a.5.5 0 1 1 .7-.7L2.3 5.36z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    padding-right: calc(1.5em + 0.75rem);
}

.was-validated .form-control:invalid {
    border-color: var(--youPrice-danger);
    background-image: url("data:image/svg+xml;charset=utf-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    padding-right: calc(1.5em + 0.75rem);
}

/* Placeholder Styling */
.form-control::placeholder {
    color: #6c757d;
    opacity: 0.7;
    font-style: italic;
}

/* Required Field Indicator */
.required-fields-note {
    background: rgba(132, 188, 0, 0.1);
    border: 1px solid rgba(132, 188, 0, 0.2);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    color: var(--youPrice-accent);
}

.required-fields-note::before {
    content: "ℹ️ ";
    margin-right: 0.5rem;
}
</style>