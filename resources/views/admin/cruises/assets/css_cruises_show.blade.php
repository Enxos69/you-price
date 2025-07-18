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
.cruise-show-wrapper {
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

/* Cards */
.show-card,
.similar-cruises-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(78, 205, 196, 0.1);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.card-body {
    padding: 2.5rem;
}

/* Info Sections */
.info-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid rgba(78, 205, 196, 0.1);
}

.info-section:last-child {
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

/* Info Items */
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
    font-size: 0.95rem;
}

.info-item span {
    color: var(--youPrice-dark);
    font-weight: 500;
    text-align: right;
}

.value-highlight {
    font-size: 1.1rem;
    font-weight: 700 !important;
    color: var(--youPrice-primary) !important;
}

/* Badge Company */
.badge-company {
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-company.msc-cruises {
    background: rgba(23, 162, 184, 0.1);
    color: var(--youPrice-info);
    border: 1px solid rgba(23, 162, 184, 0.2);
}

.badge-company.costa-crociere {
    background: rgba(40, 167, 69, 0.1);
    color: var(--youPrice-success);
    border: 1px solid rgba(40, 167, 69, 0.2);
}

.badge-company.royal-caribbean {
    background: rgba(255, 193, 7, 0.1);
    color: var(--youPrice-warning);
    border: 1px solid rgba(255, 193, 7, 0.2);
}

.badge-company.norwegian-cruise-line {
    background: rgba(132, 188, 0, 0.1);
    color: var(--youPrice-primary);
    border: 1px solid rgba(132, 188, 0, 0.2);
}

/* Itinerary Container */
.itinerary-container {
    background: rgba(78, 205, 196, 0.05);
    border-radius: 12px;
    padding: 1.5rem;
    position: relative;
}

.itinerary-point {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 1rem;
}

.itinerary-point:last-child {
    margin-bottom: 0;
}

.point-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.departure .point-icon {
    background: var(--youPrice-success);
}

.arrival .point-icon {
    background: var(--youPrice-danger);
}

.point-details {
    flex: 1;
}

.point-details h6 {
    color: var(--youPrice-accent);
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.point-details .date {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--youPrice-dark);
    margin-bottom: 0.25rem;
}

.point-details .port {
    color: #6c757d;
    font-size: 0.9rem;
}

.duration-info {
    background: rgba(132, 188, 0, 0.1);
    border: 1px solid rgba(132, 188, 0, 0.2);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    margin-top: 1rem;
    text-align: center;
    color: var(--youPrice-accent);
    font-weight: 600;
}

/* Prices Grid */
.prices-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.price-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.price-card.available {
    border-color: var(--youPrice-primary);
    background: rgba(132, 188, 0, 0.02);
}

.price-card.available:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(132, 188, 0, 0.15);
}

.price-card.unavailable {
    border-color: #dee2e6;
    background: #f8f9fa;
    opacity: 0.7;
}

.price-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin: 0 auto 1rem;
    background: var(--youPrice-primary);
}

.price-card.unavailable .price-icon {
    background: #6c757d;
}

.price-details h6 {
    color: var(--youPrice-dark);
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.price-details .price {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--youPrice-primary);
}

.price-card.unavailable .price-details .price {
    color: #6c757d;
}

/* Price Summary */
.price-summary {
    background: rgba(0, 97, 112, 0.05);
    border: 1px solid rgba(0, 97, 112, 0.1);
    border-radius: 10px;
    padding: 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.summary-item .label {
    font-weight: 600;
    color: var(--youPrice-accent);
    font-size: 0.9rem;
}

.summary-item .value {
    font-weight: 700;
    color: var(--youPrice-dark);
}

.summary-item .min-price {
    color: var(--youPrice-success) !important;
}

.summary-item .max-price {
    color: var(--youPrice-danger) !important;
}

/* Details Content */
.details-content {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    border-left: 4px solid var(--youPrice-primary);
    font-size: 0.95rem;
    line-height: 1.6;
    color: var(--youPrice-dark);
}

/* Badge Status */
.badge {
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge.bg-success {
    background-color: var(--youPrice-success) !important;
}

.badge.bg-warning {
    background-color: var(--youPrice-warning) !important;
    color: var(--youPrice-dark) !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}

/* Actions Section */
.actions-section {
    background: rgba(150, 206, 180, 0.1);
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 2rem;
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
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
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

.action-btn.primary {
    border-color: var(--youPrice-primary);
}

.action-btn.primary:hover {
    background: rgba(132, 188, 0, 0.05);
}

.action-btn.danger {
    border-color: var(--youPrice-danger);
}

.action-btn.danger:hover {
    background: rgba(220, 53, 69, 0.05);
}

.action-btn.secondary {
    border-color: #6c757d;
}

.action-btn.secondary:hover {
    background: rgba(108, 117, 125, 0.05);
}

.action-btn.info {
    border-color: var(--youPrice-info);
}

.action-btn.info:hover {
    background: rgba(23, 162, 184, 0.05);
}

.action-btn i {
    font-size: 1.5rem;
    color: var(--youPrice-primary);
}

.action-btn.danger i {
    color: var(--youPrice-danger);
}

.action-btn.secondary i {
    color: #6c757d;
}

.action-btn.info i {
    color: var(--youPrice-info);
}

.action-content {
    flex: 1;
}

.action-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: var(--youPrice-dark);
}

.action-subtitle {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Similar Cruises */
.similar-cruises-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
}

.similar-cruise-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1.25rem;
    transition: all 0.3s ease;
}

.similar-cruise-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    border-color: var(--youPrice-primary);
}

.similar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.similar-header h6 {
    color: var(--youPrice-dark);
    font-weight: 600;
    margin-bottom: 0;
    font-size: 1rem;
}

.similar-details {
    margin-bottom: 1rem;
}

.similar-cruise {
    color: var(--youPrice-accent);
    font-weight: 500;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.similar-price {
    color: var(--youPrice-primary);
    font-weight: 600;
    font-size: 0.9rem;
}

.similar-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.similar-actions .btn {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    border-radius: 6px;
    border: 1px solid;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-outline-primary {
    color: var(--youPrice-primary);
    border-color: var(--youPrice-primary);
    background: transparent;
}

.btn-outline-primary:hover {
    background: var(--youPrice-primary);
    color: white;
    transform: translateY(-1px);
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
    background: transparent;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-1px);
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

/* Responsive */
@media (max-width: 768px) {
    .cruise-show-wrapper {
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
    
    .info-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
    }
    
    .section-title {
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }
    
    .prices-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .price-summary {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .similar-cruises-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .info-item span {
        text-align: left;
    }
}

@media (max-width: 480px) {
    .card-body {
        padding: 1rem;
    }
    
    .prices-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .itinerary-point {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
    
    .point-details .date,
    .point-details .port {
        text-align: center;
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

.info-section {
    animation: fadeIn 0.6s ease forwards;
}

.info-section:nth-child(1) {
    animation-delay: 0.1s;
}

.info-section:nth-child(2) {
    animation-delay: 0.2s;
}

.info-section:nth-child(3) {
    animation-delay: 0.3s;
}

.info-section:nth-child(4) {
    animation-delay: 0.4s;
}

/* Print Styles */
@media print {
    .cruise-show-wrapper {
        padding: 0;
        background: white;
    }
    
    .page-header,
    .actions-section,
    .header-actions,
    .similar-cruises-card {
        display: none !important;
    }
    
    .show-card {
        box-shadow: none;
        border: 1px solid #ccc;
    }
    
    .prices-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>