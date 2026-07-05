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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: #080C10;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #ffffff;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ── Top Header Bar ── */
        .admin-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            height: 64px;
            min-height: 64px;
            background: rgba(18, 18, 18, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            z-index: 200;
            position: sticky;
            top: 0;
        }
        .admin-topbar-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }
        .admin-topbar-logo img {
            height: 40px;
            width: auto;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.3));
        }
        .admin-topbar-logo .admin-badge {
            padding: 3px 10px;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            background: linear-gradient(135deg, #D4AF37 0%, #F2D06B 100%);
            color: #080C10;
            border-radius: 4px;
            line-height: 1.4;
        }
        .admin-topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 18px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.2s ease;
        }
        .admin-topbar-user i {
            color: #D4AF37;
            font-size: 0.9rem;
        }
        .admin-topbar-user strong {
            color: #D4AF37;
            font-weight: 700;
        }
        .admin-topbar-user:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(212, 175, 55, 0.2);
        }

        /* ── Layout Grid ── */
        .admin-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            flex: 1;
            min-height: calc(100vh - 64px);
        }

        /* ── Sidebar styles moved to sidebar.php ── */

        /* ── Main Content ── */
        .admin-content {
            padding: 36px 40px;
            overflow-y: auto;
            min-height: calc(100vh - 64px);
        }

        /* ── Card Grid ── */
        .admin-card-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 36px;
        }

        /* ── Glass Card ── */
        .glass-card,
        .admin-card {
            background: rgba(18, 18, 18, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
        }
        .glass-card:hover,
        .admin-card:hover {
            transform: translateY(-2px);
            border-color: rgba(212, 175, 55, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), 0 0 20px rgba(212, 175, 55, 0.05);
        }
        .admin-card h4 {
            color: rgba(255, 255, 255, 0.45);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 10px;
        }
        .admin-card div.val,
        .admin-card .val {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #D4AF37 0%, #F2D06B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        /* ── Table ── */
        .admin-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.06);
            margin-top: 20px;
        }
        .admin-table th {
            background: rgba(18, 18, 18, 0.8);
            padding: 14px 20px;
            text-align: left;
            color: #D4AF37;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.12);
        }
        .admin-table td {
            padding: 14px 20px;
            text-align: left;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            background: rgba(18, 18, 18, 0.2);
            transition: all 0.15s ease;
        }
        .admin-table tr:last-child td { border-bottom: none; }
        .admin-table tr:hover td {
            background: rgba(255, 255, 255, 0.025);
            color: #ffffff;
        }

        /* ── Badges ── */
        .admin-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .badge-pending {
            background: rgba(212, 175, 55, 0.1);
            color: #D4AF37;
            border: 1px solid rgba(212, 175, 55, 0.15);
        }
        .badge-completed {
            background: rgba(74, 222, 128, 0.1);
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.15);
        }
        .badge-failed {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.15);
        }

        /* ── Form Controls ── */
        .form-control {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.875rem;
            color: #ffffff !important;
            font-family: 'Inter', system-ui, sans-serif;
            transition: all 0.2s ease;
            outline: none;
            width: 100%;
        }
        .form-control:focus {
            border-color: #D4AF37 !important;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.12);
        }
        .form-control::placeholder { color: rgba(255, 255, 255, 0.3); }
        select.form-control {
            color: #ffffff !important;
            background-color: rgba(30, 30, 30, 0.95) !important;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='rgba(255,255,255,0.4)' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }
        select.form-control option {
            background: #1a1a2e;
            color: #ffffff;
            padding: 8px;
        }
        select.form-control option:hover { background: rgba(212, 175, 55, 0.2); }
        textarea.form-control { resize: vertical; min-height: 100px; }

        /* ── Buttons ── */
        .btn-gold {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: linear-gradient(135deg, #D4AF37 0%, #F2D06B 100%);
            color: #080C10;
            font-size: 0.85rem;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            font-family: 'Inter', system-ui, sans-serif;
            box-shadow: 0 4px 16px rgba(212, 175, 55, 0.2);
        }
        .btn-gold:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(212, 175, 55, 0.35);
        }

        .btn-outline-gold {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: transparent;
            color: #D4AF37;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .btn-outline-gold:hover {
            background: rgba(212, 175, 55, 0.08);
            border-color: #D4AF37;
        }

        /* ── Mobile Sidebar Toggle ── */
        .admin-mobile-toggle {
            display: none;
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 300;
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #D4AF37 0%, #F2D06B 100%);
            color: #080C10;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 4px 24px rgba(212, 175, 55, 0.3);
            transition: all 0.2s ease;
            align-items: center;
            justify-content: center;
        }
        .admin-mobile-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 32px rgba(212, 175, 55, 0.4);
        }
        .admin-mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 150;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .admin-mobile-overlay.visible {
            opacity: 1;
        }

        /* ── Section Headings ── */
        .section-heading {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 24px;
        }
        .section-subheading {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.45);
            font-weight: 500;
            margin-top: -16px;
            margin-bottom: 24px;
        }

        /* ── Utility ── */
        .text-gold { color: #D4AF37; }
        .text-muted { color: rgba(255, 255, 255, 0.45); }
        .text-success { color: #4ade80; }
        .text-danger { color: #ef4444; }
        .mt-1 { margin-top: 8px; }
        .mt-2 { margin-top: 16px; }
        .mb-1 { margin-bottom: 8px; }
        .mb-2 { margin-bottom: 16px; }

        /* ── Responsive: Tablet ── */
        @media (max-width: 1024px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }
            /* Sidebar responsive handled in sidebar.php */
            .admin-content {
                padding: 28px 24px;
            }
            .admin-card-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
        }

        /* ── Responsive: Mobile ── */
        @media (max-width: 768px) {
            .admin-topbar {
                padding: 0 16px;
                height: 56px;
                min-height: 56px;
            }
            .admin-topbar-logo img { height: 32px; }
            .admin-topbar-logo .admin-badge {
                font-size: 0.55rem;
                padding: 2px 8px;
            }
            .admin-topbar-user {
                padding: 6px 12px;
                font-size: 0.78rem;
            }
            .admin-topbar-user span.full-name { display: none; }
            .admin-layout {
                min-height: calc(100vh - 56px);
            }
            /* Sidebar responsive handled in sidebar.php */
            .admin-content {
                padding: 20px 16px;
            }
            .admin-card-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .admin-card { padding: 18px; }
            .admin-card div.val,
            .admin-card .val { font-size: 1.5rem; }
            .admin-table { font-size: 0.8rem; }
            .admin-table th,
            .admin-table td { padding: 10px 12px; }
            .section-heading { font-size: 1.1rem; }
        }
    </style>
</head>
<body>

    <!-- Top Header Bar -->
    <header class="admin-topbar">
        <a href="dashboard.php" class="admin-topbar-logo">
            <img src="../assets/images/logo.png" alt="Wolf Nutrition">
            <span class="admin-badge">Admin</span>
        </a>
        <div class="admin-topbar-user">
            <i class="fas fa-shield-halved"></i>
            <span class="full-name">Active Shield: <strong><?php echo htmlspecialchars($admin_name); ?></strong></span>
        </div>
    </header>

    <div class="admin-layout">
