<?php
// admin/includes/header.php
require_once __DIR__ . '/../../includes/functions.php';

// Verify admin login
if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit();
}
$admin_name = $_SESSION['admin_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wolf Nutrition | Admin Control Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Admin dashboard adjustments */
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background-color: var(--bg-primary);
            font-family: var(--font-body);
        }
        .admin-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            flex: 1;
            min-height: calc(100vh - 75px);
        }
        .admin-sidebar {
            background-color: #0b0c10;
            border-right: 1px solid rgba(212, 175, 55, 0.1);
            padding: 30px 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: 5px 0 25px rgba(0,0,0,0.3);
        }
        .admin-sidebar-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 18px;
            border-radius: 8px;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.3px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border-left: 3px solid transparent;
        }
        .admin-sidebar-link i {
            font-size: 1rem;
            width: 18px;
            text-align: center;
            opacity: 0.75;
            transition: transform 0.3s;
        }
        .admin-sidebar-link:hover {
            color: var(--gold-light);
            background: rgba(212, 175, 55, 0.05);
            border-left: 3px solid rgba(212, 175, 55, 0.4);
            padding-left: 22px;
        }
        .admin-sidebar-link:hover i {
            transform: scale(1.1);
            opacity: 1;
        }
        .admin-sidebar-link.active {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.15) 0%, rgba(212, 175, 55, 0.03) 100%);
            color: var(--gold-primary);
            border-left: 3px solid var(--gold-primary);
            padding-left: 22px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .admin-sidebar-link.active i {
            opacity: 1;
            color: var(--gold-primary);
        }
        .admin-content {
            padding: 45px 50px;
            background-color: #0f0f12;
            overflow-y: auto;
        }
        .admin-card-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }
        .admin-card {
            background: rgba(26, 26, 29, 0.45);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(212, 175, 55, 0.1);
            padding: 25px;
            border-radius: 10px;
            text-align: left;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        .admin-card:hover {
            transform: translateY(-5px);
            border-color: var(--gold-primary);
            box-shadow: 0 15px 35px rgba(212, 175, 55, 0.15), 0 0 20px rgba(212, 175, 55, 0.05);
        }
        .admin-card h4 {
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .admin-card div.val {
            font-size: 2.1rem;
            font-weight: 800;
            color: #ffffff;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .admin-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .admin-table th, .admin-table td {
            padding: 16px 20px;
            text-align: left;
        }
        .admin-table th {
            background-color: rgba(26, 26, 29, 0.8);
            color: var(--gold-primary);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.15);
        }
        .admin-table td {
            border-bottom: 1px solid rgba(255,255,255,0.03);
            color: var(--text-secondary);
            font-size: 0.9rem;
            background-color: rgba(26, 26, 29, 0.2);
        }
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        .admin-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.02);
            color: #ffffff;
        }
        .admin-badge {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-pending { 
            background-color: rgba(212, 175, 55, 0.1); 
            color: var(--gold-primary); 
            border: 1px solid rgba(212, 175, 55, 0.15);
        }
        .badge-completed { 
            background-color: rgba(46, 204, 113, 0.1); 
            color: var(--success-color); 
            border: 1px solid rgba(46, 204, 113, 0.15);
        }
        .badge-failed { 
            background-color: rgba(231, 76, 60, 0.1); 
            color: var(--danger-color); 
            border: 1px solid rgba(231, 76, 60, 0.15);
        }
        
        /* Admin Global Form overrides for modern clean feel */
        .admin-content .form-control {
            background-color: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 0.9rem;
        }
        .admin-content .form-control:focus {
            border-color: var(--gold-primary) !important;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.2) !important;
        }

        /* Responsive Media Queries for Admin Panel Control Center */
        @media (max-width: 1024px) {
            .admin-layout {
                grid-template-columns: 1fr;
                min-height: auto;
            }
            .admin-sidebar {
                border-right: none;
                border-bottom: 1px solid rgba(212, 175, 55, 0.15);
                padding: 20px 15px;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }
            .admin-sidebar-link {
                padding: 10px 15px;
                font-size: 0.85rem;
                border-left: none;
                border-bottom: 2px solid transparent;
            }
            .admin-sidebar-link:hover {
                border-left: none;
                border-bottom: 2px solid rgba(212, 175, 55, 0.4);
                padding-left: 15px;
            }
            .admin-sidebar-link.active {
                border-left: none;
                border-bottom: 2px solid var(--gold-primary);
                padding-left: 15px;
            }
            .admin-content {
                padding: 30px 20px;
                max-width: 100% !important;
                overflow-x: hidden;
            }
            .admin-card-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            /* FORCE ALL INLINE GRIDS TO STACK 1-COLUMN ON TABLETS AND MOBILE */
            .admin-content div[style*="display:grid"],
            .admin-content div[style*="display: grid"] {
                grid-template-columns: 1fr !important;
                gap: 20px !important;
                width: 100% !important;
            }
            
            /* Prevent grid tracks/glass cards from expanding past parent boundaries */
            .admin-layout, .admin-content, .admin-card-grid, .admin-content div {
                min-width: 0 !important;
            }
            .glass-card {
                max-width: 100% !important;
                overflow-x: auto !important;
            }
            
            /* Globally make all admin tables scrollable and responsive on tablet/mobile viewports */
            .admin-table, .admin-content table {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                white-space: nowrap;
            }
        }

        @media (max-width: 768px) {
            header {
                display: block !important;
                height: auto !important;
                padding: 10px 0 !important;
            }
            .header-container {
                flex-direction: column !important;
                align-items: center !important;
                gap: 12px !important;
                text-align: center !important;
                padding: 0 15px !important;
                height: auto !important;
            }
            .header-container .logo {
                flex-direction: column !important;
                align-items: center !important;
                gap: 6px !important;
            }
            
            /* Collapsible Mobile Sidebar styling */
            .admin-sidebar {
                flex-direction: column;
                align-items: stretch;
                padding: 12px 15px;
                gap: 8px;
            }
            
            /* Hide links by default when menu collapsed on mobile */
            .admin-sidebar-link {
                display: none !important;
                border-bottom: none;
                border-left: 3px solid transparent;
            }
            .admin-sidebar-link:hover {
                border-left: 3px solid rgba(212, 175, 55, 0.4);
                padding-left: 20px;
            }
            .admin-sidebar-link.active {
                border-left: 3px solid var(--gold-primary);
                padding-left: 20px;
            }
            
            /* Show all links when expanded */
            .admin-sidebar.expanded .admin-sidebar-link {
                display: flex !important;
            }
            
            .admin-card-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            /* Make admin content responsive and reduce spacing */
            .admin-content {
                padding: 25px 15px;
            }

            /* FORCE ALL INLINE FLEX HEADERS TO STACK VERTICALLY ON MOBILE */
            .admin-content > div[style*="display:flex"],
            .admin-content > div[style*="display: flex"] {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px !important;
                margin-bottom: 25px !important;
                width: 100% !important;
            }
            .admin-content > div[style*="display:flex"] p,
            .admin-content > div[style*="display: flex"] p,
            .admin-content > div[style*="display:flex"] div,
            .admin-content > div[style*="display: flex"] div {
                text-align: left !important;
                width: 100% !important;
            }
            
            /* Restrict input form widths */
            .admin-content input, 
            .admin-content select, 
            .admin-content textarea {
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>

    <!-- Header bar -->
    <header style="position:static; border-bottom:1px solid rgba(212, 175, 55, 0.1); background:#0b0c10; min-height:75px; height:auto; padding:10px 0; display:flex; align-items:center; box-shadow: 0 4px 20px rgba(0,0,0,0.25); z-index:100; position:relative;">
        <div class="container header-container" style="max-width:100%; padding:0 30px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; width:100%;">
            <div class="logo" style="margin:0;">
                <img src="../assets/images/logo.png" alt="Wolf Logo" style="height:45px;">
                <div class="logo-text" style="font-size:1.3rem; font-family:var(--font-heading); font-weight:800; color:#fff;">WOLF <span style="color:var(--gold-primary);">NUTRITION</span> <small style="font-size:0.65rem; font-weight:800; letter-spacing:1px; background:var(--gold-gradient); color:#000; padding:2px 8px; border-radius:4px; margin-left:10px; text-transform:uppercase;">ADMIN</small></div>
            </div>
            
            <div style="font-weight:600; font-size:0.9rem; color:#fff; display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.03); padding:8px 16px; border-radius:30px; border:1px solid rgba(255,255,255,0.05);">
                <i class="fas fa-user-shield" style="color:var(--gold-primary); font-size:0.95rem;"></i>
                <span>Active Shield: <strong style="color:var(--gold-primary);"><?php echo htmlspecialchars($admin_name); ?></strong></span>
            </div>
        </div>
    </header>

    <div class="admin-layout">
