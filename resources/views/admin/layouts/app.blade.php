<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Admin Panel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Pagination override - must come after Bootstrap */
        .admin-panel .pagination .page-link,
        .card-footer .pagination .page-link {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
            min-width: 2rem !important;
            height: 2rem !important;
        }
        .admin-panel .pagination .page-link i::before,
        .card-footer .pagination .page-link i::before,
        .admin-panel .pagination .page-link .bi::before,
        .card-footer .pagination .page-link .bi::before {
            font-size: 0.875rem !important;
            line-height: 1 !important;
        }
    </style>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar.collapsed .sidebar-header h4 span,
        .sidebar.collapsed .menu-category-title,
        .sidebar.collapsed .menu-badge,
        .sidebar.collapsed .arrow,
        .sidebar.collapsed .submenu {
            display: none;
        }
        
        .sidebar.collapsed .menu-category-header {
            justify-content: center;
            padding: 12px;
        }
        
        .sidebar.collapsed .menu-category-header i.menu-icon {
            margin-right: 0;
            font-size: 1.3rem;
        }
        
        .sidebar.collapsed .sidebar-header h4 {
            text-align: center;
        }
        
        .sidebar.collapsed .sidebar-header h4 i {
            margin-right: 0 !important;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            flex-shrink: 0;
        }
        
        .sidebar-header h4 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-menu {
            padding: 10px 0;
            overflow-y: auto;
            flex: 1;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.3) transparent;
        }
        
        .sidebar-menu::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-menu::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar-menu::-webkit-scrollbar-thumb {
            background-color: rgba(255,255,255,0.3);
            border-radius: 3px;
        }
        
        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255,255,255,0.5);
        }
        
        /* Menu Category */
        .menu-category {
            margin-bottom: 5px;
        }
        
        .menu-category-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            color: rgba(255,255,255,0.9);
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            text-decoration: none !important;
        }
        
        .menu-category-header:hover {
            background: rgba(255,255,255,0.05);
            color: white;
            text-decoration: none !important;
        }
        
        .menu-category-header.active {
            background: rgba(255,255,255,0.1);
            border-left-color: #667eea;
            color: white;
            text-decoration: none !important;
        }
        
        .menu-category-header i.menu-icon {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        .menu-category-header i.arrow {
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }
        
        .menu-category-header[aria-expanded="true"] i.arrow {
            transform: rotate(180deg);
        }
        
        .menu-category-title {
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Submenu */
        .submenu {
            background: rgba(0,0,0,0.15);
            padding: 5px 0;
        }
        
        .submenu .nav-item {
            margin: 1px 10px;
        }
        
        .submenu .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 10px 20px 10px 47px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }
        
        .submenu .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.08);
        }
        
        .submenu .nav-link.active {
            color: white;
            background: rgba(102, 126, 234, 0.3);
        }
        
        .submenu .nav-link i {
            margin-right: 10px;
            font-size: 0.9rem;
            width: 16px;
        }
        
        /* Badge */
        .menu-badge {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: auto;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        /* Sidebar Toggle Button */
        .sidebar-toggle-btn {
            position: absolute;
            top: 50%;
            right: -12px;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            background: #667eea;
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1001;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .sidebar-toggle-btn:hover {
            background: #5a6fd6;
            transform: translateY(-50%) scale(1.1);
        }
        
        .sidebar-toggle-btn i {
            font-size: 0.7rem;
            transition: transform 0.3s ease;
        }
        
        .sidebar.collapsed .sidebar-toggle-btn i {
            transform: rotate(180deg);
        }
        
        /* Header */
        .main-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #e9ecef;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .main-header .navbar {
            height: 100%;
        }
        
        /* Content Area */
        .content-area {
            padding: 20px;
        }
        
        /* All Bootstrap Icons - Force visibility */
        i[class*="bi bi-"],
        i[class*="bi-"]:before,
        span[class*="bi bi-"],
        span[class*="bi bi-"]:before {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* Card Header Icons - Ensure visibility */
        .card-header i.bi {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            font-family: 'bootstrap-icons' !important;
            font-style: normal !important;
            font-weight: normal !important;
            font-variant: normal !important;
            text-transform: none !important;
            line-height: 1 !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
        }
        
        /* Card Body Icons - Ensure visibility */
        .card-body i.bi {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            font-family: 'bootstrap-icons' !important;
            font-style: normal !important;
            font-weight: normal !important;
            font-variant: normal !important;
            text-transform: none !important;
            line-height: 1 !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
        }
        
        /* Statistics Card - Universal Global Style */
        /* Use this class on any page for consistent stats cards */
        .stat-card-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-card-grid > .stat-card-item {
            flex: 1 1 calc(20% - 0.5rem);
            min-width: 140px;
            max-width: 100%;
        }
        
        .stat-card-grid .stat-card-item .card {
            height: 100%;
            border: 0;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
        }
        
        .stat-card-grid .stat-card-item .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .stat-card-grid .stat-card-item .card-body {
            padding: 1rem;
            text-align: center;
        }
        
        /* GLOBAL: Same font/label style for ALL pages */
        .stat-card-grid .stat-card-item .card-body .stat-label {
            font-size: 0.75rem;
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        
        /* GLOBAL: Same font/value style for ALL pages */
        .stat-card-grid .stat-card-item .card-body .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 0;
        }
        
        /* Colors */
        .stat-card-grid .stat-card-item .card-body .stat-value.text-primary { color: #0d6efd; }
        .stat-card-grid .stat-card-item .card-body .stat-value.text-success { color: #198754; }
        .stat-card-grid .stat-card-item .card-body .stat-value.text-warning { color: #ffc107; }
        .stat-card-grid .stat-card-item .card-body .stat-value.text-danger { color: #dc3545; }
        .stat-card-grid .stat-card-item .card-body .stat-value.text-info { color: #0dcaf0; }
        .stat-card-grid .stat-card-item .card-body .stat-value.text-secondary { color: #6c757d; }
        .stat-card-grid .stat-card-item .card-body .stat-value.text-dark { color: #212529; }
        
        /* =====================================================
        GLOBAL: Make ALL Statistics Cards look consistent
        This CSS applies to ALL pages without changes
        ===================================================== */
        
        /* Make all row-based stat cards consistent */
        #statsCards,
        .stat-card-grid,
        .row.mb-4,
        .row.g-2.mb-4,
        .row.g-3.mb-4,
        .row.g-4.mb-4 {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        #statsCards > [class*="col-"],
        .stat-card-grid > [class*="col-"],
        .stat-card-grid > .stat-card-item,
        .row.mb-4 > [class*="col-"],
        .row.g-2.mb-4 > [class*="col-"],
        .row.g-3.mb-4 > [class*="col-"],
        .row.g-4.mb-4 > [class*="col-"] {
            flex: 1 1 calc(20% - 0.5rem);
            min-width: 140px;
            max-width: 100%;
            margin-bottom: 0 !important;
        }
        
        /* Make all stat cards consistent styling */
        #statsCards .card,
        .stat-card-grid .card,
        .stat-card-grid .stat-card-item .card,
        .row.mb-4 .card,
        .row.g-2.mb-4 .card,
        .row.g-3.mb-4 .card,
        .row.g-4.mb-4 .card {
            height: 100%;
            border: 0;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
        }
        
        #statsCards .card:hover,
        .stat-card-grid .card:hover,
        .stat-card-grid .stat-card-item .card:hover,
        .row.mb-4 .card:hover,
        .row.g-2.mb-4 .card:hover,
        .row.g-3.mb-4 .card:hover,
        .row.g-4.mb-4 .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        /* Make all card-bodies consistent */
        #statsCards .card-body,
        .stat-card-grid .card-body,
        .stat-card-grid .stat-card-item .card-body,
        .row.mb-4 .card-body,
        .row.g-2.mb-4 .card-body,
        .row.g-3.mb-4 .card-body,
        .row.g-4.mb-4 .card-body {
            padding: 1rem;
            text-align: center;
        }
        
        /* Make all labels consistent */
        #statsCards .card-body .text-muted,
        .stat-card-grid .card-body .text-muted,
        .row.mb-4 .card-body .text-muted,
        .row.g-2.mb-4 .card-body .text-muted,
        .row.g-3.mb-4 .card-body .text-muted,
        .row.g-4.mb-4 .card-body .text-muted,
        #statsCards .card-body .small,
        .stat-card-grid .card-body .small,
        .row.mb-4 .card-body .small,
        .row.g-2.mb-4 .card-body .small,
        .row.g-3.mb-4 .card-body .small,
        .row.g-4.mb-4 .card-body .small {
            font-size: 0.75rem !important;
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d !important;
            margin-bottom: 0.25rem;
        }
        
        /* Make all values consistent */
        #statsCards .card-body .h4,
        .stat-card-grid .card-body .h4,
        .row.mb-4 .card-body .h4,
        .row.g-2.mb-4 .card-body .h4,
        .row.g-3.mb-4 .card-body .h4,
        .row.g-4.mb-4 .card-body .h4 {
            font-size: 1.5rem !important;
            font-weight: 700;
            line-height: 1.2;
            margin: 0;
        }
        
        /* Responsive: 2 columns on mobile */
        @media (max-width: 576px) {
            .stat-card-grid > .stat-card-item,
            #statsCards > [class*="col-"],
            .stat-card-grid > [class*="col-"],
            .row.mb-4 > [class*="col-"] {
                flex: 1 1 calc(50% - 0.5rem);
            }
        }
        
        /* Cards */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: #fff;
            border: 1px solid #f0f0f0;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
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

        .stat-card-row-6 {
            grid-template-columns: repeat(6, 1fr);
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box;
        }

        .stat-card-row-7 {
            grid-template-columns: repeat(7, 1fr);
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box;
        }
        
        @media (max-width: 992px) {
            .stat-card-row {
                grid-template-columns: repeat(2, 1fr);
            }
            .stat-card-row-6 {
                grid-template-columns: repeat(3, 1fr);
            }
            .stat-card-row-7 {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stat-card-row-6 {
                grid-template-columns: repeat(2, 1fr);
            }
            .stat-card-row-7 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .stat-card-row {
                grid-template-columns: 1fr;
            }
            .stat-card-row-6 {
                grid-template-columns: 1fr;
            }
            .stat-card-row-7 {
                grid-template-columns: 1fr;
            }
        }
        
        .stat-card-row .stat-card {
            min-height: 80px;
            align-items: stretch;
            box-sizing: border-box;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .stat-card-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 22px;
        }
        
        /* Force Bootstrap Icons to display */
        .stat-card-icon i {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: auto !important;
            height: auto !important;
            position: relative !important;
            font-family: 'bootstrap-icons' !important;
        }
        
        .stat-card-icon i:before {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: auto !important;
            height: auto !important;
        }
        
        /* Explicit colors for stat card variants */
        .stat-card-primary .stat-card-icon { background: #e8f4fd; }
        .stat-card-primary .stat-card-icon i:before { color: #0d6efd !important; }
        .stat-card-primary .stat-card-icon i { color: #0d6efd !important; }
        
        .stat-card-success .stat-card-icon { background: #d1e7dd; }
        .stat-card-success .stat-card-icon i:before { color: #198754 !important; }
        .stat-card-success .stat-card-icon i { color: #198754 !important; }
        
        .stat-card-info .stat-card-icon { background: #cff4fc; }
        .stat-card-info .stat-card-icon i:before { color: #0dcaf0 !important; }
        .stat-card-info .stat-card-icon i { color: #0dcaf0 !important; }
        
        .stat-card-warning .stat-card-icon { background: #fff3cd; }
        .stat-card-warning .stat-card-icon i:before { color: #ffc107 !important; }
        .stat-card-warning .stat-card-icon i { color: #ffc107 !important; }
        
        .stat-card-danger .stat-card-icon { background: #f8d7da; }
        .stat-card-danger .stat-card-icon i:before { color: #dc3545 !important; }
        .stat-card-danger .stat-card-icon i { color: #dc3545 !important; }
        
        .stat-card-secondary .stat-card-icon { background: #e2e3e5; }
        .stat-card-secondary .stat-card-icon i:before { color: #6c757d !important; }
        .stat-card-secondary .stat-card-icon i { color: #6c757d !important; }
        
        /* Stat Card Row - 3 Columns (for payment breakdown, etc.) */
        .stat-card-row-3 {
            grid-template-columns: repeat(3, 1fr);
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box;
        }
        
        @media (max-width: 992px) {
            .stat-card-row-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .stat-card-row-3 {
                grid-template-columns: 1fr;
            }
        }
        
        /* Stat Card Content Area */
        .stat-card-content {
            display: flex;
            flex-direction: column;
            text-align: center;
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
            line-height: 1.2;
        }
        .stat-card-warning .stat-card-value { color: #ffc107; }
        
        /* Stat Card Progress Bar */
        .stat-card-progress {
            width: 100%;
            margin-top: 8px;
        }
        
        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar.collapsed {
                width: var(--sidebar-width) !important;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.expanded {
                margin-left: 0;
            }
            
            .sidebar-toggle-btn {
                display: none;
            }
        }
        
        /* Floating Save Button */
        .floating-save-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
            display: flex;
            gap: 10px;
            align-items: center;
            pointer-events: none;
        }
        
        .floating-save-container * {
            pointer-events: auto;
        }
        
        .floating-save-btn {
            padding: 12px 24px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .floating-save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }
        
        .floating-save-btn.btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .floating-save-btn.btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
        }
        
        .floating-reset-btn {
            padding: 12px 20px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            background-color: #6c757d;
            border: none;
            color: white;
        }
        
        .floating-reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            background-color: #5c636a;
            color: white;
        }
        
        /* Add padding at bottom of content to prevent overlap with floating button */
        .content-area.has-floating-save {
            padding-bottom: 100px;
        }
        
        /* Global: Fix delete button border-radius in btn-group with form wrapper */
        .btn-group form.d-flex .btn,
        .btn-group form .btn {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }
        
        /* Admin Toast Notification */
        .admin-toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .admin-toast {
            min-width: 300px;
            max-width: 400px;
            padding: 16px 20px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateX(120%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .admin-toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .admin-toast.hide {
            transform: translateX(120%);
            opacity: 0;
        }
        
        .admin-toast.success {
            border-left: 4px solid #10b981;
        }
        
        .admin-toast.error {
            border-left: 4px solid #ef4444;
        }
        
        .admin-toast.warning {
            border-left: 4px solid #f59e0b;
        }
        
        .admin-toast-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .admin-toast.success .admin-toast-icon {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .admin-toast.error .admin-toast-icon {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .admin-toast.warning .admin-toast-icon {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        
        .admin-toast-content {
            flex: 1;
        }
        
        .admin-toast-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 2px;
        }
        
        .admin-toast-message {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .admin-toast-close {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            transition: color 0.2s;
        }
        
        .admin-toast-close:hover {
            color: #4b5563;
        }
        
        /* Notification Dropdown Styles */
        .notification-dropdown {
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .notification-dropdown .notification-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 12px 16px;
            white-space: normal;
            transition: background-color 0.2s;
        }
        
        .notification-dropdown .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-dropdown .notification-item.unread {
            background-color: #e7f1ff;
        }
        
        .notification-dropdown .notification-item.unread:hover {
            background-color: #dbe7f5;
        }
        
        .notification-list::-webkit-scrollbar {
            width: 6px;
        }
        
        .notification-list::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .notification-list::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .notification-list::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        .notification-item-wrapper {
            display: flex;
            align-items: flex-start;
            position: relative;
            border-bottom: 1px solid #f0f0f0;
            transition: opacity 0.3s;
        }
        
        .notification-item-wrapper .notification-item {
            flex: 1;
            padding: 12px 16px;
            white-space: normal;
            transition: background-color 0.2s;
        }
        
        .notification-item-wrapper .notification-delete-btn {
            opacity: 0;
            padding: 12px 8px;
            color: #dc3545;
            font-size: 0.75rem;
            transition: opacity 0.2s;
        }
        
        .notification-item-wrapper:hover .notification-delete-btn {
            opacity: 1;
        }
        
        .notification-error {
            background-color: #fff3cd;
            border-radius: 4px;
            margin: 8px;
        }
        
        /* Hide original save buttons when floating is active */
        .original-save-container {
            display: none;
        }
        
        @media (max-width: 575.98px) {
            .floating-save-container {
                bottom: 15px;
                right: 15px;
                left: 15px;
                justify-content: center;
            }
            
            .floating-save-btn,
            .floating-reset-btn {
                padding: 10px 18px;
                font-size: 0.9rem;
            }
        }
        
        /* Pagination icon sizing - fix large icons */
        .pagination .page-link i,
        .pagination .page-link i::before,
        .pagination .page-link .bi,
        .pagination .page-link .bi::before {
            font-size: 14px !important;
            line-height: 1 !important;
        }
        
        .page-item .page-link i,
        .page-item .page-link i::before,
        .page-item .page-link .bi,
        .page-item .page-link .bi::before {
            font-size: 14px !important;
            line-height: 1 !important;
        }
        
        .dataTables_paginate .paginate_button {
            min-width: auto !important;
            padding: 0.25rem 0.5rem !important;
        }
        
        .dataTables_paginate .paginate_button i,
        .dataTables_paginate .paginate_button i::before {
            font-size: 14px !important;
        }


    </style>
    
    @include('partials.global-styles')
    @stack('styles')
</head>
<body class="admin-panel">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <button class="sidebar-toggle-btn" id="sidebarCollapseBtn" title="Toggle Sidebar">
            <i class="bi bi-chevron-left"></i>
        </button>
        <div class="sidebar-header">
            <h4><i class="bi bi-shop me-2"></i><span>{{ config('app.name', 'E-Commerce') }}</span></h4>
        </div>
        
        <nav class="sidebar-menu">
            <!-- Dashboard -->
            @if(Auth::user()->hasPermission('dashboard') && \App\Helpers\PermissionHelper::isModuleVisible('dashboard'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <div>
                        <i class="bi bi-speedometer2 menu-icon"></i>
                        <span class="menu-category-title">Dashboard</span>
                    </div>
                </a>
            </div>
            @endif
            
            <!-- ANALYTICS -->
            @if(Auth::user()->hasPermission('analytics') && \App\Helpers\PermissionHelper::isModuleVisible('analytics'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.analytics') ? 'active' : '' }}" href="{{ route('admin.analytics') }}">
                    <div>
                        <i class="bi bi-graph-up-arrow menu-icon"></i>
                        <span class="menu-category-title">Analytics</span>
                    </div>
                    <span class="badge bg-success menu-badge">New</span>
                </a>
            </div>
            @endif
            
            <!-- PRODUCTS -->
            @if(Auth::user()->hasPermission('products') && \App\Helpers\PermissionHelper::isModuleVisible('products'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.reviews.*') || request()->routeIs('admin.brands.*') || request()->routeIs('admin.attributes.*') || request()->routeIs('admin.colors.*') || request()->routeIs('admin.digital-categories.*') || request()->routeIs('admin.product-qa.*') || request()->routeIs('admin.wishlists.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuProducts" role="button" aria-expanded="{{ request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.reviews.*') || request()->routeIs('admin.brands.*') || request()->routeIs('admin.attributes.*') || request()->routeIs('admin.colors.*') || request()->routeIs('admin.digital-categories.*') || request()->routeIs('admin.product-qa.*') || request()->routeIs('admin.wishlists.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-box menu-icon"></i>
                        <span class="menu-category-title">Products</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.reviews.*') || request()->routeIs('admin.brands.*') || request()->routeIs('admin.attributes.*') || request()->routeIs('admin.colors.*') || request()->routeIs('admin.digital-categories.*') || request()->routeIs('admin.product-qa.*') || request()->routeIs('admin.wishlists.*') ? 'show' : '' }}" id="menuProducts">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('products'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.products.create'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}" href="{{ route('admin.products.create') }}">
                                <i class="bi bi-plus-circle"></i> Add New Product
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.products.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.products.index') && !request()->routeIs('admin.products.create') && !request()->routeIs('admin.products.digital.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                                <i class="bi bi-list-ul"></i> All Products
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.categories.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                                <i class="bi bi-folder"></i> Category
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.products.in-house'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.products.in-house') ? 'active' : '' }}" href="{{ route('admin.products.in-house') }}">
                                <i class="bi bi-house-door"></i> In-House Products
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.products.digital.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.products.digital.*') ? 'active' : '' }}" href="{{ route('admin.products.digital.index') }}">
                                <i class="bi bi-file-earmark-binary"></i> Digital Products
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.digital-categories.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.digital-categories.*') ? 'active' : '' }}" href="{{ route('admin.digital-categories.index') }}">
                                <i class="bi bi-folder2-open"></i> Digital Categories
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.products.bulk-import'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.products.bulk-import*') ? 'active' : '' }}" href="{{ route('admin.products.bulk-import') }}">
                                <i class="bi bi-upload"></i> Bulk Import
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.products.bulk-export'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.products.bulk-export') ? 'active' : '' }}" href="{{ route('admin.products.bulk-export') }}">
                                <i class="bi bi-download"></i> Bulk Export
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.products.bulk-discount'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.products.bulk-discount*') ? 'active' : '' }}" href="{{ route('admin.products.bulk-discount') }}">
                                <i class="bi bi-percent"></i> Bulk Discount
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.brands.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}" href="{{ route('admin.brands.index') }}">
                                <i class="bi bi-award"></i> Brand
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.attributes.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.attributes.*') ? 'active' : '' }}" href="{{ route('admin.attributes.index') }}">
                                <i class="bi bi-sliders"></i> Attribute
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.colors.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.colors.*') ? 'active' : '' }}" href="{{ route('admin.colors.index') }}">
                                <i class="bi bi-palette"></i> Colors
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.reviews.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}">
                                <i class="bi bi-star"></i> Product Reviews
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.product-qa.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.product-qa.*') ? 'active' : '' }}" href="{{ route('admin.product-qa.index') }}">
                                <i class="bi bi-question-circle"></i> Product Q&A
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.wishlists.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.wishlists.*') ? 'active' : '' }}" href="{{ route('admin.wishlists.index') }}">
                                <i class="bi bi-heart"></i> Wishlist Management
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif

            <!-- INVENTORY -->
            @if(Auth::user()->hasPermission('inventory') && Auth::user()->hasPermission('inventory.view') && \App\Helpers\PermissionHelper::isModuleVisible('inventory'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuInventory" role="button" aria-expanded="{{ request()->routeIs('admin.inventory.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-boxes menu-icon"></i>
                        <span class="menu-category-title">Inventory</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.inventory.*') ? 'show' : '' }}" id="menuInventory">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('inventory'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.inventory.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.inventory.index') ? 'active' : '' }}" href="{{ route('admin.inventory.index') }}">
                                <i class="bi bi-boxes"></i> Inventory Overview
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.inventory.stock-alerts'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.inventory.stock-alerts') ? 'active' : '' }}" href="{{ route('admin.inventory.stock-alerts') }}">
                                <i class="bi bi-exclamation-triangle"></i> Stock Alerts
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.inventory.stock-history'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.inventory.stock-history') ? 'active' : '' }}" href="{{ route('admin.inventory.stock-history') }}">
                                <i class="bi bi-clock-history"></i> Stock History
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif

            <!-- SALES -->
            @if(Auth::user()->hasPermission('orders') && Auth::user()->hasPermission('orders.view') && \App\Helpers\PermissionHelper::isModuleVisible('orders'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.orders.*') || request()->routeIs('admin.quotations.*') || request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuSales" role="button" aria-expanded="{{ request()->routeIs('admin.orders.*') || request()->routeIs('admin.quotations.*') || request()->routeIs('admin.subscriptions.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-cart-check menu-icon"></i>
                        <span class="menu-category-title">Sales</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.orders.*') || request()->routeIs('admin.quotations.*') || request()->routeIs('admin.subscriptions.*') ? 'show' : '' }}" id="menuSales">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('orders'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.orders.in-house'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.orders.in-house*') ? 'active' : '' }}" href="{{ route('admin.orders.in-house') }}">
                                <i class="bi bi-house-door"></i> Inhouse Orders
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.orders.seller'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.orders.seller*') ? 'active' : '' }}" href="{{ route('admin.orders.seller') }}">
                                <i class="bi bi-people"></i> Seller Orders
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.orders.pickup-point'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.orders.pickup-point') ? 'active' : '' }}" href="{{ route('admin.orders.pickup-point') }}">
                                <i class="bi bi-geo-alt"></i> Pick-up Point Order
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.quotations.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.quotations.*') ? 'active' : '' }}" href="{{ route('admin.quotations.index') }}">
                                <i class="bi bi-file-earmark-text"></i> Quotations
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.subscriptions.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}" href="{{ route('admin.subscriptions.index') }}">
                                <i class="bi bi-arrow-repeat"></i> Subscriptions
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- DELIVERY -->
            @if(Auth::user()->hasPermission('delivery') && Auth::user()->hasPermission('delivery.view') && \App\Helpers\PermissionHelper::isModuleVisible('delivery'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.delivery.*') || request()->routeIs('admin.pickup-points*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuDelivery" role="button" aria-expanded="{{ request()->routeIs('admin.delivery.*') || request()->routeIs('admin.pickup-points*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-truck menu-icon"></i>
                        <span class="menu-category-title">Delivery</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.delivery.*') || request()->routeIs('admin.pickup-points*') ? 'show' : '' }}" id="menuDelivery">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('delivery'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.delivery.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.delivery.index') ? 'active' : '' }}" href="{{ route('admin.delivery.index') }}">
                                <i class="bi bi-speedometer2"></i> Delivery Dashboard
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.delivery.partners.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.delivery.partners.index') ? 'active' : '' }}" href="{{ route('admin.delivery.partners.index') }}">
                                <i class="bi bi-building"></i> Delivery Partners
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.delivery.carriers.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.delivery.carriers.*') ? 'active' : '' }}" href="{{ route('admin.delivery.carriers.index') }}">
                                <i class="bi bi-truck"></i> Carriers
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.delivery.tracking'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.delivery.tracking') ? 'active' : '' }}" href="{{ route('admin.delivery.tracking') }}">
                                <i class="bi bi-geo-alt"></i> Shipment Tracking
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.delivery.zones.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.delivery.zones.index') ? 'active' : '' }}" href="{{ route('admin.delivery.zones.index') }}">
                                <i class="bi bi-map"></i> Delivery Zones
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.delivery.courier-integration'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.delivery.courier-integration') ? 'active' : '' }}" href="{{ route('admin.delivery.courier-integration') }}">
                                <i class="bi bi-plug"></i> Courier Integration
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.delivery.delivery-boys.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.delivery.delivery-boys.*') ? 'active' : '' }}" href="{{ route('admin.delivery.delivery-boys.index') }}">
                                <i class="bi bi-person-badge"></i> Delivery Boys
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.pickup-points.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.pickup-points*') ? 'active' : '' }}" href="{{ route('admin.pickup-points.index') }}">
                                <i class="bi bi-shop"></i> Pick-up Points
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.delivery.schedules.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.delivery.schedules*') ? 'active' : '' }}" href="{{ route('admin.delivery.schedules.index') }}">
                                <i class="bi bi-calendar-week"></i> Delivery Schedules
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.delivery.reports'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.delivery.reports*') ? 'active' : '' }}" href="{{ route('admin.delivery.reports') }}">
                                <i class="bi bi-bar-chart"></i> Delivery Reports
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- REFUND -->
            @if(Auth::user()->hasPermission('refund') && Auth::user()->hasPermission('refund.view') && \App\Helpers\PermissionHelper::isModuleVisible('refund'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.refunds.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuRefund" role="button" aria-expanded="{{ request()->routeIs('admin.refunds.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-arrow-return-left menu-icon"></i>
                        <span class="menu-category-title">Refund</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.refunds.*') ? 'show' : '' }}" id="menuRefund">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('refund'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.refunds.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.refunds.index') ? 'active' : '' }}" href="{{ route('admin.refunds.index') }}">
                                <i class="bi bi-list-ul"></i> All Refunds
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.refunds.requests'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.refunds.requests') ? 'active' : '' }}" href="{{ route('admin.refunds.requests') }}">
                                <i class="bi bi-inbox"></i> Refund Requests
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.refunds.approved'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.refunds.approved') ? 'active' : '' }}" href="{{ route('admin.refunds.approved') }}">
                                <i class="bi bi-check-circle"></i> Approved Refunds
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.refunds.rejected'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.refunds.rejected') ? 'active' : '' }}" href="{{ route('admin.refunds.rejected') }}">
                                <i class="bi bi-x-circle"></i> Rejected Refunds
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.refunds.configuration'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.refunds.configuration') ? 'active' : '' }}" href="{{ route('admin.refunds.configuration') }}">
                                <i class="bi bi-gear"></i> Refund Configuration
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- CUSTOMERS -->
            @if(Auth::user()->hasPermission('customers') && Auth::user()->hasPermission('customers.view') && \App\Helpers\PermissionHelper::isModuleVisible('customers'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.customers.groups.*') || request()->routeIs('admin.customers.segmentation.*') || request()->routeIs('admin.customers.loyalty.*') || request()->routeIs('admin.customers.membership.*') || request()->routeIs('admin.customers.wallet.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuCustomers" role="button" aria-expanded="{{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.customers.groups.*') || request()->routeIs('admin.customers.segmentation.*') || request()->routeIs('admin.customers.loyalty.*') || request()->routeIs('admin.customers.membership.*') || request()->routeIs('admin.customers.wallet.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-people menu-icon"></i>
                        <span class="menu-category-title">Customers</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.customers.groups.*') || request()->routeIs('admin.customers.segmentation.*') || request()->routeIs('admin.customers.loyalty.*') || request()->routeIs('admin.customers.membership.*') || request()->routeIs('admin.customers.wallet.*') ? 'show' : '' }}" id="menuCustomers">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('customers'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.customers.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">
                                <i class="bi bi-list-ul"></i> All Customers
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.customers.groups.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.customers.groups*') ? 'active' : '' }}" href="{{ route('admin.customers.groups.index') }}">
                                <i class="bi bi-people-fill"></i> Customer Groups
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.customers.segmentation.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.customers.segmentation*') ? 'active' : '' }}" href="{{ route('admin.customers.segmentation.index') }}">
                                <i class="bi bi-diagram-3"></i> Customer Segmentation
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.customers.loyalty.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.customers.loyalty*') ? 'active' : '' }}" href="{{ route('admin.customers.loyalty.index') }}">
                                <i class="bi bi-star"></i> Loyalty Points
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.customers.membership.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.customers.membership*') ? 'active' : '' }}" href="{{ route('admin.customers.membership.index') }}">
                                <i class="bi bi-card-checklist"></i> Membership Plans
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.customers.wallet.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.customers.wallet*') ? 'active' : '' }}" href="{{ route('admin.customers.wallet.index') }}">
                                <i class="bi bi-wallet2"></i> Customer Wallet
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- SELLERS (B2B) -->
            @if(Auth::user()->hasPermission('sellers') && Auth::user()->hasPermission('sellers.view') && \App\Helpers\PermissionHelper::isModuleVisible('sellers'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuSellers" role="button" aria-expanded="{{ request()->routeIs('admin.sellers.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-shop-window menu-icon"></i>
                        <span class="menu-category-title">Sellers (B2B)</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.sellers.*') ? 'show' : '' }}" id="menuSellers">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('sellers'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.sellers.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.sellers.index') ? 'active' : '' }}" href="{{ route('admin.sellers.index') }}">
                                <i class="bi bi-list-ul"></i> All Sellers
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.sellers.payouts'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.sellers.payouts') ? 'active' : '' }}" href="{{ route('admin.sellers.payouts') }}">
                                <i class="bi bi-cash-stack"></i> Payouts
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.sellers.payout-requests'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.sellers.payout-requests') ? 'active' : '' }}" href="{{ route('admin.sellers.payout-requests') }}">
                                <i class="bi bi-wallet2"></i> Payout Requests
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.sellers.commission'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.sellers.commission') ? 'active' : '' }}" href="{{ route('admin.sellers.commission') }}">
                                <i class="bi bi-percent"></i> Seller Commission
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.sellers.verification'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.sellers.verification') ? 'active' : '' }}" href="{{ route('admin.sellers.verification') }}">
                                <i class="bi bi-patch-check"></i> Seller Verification
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- AFFILIATE -->
            @if(Auth::user()->hasPermission('affiliate') && Auth::user()->hasPermission('affiliate.view') && \App\Helpers\PermissionHelper::isModuleVisible('affiliate'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.affiliate.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuAffiliate" role="button" aria-expanded="{{ request()->routeIs('admin.affiliate.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-link-45deg menu-icon"></i>
                        <span class="menu-category-title">Affiliate</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.affiliate.*') ? 'show' : '' }}" id="menuAffiliate">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('affiliate'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.affiliate.users.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.affiliate.users.index') ? 'active' : '' }}" href="{{ route('admin.affiliate.users.index') }}">
                                <i class="bi bi-people"></i> Affiliate Users
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.affiliate.configuration'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.affiliate.configuration') ? 'active' : '' }}" href="{{ route('admin.affiliate.configuration') }}">
                                <i class="bi bi-gear"></i> Affiliate Configuration
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.affiliate.payouts'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.affiliate.payouts') ? 'active' : '' }}" href="{{ route('admin.affiliate.payouts') }}">
                                <i class="bi bi-cash-stack"></i> Affiliate Payouts
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.affiliate.requests'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.affiliate.requests') ? 'active' : '' }}" href="{{ route('admin.affiliate.requests') }}">
                                <i class="bi bi-inbox"></i> Affiliate Requests
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.affiliate.categories.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.affiliate.categories.index') ? 'active' : '' }}" href="{{ route('admin.affiliate.categories.index') }}">
                                <i class="bi bi-folder"></i> Affiliate Categories
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.affiliate.products.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.affiliate.products.index') ? 'active' : '' }}" href="{{ route('admin.affiliate.products.index') }}">
                                <i class="bi bi-box-seam"></i> Affiliate Products
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.affiliate.links.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.affiliate.links.index') ? 'active' : '' }}" href="{{ route('admin.affiliate.links.index') }}">
                                <i class="bi bi-link"></i> Affiliate Links
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.affiliate.banners.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.affiliate.banners.index') ? 'active' : '' }}" href="{{ route('admin.affiliate.banners.index') }}">
                                <i class="bi bi-card-image"></i> Affiliate Banners
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.affiliate.reports'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.affiliate.reports') ? 'active' : '' }}" href="{{ route('admin.affiliate.reports') }}">
                                <i class="bi bi-graph-up"></i> Affiliate Reports
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- MEDIA -->
            @if(Auth::user()->hasPermission('media') && Auth::user()->hasPermission('media.view') && \App\Helpers\PermissionHelper::isModuleVisible('media'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.media.*') ? 'active' : '' }}" href="{{ route('admin.media.index') }}">
                    <div>
                        <i class="bi bi-images menu-icon"></i>
                        <span class="menu-category-title">Media</span>
                    </div>
                    <span class="badge bg-info menu-badge">Files</span>
                </a>
            </div>
            @endif
            
            <!-- REPORTS -->
            @if(Auth::user()->hasPermission('reports') && Auth::user()->hasPermission('reports.view') && \App\Helpers\PermissionHelper::isModuleVisible('reports'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.reports.*') || request()->routeIs('admin.jakat.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuReports" role="button" aria-expanded="{{ request()->routeIs('admin.reports.*') || request()->routeIs('admin.jakat.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-graph-up menu-icon"></i>
                        <span class="menu-category-title">Reports</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.reports.*') || request()->routeIs('admin.jakat.*') ? 'show' : '' }}" id="menuReports">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('reports'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.reports.in-house-product-sale'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.in-house-product-sale') ? 'active' : '' }}" href="{{ route('admin.reports.in-house-product-sale') }}">
                                <i class="bi bi-house-door"></i> In-House Product Sale
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.reports.seller-sales'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.seller-sales') ? 'active' : '' }}" href="{{ route('admin.reports.seller-sales') }}">
                                <i class="bi bi-shop"></i> Seller Products Sale
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.reports.inventory'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.inventory') ? 'active' : '' }}" href="{{ route('admin.reports.inventory') }}">
                                <i class="bi bi-boxes"></i> Products Stock
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.reports.wishlist'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.wishlist') ? 'active' : '' }}" href="{{ route('admin.reports.wishlist') }}">
                                <i class="bi bi-heart"></i> Products Wishlist
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.reports.user-searches'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.user-searches') ? 'active' : '' }}" href="{{ route('admin.reports.user-searches') }}">
                                <i class="bi bi-search"></i> User Searches
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.reports.commission-history'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.commission-history') ? 'active' : '' }}" href="{{ route('admin.reports.commission-history') }}">
                                <i class="bi bi-currency-dollar"></i> Commission History
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.reports.wallet-history'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.wallet-history') ? 'active' : '' }}" href="{{ route('admin.reports.wallet-history') }}">
                                <i class="bi bi-wallet"></i> Wallet Recharge History
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.jakat.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.jakat.*') ? 'active' : '' }}" href="{{ route('admin.jakat.index') }}">
                                <i class="bi bi-calculator"></i> Jakat Calculator
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- MARKETING -->
            @if(Auth::user()->hasPermission('marketing') && Auth::user()->hasPermission('marketing.view') && \App\Helpers\PermissionHelper::isModuleVisible('marketing'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.marketing.*') || request()->routeIs('admin.coupons.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuMarketing" role="button" aria-expanded="{{ request()->routeIs('admin.marketing.*') || request()->routeIs('admin.coupons.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-megaphone menu-icon"></i>
                        <span class="menu-category-title">Marketing</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.marketing.*') || request()->routeIs('admin.coupons.*') ? 'show' : '' }}" id="menuMarketing">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('marketing'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.marketing.flash-deals.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.marketing.flash-deals.*') ? 'active' : '' }}" href="{{ route('admin.marketing.flash-deals.index') }}">
                                <i class="bi bi-lightning"></i> Flash Deals
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.marketing.newsletters.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.marketing.newsletters*') ? 'active' : '' }}" href="{{ route('admin.marketing.newsletters.index') }}">
                                <i class="bi bi-envelope"></i> Newsletters
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.marketing.bulk-sms.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.marketing.bulk-sms*') ? 'active' : '' }}" href="{{ route('admin.marketing.bulk-sms.index') }}">
                                <i class="bi bi-phone"></i> Bulk SMS
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.marketing.subscribers.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.marketing.subscribers.*') ? 'active' : '' }}" href="{{ route('admin.marketing.subscribers.index') }}">
                                <i class="bi bi-person-plus"></i> Subscribers
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.coupons.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}">
                                <i class="bi bi-tag"></i> Coupon
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.marketing.abandoned-cart.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.marketing.abandoned-cart*') ? 'active' : '' }}" href="{{ route('admin.marketing.abandoned-cart.index') }}">
                                <i class="bi bi-cart-x"></i> Abandoned Cart Recovery
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.marketing.gift-cards.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.marketing.gift-cards*') ? 'active' : '' }}" href="{{ route('admin.marketing.gift-cards.index') }}">
                                <i class="bi bi-gift"></i> Gift Cards
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.marketing.push-notifications.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.marketing.push-notifications*') ? 'active' : '' }}" href="{{ route('admin.marketing.push-notifications.index') }}">
                                <i class="bi bi-bell"></i> Push Notifications
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.marketing.price-rules.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.marketing.price-rules*') ? 'active' : '' }}" href="{{ route('admin.marketing.price-rules.index') }}">
                                <i class="bi bi-percent"></i> Price Rules
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- SUPPORT -->
            @if(Auth::user()->hasPermission('support') && Auth::user()->hasPermission('support.view') && \App\Helpers\PermissionHelper::isModuleVisible('support'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.support.*') || request()->routeIs('admin.chat.*') || request()->routeIs('admin.settings.whatsapp') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuSupport" role="button" aria-expanded="{{ request()->routeIs('admin.support.*') || request()->routeIs('admin.chat.*') || request()->routeIs('admin.settings.whatsapp') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-headset menu-icon"></i>
                        <span class="menu-category-title">Support</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.support.*') || request()->routeIs('admin.chat.*') || request()->routeIs('admin.settings.whatsapp') ? 'show' : '' }}" id="menuSupport">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('support'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.support.tickets.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.support.tickets*') ? 'active' : '' }}" href="{{ route('admin.support.tickets.index') }}">
                                <i class="bi bi-ticket-detailed"></i> Ticket
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.chat.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.chat.index') ? 'active' : '' }}" href="{{ route('admin.chat.index') }}">
                                <i class="bi bi-chat-dots"></i> Live Chat
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.chat.ai-settings.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.chat.ai-settings*') ? 'active' : '' }}" href="{{ route('admin.chat.ai-settings.index') }}">
                                <i class="bi bi-robot"></i> AI Chatbot Settings
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.chat.widget-settings.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.chat.widget-settings*') ? 'active' : '' }}" href="{{ route('admin.chat.widget-settings.index') }}">
                                <i class="bi bi-chat-dots-fill"></i> Chat Widget Settings
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.chat.predefined.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.chat.predefined.*') ? 'active' : '' }}" href="{{ route('admin.chat.predefined.index') }}">
                                <i class="bi bi-chat-text"></i> Quick Replies
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.whatsapp'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.whatsapp') ? 'active' : '' }}" href="{{ route('admin.settings.whatsapp') }}">
                                <i class="bi bi-whatsapp"></i> WhatsApp Chat
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.support.product-queries.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.support.product-queries*') ? 'active' : '' }}" href="{{ route('admin.support.product-queries.index') }}">
                                <i class="bi bi-question-circle"></i> Product Queries
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- OTP SYSTEM -->
            @if(Auth::user()->hasPermission('otp') && Auth::user()->hasPermission('otp.view') && \App\Helpers\PermissionHelper::isModuleVisible('otp'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.otp.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuOtp" role="button" aria-expanded="{{ request()->routeIs('admin.otp.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-shield-lock menu-icon"></i>
                        <span class="menu-category-title">OTP System</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.otp.*') ? 'show' : '' }}" id="menuOtp">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('otp'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.otp.configuration'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.otp.configuration') ? 'active' : '' }}" href="{{ route('admin.otp.configuration') }}">
                                <i class="bi bi-gear"></i> OTP Configurations
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.otp.sms-templates'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.otp.sms-templates') ? 'active' : '' }}" href="{{ route('admin.otp.sms-templates') }}">
                                <i class="bi bi-file-text"></i> SMS Templates
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.otp.credentials'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.otp.credentials') ? 'active' : '' }}" href="{{ route('admin.otp.credentials') }}">
                                <i class="bi bi-key"></i> Set OTP Credentials
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- CONTENT -->
            @if(Auth::user()->hasPermission('content') && Auth::user()->hasPermission('content.view') && \App\Helpers\PermissionHelper::isModuleVisible('content'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.pages.*') || request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-settings.*') || request()->routeIs('admin.blog-categories.*') || request()->routeIs('admin.blog-tags.*') || request()->routeIs('admin.form-builder.*') || request()->routeIs('admin.faqs.*') || request()->routeIs('admin.content.widgets.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuContent" role="button" aria-expanded="{{ request()->routeIs('admin.pages.*') || request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-settings.*') || request()->routeIs('admin.blog-categories.*') || request()->routeIs('admin.blog-tags.*') || request()->routeIs('admin.form-builder.*') || request()->routeIs('admin.faqs.*') || request()->routeIs('admin.content.widgets.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-file-earmark-text menu-icon"></i>
                        <span class="menu-category-title">Content</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.pages.*') || request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-settings.*') || request()->routeIs('admin.blog-categories.*') || request()->routeIs('admin.blog-tags.*') || request()->routeIs('admin.form-builder.*') || request()->routeIs('admin.faqs.*') || request()->routeIs('admin.content.widgets.*') ? 'show' : '' }}" id="menuContent">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('content'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.pages.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}">
                                <i class="bi bi-file-text"></i> Pages
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.blogs.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-settings.*') ? 'active' : '' }}" href="{{ route('admin.blogs.index') }}">
                                <i class="bi bi-newspaper"></i> Blog Posts
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.blog-categories.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.blog-categories.*') ? 'active' : '' }}" href="{{ route('admin.blog-categories.index') }}">
                                <i class="bi bi-folder2-open"></i> Blog Categories
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.blog-tags.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.blog-tags.*') ? 'active' : '' }}" href="{{ route('admin.blog-tags.index') }}">
                                <i class="bi bi-tags"></i> Blog Tags
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.form-builder.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.form-builder.*') ? 'active' : '' }}" href="{{ route('admin.form-builder.index') }}">
                                <i class="bi bi-ui-checks"></i> Form Builder
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.faqs.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}" href="{{ route('admin.faqs.index') }}">
                                <i class="bi bi-question-diamond"></i> FAQs
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.content.widgets.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.content.widgets.*') ? 'active' : '' }}" href="{{ route('admin.content.widgets.index') }}">
                                <i class="bi bi-grid-3x3-gap"></i> Widget Manager
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- APPEARANCE -->
            @if(Auth::user()->hasPermission('appearance') && Auth::user()->hasPermission('appearance.view') && \App\Helpers\PermissionHelper::isModuleVisible('appearance'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.themes.*') || request()->routeIs('admin.hero.*') || request()->routeIs('admin.homepage.*') || request()->routeIs('admin.sliders.*') || request()->routeIs('admin.banners.*') || request()->routeIs('admin.menus.*') || request()->routeIs('admin.widgets.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuAppearance" role="button" aria-expanded="{{ request()->routeIs('admin.themes.*') || request()->routeIs('admin.hero.*') || request()->routeIs('admin.homepage.*') || request()->routeIs('admin.sliders.*') || request()->routeIs('admin.banners.*') || request()->routeIs('admin.menus.*') || request()->routeIs('admin.widgets.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-palette2 menu-icon"></i>
                        <span class="menu-category-title">Appearance</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.themes.*') || request()->routeIs('admin.hero.*') || request()->routeIs('admin.homepage.*') || request()->routeIs('admin.sliders.*') || request()->routeIs('admin.banners.*') || request()->routeIs('admin.menus.*') || request()->routeIs('admin.widgets.*') ? 'show' : '' }}" id="menuAppearance">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('appearance'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.themes.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.themes.*') ? 'active' : '' }}" href="{{ route('admin.themes.index') }}">
                                <i class="bi bi-palette"></i> Themes
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.menus.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}" href="{{ route('admin.menus.index') }}">
                                <i class="bi bi-list-nested"></i> Menu Builder
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.sliders.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}" href="{{ route('admin.sliders.index') }}">
                                <i class="bi bi-images"></i> Sliders
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.banners.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" href="{{ route('admin.banners.index') }}">
                                <i class="bi bi-card-image"></i> Banners
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.hero.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.hero.*') ? 'active' : '' }}" href="{{ route('admin.hero.index') }}">
                                <i class="bi bi-image"></i> Hero Section
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.homepage.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.homepage.*') ? 'active' : '' }}" href="{{ route('admin.homepage.index') }}">
                                <i class="bi bi-house-door"></i> Home Page Settings
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- SETTINGS -->
            @if(Auth::user()->hasPermission('settings') && Auth::user()->hasPermission('settings.view') && \App\Helpers\PermissionHelper::isModuleVisible('settings'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.payment.*') || request()->routeIs('admin.payment-gateways.*') || request()->routeIs('admin.seo.*') || request()->routeIs('admin.api-keys*') || request()->routeIs('admin.backup*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuSettings" role="button" aria-expanded="{{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.payment.*') || request()->routeIs('admin.payment-gateways.*') || request()->routeIs('admin.seo.*') || request()->routeIs('admin.api-keys*') || request()->routeIs('admin.backup*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-gear-fill menu-icon"></i>
                        <span class="menu-category-title">Settings</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.payment.*') || request()->routeIs('admin.payment-gateways.*') || request()->routeIs('admin.seo.*') || request()->routeIs('admin.api-keys*') || request()->routeIs('admin.backup*') ? 'show' : '' }}" id="menuSettings">
                    <ul class="nav flex-column submenu">
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.general'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.general') || request()->routeIs('admin.settings.index') ? 'active' : '' }}" href="{{ route('admin.settings.general') }}">
                                <i class="bi bi-sliders"></i> General Settings
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.languages'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.languages*') ? 'active' : '' }}" href="{{ route('admin.settings.languages') }}">
                                <i class="bi bi-translate"></i> Languages
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.currency'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.currency') ? 'active' : '' }}" href="{{ route('admin.settings.currency') }}">
                                <i class="bi bi-currency-exchange"></i> Currency
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.vat-tax'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.vat-tax') ? 'active' : '' }}" href="{{ route('admin.settings.vat-tax') }}">
                                <i class="bi bi-receipt"></i> VAT & Tax
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.email'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.email') ? 'active' : '' }}" href="{{ route('admin.settings.email') }}">
                                <i class="bi bi-envelope"></i> SMTP Settings
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.payment-gateways.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.payment.*') || request()->routeIs('admin.payment-gateways.*') ? 'active' : '' }}" href="{{ route('admin.payment-gateways.index') }}">
                                <i class="bi bi-credit-card"></i> Payment Methods
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.order-configuration'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.order-configuration') ? 'active' : '' }}" href="{{ route('admin.settings.order-configuration') }}">
                                <i class="bi bi-bag-check"></i> Order Configuration
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.file-system'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.file-system') ? 'active' : '' }}" href="{{ route('admin.settings.file-system') }}">
                                <i class="bi bi-hdd"></i> File System & Cache
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.social-login'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.social-login') ? 'active' : '' }}" href="{{ route('admin.settings.social-login') }}">
                                <i class="bi bi-google"></i> Social Media Logins
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.shipping'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.shipping') ? 'active' : '' }}" href="{{ route('admin.settings.shipping') }}">
                                <i class="bi bi-truck"></i> Shipping
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.seo.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.seo.*') ? 'active' : '' }}" href="{{ route('admin.seo.index') }}">
                                <i class="bi bi-search"></i> SEO Settings
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.footer'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.footer') ? 'active' : '' }}" href="{{ route('admin.settings.footer') }}">
                                <i class="bi bi-layout-text-window-reverse"></i> Footer Settings
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.email-templates.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.email-templates*') ? 'active' : '' }}" href="{{ route('admin.settings.email-templates.index') }}">
                                <i class="bi bi-envelope-paper"></i> Email Templates
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.settings.notifications.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.notifications*') ? 'active' : '' }}" href="{{ route('admin.settings.notifications.index') }}">
                                <i class="bi bi-bell"></i> Notification Settings
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.api-keys.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.api-keys*') ? 'active' : '' }}" href="{{ route('admin.api-keys.index') }}">
                                <i class="bi bi-key"></i> API Keys & Integrations
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.backup'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.backup*') ? 'active' : '' }}" href="{{ route('admin.backup') }}">
                                <i class="bi bi-cloud-arrow-up"></i> Backup & Restore
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- LOCATIONS -->
            @if(Auth::user()->hasPermission('locations') && Auth::user()->hasPermission('locations.view') && \App\Helpers\PermissionHelper::isModuleVisible('locations'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuLocations" role="button" aria-expanded="{{ request()->routeIs('admin.locations.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-geo-alt menu-icon"></i>
                        <span class="menu-category-title">Locations</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.locations.*') ? 'show' : '' }}" id="menuLocations">
                    <ul class="nav flex-column submenu">
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.locations.states.index'))
                        @if(Auth::user()->hasPermission('locations.states'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.locations.states*') ? 'active' : '' }}" href="{{ route('admin.locations.states.index') }}">
                                <i class="bi bi-map"></i> States
                            </a>
                        </li>
                        @endif
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.locations.cities.index'))
                        @if(Auth::user()->hasPermission('locations.cities'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.locations.cities*') ? 'active' : '' }}" href="{{ route('admin.locations.cities.index') }}">
                                <i class="bi bi-building"></i> Cities
                            </a>
                        </li>
                        @endif
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.locations.areas.index'))
                        @if(Auth::user()->hasPermission('locations.areas'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.locations.areas*') ? 'active' : '' }}" href="{{ route('admin.locations.areas.index') }}">
                                <i class="bi bi-geo"></i> Areas
                            </a>
                        </li>
                        @endif
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.locations.country-settings'))
                        @if(Auth::user()->hasPermission('locations.settings'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.locations.country-settings*') ? 'active' : '' }}" href="{{ route('admin.locations.country-settings') }}">
                                <i class="bi bi-globe"></i> Country Settings
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif

            <!-- WAREHOUSE -->
            @if(Auth::user()->hasPermission('warehouse') && Auth::user()->hasPermission('warehouse.view') && \App\Helpers\PermissionHelper::isModuleVisible('warehouse'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuWarehouse" role="button" aria-expanded="{{ request()->routeIs('admin.warehouses.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-building menu-icon"></i>
                        <span class="menu-category-title">Warehouse</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.warehouses.*') ? 'show' : '' }}" id="menuWarehouse">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('warehouse') && Auth::user()->hasPermission('warehouse.view'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.warehouses.picking') && auth()->user()->warehouse_id)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.warehouses.picking') ? 'active' : '' }}" href="{{ route('admin.warehouses.picking', auth()->user()->warehouse_id) }}">
                                <i class="bi bi-box-seam"></i> My Warehouse
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.warehouses.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.warehouses.index') && !request()->routeIs('admin.warehouses.create') ? 'active' : '' }}" href="{{ route('admin.warehouses.index') }}">
                                <i class="bi bi-list-ul"></i> All Warehouses
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.warehouses.create') && Auth::user()->hasPermission('warehouse.create'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.warehouses.create') ? 'active' : '' }}" href="{{ route('admin.warehouses.create') }}">
                                <i class="bi bi-plus-circle"></i> Add Warehouse
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- STAFFS -->
            @if(Auth::user()->hasPermission('staffs') && Auth::user()->hasPermission('staffs.view') && \App\Helpers\PermissionHelper::isModuleVisible('staffs'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.staffs.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('admin.roles.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuStaffs" role="button" aria-expanded="{{ request()->routeIs('admin.staffs.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('admin.roles.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-person-badge menu-icon"></i>
                        <span class="menu-category-title">Staffs</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.staffs.*') || request()->routeIs('admin.permissions.index') || request()->routeIs('admin.permissions.dashboard') || request()->routeIs('admin.roles.*') ? 'show' : '' }}" id="menuStaffs">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('staffs'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.staffs.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.staffs.index') ? 'active' : '' }}" href="{{ route('admin.staffs.index') }}">
                                <i class="bi bi-people"></i> All Staffs
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.staffs.warehouse'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.staffs.warehouse') ? 'active' : '' }}" href="{{ route('admin.staffs.warehouse') }}">
                                <i class="bi bi-building"></i> Warehouse Staffs
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.permissions.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.permissions.index') || request()->routeIs('admin.permissions.dashboard') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}">
                                <i class="bi bi-key"></i> Permission Settings
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- SYSTEM -->
            @if(Auth::user()->hasPermission('system') && Auth::user()->hasPermission('system.view') && \App\Helpers\PermissionHelper::isModuleVisible('system'))
            <div class="menu-category">
                <a class="menu-category-header" data-bs-toggle="collapse" href="#menuSystem" role="button" aria-expanded="{{ request()->routeIs('admin.system.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-cpu menu-icon"></i>
                        <span class="menu-category-title">System</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.system.*') ? 'show' : '' }}" id="menuSystem">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('system') && Auth::user()->hasPermission('system.view'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.system.update'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.system.update') ? 'active' : '' }}" href="{{ route('admin.system.update') }}">
                                <i class="bi bi-arrow-up-circle"></i> Update
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.system.server-status'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.system.server-status') ? 'active' : '' }}" href="{{ route('admin.system.server-status') }}">
                                <i class="bi bi-activity"></i> Server Status
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.system.activity-logs.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.system.activity-logs*') ? 'active' : '' }}" href="{{ route('admin.system.activity-logs.index') }}">
                                <i class="bi bi-journal-text"></i> Activity Logs
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.system.data-export.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.system.data-export*') ? 'active' : '' }}" href="{{ route('admin.system.data-export.index') }}">
                                <i class="bi bi-database-down"></i> Data Export/Import
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- POS -->
            @if(Auth::user()->hasPermission('pos') && Auth::user()->hasPermission('pos.view') && \App\Helpers\PermissionHelper::isModuleVisible('pos'))
            <div class="menu-category">
                <a class="menu-category-header" data-bs-toggle="collapse" href="#menuPos" role="button" aria-expanded="{{ request()->routeIs('admin.pos.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-calculator menu-icon"></i>
                        <span class="menu-category-title">POS</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.pos.*') ? 'show' : '' }}" id="menuPos">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('pos') && Auth::user()->hasPermission('pos.view'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.pos.terminal'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.pos.terminal') ? 'active' : '' }}" href="{{ route('admin.pos.terminal') }}">
                                <i class="bi bi-terminal"></i> POS Terminal
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.pos.cash-register'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.pos.cash-register') ? 'active' : '' }}" href="{{ route('admin.pos.cash-register') }}">
                                <i class="bi bi-cash"></i> Cash Register
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.pos.reports'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.pos.reports') ? 'active' : '' }}" href="{{ route('admin.pos.reports') }}">
                                <i class="bi bi-graph-up"></i> POS Reports
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            
            <!-- ADDON MANAGER -->
            @if(Auth::user()->hasPermission('addon') && Auth::user()->hasPermission('addon.view') && \App\Helpers\PermissionHelper::isModuleVisible('addon'))
            <div class="menu-category">
                <a class="menu-category-header {{ request()->routeIs('admin.addons.*') ? 'active' : '' }}" href="{{ route('admin.addons.index') }}">
                    <div>
                        <i class="bi bi-puzzle menu-icon"></i>
                        <span class="menu-category-title">Addon Manager</span>
                    </div>
                </a>
            </div>
            @endif
            
            <!-- MULTI-STORE -->
            @if(Auth::user()->hasPermission('multistore') && Auth::user()->hasPermission('multistore.view') && \App\Helpers\PermissionHelper::isModuleVisible('multistore'))
            <div class="menu-category">
                <a class="menu-category-header" data-bs-toggle="collapse" href="#menuMultiStore" role="button" aria-expanded="{{ request()->routeIs('admin.multi-store.*') ? 'true' : 'false' }}">
                    <div>
                        <i class="bi bi-shop menu-icon"></i>
                        <span class="menu-category-title">Multi-Store</span>
                    </div>
                    <i class="bi bi-chevron-down arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.multi-store.*') ? 'show' : '' }}" id="menuMultiStore">
                    <ul class="nav flex-column submenu">
                        @if(Auth::user()->hasPermission('multistore') && Auth::user()->hasPermission('multistore.view'))
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.multi-store.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.multi-store.index') ? 'active' : '' }}" href="{{ route('admin.multi-store.index') }}">
                                <i class="bi bi-shop"></i> All Stores
                            </a>
                        </li>
                        @endif
                        @if(\App\Helpers\PermissionHelper::canUserSeeSubmenu('admin.multi-store.create'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.multi-store.create') ? 'active' : '' }}" href="{{ route('admin.multi-store.create') }}">
                                <i class="bi bi-plus-circle"></i> Add Store
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </div>
            </div>
            @endif
        </nav>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <header class="main-header">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <button class="btn btn-link d-lg-none" id="sidebarToggle">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    
                    <div class="ms-auto d-flex align-items-center">
                        <!-- View Store -->
                        <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-primary btn-sm me-3">
                            <i class="bi bi-box-arrow-up-right me-1"></i> View Store
                        </a>
                        
                        <!-- Notifications -->
                        <div class="dropdown me-3" id="notificationDropdown">
                            <button class="btn btn-light position-relative" data-bs-toggle="dropdown" id="notificationBtn">
                                <i class="bi bi-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display: none;">
                                    0
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 360px; max-height: 400px;">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                    <h6 class="mb-0">Notifications</h6>
                                    <div>
                                        <a href="javascript:void(0)" class="btn btn-sm btn-link text-decoration-none p-0" onclick="markAllNotificationsAsRead()" title="Mark all as read">
                                            <small>Mark all read</small>
                                        </a>
                                    </div>
                                </div>
                                <div class="notification-list" id="notificationList" style="max-height: 300px; overflow-y: auto;">
                                    <div class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm" role="status"></div>
                                        <p class="mb-0 mt-2 small text-muted">Loading notifications...</p>
                                    </div>
                                </div>
                                <div class="border-top">
                                    <a href="{{ route('admin.notifications.index') }}" class="dropdown-item text-center text-primary">
                                        View All Notifications
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- User Menu -->
                        @php
                            $userRole = Auth::user()->role;
                            $roleLabel = '';
                            $dashboardRoute = '';
                            $profileRoute = '';
                            $logoutRoute = '';
                            
                            if ($userRole === 'super_admin') {
                                $roleLabel = 'Super Admin';
                                $dashboardRoute = route('super-admin.dashboard');
                                $profileRoute = route('admin.profile');
                                $logoutRoute = route('super-admin.logout');
                            } elseif ($userRole === 'admin') {
                                $roleLabel = 'Admin';
                                $dashboardRoute = route('admin.dashboard');
                                $profileRoute = route('admin.profile');
                                $logoutRoute = route('admin.logout');
                            } else {
                                $roleLabel = 'Staff';
                                $dashboardRoute = route('admin.dashboard'); // Staff uses admin dashboard
                                $profileRoute = route('admin.profile');
                                $logoutRoute = route('staff.logout');
                            }
                        @endphp
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                                <span class="badge bg-{{ $userRole === 'super_admin' ? 'warning' : ($userRole === 'admin' ? 'success' : 'info') }} me-2">
                                    {{ $roleLabel }}
                                </span>
                                <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" 
                                     alt="{{ Auth::user()->name }}" class="rounded-circle me-2" width="32" height="32">
                                {{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($dashboardRoute)
                                <li><a class="dropdown-item" href="{{ $dashboardRoute }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                @endif
                                <li><a class="dropdown-item" href="{{ $profileRoute }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form id="logout-form" action="{{ $logoutRoute }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item" onclick="event.stopPropagation(); document.getElementById('logout-form').submit(); return false;"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
        </header>
        
        <!-- Content -->
        <div class="content-area">
            @yield('content')
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
        
        // Sidebar collapse/expand for desktop
        const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        // Check for saved sidebar state
        const savedSidebarState = localStorage.getItem('sidebarCollapsed');
        if (savedSidebarState === 'true') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
        
        sidebarCollapseBtn?.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
        
        // Save sidebar state (expanded/collapsed menus) before page unload
        window.addEventListener('beforeunload', function() {
            const sidebarMenu = document.querySelector('.sidebar-menu');
            if (sidebarMenu) {
                sessionStorage.setItem('sidebarScrollPosition', sidebarMenu.scrollTop);
                
                // Save expanded menus
                const expandedMenus = [];
                document.querySelectorAll('.collapse.show').forEach(function(collapse) {
                    expandedMenus.push(collapse.id);
                });
                sessionStorage.setItem('expandedMenus', JSON.stringify(expandedMenus));
            }
        });
        
        // Restore sidebar scroll position on page load
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarMenu = document.querySelector('.sidebar-menu');
            const savedPosition = sessionStorage.getItem('sidebarScrollPosition');
            
            // Restore saved scroll position if available
            if (sidebarMenu && savedPosition) {
                sidebarMenu.scrollTop = parseInt(savedPosition);
            } else {
                // Only scroll to active link if no saved position exists
                // This preserves the user's clicked position
                const activeLink = document.querySelector('.submenu .nav-link.active, .menu-category-header.active');
                if (activeLink) {
                    // Small delay to ensure DOM is ready
                    setTimeout(function() {
                        activeLink.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 100);
                }
            }
        });
        
        // Initialize DataTables only on tables with data-table class
        $(document).ready(function() {
            $('.data-table').each(function() {
                // Check if table has proper structure (no colspan in tbody)
                var hasColspan = $(this).find('tbody td[colspan]').length > 0;
                if (!hasColspan && !$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        pageLength: 25,
                        responsive: true,
                        language: {
                            emptyTable: 'No data available'
                        }
                    });
                }
            });
        });
        
        // Admin Toast Notification System
        window.adminToast = function(type, title, message, duration = 4000) {
            let container = document.querySelector('.admin-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'admin-toast-container';
                document.body.appendChild(container);
            }
            
            const toast = document.createElement('div');
            toast.className = `admin-toast ${type}`;
            
            const icons = {
                success: 'bi-check-circle-fill',
                error: 'bi-x-circle-fill',
                warning: 'bi-exclamation-triangle-fill'
            };
            
            toast.innerHTML = `
                <div class="admin-toast-icon">
                    <i class="bi ${icons[type]} fs-5"></i>
                </div>
                <div class="admin-toast-content">
                    <div class="admin-toast-title">${title}</div>
                    <div class="admin-toast-message">${message}</div>
                </div>
                <button class="admin-toast-close" onclick="this.parentElement.remove()">
                    <i class="bi bi-x-lg"></i>
                </button>
            `;
            
            container.appendChild(toast);
            
            // Trigger animation
            setTimeout(() => toast.classList.add('show'), 10);
            
            // Auto remove
            setTimeout(() => {
                toast.classList.remove('show');
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 400);
            }, duration);
        };
        
        // Show session messages as toasts
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                adminToast('success', 'Success!', '{{ session('success') }}');
            @endif
            @if(session('error'))
                adminToast('error', 'Error!', '{{ session('error') }}');
            @endif
            @if(session('warning'))
                adminToast('warning', 'Warning!', '{{ session('warning') }}');
            @endif
            
            // Auto-add has-floating-save class to content-area when floating-save-container exists
            if (document.querySelector('.floating-save-container')) {
                document.querySelector('.content-area').classList.add('has-floating-save');
            }
        });
    </script>
    
    <!-- Notification System JavaScript -->
    <script>
        let previousUnreadCount = null;
        let notificationSoundEnabled = true;
        let notificationAudio = new Audio('{{ asset('sounds/notification.mp3') }}');
        let notificationPage = 1;
        let notificationsLoading = false;
        let notificationsHasMore = true;
        
        function loadNotifications(reset = true) {
            if (notificationsLoading) return;
            notificationsLoading = true;
            
            if (reset) {
                notificationPage = 1;
                notificationsHasMore = true;
            }
            
            fetch('{{ route('admin.notifications.recent') }}?page=' + notificationPage, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (reset && previousUnreadCount !== null && data.unread_count > previousUnreadCount) {
                    playNotificationSound();
                }
                previousUnreadCount = data.unread_count;
                updateNotificationBadge(data.unread_count);
                updateNotificationList(data.notifications, reset);
                notificationsHasMore = data.has_more;
                notificationPage = data.current_page + 1;
                hideNotificationError();
                notificationsLoading = false;
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                showNotificationError();
                notificationsLoading = false;
            });
        }
        
        function loadMoreNotifications() {
            if (notificationsLoading || !notificationsHasMore) return;
            loadNotifications(false);
        }
        
        function playNotificationSound() {
            if (notificationSoundEnabled) {
                notificationAudio.play().catch(e => console.log('Audio play failed:', e));
            }
        }
        
        function showNotificationError() {
            const list = document.getElementById('notificationList');
            if (list && !list.querySelector('.notification-error')) {
                list.innerHTML = `
                    <div class="notification-error text-center py-4">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                        <p class="mb-0 mt-2 small text-muted">Failed to load notifications</p>
                        <button class="btn btn-sm btn-link mt-2" onclick="loadNotifications()">Retry</button>
                    </div>
                `;
            }
        }
        
        function hideNotificationError() {
            const list = document.getElementById('notificationList');
            if (list && list.querySelector('.notification-error')) {
                list.innerHTML = '';
            }
        }
        
        function updateNotificationBadge(count) {
            const badge = document.getElementById('notificationBadge');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
        
        function updateNotificationList(notifications, reset = true) {
            const list = document.getElementById('notificationList');
            if (!list) return;
            
            if (reset && (!notifications || notifications.length === 0)) {
                list.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 2rem;"></i>
                        <p class="mb-0 mt-2 small text-muted">No notifications yet</p>
                    </div>
                `;
                return;
            }
            
            const typeColors = {
                'order': 'primary',
                'review': 'warning',
                'stock': 'danger',
                'refund': 'info',
                'customer': 'success',
                'support': 'secondary',
                'system': 'dark',
                'product': 'primary'
            };
            
            const typeIcons = {
                'order': 'bi-bag',
                'review': 'bi-star',
                'stock': 'bi-box',
                'refund': 'bi-arrow-return-left',
                'customer': 'bi-person-plus',
                'support': 'bi-headset',
                'system': 'bi-gear',
                'product': 'bi-box-seam'
            };
            
            let html = reset ? '' : list.innerHTML.replace(/<button class="btn btn-sm btn-link text-primary load-more-btn"[^>]*>.*?<\/button>/, '');
            notifications.forEach(function(notification) {
                const color = typeColors[notification.type] || 'primary';
                const icon = typeIcons[notification.type] || 'bi-bell';
                const timeAgo = notification.time_ago || 'Just now';
                const link = notification.link || '#';
                
                html += `
                    <div class="notification-item-wrapper">
                        <a href="${link}" class="dropdown-item notification-item ${notification.is_read ? '' : 'unread'}" onclick="handleNotificationClick(event, ${notification.id}, '${link}')">
                            <div class="d-flex align-items-start">
                                <div class="bg-${color} bg-opacity-10 rounded p-2 me-2">
                                    <i class="bi ${icon} text-${color}"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <strong class="small">${notification.title}</strong>
                                        <small class="text-muted">${timeAgo}</small>
                                    </div>
                                    <p class="mb-0 small text-muted text-truncate">${notification.message}</p>
                                </div>
                            </div>
                        </a>
                        <button class="btn btn-sm btn-link text-danger notification-delete-btn" onclick="deleteNotification(event, ${notification.id})" title="Delete notification">
                            <i class="bi bi-x small"></i>
                        </button>
                    </div>
                `;
            });
            
            if (notificationsHasMore) {
                html += `<button class="btn btn-sm btn-link text-primary load-more-btn" onclick="loadMoreNotifications()" style="width: 100%; border-top: 1px solid #f0f0f0;">Load More</button>`;
            }
            
            list.innerHTML = html;
        }
        
        function handleNotificationClick(event, notificationId, link) {
            event.preventDefault();
            
            fetch('{{ route('admin.notifications.mark-read') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ ids: [notificationId] })
            })
            .then(response => response.json())
            .then(data => {
                updateNotificationBadge(data.unread_count);
                if (link && link !== '#') {
                    window.location.href = link;
                }
            })
            .catch(error => console.error('Error marking notification as read:', error));
        }
        
        function deleteNotification(event, notificationId) {
            event.preventDefault();
            event.stopPropagation();
            
            const wrapper = event.target.closest('.notification-item-wrapper');
            
            fetch(`/admin/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (wrapper) {
                    wrapper.style.opacity = '0';
                    wrapper.style.transition = 'opacity 0.3s';
                    setTimeout(() => wrapper.remove(), 300);
                }
                if (data.unread_count !== undefined) {
                    updateNotificationBadge(data.unread_count);
                }
            })
            .catch(error => console.error('Error deleting notification:', error));
        }
        
        function markAllNotificationsAsRead() {
            fetch('{{ route('admin.notifications.mark-all-read') }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                updateNotificationBadge(0);
                loadNotifications();
            })
            .catch(error => console.error('Error marking all as read:', error));
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
            setInterval(loadNotifications, 30000);
        });

        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
            setInterval(loadNotifications, 30000);
        });
    </script>

    @stack('scripts')
</body>
</html>
