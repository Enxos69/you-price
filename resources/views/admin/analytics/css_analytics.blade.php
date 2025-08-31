    <style>
        /* Stili personalizzati per la dashboard analytics */
        .stats-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.15s ease-in-out;
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
            background: linear-gradient(135deg, #007bff, #0056b3);
        }

        .stats-card.stats-primary::before {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }

        .stats-card.stats-success::before {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }

        .stats-card.stats-info::before {
            background: linear-gradient(135deg, #17a2b8, #117a8b);
        }

        .stats-card.stats-warning::before {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }

        .stats-icon {
            width: 3rem;
            height: 3rem;
            background: linear-gradient(135deg, var(--bs-primary), var(--bs-primary-dark, #0056b3));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .stats-success .stats-icon {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }

        .stats-info .stats-icon {
            background: linear-gradient(135deg, #17a2b8, #117a8b);
        }

        .stats-warning .stats-icon {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .analytics-page-wrapper {
            padding-top: 50px;
            padding-bottom: 20px;
            background: linear-gradient(135deg, #f8fdfc 0%, #e8f5f3 100%);
            min-height: 100vh;
        }

        .stats-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1;
        }

        .stats-change {
            font-size: 0.875rem;
            color: #28a745;
            font-weight: 500;
        }

        .performance-metric {
            padding: 1rem 0;
        }

        .metric-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 0.25rem;
        }

        .metric-label {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }

        .table-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }

        .table-header th {
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .list-group-item {
            border: none;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .country-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .country-name {
            font-weight: 500;
        }

        .country-count {
            background: #e9ecef;
            color: #495057;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
        }

        .device-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .user-type-badge {
            background: #f3e5f5;
            color: #7b1fa2;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .btn-group .btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-number {
                font-size: 1.5rem;
            }

            .stats-icon {
                width: 2.5rem;
                height: 2.5rem;
                font-size: 1rem;
            }

            .metric-value {
                font-size: 1.25rem;
            }

            .performance-metric {
                padding: 0.5rem 0;
            }
        }

        /* CSS per layout orizzontale compatto - sostituisci il CSS daily-stats esistente */

        .main-stat {
            flex-shrink: 0;
        }

        .daily-comparison {
            text-align: right;
            margin-left: 1rem;
        }

        .comparison-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-width: 80px;
            margin-bottom: 0.125rem;
        }

        .comparison-row.variation {
            justify-content: flex-end;
            margin-top: 0.25rem;
            padding-top: 0.25rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .comp-label {
            font-size: 0.7rem;
            color: #6c757d;
            font-weight: 500;
            margin-right: 0.5rem;
        }

        .comp-value {
            font-size: 0.75rem;
            font-weight: 600;
            color: #495057;
        }

        .stats-change {
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Mantieni altezza standard delle card */
        .stats-card .card-body {
            min-height: 100px;
            display: flex;
            align-items: center;
        }

        /* Responsive per layout orizzontale */
        @media (max-width: 992px) {
            .daily-comparison {
                margin-left: 0.5rem;
            }

            .comparison-row {
                min-width: 70px;
            }

            .comp-label {
                font-size: 0.65rem;
            }

            .comp-value {
                font-size: 0.7rem;
            }

            .stats-change {
                font-size: 0.75rem;
            }
        }

        @media (max-width: 768px) {
            .daily-comparison {
                margin-left: 0.4rem;
            }

            .comparison-row {
                min-width: 60px;
                margin-bottom: 0.1rem;
            }

            .comp-label {
                font-size: 0.6rem;
                margin-right: 0.3rem;
            }

            .comp-value {
                font-size: 0.65rem;
            }

            .stats-change {
                font-size: 0.7rem;
            }
        }

        @media (max-width: 576px) {
            .d-flex.justify-content-between.align-items-start {
                gap: 0.5rem;
            }

            .daily-comparison {
                margin-left: 0.25rem;
            }

            .comparison-row {
                min-width: 50px;
            }
        }

        /* =======================
        STILI PER PORTI PIÙ RICERCATI
        ======================= */

        /* Container scrollabile per i porti */
        #ports-list {
            max-height: 320px;
            overflow-y: auto;
            padding: 0.5rem;
            position: relative;
        }

        /* Scrollbar personalizzata */
        #ports-list::-webkit-scrollbar {
            width: 6px;
        }

        #ports-list::-webkit-scrollbar-track {
            background: #f1f3f4;
            border-radius: 3px;
        }

        #ports-list::-webkit-scrollbar-thumb {
            background: var(--color-primary);
            border-radius: 3px;
        }

        #ports-list::-webkit-scrollbar-thumb:hover {
            background: var(--color-primary-dark);
        }

        /* Stili per le pills dei porti */
        .port-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 25px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .port-pill::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, var(--color-success), var(--color-success-light));
            transition: width 0.3s ease;
        }

        .port-pill:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(45, 80, 22, 0.15);
            border-color: var(--color-primary);
            background: linear-gradient(135deg, #fff, #f8fff9);
        }

        .port-pill:hover::before {
            width: 100%;
            opacity: 0.05;
        }

        .port-pill.top-port {
            border-color: var(--color-success);
            background: linear-gradient(135deg, #fff, #f0fff4);
            box-shadow: 0 2px 8px rgba(34, 84, 61, 0.1);
        }

        .port-pill.top-port::before {
            background: linear-gradient(135deg, #ffd700, #ff8c00);
        }

        .port-info {
            display: flex;
            align-items: center;
            flex-grow: 1;
            min-width: 0;
        }

        .port-icon {
            width: 2.5rem;
            height: 2.5rem;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            margin-right: 0.75rem;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }

        .port-pill.top-port .port-icon {
            background: linear-gradient(135deg, #ffd700, #ff8c00);
            box-shadow: 0 3px 10px rgba(255, 215, 0, 0.3);
        }

        .port-icon::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
            transform: rotate(45deg);
            transition: all 0.3s ease;
        }

        .port-pill:hover .port-icon::after {
            transform: rotate(45deg) scale(1.2);
        }

        .port-details {
            flex-grow: 1;
            min-width: 0;
        }

        .port-name {
            font-weight: 600;
            color: var(--color-dark);
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .port-stats {
            display: flex;
            gap: 0.75rem;
            font-size: 0.8rem;
            color: var(--color-muted);
        }

        .port-stat {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .port-stat i {
            color: var(--color-success-light);
            font-size: 0.75rem;
        }

        .port-count {
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light));
            color: white;
            padding: 0.4rem 0.75rem;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.85rem;
            min-width: 2.5rem;
            text-align: center;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(45, 80, 22, 0.2);
            position: relative;
            overflow: hidden;
        }

        .port-count::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }

        .port-pill:hover .port-count::before {
            left: 100%;
        }

        .port-pill.top-port .port-count {
            background: linear-gradient(135deg, #ffd700, #ff8c00);
            color: #333;
            font-weight: 700;
            box-shadow: 0 3px 8px rgba(255, 215, 0, 0.3);
            animation: goldPulse 2s ease-in-out infinite alternate;
        }

        @keyframes goldPulse {
            0% {
                box-shadow: 0 3px 8px rgba(255, 215, 0, 0.3);
            }

            100% {
                box-shadow: 0 4px 12px rgba(255, 215, 0, 0.5);
            }
        }

        /* Badge per il ranking */
        .rank-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 1.5rem;
            height: 1.5rem;
            background: linear-gradient(135deg, var(--color-danger), #c82333);
            color: white;
            border-radius: 50%;
            font-size: 0.7rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            z-index: 2;
            box-shadow: 0 2px 6px rgba(220, 53, 69, 0.3);
        }

        .port-pill.top-port .rank-badge {
            background: linear-gradient(135deg, #ffd700, #ff8c00);
            color: #333;
            animation: crownGlow 3s ease-in-out infinite alternate;
        }

        @keyframes crownGlow {
            0% {
                transform: scale(1);
                box-shadow: 0 2px 6px rgba(255, 215, 0, 0.4);
            }

            100% {
                transform: scale(1.1);
                box-shadow: 0 3px 10px rgba(255, 215, 0, 0.6);
            }
        }

        /* Gradient fade per indicare scroll */
        #ports-list::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: linear-gradient(transparent, rgba(248, 249, 250, 0.9));
            pointer-events: none;
        }

        /* Empty state per i porti */
        .ports-empty {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--color-muted);
        }

        .ports-empty i {
            color: var(--color-muted);
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .ports-empty h6 {
            color: var(--color-dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        /* View More Button */
        .view-more-btn {
            position: sticky;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(248, 249, 250, 1), rgba(248, 249, 250, 0.8));
            border: none;
            padding: 0.75rem;
            text-align: center;
            color: var(--color-primary);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            margin: 0.5rem -0.5rem -0.5rem -0.5rem;
        }

        .view-more-btn:hover {
            background: linear-gradient(to top, rgba(45, 80, 22, 0.1), rgba(248, 249, 250, 0.9));
            color: var(--color-primary-dark);
            transform: translateY(-1px);
        }

        .view-more-btn i {
            transition: transform 0.3s ease;
        }

        .view-more-btn:hover i {
            transform: translateY(2px);
        }

        /* Animazioni di hover */
        @keyframes portPulse {
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

        .port-pill:hover .port-icon {
            animation: portPulse 0.6s ease-in-out;
        }

        /* Effetto shimmer per i top porti */
        .port-pill.top-port::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.1), transparent);
            animation: shimmer 3s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes shimmer {
            0% {
                left: -100%;
            }

            50% {
                left: -100%;
            }

            100% {
                left: 100%;
            }
        }

        /* Responsive adjustments per i porti */
        @media (max-width: 768px) {
            #ports-list {
                max-height: 280px;
            }

            .port-pill {
                padding: 0.6rem 0.8rem;
                margin-bottom: 0.4rem;
            }

            .port-icon {
                width: 2rem;
                height: 2rem;
                font-size: 0.9rem;
                margin-right: 0.6rem;
            }

            .port-name {
                font-size: 0.9rem;
            }

            .port-stats {
                font-size: 0.75rem;
                gap: 0.5rem;
            }

            .port-count {
                padding: 0.3rem 0.6rem;
                font-size: 0.8rem;
                min-width: 2rem;
            }

            .rank-badge {
                width: 1.3rem;
                height: 1.3rem;
                font-size: 0.65rem;
            }

            .view-more-btn {
                padding: 0.6rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .port-pill {
                padding: 0.5rem 0.7rem;
                border-radius: 20px;
            }

            .port-stats {
                flex-direction: column;
                gap: 0.25rem;
                font-size: 0.7rem;
            }

            .port-name {
                font-size: 0.85rem;
                margin-bottom: 0.3rem;
            }

            .port-pill:hover {
                transform: translateX(2px);
            }
        }

        /* Transizioni fluide */
        .port-pill,
        .port-icon,
        .port-count,
        .rank-badge {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Focus styles per accessibilità */
        .port-pill:focus {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }

        .view-more-btn:focus {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }

        /* High contrast mode support per i porti */
        @media (prefers-contrast: high) {
            .port-pill {
                border: 2px solid #000;
            }

            .port-pill.top-port {
                border: 3px solid #000;
            }

            .port-icon,
            .port-count {
                border: 1px solid #000;
            }
        }

        /* Riduzione movimento per accessibilità */
        @media (prefers-reduced-motion: reduce) {

            .port-pill,
            .port-icon,
            .port-count,
            .rank-badge,
            .view-more-btn {
                animation: none !important;
                transition: none !important;
            }

            .port-pill:hover {
                transform: none !important;
            }
        }
    </style>
