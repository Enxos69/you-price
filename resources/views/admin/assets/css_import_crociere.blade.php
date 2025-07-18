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

/* Wrapper principale con spazio dalla navbar - RIDOTTO */
.import-page-wrapper {
    padding-top: 50px; /* Ridotto da 100px */
    padding-bottom: 20px; /* Ridotto da 40px */
    background: linear-gradient(135deg, #f8fdfc 0%, #e8f5f3 100%);
    min-height: 100vh;
}

/* Header della pagina - COMPATTO */
.page-header {
    background: var(--youPrice-gradient);
    border-radius: 15px; /* Ridotto da 20px */
    padding: 1.5rem; /* Ridotto da 2rem */
    margin-bottom: 1.5rem; /* Ridotto da 2rem */
    box-shadow: 0 8px 25px rgba(78, 205, 196, 0.2);
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1rem; /* Ridotto da 1.5rem */
}

.header-icon {
    width: 60px; /* Ridotto da 80px */
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem; /* Ridotto da 2.5rem */
    color: white;
    backdrop-filter: blur(10px);
}

.header-text h1 {
    color: white;
    font-size: 2rem; /* Ridotto da 2.5rem */
    font-weight: 700;
    margin-bottom: 0.25rem; /* Ridotto da 0.5rem */
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-text p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem; /* Ridotto da 1.1rem */
    margin-bottom: 0;
}

/* Card principale */
.import-card {
    background: white;
    border-radius: 15px; /* Ridotto da 20px */
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(78, 205, 196, 0.1);
    overflow: hidden;
}

.card-body {
    padding: 2rem; /* Ridotto da 3rem */
}

/* Upload Zone - COMPATTA */
.upload-zone {
    border: 3px dashed var(--youPrice-primary);
    border-radius: 15px; /* Ridotto da 20px */
    padding: 2rem; /* Ridotto da 3rem */
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--youPrice-light);
    margin-bottom: 1.5rem; /* Ridotto da 2rem */
}

.upload-zone:hover {
    border-color: var(--youPrice-accent);
    background: rgba(78, 205, 196, 0.05);
    transform: translateY(-2px);
}

.upload-zone.dragover {
    border-color: var(--youPrice-secondary);
    background: rgba(150, 206, 180, 0.1);
    transform: scale(1.02);
}

.upload-zone.has-file {
    border-color: var(--youPrice-secondary);
    background: rgba(150, 206, 180, 0.1);
}

.upload-icon i {
    font-size: 3rem; /* Ridotto da 4rem */
    color: var(--youPrice-primary);
    margin-bottom: 0.75rem; /* Ridotto da 1rem */
    transition: all 0.3s ease;
}

.upload-zone:hover .upload-icon i {
    color: var(--youPrice-accent);
    transform: scale(1.1);
}

.upload-title {
    color: var(--youPrice-dark);
    font-weight: 600;
    margin-bottom: 0.25rem; /* Ridotto da 0.5rem */
    font-size: 1.2rem; /* Ridotto da 1.3rem */
}

.upload-subtitle {
    color: #6c757d;
    margin-bottom: 0;
    font-size: 0.9rem;
}

