{{-- 
    Global Styles - Reusable Component Styles
    Include this file in your layouts to have consistent styling across all pages.
    Change styles here to update all pages at once.
--}}

<style>
    /* ============================================
       GLOBAL STAT CARD STYLES
       ============================================ */
    .stat-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        box-sizing: border-box;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .stat-card .stat-icon,
    .stat-card .stat-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .stat-card .stat-card-icon i::before {
        display: inline-block;
    }

    /* Stat Card Variants */
    .stat-card-primary .stat-card-icon { background: #e8f4fd; color: #0d6efd; }
    .stat-card-success .stat-card-icon { background: #d1e7dd; color: #198754; }
    .stat-card-info .stat-card-icon { background: #cff4fc; color: #0dcaf0; }
    .stat-card-warning .stat-card-icon { background: #fff3cd; color: #ffc107; }
    .stat-card-danger .stat-card-icon { background: #f8d7da; color: #dc3545; }
    .stat-card-secondary .stat-card-icon { background: #e2e3e5; color: #6c757d; }

    /* Stat Card Row - Always 4 Columns */
    .stat-card-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
    }
    
    .stat-card-row-5 {
        grid-template-columns: repeat(5, 1fr);
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
    }
    
    .stat-card-row-6 {
        grid-template-columns: repeat(6, 1fr);
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
    }
    
    @media (max-width: 992px) {
        .stat-card-row-5 {
            grid-template-columns: repeat(3, 1fr);
        }
        .stat-card-row-6 {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .stat-card-row-5 {
            grid-template-columns: repeat(2, 1fr);
        }
        .stat-card-row-6 {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 576px) {
        .stat-card-row-5 {
            grid-template-columns: 1fr;
        }
        .stat-card-row-6 {
            grid-template-columns: 1fr;
        }
    }

    .stat-card-row .stat-card {
        min-height: 80px;
        align-items: stretch;
    }

    @media (max-width: 992px) {
        .stat-card-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .stat-card-row {
            grid-template-columns: 1fr;
        }
    }

    .stat-card-content {
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .stat-card-label {
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 2px;
    }

    .stat-card-value {
        font-size: 24px;
        font-weight: 700;
        color: #212529;
    }

    .stat-card-warning .stat-card-value { color: #ffc107; }

    .stat-card-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 22px;
    }

    /* ============================================
       CSS CUSTOM PROPERTIES (THEME VARIABLES)
       ============================================ */
    :root {
        /* Color Palette - Change these to update entire theme */
        --color-primary: #4f46e5;
        --color-primary-light: #818cf8;
        --color-primary-dark: #3730a3;
        --color-secondary: #7c3aed;
        --color-secondary-light: #a78bfa;
        --color-accent: #10b981;
        --color-accent-light: #34d399;
        
        /* Semantic Colors */
        --color-success: #10b981;
        --color-success-light: #d1fae5;
        --color-warning: #f59e0b;
        --color-warning-light: #fef3c7;
        --color-danger: #ef4444;
        --color-danger-light: #fee2e2;
        --color-info: #3b82f6;
        --color-info-light: #dbeafe;
        
        /* Neutral Colors */
        --color-white: #ffffff;
        --color-gray-50: #f9fafb;
        --color-gray-100: #f3f4f6;
        --color-gray-200: #e5e7eb;
        --color-gray-300: #d1d5db;
        --color-gray-400: #9ca3af;
        --color-gray-500: #6b7280;
        --color-gray-600: #4b5563;
        --color-gray-700: #374151;
        --color-gray-800: #1f2937;
        --color-gray-900: #111827;
        --color-black: #000000;
        
        /* Typography */
        --font-family-base: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        --font-family-heading: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        --font-size-xs: 0.75rem;
        --font-size-sm: 0.875rem;
        --font-size-base: 1rem;
        --font-size-lg: 1.125rem;
        --font-size-xl: 1.25rem;
        --font-size-2xl: 1.5rem;
        --font-size-3xl: 1.875rem;
        --font-size-4xl: 2.25rem;
        
        /* Spacing */
        --spacing-xs: 0.25rem;
        --spacing-sm: 0.5rem;
        --spacing-md: 1rem;
        --spacing-lg: 1.5rem;
        --spacing-xl: 2rem;
        --spacing-2xl: 3rem;
        
        /* Border Radius */
        --radius-sm: 0.25rem;
        --radius-md: 0.375rem;
        --radius-lg: 0.5rem;
        --radius-xl: 0.75rem;
        --radius-2xl: 1rem;
        --radius-full: 9999px;
        
        /* Shadows */
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        
        /* Transitions */
        --transition-fast: 150ms ease-in-out;
        --transition-base: 200ms ease-in-out;
        --transition-slow: 300ms ease-in-out;
        
        /* Layout */
        --page-padding: 1.5rem;
        --card-padding: 1.5rem;
        --section-spacing: 2rem;
    }

    /* ============================================
       BUTTON STYLES
       ============================================ */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.625rem 1.25rem;
        font-size: var(--font-size-sm);
        font-weight: 500;
        border-radius: var(--radius-md);
        border: 1px solid transparent;
        cursor: pointer;
        transition: all var(--transition-base);
        text-decoration: none;
        gap: var(--spacing-sm);
    }
    
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .btn-primary {
        background-color: var(--color-primary) !important;
        color: var(--color-white) !important;
        border-color: var(--color-primary) !important;
    }
    .btn-primary:hover:not(:disabled) {
        background-color: var(--color-primary-dark) !important;
        border-color: var(--color-primary-dark) !important;
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }
    
    .btn-secondary {
        background-color: var(--color-secondary);
        color: var(--color-white);
        border-color: var(--color-secondary);
    }
    .btn-secondary:hover:not(:disabled) {
        background-color: #6d28d9;
        border-color: #6d28d9;
        transform: translateY(-1px);
    }
    
    .btn-success {
        background-color: var(--color-success);
        color: var(--color-white);
        border-color: var(--color-success);
    }
    .btn-success:hover:not(:disabled) {
        background-color: #059669;
        border-color: #059669;
        transform: translateY(-1px);
    }
    
    .btn-danger {
        background-color: var(--color-danger);
        color: var(--color-white);
        border-color: var(--color-danger);
    }
    .btn-danger:hover:not(:disabled) {
        background-color: #dc2626;
        border-color: #dc2626;
        transform: translateY(-1px);
    }
    
    .btn-warning {
        background-color: var(--color-warning);
        color: var(--color-gray-900);
        border-color: var(--color-warning);
    }
    .btn-warning:hover:not(:disabled) {
        background-color: #d97706;
        border-color: #d97706;
        transform: translateY(-1px);
    }
    
    .btn-info {
        background-color: var(--color-info);
        color: var(--color-white);
        border-color: var(--color-info);
    }
    .btn-info:hover:not(:disabled) {
        background-color: #2563eb;
        border-color: #2563eb;
        transform: translateY(-1px);
    }
    
    .btn-outline-primary {
        background-color: transparent;
        border: 1px solid var(--color-primary);
        color: var(--color-primary);
    }
    .btn-outline-primary:hover:not(:disabled) {
        background-color: var(--color-primary);
        color: var(--color-white);
    }
    
    .btn-outline-secondary {
        background-color: transparent;
        border: 1px solid var(--color-secondary);
        color: var(--color-secondary);
    }
    .btn-outline-secondary:hover:not(:disabled) {
        background-color: var(--color-secondary);
        color: var(--color-white);
    }
    
    .btn-outline-success {
        background-color: transparent;
        border: 1px solid var(--color-success);
        color: var(--color-success);
    }
    .btn-outline-success:hover:not(:disabled) {
        background-color: var(--color-success);
        color: var(--color-white);
    }
    
    .btn-outline-danger {
        background-color: transparent;
        border: 1px solid var(--color-danger);
        color: var(--color-danger);
    }
    .btn-outline-danger:hover:not(:disabled) {
        background-color: var(--color-danger);
        color: var(--color-white);
    }
    
    .btn-outline-warning {
        background-color: transparent;
        border: 1px solid var(--color-warning);
        color: var(--color-warning);
    }
    .btn-outline-warning:hover:not(:disabled) {
        background-color: var(--color-warning);
        color: var(--color-gray-900);
    }
    
    .btn-outline-info {
        background-color: transparent;
        border: 1px solid var(--color-info);
        color: var(--color-info);
    }
    .btn-outline-info:hover:not(:disabled) {
        background-color: var(--color-info);
        color: var(--color-white);
    }
    
    .btn-light {
        background-color: var(--color-gray-100);
        color: var(--color-gray-700);
        border-color: var(--color-gray-200);
    }
    .btn-light:hover:not(:disabled) {
        background-color: var(--color-gray-200);
        border-color: var(--color-gray-300);
    }
    
    .btn-dark {
        background-color: var(--color-gray-800);
        color: var(--color-white);
        border-color: var(--color-gray-800);
    }
    .btn-dark:hover:not(:disabled) {
        background-color: var(--color-gray-900);
        border-color: var(--color-gray-900);
    }
    
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: var(--font-size-xs);
    }
    
    .btn-lg {
        padding: 0.875rem 1.75rem;
        font-size: var(--font-size-base);
    }
    
    .btn-icon {
        padding: 0.5rem;
        width: 2.5rem;
        height: 2.5rem;
    }
    
    .btn-icon-sm {
        padding: 0.375rem;
        width: 2rem;
        height: 2rem;
    }

    /* ============================================
       CARD STYLES - Modern & Professional
       ============================================ */
    .card {
        background-color: var(--color-white);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--color-gray-100);
        overflow: hidden;
        transition: all var(--transition-base);
    }
    
    .card:hover {
        box-shadow: var(--shadow-md);
    }
    
    .card-header {
        padding: var(--card-padding);
        border-bottom: 1px solid var(--color-gray-100);
        background-color: transparent;
    }
    
    .card-header-transparent {
        background-color: transparent !important;
        border-bottom: none !important;
    }
    
    .card-body {
        padding: var(--card-padding);
    }
    
    .card-body-no-padding {
        padding: 0 !important;
    }
    
    .card-footer {
        padding: var(--card-padding);
        border-top: 1px solid var(--color-gray-100);
        background-color: transparent;
    }
    
    .card-title {
        font-size: var(--font-size-lg);
        font-weight: 600;
        color: var(--color-gray-900);
        margin: 0;
        line-height: 1.4;
    }
    
    .card-subtitle {
        font-size: var(--font-size-sm);
        color: var(--color-gray-500);
        margin-top: var(--spacing-xs);
    }
    
    /* Card Variants */
    .card-elevated {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    .card-elevated:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }
    
    .card-bordered {
        border-width: 2px;
        border-color: var(--color-gray-200);
    }
    
    .card-flat {
        box-shadow: none;
        border: 1px solid var(--color-gray-200);
    }
    
    /* Modern Card with Gradient Border */
    .card-gradient {
        position: relative;
        background: var(--color-white);
        border-radius: var(--radius-xl);
    }
    
    .card-gradient::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: var(--radius-xl);
        padding: 2px;
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }
    
    /* Glassmorphism Card */
    .card-glass {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    }
    
    /* Card with Hover Lift Effect */
    .card-lift {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card-lift:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
    }
    
    /* Stat Card - Perfect for Dashboard */
    .card-stat {
        position: relative;
        overflow: hidden;
    }
    
    .card-stat::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(79, 70, 229, 0.05) 0%, transparent 70%);
        pointer-events: none;
    }
    
    .card-stat-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: var(--spacing-md);
    }
    
    .card-stat-value {
        font-size: var(--font-size-2xl);
        font-weight: 700;
        color: var(--color-gray-900);
        line-height: 1.2;
    }
    
    .card-stat-label {
        font-size: var(--font-size-sm);
        color: var(--color-gray-500);
        margin-top: var(--spacing-xs);
    }
    
    .card-stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: var(--font-size-xs);
        font-weight: 500;
        padding: 4px 8px;
        border-radius: var(--radius-full);
        margin-top: var(--spacing-sm);
    }
    
    .card-stat-trend-up {
        background-color: var(--color-success-light);
        color: var(--color-success);
    }
    
    .card-stat-trend-down {
        background-color: var(--color-danger-light);
        color: var(--color-danger);
    }
    
    /* Image Card - For Products/Featured Items */
    .card-image {
        position: relative;
        overflow: hidden;
    }
    
    .card-image img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .card-image:hover img {
        transform: scale(1.05);
    }
    
    .card-image-badge {
        position: absolute;
        top: var(--spacing-md);
        left: var(--spacing-md);
        padding: 6px 12px;
        border-radius: var(--radius-full);
        font-size: var(--font-size-xs);
        font-weight: 600;
    }
    
    /* Card with Side Accent */
    .card-accent {
        position: relative;
    }
    
    .card-accent::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, var(--color-primary), var(--color-secondary));
        border-radius: var(--radius-xl) 0 0 var(--radius-xl);
    }
    
    /* Compact Card */
    .card-compact {
        padding: var(--spacing-md);
    }
    
    /* Card Group */
    .card-group {
        display: grid;
        gap: var(--spacing-lg);
    }
    
    .card-group-2 {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .card-group-3 {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .card-group-4 {
        grid-template-columns: repeat(4, 1fr);
    }
    
    @media (max-width: 992px) {
        .card-group-4 {
            grid-template-columns: repeat(2, 1fr);
        }
        .card-group-3 {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 576px) {
        .card-group-4,
        .card-group-3,
        .card-group-2 {
            grid-template-columns: 1fr;
        }
    }

    /* ============================================
       TABLE STYLES
       ============================================ */
    .table-container {
        overflow-x: auto;
        border-radius: var(--radius-lg);
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: var(--font-size-sm);
    }
    
    .table th {
        background-color: var(--color-gray-50);
        padding: var(--spacing-md);
        text-align: left;
        font-weight: 600;
        color: var(--color-gray-700);
        border-bottom: 2px solid var(--color-gray-200);
        white-space: nowrap;
    }
    
    .table td {
        padding: var(--spacing-md);
        border-bottom: 1px solid var(--color-gray-200);
        color: var(--color-gray-700);
        vertical-align: middle;
    }
    
    .table tbody tr:hover {
        background-color: var(--color-gray-50);
    }
    
    .table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .table-striped tbody tr:nth-child(odd) {
        background-color: var(--color-gray-50);
    }
    
    .table-bordered th,
    .table-bordered td {
        border: 1px solid var(--color-gray-200);
    }

    /* ============================================
       FORM STYLES
       ============================================ */
    .form-group {
        margin-bottom: var(--spacing-lg);
    }
    
    .form-label {
        display: block;
        font-size: var(--font-size-sm);
        font-weight: 500;
        color: var(--color-gray-700);
        margin-bottom: var(--spacing-sm);
    }
    
    .form-control {
        width: 100%;
        padding: 0.625rem 0.875rem;
        font-size: var(--font-size-sm);
        border: 1px solid var(--color-gray-300);
        border-radius: var(--radius-md);
        background-color: var(--color-white);
        transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .form-control::placeholder {
        color: var(--color-gray-400);
    }
    
    .form-control:disabled {
        background-color: var(--color-gray-100);
        cursor: not-allowed;
    }
    
    .form-control-lg {
        padding: 0.875rem 1rem;
        font-size: var(--font-size-base);
    }
    
    .form-control-sm {
        padding: 0.375rem 0.625rem;
        font-size: var(--font-size-xs);
    }
    
    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23374151' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        padding-right: 2.5rem;
    }
    
    .form-check-input:checked {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
    }
    
    .form-switch .form-check-input:checked {
        background-color: var(--color-primary);
    }
    
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        color: var(--color-primary);
    }

    /* ============================================
       BADGE STYLES
       ============================================ */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.625rem;
        font-size: var(--font-size-xs);
        font-weight: 500;
        border-radius: var(--radius-full);
        line-height: 1;
    }
    
    .badge-primary {
        background-color: rgba(79, 70, 229, 0.1);
        color: var(--color-primary);
    }
    
    .badge-secondary {
        background-color: rgba(124, 58, 237, 0.1);
        color: var(--color-secondary);
    }
    
    .badge-success {
        background-color: var(--color-success-light);
        color: var(--color-success);
    }
    
    .badge-warning {
        background-color: var(--color-warning-light);
        color: #b45309;
    }
    
    .badge-danger {
        background-color: var(--color-danger-light);
        color: var(--color-danger);
    }
    
    .badge-info {
        background-color: var(--color-info-light);
        color: var(--color-info);
    }
    
    .badge-light {
        background-color: var(--color-gray-100);
        color: var(--color-gray-700);
    }
    
    .badge-dark {
        background-color: var(--color-gray-800);
        color: var(--color-white);
    }

    /* ============================================
       PAGINATION STYLES
       ============================================ */
    .pagination {
        display: flex;
        gap: var(--spacing-xs);
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .page-item .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0 0.5rem;
        font-size: 0.875rem;
        line-height: 1;
        color: var(--color-gray-700);
        background-color: var(--color-white);
        border: 1px solid var(--color-gray-300);
        border-radius: var(--radius-md);
        text-decoration: none;
        transition: all var(--transition-fast);
    }
    
    .page-item .page-link i,
    .page-item .page-link i::before,
    .page-item .page-link .bi,
    .page-item .page-link .bi::before {
        font-size: 0.875rem !important;
        line-height: 1 !important;
    }
    
    .page-item .page-link:hover {
        background-color: var(--color-gray-100);
        border-color: var(--color-gray-300);
    }
    
    .page-item.active .page-link {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
        color: var(--color-white);
    }
    
    .page-item.disabled .page-link {
        color: var(--color-gray-400);
        background-color: var(--color-gray-100);
        border-color: var(--color-gray-200);
        cursor: not-allowed;
    }

    /* ============================================
       ALERT STYLES
       ============================================ */
    .alert {
        padding: var(--spacing-md) var(--spacing-lg);
        border-radius: var(--radius-md);
        border: none;
        font-size: var(--font-size-sm);
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
    
    .alert-success {
        background-color: var(--color-success-light);
        color: #065f46;
    }
    
    .alert-warning {
        background-color: var(--color-warning-light);
        color: #92400e;
    }
    
    .alert-danger {
        background-color: var(--color-danger-light);
        color: #991b1b;
    }
    
    .alert-info {
        background-color: var(--color-info-light);
        color: #1e40af;
    }
    
    .alert-dismissible .btn-close {
        padding: var(--spacing-md);
    }

    /* ============================================
       MODAL STYLES
       ============================================ */
    .modal-content {
        border: none;
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-xl);
    }
    
    .modal-header {
        padding: var(--spacing-lg);
        border-bottom: 1px solid var(--color-gray-200);
    }
    
    .modal-body {
        padding: var(--spacing-lg);
    }
    
    .modal-footer {
        padding: var(--spacing-lg);
        border-top: 1px solid var(--color-gray-200);
    }

    /* ============================================
       DROPDOWN MENU
       ============================================ */
    .dropdown-menu {
        border: 1px solid var(--color-gray-200);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        padding: var(--spacing-sm);
    }
    
    .dropdown-item {
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: var(--radius-md);
        font-size: var(--font-size-sm);
        color: var(--color-gray-700);
    }
    
    .dropdown-item:hover {
        background-color: var(--color-gray-100);
        color: var(--color-gray-900);
    }
    
    .dropdown-item.active,
    .dropdown-item:active {
        background-color: var(--color-primary);
        color: var(--color-white);
    }

    /* ============================================
       SPACING UTILITIES - PADDING & MARGIN
       (Scoped to .global-styles to avoid conflicts with Tailwind/Bootstrap)
       ============================================ */
    /* Padding */
    .global-styles .p-0 { padding: 0 !important; }
    .global-styles .p-1 { padding: var(--spacing-xs) !important; }
    .global-styles .p-2 { padding: var(--spacing-sm) !important; }
    .global-styles .p-3 { padding: var(--spacing-md) !important; }
    .global-styles .p-4 { padding: var(--spacing-lg) !important; }
    .global-styles .p-5 { padding: var(--spacing-xl) !important; }
    
    .global-styles .px-3 { padding-left: var(--spacing-md) !important; padding-right: var(--spacing-md) !important; }
    .global-styles .px-4 { padding-left: var(--spacing-lg) !important; padding-right: var(--spacing-lg) !important; }
    .global-styles .px-5 { padding-left: var(--spacing-xl) !important; padding-right: var(--spacing-xl) !important; }
    
    .global-styles .py-3 { padding-top: var(--spacing-md) !important; padding-bottom: var(--spacing-md) !important; }
    .global-styles .py-4 { padding-top: var(--spacing-lg) !important; padding-bottom: var(--spacing-lg) !important; }
    .global-styles .py-5 { padding-top: var(--spacing-xl) !important; padding-bottom: var(--spacing-xl) !important; }
    
    /* Margin */
    .global-styles .m-0 { margin: 0 !important; }
    .global-styles .m-1 { margin: var(--spacing-xs) !important; }
    .global-styles .m-2 { margin: var(--spacing-sm) !important; }
    .global-styles .m-3 { margin: var(--spacing-md) !important; }
    .global-styles .m-4 { margin: var(--spacing-lg) !important; }
    .global-styles .m-5 { margin: var(--spacing-xl) !important; }
    
    .global-styles .mx-auto { margin-left: auto !important; margin-right: auto !important; }
    .global-styles .my-3 { margin-top: var(--spacing-md) !important; margin-bottom: var(--spacing-md) !important; }
    .global-styles .my-4 { margin-top: var(--spacing-lg) !important; margin-bottom: var(--spacing-lg) !important; }
    
    .global-styles .mt-0 { margin-top: 0 !important; }
    .global-styles .mt-1 { margin-top: var(--spacing-xs) !important; }
    .global-styles .mt-2 { margin-top: var(--spacing-sm) !important; }
    .global-styles .mt-3 { margin-top: var(--spacing-md) !important; }
    .global-styles .mt-4 { margin-top: var(--spacing-lg) !important; }
    .global-styles .mt-5 { margin-top: var(--spacing-xl) !important; }
    
    .global-styles .mb-0 { margin-bottom: 0 !important; }
    .global-styles .mb-1 { margin-bottom: var(--spacing-xs) !important; }
    .global-styles .mb-2 { margin-bottom: var(--spacing-sm) !important; }
    .global-styles .mb-3 { margin-bottom: var(--spacing-md) !important; }
    .global-styles .mb-4 { margin-bottom: var(--spacing-lg) !important; }
    .global-styles .mb-5 { margin-bottom: var(--spacing-xl) !important; }
    
    .global-styles .ms-auto { margin-left: auto !important; }
    .global-styles .me-auto { margin-right: auto !important; }

    /* ============================================
       PAGE LAYOUT UTILITIES
       ============================================ */
    .page-wrapper {
        padding: var(--page-padding);
        min-height: 100vh;
    }
    
    .section {
        margin-bottom: var(--section-spacing);
    }
    
    .section-title {
        font-size: var(--font-size-xl);
        font-weight: 600;
        color: var(--color-gray-900);
        margin-bottom: var(--spacing-lg);
    }
    
    .section-subtitle {
        font-size: var(--font-size-sm);
        color: var(--color-gray-500);
        margin-bottom: var(--spacing-lg);
    }

    /* ============================================
       TEXT UTILITIES
       (Scoped to .global-styles to avoid conflicts with Tailwind/Bootstrap)
       ============================================ */
    .global-styles .text-primary { color: var(--color-primary) !important; }
    .global-styles .text-secondary { color: var(--color-secondary) !important; }
    .global-styles .text-success { color: var(--color-success) !important; }
    .global-styles .text-warning { color: var(--color-warning) !important; }
    .global-styles .text-danger { color: var(--color-danger) !important; }
    .global-styles .text-info { color: var(--color-info) !important; }
    .global-styles .text-muted { color: var(--color-gray-500) !important; }
    .global-styles .text-dark { color: var(--color-gray-900) !important; }
    .global-styles .text-white { color: var(--color-white) !important; }
    
    .global-styles .text-xs { font-size: var(--font-size-xs) !important; }
    .global-styles .text-sm { font-size: var(--font-size-sm) !important; }
    .global-styles .text-base { font-size: var(--font-size-base) !important; }
    .global-styles .text-lg { font-size: var(--font-size-lg) !important; }
    .global-styles .text-xl { font-size: var(--font-size-xl) !important; }
    
    .global-styles .font-medium { font-weight: 500 !important; }
    .global-styles .font-semibold { font-weight: 600 !important; }
    .global-styles .font-bold { font-weight: 700 !important; }

    /* ============================================
       BACKGROUND UTILITIES
       (Scoped to .global-styles to avoid conflicts with Tailwind/Bootstrap)
       ============================================ */
    .global-styles .bg-primary { background-color: var(--color-primary) !important; }
    .global-styles .bg-secondary { background-color: var(--color-secondary) !important; }
    .global-styles .bg-success { background-color: var(--color-success) !important; }
    .global-styles .bg-warning { background-color: var(--color-warning) !important; }
    .global-styles .bg-danger { background-color: var(--color-danger) !important; }
    .global-styles .bg-info { background-color: var(--color-info) !important; }
    .global-styles .bg-light { background-color: var(--color-gray-100) !important; }
    .global-styles .bg-dark { background-color: var(--color-gray-800) !important; }
    .global-styles .bg-white { background-color: var(--color-white) !important; }

    /* ============================================
       BORDER UTILITIES
       (Scoped to .global-styles to avoid conflicts with Tailwind/Bootstrap)
       ============================================ */
    .global-styles .border { border: 1px solid var(--color-gray-200) !important; }
    .global-styles .border-0 { border: 0 !important; }
    .global-styles .border-top { border-top: 1px solid var(--color-gray-200) !important; }
    .global-styles .border-bottom { border-bottom: 1px solid var(--color-gray-200) !important; }
    .global-styles .border-primary { border-color: var(--color-primary) !important; }
    .global-styles .rounded { border-radius: var(--radius-md) !important; }
    .global-styles .rounded-lg { border-radius: var(--radius-lg) !important; }
    .global-styles .rounded-xl { border-radius: var(--radius-xl) !important; }
    .global-styles .rounded-full { border-radius: var(--radius-full) !important; }
    .global-styles .rounded-0 { border-radius: 0 !important; }

    /* ============================================
       SHADOW UTILITIES
       (Scoped to .global-styles to avoid conflicts with Tailwind/Bootstrap)
       ============================================ */
    .global-styles .shadow-sm { box-shadow: var(--shadow-sm) !important; }
    .global-styles .shadow { box-shadow: var(--shadow-md) !important; }
    .global-styles .shadow-lg { box-shadow: var(--shadow-lg) !important; }
    .global-styles .shadow-none { box-shadow: none !important; }

    /* ============================================
       FLEX UTILITIES
       (Scoped to .global-styles to avoid conflicts with Tailwind/Bootstrap)
       ============================================ */
    .global-styles .d-flex { display: flex !important; }
    .global-styles .d-inline-flex { display: inline-flex !important; }
    .global-styles .flex-row { flex-direction: row !important; }
    .global-styles .flex-column { flex-direction: column !important; }
    .global-styles .flex-wrap { flex-wrap: wrap !important; }
    .global-styles .flex-nowrap { flex-wrap: nowrap !important; }
    .global-styles .justify-content-start { justify-content: flex-start !important; }
    .global-styles .justify-content-center { justify-content: center !important; }
    .global-styles .justify-content-end { justify-content: flex-end !important; }
    .global-styles .justify-content-between { justify-content: space-between !important; }
    .global-styles .align-items-start { align-items: flex-start !important; }
    .global-styles .align-items-center { align-items: center !important; }
    .global-styles .align-items-end { align-items: flex-end !important; }
    .global-styles .gap-1 { gap: var(--spacing-xs) !important; }
    .global-styles .gap-2 { gap: var(--spacing-sm) !important; }
    .global-styles .gap-3 { gap: var(--spacing-md) !important; }
    .global-styles .gap-4 { gap: var(--spacing-lg) !important; }

    /* ============================================
       ICON STYLES
       (Scoped to .global-styles to avoid conflicts with Tailwind/Bootstrap)
       ============================================ */
    .global-styles .icon-sm { font-size: 1rem !important; }
    .global-styles .icon-base { font-size: 1.25rem !important; }
    .global-styles .icon-lg { font-size: 1.5rem !important; }
    .global-styles .icon-xl { font-size: 2rem !important; }
    
    .global-styles .icon-primary { color: var(--color-primary); }
    .global-styles .icon-success { color: var(--color-success); }
    .global-styles .icon-warning { color: var(--color-warning); }
    .global-styles .icon-danger { color: var(--color-danger); }

    /* ============================================
       AVATAR STYLES
       ============================================ */
    .avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-full);
        object-fit: cover;
    }
    
    .avatar-sm {
        width: 2rem;
        height: 2rem;
    }
    
    .avatar-lg {
        width: 3.5rem;
        height: 3.5rem;
    }
    
    .avatar-xl {
        width: 4.5rem;
        height: 4.5rem;
    }
    
    .avatar-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--color-gray-200);
        color: var(--color-gray-600);
        font-weight: 600;
    }

    /* ============================================
       STAT CARD - Dashboard Statistics
       (Uses Admin Layout styles - see admin/layouts/app.blade.php)
       ============================================ */

    /* ============================================
       FILTER STYLES
       ============================================ */
    .filter-container {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--color-gray-200);
    }
    
    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: var(--spacing-md);
        align-items: flex-end;
    }
    
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-label {
        display: block;
        font-size: var(--font-size-xs);
        font-weight: 500;
        color: var(--color-gray-600);
        margin-bottom: var(--spacing-xs);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .filter-actions {
        display: flex;
        gap: var(--spacing-sm);
    }

    /* ============================================
       ACTION BUTTONS (Edit/Delete/View)
       ============================================ */
    .action-btns {
        display: flex;
        gap: var(--spacing-xs);
    }
    
    .action-btn {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-md);
        border: none;
        cursor: pointer;
        transition: all var(--transition-fast);
        text-decoration: none;
    }
    
    .action-btn-view {
        background-color: var(--color-info-light);
        color: var(--color-info);
    }
    .action-btn-view:hover {
        background-color: var(--color-info);
        color: var(--color-white);
    }
    
    .action-btn-edit {
        background-color: var(--color-warning-light);
        color: #b45309;
    }
    .action-btn-edit:hover {
        background-color: var(--color-warning);
        color: var(--color-white);
    }
    
    .action-btn-delete {
        background-color: var(--color-danger-light);
        color: var(--color-danger);
    }
    .action-btn-delete:hover {
        background-color: var(--color-danger);
        color: var(--color-white);
    }

    /* ============================================
       EMPTY STATE
       ============================================ */
    .empty-state {
        text-align: center;
        padding: var(--spacing-2xl);
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: var(--color-gray-300);
        margin-bottom: var(--spacing-lg);
    }
    
    .empty-state-title {
        font-size: var(--font-size-lg);
        font-weight: 600;
        color: var(--color-gray-700);
        margin-bottom: var(--spacing-sm);
    }
    
    .empty-state-text {
        font-size: var(--font-size-sm);
        color: var(--color-gray-500);
        margin-bottom: var(--spacing-lg);
    }

    /* ============================================
       LOADING SPINNER
       ============================================ */
    .spinner {
        width: 2rem;
        height: 2rem;
        border: 3px solid var(--color-gray-200);
        border-top-color: var(--color-primary);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    
    .spinner-sm {
        width: 1.25rem;
        height: 1.25rem;
        border-width: 2px;
    }
    
    .spinner-lg {
        width: 3rem;
        height: 3rem;
        border-width: 4px;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ============================================
       RESPONSIVE HELPERS
       ============================================ */
    @media (max-width: 768px) {
        :root {
            --page-padding: 1rem;
            --card-padding: 1rem;
            --section-spacing: 1.5rem;
        }
        
        .hide-mobile { display: none !important; }
    }
    
    @media (min-width: 769px) {
        .hide-desktop { display: none !important; }
    }
    
    /* ============================================
       CONTENT AREA & EXTRA PADDING FIX
       Prevent double padding/wrappers in admin pages
       ============================================ */
    
    /* Fix: Remove extra padding when page adds duplicate wrapper */
    .content-area > .content-area,
    .content-area > .container-fluid,
    .content-area > .container {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
    
    /* Fix: Prevent double padding on content-area */
    .content-area.pb-4,
    .content-area.pt-4,
    .content-area.py-4,
    .content-area.p-4 {
        padding: var(--page-padding) !important;
    }
    
    /* Fix: Ensure first element in content-area has no extra top margin */
    .content-area > .row:first-child,
    .content-area > .card:first-child,
    .content-area > div:first-of-type {
        margin-top: 0 !important;
    }
    
    /* Fix: Remove unnecessary extra wrappers that conflict with layout */
    .content-area .content-area {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    /* Fix: Consistent spacing for page headers */
    .content-area .page-header,
    .content-area .page-header-row {
        margin-bottom: var(--spacing-lg) !important;
    }
    
    /* Fix: Floating save button container spacing */
    .floating-save-btn,
    .floating-reset-btn {
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }
    
    /* Fix: Ensure content-area has proper padding from layout */
    .admin-page-content {
        padding: var(--page-padding);
    }
    
    /* ============================================
       POS PAGE STYLES
       ============================================ */
    
    /* POS Terminal - Full height layout */
    .pos-terminal {
        height: calc(100vh - 140px);
        display: flex;
        flex-direction: column;
    }
    
    .pos-products-panel {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        border: 1px solid var(--color-gray-200);
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .pos-search-bar {
        background: var(--color-white);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }
    
    .pos-products-grid {
        background: var(--color-gray-50);
        max-height: calc(100vh - 300px);
        overflow-y: auto;
    }
    
    /* POS Product Card */
    .pos-product-card {
        background: var(--color-white);
        border: 1px solid var(--color-gray-200);
        border-radius: var(--radius-lg);
        padding: 12px;
        cursor: pointer;
        transition: all var(--transition-base);
    }

    .pos-product-card:hover {
        border-color: var(--color-primary);
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .pos-product-card .product-image {
        width: 100%;
        height: 100px;
        object-fit: cover;
        border-radius: var(--radius-md);
        background: var(--color-gray-100);
    }

    .pos-product-card .product-name {
        font-size: 0.9rem;
        font-weight: 500;
        margin: 8px 0 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pos-product-card .product-price {
        font-size: 1rem;
        font-weight: 700;
        color: var(--color-success);
    }

    .pos-product-card .product-stock {
        font-size: 0.75rem;
        color: var(--color-gray-500);
    }
    
    /* POS Cart Panel */
    .pos-cart-panel {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        border: 1px solid var(--color-gray-200);
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .pos-cart-header {
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }
    
    .pos-cart-items {
        background: var(--color-gray-50);
    }

    /* POS Cart Item */
    .pos-cart-item {
        display: flex;
        align-items: center;
        padding: 10px;
        background: var(--color-white);
        border-radius: var(--radius-lg);
        margin-bottom: 10px;
        box-shadow: var(--shadow-sm);
    }

    .pos-cart-item .item-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: var(--radius-md);
        background: var(--color-gray-100);
        margin-right: 10px;
    }

    .pos-cart-item .item-details {
        flex: 1;
    }

    .pos-cart-item .item-name {
        font-weight: 500;
        font-size: 0.9rem;
    }

    .pos-cart-item .item-price {
        color: var(--color-gray-500);
        font-size: 0.85rem;
    }

    .pos-cart-item .item-quantity {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .pos-cart-item .qty-btn {
        width: 28px;
        height: 28px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pos-cart-item .item-total {
        font-weight: 600;
        color: var(--color-success);
    }

    .pos-cart-item .remove-btn {
        color: var(--color-danger);
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
    }
    
    .pos-cart-summary {
        border-radius: 0 0 var(--radius-lg) var(--radius-lg);
    }
    
    /* Cash Register Page */
    .cash-register-page {
        min-height: calc(100vh - 200px);
    }
    
    /* POS Reports Page */
    .pos-reports-page {
        min-height: calc(100vh - 200px);
    }
    
    /* Card styling within stat-card-row for Cash Register & POS Reports */
    .stat-card-row .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        width: 100%;
        height: 100%;
        box-sizing: border-box;
    }
    
    .stat-card-row .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .stat-card-row .card .card-body {
        padding: 16px;
    }
    
    .stat-card-row .card .card-body .h3,
    .stat-card-row .card .card-body h3 {
        font-size: 24px;
        font-weight: 700;
    }
    
    /* GLOBAL STAT CARD STYLES - Transform Bootstrap .card to stat-card like Staffs page */
    /* The Staffs page uses .stat-card structure - we transform .card to match */
    
    /* Container: grid layout 4 columns */
    .stat-card-row {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 16px !important;
        width: 100% !important;
    }
    
    /* Override Bootstrap columns inside stat-card-row */
    .stat-card-row .col-md-3,
    .stat-card-row .col-sm-6,
    .stat-card-row .col-6 {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
        padding: 0 !important;
    }
    
    /* Transform Bootstrap .card to stat-card appearance */
    .stat-card-row .card {
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05) !important;
        transition: all 0.3s ease !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 16px !important;
        min-height: 80px !important;
        width: 100% !important;
        height: 100% !important;
        box-sizing: border-box !important;
    }
    
    .stat-card-row .card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }
    
    /* Card body: column layout - stacked with icon area above content */
    .stat-card-row .card .card-body {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    
    
    /* Label: small muted text */
    .stat-card-row .card .card-body .text-muted {
        font-size: 13px !important;
        color: #6c757d !important;
    }
    
    /* Value: h3 */
    .stat-card-row .card .card-body .h3,
    .stat-card-row .card .card-body h3 {
        font-size: 24px !important;
        font-weight: 700 !important;
        margin: 0 !important;
    }
    
    /* Color variants */
    .stat-card-row .card .card-body .text-primary { color: #0d6efd !important; }
    .stat-card-row .card .card-body .text-success { color: #198754 !important; }
    .stat-card-row .card .card-body .text-info { color: #0dcaf0 !important; }
    .stat-card-row .card .card-body .text-warning { color: #ffc107 !important; }
    
    /* Responsive: 2 columns on tablet */
    @media (max-width: 992px) {
        .stat-card-row {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
    
    /* Responsive: 1 column on mobile */
    @media (max-width: 576px) {
        .stat-card-row {
            grid-template-columns: 1fr !important;
        }
    }
</style>
