<style>
    /* =======================
       COLORI E STILI COORDINATI
       ======================= */

    /* Variabili CSS per mantenere coerenza - Palette Verde Migliorata */
    :root {
        --color-primary: #2d5016;
        --color-primary-light: #4a7c59;
        --color-primary-dark: #1a3009;
        --color-secondary: #6c757d;
        --color-success: #22543d;
        --color-success-light: #38a169;
        --color-success-lighter: #68d391;
        --color-info: #17a2b8;
        --color-warning: #d69e2e;
        --color-danger: #e53e3e;
        --color-light: #f8f9fa;
        --color-dark: #343a40;
        --color-white: #ffffff;
        --color-muted: #6c757d;
        --border-radius: 0.375rem;
        --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        --transition: all 0.15s ease-in-out;
    }

    /* Layout generale - Content abbassato di 50px */
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container-fluid {
        padding: 1rem 1.5rem;
        margin-top: 50px;
        /* Abbassa tutto il contenuto */
    }

    /* Header più compatto */
    h2 {
        color: var(--color-dark);
        font-weight: 600;
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
    }

    .text-muted {
        color: var(--color-muted) !important;
        font-size: 0.85rem;
    }

    /* Cards base - Spazi ridotti */
    .card {
        border: 1px solid #e3e6f0;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: var(--transition);
        background: var(--color-white);
        margin-bottom: 0.75rem;
    }

    .card:hover {
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    .card-header {
        background-color: var(--color-light);
        border-bottom: 1px solid #e3e6f0;
        padding: 0.75rem 1rem;
        font-weight: 600;
        color: var(--color-dark);
    }

    .card-body {
        padding: 1rem;
    }

    /* Mini stat cards più compatti */
    .mini-stat-card .card-body {
        padding: 0.75rem;
    }

    /* Enhanced Satisfaction Gauges */
    .satisfaction-card,
    .optimization-card {
        border: 2px solid transparent;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .satisfaction-card::before,
    .optimization-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #dc3545 0%, #ffc107 50%, #28a745 100%);
        opacity: 0.3;
    }

    .satisfaction-card.excellent::before,
    .optimization-card.excellent::before {
        background: linear-gradient(90deg, #22543d, #2d5016);
        opacity: 1;
    }

    .satisfaction-card.good::before,
    .optimization-card.good::before {
        background: linear-gradient(90deg, #38a169, #4a7c59);
        opacity: 1;
    }

    .satisfaction-card.average::before,
    .optimization-card.average::before {
        background: linear-gradient(90deg, #68d391, #9ae6b4);
        opacity: 1;
    }

    .satisfaction-card.poor::before,
    .optimization-card.poor::before {
        background: linear-gradient(90deg, #fd7e14, #dc3545);
        opacity: 1;
    }

    .satisfaction-gauge-container {
        position: relative;
        display: inline-block;
    }

    .gauge-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
    }

    .gauge-score {
        font-size: 1.75rem;
        font-weight: 700;
        color: #343a40;
        line-height: 1;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .gauge-percent {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 600;
        margin-top: -0.25rem;
    }

    /* Dynamic gauge colors - Sfumature Verde */
    .gauge-excellent .gauge-score {
        color: #22543d;
    }

    .gauge-good .gauge-score {
        color: #38a169;
    }

    .gauge-average .gauge-score {
        color: #68d391;
    }

    .gauge-poor .gauge-score {
        color: #dc3545;
    }

    /* Satisfaction rating text */
    #satisfaction-rating,
    #optimization-suggestion {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--color-dark);
    }

    #satisfaction-rating.excellent,
    #optimization-suggestion.excellent {
        color: #22543d;
    }

    #satisfaction-rating.good,
    #optimization-suggestion.good {
        color: #38a169;
    }

    #satisfaction-rating.average,
    #optimization-suggestion.average {
        color: #68d391;
    }

    #satisfaction-rating.poor,
    #optimization-suggestion.poor {
        color: #dc3545;
    }

    /* Statistics Cards - Colori coordinati con l'immagine */
    .stats-card {
        border: none;
        border-radius: var(--border-radius);
        overflow: hidden;
        position: relative;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--color-primary);
    }

    .stats-card.stats-total::before {
        background: linear-gradient(135deg, #2d5016, #4a7c59);
    }

    .stats-card.stats-available::before {
        background: linear-gradient(135deg, #22543d, #38a169);
    }

    .stats-card.stats-companies::before {
        background: linear-gradient(135deg, #17a2b8, #0dcaf0);
    }

    .stats-icon {
        width: 3rem;
        height: 3rem;
        background: linear-gradient(135deg, var(--color-primary), var(--color-success));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .stats-total .stats-icon {
        background: linear-gradient(135deg, #2d5016, #4a7c59);
    }

    .stats-available .stats-icon {
        background: linear-gradient(135deg, #22543d, #38a169);
    }

    .stats-companies .stats-icon {
        background: linear-gradient(135deg, #17a2b8, #0dcaf0);
    }

    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--color-dark);
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stats-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--color-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        line-height: 1;
    }

    /* Mini stats icons */
    .stats-mini-icon {
        width: 2.5rem;
        height: 2.5rem;
        background: linear-gradient(135deg, var(--color-primary), var(--color-success));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        margin: 0 auto;
    }

    /* Form styling */
    .form-label {
        font-weight: 600;
        color: var(--color-dark);
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-control {
        border: 1px solid #d1d3e2;
        border-radius: var(--border-radius);
        /* padding: 0.75rem 1rem; */
        font-size: 0.875rem;
        transition: var(--transition);
        background-color: var(--color-white);
    }

    .form-control:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        background-color: var(--color-white);
    }

    .form-control.is-valid {
        border-color: var(--color-success);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.79-.79L4.25 4.8l1.16 1.16.79-.79L3.04 2 Z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .form-control.is-invalid {
        border-color: var(--color-danger);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 2.4 2.4m0-2.4-2.4 2.4'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: var(--color-danger);
    }

    .form-text small {
        color: var(--color-primary);
        font-weight: 500;
    }

    /* Buttons */
    .btn {
        border-radius: var(--border-radius);
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        transition: var(--transition);
        border: 1px solid transparent;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--color-primary), var(--color-success));
        border-color: var(--color-primary);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--color-primary-dark), var(--color-primary));
        border-color: var(--color-primary-dark);
        transform: translateY(-1px);
        box-shadow: 0 0.25rem 0.5rem rgba(45, 80, 22, 0.3);
    }

    .btn-secondary {
        background-color: var(--color-secondary);
        border-color: var(--color-secondary);
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }

    /* Tables - Più compatte */
    .table {
        margin-bottom: 0;
        background-color: var(--color-white);
        font-size: 0.85rem;
    }

    .table-sm th,
    .table-sm td {
        padding: 0.5rem 0.5rem;
        font-size: 0.8rem;
    }

    .table-header {
        background: linear-gradient(135deg, #2d5016, #38a169);
        color: white;
    }

    .table-header th {
        border: none;
        padding: 0.75rem 0.5rem;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        vertical-align: middle;
    }

    .table td {
        padding: 0.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #e3e6f0;
    }

    .table tbody tr:hover {
        background-color: rgba(45, 80, 22, 0.08);
        transform: scale(1.01);
        transition: var(--transition);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .table-responsive {
        border-radius: var(--border-radius);
        overflow: hidden;
    }

    /* Badges - Colori coordinati */
    .badge {
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .bg-success {
        background-color: var(--color-success-lighter) !important;
    }

    .bg-warning {
        background-color: var(--color-warning) !important;
    }

    .bg-info {
        background-color: var(--color-info) !important;
    }

    .bg-danger {
        background-color: var(--color-danger) !important;
    }

    /* Company badges - Colori specifici come nell'immagine */
    .company-royal {
        background: linear-gradient(135deg, #FFA000, #FF8F00);
        color: white;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .company-msc {
        background: linear-gradient(135deg, #1565C0, #0D47A1);
        color: white;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .company-costa {
        background: linear-gradient(135deg, #FFD600, #FFC107);
        color: #333;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .company-norwegian {
        background: linear-gradient(135deg, #0277BD, #0288D1);
        color: white;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .company-carnival {
        background: linear-gradient(135deg, #D32F2F, #F44336);
        color: white;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .company-celebrity {
        background: linear-gradient(135deg, #512DA8, #673AB7);
        color: white;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    /* Duration badges */
    .duration-badge {
        background: linear-gradient(135deg, var(--color-info), #0dcaf0);
        color: white;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
    }

    /* Action buttons - Style come nell'immagine */
    .action-btn {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 0.125rem;
        font-size: 0.75rem;
        transition: var(--transition);
    }

    .action-btn.btn-info {
        background: linear-gradient(135deg, #17a2b8, #0dcaf0);
        color: white;
    }

    .action-btn.btn-warning {
        background: linear-gradient(135deg, #ffc107, #ffca2c);
        color: #333;
    }

    .action-btn.btn-danger {
        background: linear-gradient(135deg, #dc3545, #e74c3c);
        color: white;
    }

    .action-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    /* Price styling - Chiarimento prezzo gruppo solo nell'intestazione */
    .price-total {
        color: var(--color-success);
        font-weight: 700;
        font-size: 1rem;
    }

    .price-daily {
        color: var(--color-success-light);
        font-weight: 600;
        font-size: 0.875rem;
    }

    .price-savings {
        color: var(--color-danger);
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Ship name styling */
    .ship-name {
        font-weight: 600;
        color: var(--color-dark);
        margin-bottom: 0.25rem;
    }

    .cruise-details {
        font-size: 0.8rem;
        color: var(--color-muted);
        font-style: italic;
    }

    /* Gauges container */
    .gauge-container {
        position: relative;
        display: inline-block;
    }

    /* Loading and empty states */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    .loading-skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200px 100%;
        animation: shimmer 1.5s infinite;
        border-radius: var(--border-radius);
        height: 1.5rem;
    }

    @keyframes shimmer {
        0% {
            background-position: -200px 0;
        }

        100% {
            background-position: calc(200px + 100%) 0;
        }
    }

    /* Animation classes */
    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }

    .slide-in-left {
        animation: slideInLeft 0.5s ease-out;
    }

    .slide-in-right {
        animation: slideInRight 0.5s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Counter animation */
    .counter-updating {
        color: var(--color-primary);
        transform: scale(1.05);
        transition: var(--transition);
    }

    /* DateRangePicker customization */
    .daterangepicker {
        border: 1px solid #e3e6f0;
        border-radius: var(--border-radius);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .daterangepicker .ranges li.active {
        background-color: var(--color-primary);
        color: white;
    }

    .daterangepicker td.active,
    .daterangepicker td.active:hover {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
        color: white;
    }

    .daterangepicker td.in-range {
        background-color: rgba(40, 167, 69, 0.1);
        border-color: rgba(40, 167, 69, 0.2);
        color: var(--color-primary);
    }

    /* Toast notifications */
    .toast {
        border-radius: var(--border-radius);
        border: none;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
        margin-bottom: 0.5rem;
    }

    .toast-success {
        background: linear-gradient(45deg, var(--color-success), #20c997);
        color: white;
    }

    .toast-error {
        background: linear-gradient(45deg, var(--color-danger), #e74c3c);
        color: white;
    }

    .toast-info {
        background: linear-gradient(45deg, var(--color-info), var(--color-primary));
        color: white;
    }

    /* Benefit tags */
    .benefit-tag {
        background: linear-gradient(135deg, var(--color-success), #20c997);
        color: white;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-weight: 500;
        text-transform: capitalize;
    }

    /* Match percentage styling */
    .match-excellent {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        font-weight: 600;
    }

    .match-good {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
        color: #333;
        font-weight: 600;
    }

    .match-average {
        background: linear-gradient(135deg, #17a2b8, #0dcaf0);
        color: white;
        font-weight: 600;
    }

    .match-low {
        background: linear-gradient(135deg, #6c757d, #495057);
        color: white;
        font-weight: 600;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 1rem;
        }

        .stats-card .card-body {
            padding: 1rem;
        }

        .stats-number {
            font-size: 1.5rem;
        }

        .stats-icon {
            width: 2.5rem;
            height: 2.5rem;
            font-size: 1rem;
        }

        .table-responsive {
            font-size: 0.8rem;
        }

        .table th,
        .table td {
            padding: 0.5rem 0.25rem;
        }

        .action-btn {
            width: 1.75rem;
            height: 1.75rem;
            font-size: 0.7rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }

        h2 {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 576px) {
        .stats-card {
            margin-bottom: 0.75rem;
        }

        .d-flex.align-items-center {
            flex-direction: column;
            text-align: center;
        }

        .stats-icon {
            margin-right: 0;
            margin-bottom: 0.5rem;
        }

        .table-nowrap th,
        .table-nowrap td {
            white-space: normal;
            word-break: break-word;
        }

        .action-btn {
            margin: 0.125rem;
        }
    }

    /* Print styles */
    @media print {
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }

        .btn,
        .action-btn,
        #loading-spinner {
            display: none !important;
        }

        .table {
            font-size: 0.8rem;
        }

        .badge,
        .company-royal,
        .company-msc,
        .company-costa,
        .company-norwegian,
        .company-carnival,
        .company-celebrity {
            background: white !important;
            color: #333 !important;
            border: 1px solid #333 !important;
        }

        .stats-icon {
            background: white !important;
            color: #333 !important;
            border: 1px solid #333;
        }
    }

    /* Accessibility improvements */
    .sr-only {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    /* Focus styles for accessibility */
    .form-control:focus,
    .btn:focus,
    .action-btn:focus {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    .table tbody tr:focus {
        outline: 2px solid var(--color-primary);
        outline-offset: -2px;
    }

    /* High contrast mode support */
    @media (prefers-contrast: high) {
        .card {
            border: 2px solid #000;
        }

        .badge,
        .company-royal,
        .company-msc,
        .company-costa,
        .company-norwegian,
        .company-carnival,
        .company-celebrity {
            border: 1px solid #000;
        }

        .btn-primary {
            background: #000;
            border: 2px solid #000;
        }

        .table-header {
            background: #000;
            color: #fff;
        }
    }

    /* Reduced motion support */
    @media (prefers-reduced-motion: reduce) {

        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }

        .card:hover,
        .btn:hover,
        .action-btn:hover,
        .table tbody tr:hover {
            transform: none !important;
        }
    }

    /* Custom scrollbar for tables */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: var(--color-primary);
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: var(--color-primary-dark);
    }

    /* Additional utility classes */
    .text-success {
        color: var(--color-success) !important;
    }

    .text-info {
        color: var(--color-info) !important;
    }

    .text-warning {
        color: var(--color-warning) !important;
    }

    .text-danger {
        color: var(--color-danger) !important;
    }

    .fw-bold {
        font-weight: 700 !important;
    }

    .fw-semibold {
        font-weight: 600 !important;
    }

    .small {
        font-size: 0.875rem;
    }

    /* Hover effects for interactive elements */
    .card:hover .stats-icon {
        transform: scale(1.05);
        transition: var(--transition);
    }

    .table tbody tr:hover .action-btn {
        opacity: 1;
        visibility: visible;
    }

    .action-btn {
        opacity: 0.7;
        transition: var(--transition);
    }

    .table tbody tr:hover .action-btn {
        opacity: 1;
    }

    /* Aggiungi queste nuove classi al tuo file css.blade.php esistente */

    /* =======================
   DYNAMIC MESSAGE BOX STYLES
   ======================= */

    /* Message Box Container */
    .message-box {
        margin-top: 20px;
        padding: 20px;
        border-radius: var(--border-radius);
        transition: all 0.5s ease;
        min-height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .message-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.8s ease;
    }

    .message-box.animate::before {
        left: 100%;
    }

    /* Message States */
    .message-default {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border: 2px dashed #6c757d;
        color: #6c757d;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .message-progress {
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        border: 2px solid #ffc107;
        color: #856404;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        animation: pulse-yellow 2s infinite;
    }

    .message-ready {
        background: linear-gradient(135deg, #d4edda, #a3d977);
        border: 2px solid #28a745;
        color: #155724;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        animation: pulse-green 2s infinite;
    }

    .message-complete {
        background: linear-gradient(135deg, #d1ecf1, #74b9ff);
        border: 2px solid #17a2b8;
        color: #0c5460;
        box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        animation: pulse-blue 2s infinite;
    }

    /* Pulse Animations */
    @keyframes pulse-yellow {

        0%,
        100% {
            transform: scale(1);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }

        50% {
            transform: scale(1.02);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
        }
    }

    @keyframes pulse-green {

        0%,
        100% {
            transform: scale(1);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        50% {
            transform: scale(1.02);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
    }

    @keyframes pulse-blue {

        0%,
        100% {
            transform: scale(1);
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }

        50% {
            transform: scale(1.02);
            box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);
        }
    }

    /* Icon Large */
    .icon-large {
        font-size: 1.5em;
        margin-right: 10px;
        transition: all 0.3s ease;
    }

    .message-ready .icon-large,
    .message-complete .icon-large {
        animation: bounce 1.5s ease infinite;
    }

    @keyframes bounce {

        0%,
        20%,
        50%,
        80%,
        100% {
            transform: translateY(0);
        }

        40% {
            transform: translateY(-5px);
        }

        60% {
            transform: translateY(-3px);
        }
    }

    /* Progress Indicator */
    .progress-indicator {
        margin-top: 15px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
    }

    .progress-step {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #6c757d;
        transition: all 0.4s ease;
        position: relative;
        cursor: help;
    }

    .progress-step::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: white;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .progress-step.active {
        background: var(--color-success);
        transform: scale(1.3);
        box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        animation: glow 2s ease-in-out infinite alternate;
    }

    .progress-step.active::before {
        opacity: 1;
    }

    @keyframes glow {
        from {
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        }

        to {
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.8), 0 0 30px rgba(40, 167, 69, 0.6);
        }
    }

    /* Tooltip per progress steps */
    .progress-step:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 120%;
        left: 50%;
        transform: translateX(-50%);
        background: #333;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
        opacity: 1;
        pointer-events: none;
    }

    .progress-step::after {
        content: '';
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    /* Form Control Enhancements per messaggi dinamici */
    .form-control.field-filled {
        border-color: var(--color-success);
        background: linear-gradient(135deg, #f8fff9, #f0f9f0);
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.1);
    }

    .form-control.field-invalid {
        border-color: var(--color-danger);
        background: linear-gradient(135deg, #fff8f8, #fef2f2);
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.1);
    }

    .form-control.field-progress {
        border-color: var(--color-warning);
        background: linear-gradient(135deg, #fffbf0, #fef8e7);
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.1);
    }

    /* Button States Enhancement */
    .btn-primary:disabled {
        background: linear-gradient(135deg, #6c757d, #545b62);
        border-color: #6c757d;
        opacity: 0.7;
        cursor: not-allowed;
        animation: none;
    }

    .btn-primary.btn-ready {
        background: linear-gradient(135deg, var(--color-success), #20c997);
        border-color: var(--color-success);
        animation: ready-pulse 2s ease-in-out infinite;
    }

    @keyframes ready-pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }
    }

    /* Enhanced Budget Per Person Display */
    #budget-per-person {
        transition: all 0.3s ease;
        font-weight: 600;
    }

    #budget-per-person.updated {
        color: var(--color-success) !important;
        transform: scale(1.05);
        text-shadow: 0 1px 3px rgba(40, 167, 69, 0.3);
    }

    /* Message Text Improvements */
    .message-box strong {
        font-weight: 700;
        font-size: 1.1em;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .message-progress strong {
        background: linear-gradient(45deg, #856404, #b8860b);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .message-ready strong {
        background: linear-gradient(45deg, #155724, #28a745);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .message-complete strong {
        background: linear-gradient(45deg, #0c5460, #17a2b8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .message-box {
            padding: 15px;
            min-height: 70px;
        }

        .icon-large {
            font-size: 1.3em;
            margin-right: 8px;
        }

        .progress-indicator {
            gap: 8px;
            margin-top: 12px;
        }

        .progress-step {
            width: 14px;
            height: 14px;
        }

        .progress-step::before {
            width: 6px;
            height: 6px;
        }

        .message-box strong {
            font-size: 1em;
        }
    }

    @media (max-width: 576px) {
        .message-box {
            padding: 12px;
            min-height: 60px;
        }

        .progress-indicator {
            flex-wrap: wrap;
            gap: 6px;
        }

        .progress-step {
            width: 12px;
            height: 12px;
        }
    }

    /* Print Styles */
    @media print {
        .message-box {
            background: white !important;
            border: 1px solid #333 !important;
            color: #333 !important;
            box-shadow: none !important;
            animation: none !important;
        }

        .progress-step {
            background: #333 !important;
            animation: none !important;
            transform: none !important;
            box-shadow: none !important;
        }
    }

    /* High Contrast Mode */
    @media (prefers-contrast: high) {
        .message-box {
            border-width: 3px !important;
        }

        .progress-step {
            border: 2px solid #000;
        }

        .progress-step.active {
            background: #000 !important;
            border-color: #000 !important;
        }
    }

    /* Reduced Motion */
    @media (prefers-reduced-motion: reduce) {

        .message-box,
        .progress-step,
        .icon-large,
        #budget-per-person {
            animation: none !important;
            transition-duration: 0.01ms !important;
        }

        .message-box::before {
            display: none;
        }

        .btn-primary.btn-ready {
            animation: none !important;
        }
    }

    /* Stili per ordinamento manuale */
    .table-header th[style*="cursor: pointer"]:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .table-header th .fa-sort,
    .table-header th .fa-sort-up,
    .table-header th .fa-sort-down {
        transition: all 0.2s ease;
    }
</style>