/* File Preview - COMPATTO */
.file-preview {
    background: rgba(78, 205, 196, 0.05);
    border: 1px solid var(--youPrice-primary);
    border-radius: 10px; /* Ridotto da 15px */
    padding: 1rem; /* Ridotto da 1.5rem */
    margin-bottom: 1.5rem; /* Ridotto da 2rem */
    animation: slideUp 0.3s ease;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.file-icon i {
    font-size: 2rem; /* Ridotto da 2.5rem */
    color: var(--youPrice-primary);
}

.file-details {
    flex-grow: 1;
}

.file-details h6 {
    color: var(--youPrice-dark);
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.btn-remove {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px; /* Ridotto da 35px */
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-remove:hover {
    background: #c82333;
    transform: scale(1.1);
}

/* Stats Preview - COMPATTO */
.stats-preview {
    background: rgba(150, 206, 180, 0.1);
    border: 1px solid var(--youPrice-secondary);
    border-radius: 10px; /* Ridotto da 15px */
    padding: 1rem; /* Ridotto da 1.5rem */
    margin-bottom: 1.5rem; /* Ridotto da 2rem */
    animation: slideUp 0.3s ease;
}

.stats-title {
    color: var(--youPrice-accent);
    font-weight: 600;
    margin-bottom: 0.75rem; /* Ridotto da 1rem */
    font-size: 1rem; /* Ridotto da 1.1rem */
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem; /* Ridotto da 1rem */
}

.stat-item {
    text-align: center;
    background: white;
    padding: 0.75rem; /* Ridotto da 1rem */
    border-radius: 8px; /* Ridotto da 10px */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.stat-number {
    font-size: 1.5rem; /* Ridotto da 1.8rem */
    font-weight: 700;
    color: var(--youPrice-primary);
    display: block;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.8rem; /* Ridotto da 0.9rem */
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Sezione bottom compatta */
.bottom-section {
    margin-top: 1rem;
}

/* Import Options - COMPATTE */
.import-options {
    background: #f8f9fa;
    border-radius: 10px; /* Ridotto da 15px */
    padding: 1.25rem; /* Ridotto da 2rem */
    margin-bottom: 1rem; /* Ridotto da 2rem */
}

.options-title {
    color: var(--youPrice-dark);
    font-weight: 600;
    margin-bottom: 1rem; /* Ridotto da 1.5rem */
    font-size: 1rem; /* Ridotto da 1.1rem */
}

.options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Layout orizzontale su schermi grandi */
    gap: 0.75rem; /* Ridotto da 1rem */
}

.form-check {
    margin-bottom: 0; /* Rimuovi margin extra */
}

.form-check-input:checked {
    background-color: var(--youPrice-primary);
    border-color: var(--youPrice-primary);
}

.form-check-label {
    color: var(--youPrice-dark);
    font-weight: 500;
    font-size: 0.9rem;
}

/* Progress Container - COMPATTO */
.progress-container {
    margin-bottom: 1rem; /* Ridotto da 2rem */
}

.progress-wrapper {
    background: rgba(78, 205, 196, 0.05);
    border-radius: 10px; /* Ridotto da 15px */
    padding: 1rem; /* Ridotto da 1.5rem */
}

.progress {
    height: 10px; /* Ridotto da 12px */
    border-radius: 8px; /* Ridotto da 10px */
    background: #e9ecef;
    overflow: hidden;
    margin-bottom: 0.75rem; /* Ridotto da 1rem */
}

.progress-bar {
    background: var(--youPrice-gradient);
    height: 100%;
    border-radius: 8px;
    transition: width 0.3s ease;
    position: relative;
    overflow: hidden;
}

.progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
    animation: shimmer 2s infinite;
}

.progress-text {
    text-align: center;
    color: var(--youPrice-accent);
    font-weight: 500;
    font-size: 0.9rem;
}

/* Submit Button - COMPATTO */
.btn-import {
    background: var(--youPrice-gradient);
    color: white;
    border: none;
    border-radius: 40px; /* Ridotto da 50px */
    padding: 0.875rem 2.5rem; /* Ridotto da 1rem 3rem */
    font-size: 1rem; /* Ridotto da 1.1rem */
    font-weight: 600;
    width: 100%;
    transition: all 0.3s ease;
    box-shadow: 0 6px 20px rgba(78, 205, 196, 0.3);
    margin-bottom: 1rem; /* Ridotto da 2rem */
}

.btn-import:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(78, 205, 196, 0.4);
}

.btn-import:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 4px 15px rgba(78, 205, 196, 0.2);
}

/* Format Info - MOLTO COMPATTO */
.format-info {
    background: rgba(68, 160, 141, 0.05);
    border: 1px solid rgba(68, 160, 141, 0.2);
    border-radius: 8px; /* Ridotto da 15px */
    padding: 1rem; /* Ridotto da 2rem */
    text-align: center;
}

.info-title {
    color: var(--youPrice-accent);
    font-weight: 500; /* Ridotto da 600 */
    margin-bottom: 0; /* Rimosso margin */
    font-size: 0.9rem; /* Ridotto da 1.1rem */
}

/* Alerts - COMPATTI */
.alert {
    border-radius: 10px; /* Ridotto da 15px */
    border: none;
    padding: 0.75rem 1rem; /* Ridotto da 1rem 1.5rem */
    margin-bottom: 1rem; /* Ridotto da 1.5rem */
    animation: slideUp 0.3s ease;
}

.alert-success {
    background: rgba(150, 206, 180, 0.1);
    color: var(--youPrice-accent);
    border-left: 4px solid var(--youPrice-secondary);
}

.alert-danger {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border-left: 4px solid #dc3545;
}

.alert-warning {
    background: rgba(255, 193, 7, 0.1);
    color: #856404;
    border-left: 4px solid #ffc107;
}

/* Animations */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(15px); /* Ridotto da 20px */
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* Responsive - OTTIMIZZATO */
@media (max-width: 768px) {
    .import-page-wrapper {
        padding-top: 70px; /* Ridotto */
    }
    
    .page-header {
        padding: 1rem; /* Ridotto */
        margin-bottom: 1rem;
    }
    
    .header-content {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
    
    .header-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .header-text h1 {
        font-size: 1.75rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .upload-zone {
        padding: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .options-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .import-page-wrapper {
        padding-top: 60px;
    }
    
    .card-body {
        padding: 1rem;
    }
}

/* Stili aggiuntivi per le quick stats */
.quick-stats {
    background: rgba(102, 126, 234, 0.05);
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid rgba(102, 126, 234, 0.1);
}

.quick-stat-item {
    text-align: center;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    margin-bottom: 1rem;
}

.quick-stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 0.25rem;
}

.quick-stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

.header-actions .btn {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.header-actions .btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
}
</style>