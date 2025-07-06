<style>
    /* Animazioni personalizzate */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -200px 0;
        }

        100% {
            background-position: calc(200px + 100%) 0;
        }
    }

    /* Effetti di hover e interazione */
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    }

    /* Stili per i form */
    .form-floating input:focus {
        border-color: #d5e927;
        box-shadow: 0 0 0 0.2rem rgba(225, 234, 102, 0.25);
    }

    .form-floating label {
        font-weight: 500;
    }

    /* Stili per i pulsanti */
    .btn-primary {
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    /* Stili per le tabelle */
    .table-hover tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.1);
        transform: scale(1.01);
        transition: all 0.2s ease;
    }

    .table th {
        font-weight: 600;
        font-size: 0.9rem;
        border-bottom: 2px solid #dee2e6;
    }

    /* Badge personalizzati */
    .badge {
        font-weight: 500;
        padding: 0.5rem 0.8rem;
        border-radius: 50px;
    }

    /* Effetti di loading */
    .loading-skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200px 100%;
        animation: shimmer 1.5s infinite;
    }

    /* Stili per i gauge */
    .gauge-container {
        position: relative;
        display: inline-block;
    }

    .gauge-value {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 2rem;
        font-weight: bold;
        color: white;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .display-4 {
            font-size: 2rem;
        }

        .card-body {
            padding: 1.5rem !important;
        }

        .table-responsive {
            font-size: 0.875rem;
        }
    }

    /* Stili per le icone */
    .fas,
    .far {
        transition: all 0.3s ease;
    }

    .card-header .fas:hover {
        transform: rotate(360deg);
    }

    /* Effetti di focus per accessibilità */
    .form-control:focus,
    .btn:focus {
        outline: 2px solid #667eea;
        outline-offset: 2px;
    }

    /* Stili per i toast/notifiche */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1055;
    }

    .toast {
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Stili per i tooltip */
    .tooltip {
        font-size: 0.875rem;
    }

    .tooltip-inner {
        background-color: #333;
        border-radius: 6px;
        padding: 8px 12px;
    }

    /* Animazioni per i risultati */
    .results-enter {
        animation: fadeInUp 0.6s ease;
    }

    .table-row-enter {
        animation: fadeInUp 0.4s ease;
    }

    /* Stili per il background */
    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }

    .gradient-bg::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        pointer-events: none;
    }

    /* Stili per i numeri animati */
    .animated-number {
        font-variant-numeric: tabular-nums;
        transition: all 0.3s ease;
    }

    /* Stili per i grafici */
    .chart-container {
        position: relative;
        height: 250px;
        margin: 20px 0;
    }

    /* Stili per gli stati di errore */
    .error-state {
        color: #dc3545;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 10px;
        padding: 1rem;
        margin: 1rem 0;
    }

    .success-state {
        color: #155724;
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        border-radius: 10px;
        padding: 1rem;
        margin: 1rem 0;
    }

    /* Stili per le transizioni delle pagine */
    .page-transition {
        transition: all 0.3s ease-in-out;
    }

    /* Stili personalizzati per DateRangePicker */
    .daterangepicker {
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .daterangepicker .ranges li.active {
        background-color: #667eea;
        color: white;
    }

    .daterangepicker td.active {
        background-color: #667eea;
    }

    /* Miglioramenti per la stampa */
    @media print {
        .gradient-bg {
            background: white !important;
        }

        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }

        .btn {
            display: none !important;
        }
    }
</style>
