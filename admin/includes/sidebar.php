<?php
// admin/includes/sidebar.php — Redesigned Sidebar
$active_subpage = basename($_SERVER['PHP_SELF']);

// Navigation grouped by function
$nav_groups = [
    [
        'label' => 'Overview',
        'items' => [
            ['page' => 'dashboard.php', 'label' => 'Dashboard', 'icon' => 'fas fa-th-large', 'match' => ['dashboard.php']],
        ]
    ],
    [
        'label' => 'Catalog',
        'items' => [
            ['page' => 'products.php',    'label' => 'Products',   'icon' => 'fas fa-box',      'match' => ['products.php', 'product_add.php', 'product_edit.php']],
            ['page' => 'categories.php',  'label' => 'Categories', 'icon' => 'fas fa-layer-group', 'match' => ['categories.php', 'category_add.php', 'category_edit.php']],
            ['page' => 'bundles.php',     'label' => 'Combos',       'icon' => 'fas fa-cubes',    'match' => ['bundles.php', 'bundle_add.php', 'bundle_edit.php']],
        ]
    ],
    [
        'label' => 'Sales',
        'items' => [
            ['page' => 'orders.php',              'label' => 'Orders',            'icon' => 'fas fa-shopping-bag', 'match' => ['orders.php']],
            ['page' => 'quantity_discounts.php',  'label' => 'Discounts',         'icon' => 'fas fa-percentage',   'match' => ['quantity_discounts.php']],
        ]
    ],
    [
        'label' => 'Marketing',
        'items' => [
            ['page' => 'announcements.php',  'label' => 'Announcements', 'icon' => 'fas fa-bullhorn',  'match' => ['announcements.php']],
            ['page' => 'newsletter.php',     'label' => 'Subscribers',   'icon' => 'fas fa-envelope',  'match' => ['newsletter.php']],
            ['page' => 'testimonials.php',   'label' => 'Testimonials',  'icon' => 'fas fa-quote-left', 'match' => ['testimonials.php', 'testimonial_add.php', 'testimonial_edit.php']],
            ['page' => 'reviews.php',        'label' => 'Reviews',       'icon' => 'fas fa-star-half-alt', 'match' => ['reviews.php']],
        ]
    ],
    [
        'label' => 'Content',
        'items' => [
            ['page' => 'blog.php',              'label' => 'Blog',           'icon' => 'fas fa-pen-nib',     'match' => ['blog.php', 'blog_add.php', 'blog_edit.php']],
            ['page' => 'blog_categories.php',   'label' => 'Blog Categories','icon' => 'fas fa-folder',      'match' => ['blog_categories.php']],
            ['page' => 'blog_tags.php',         'label' => 'Blog Tags',      'icon' => 'fas fa-tags',        'match' => ['blog_tags.php']],
            ['page' => 'certificates.php', 'label' => 'Certificates',  'icon' => 'fas fa-award',       'match' => ['certificates.php']],
        ]
    ],
    [
        'label' => 'System',
        'items' => [
            ['page' => 'reports.php',   'label' => 'Reports',  'icon' => 'fas fa-chart-bar', 'match' => ['reports.php']],
        ]
    ],
];
?>

<!-- Mobile Overlay -->
<div class="admin-mobile-overlay" id="admin-mobile-overlay"></div>

<!-- Sidebar -->
<aside class="admin-sidebar" id="admin-sidebar">
    
    <!-- Logo Section -->
    <div class="sidebar-logo">
        <img src="../assets/images/logo.png" alt="Wolf" class="sidebar-logo-img">
        <div class="sidebar-logo-text">
            <span class="sidebar-brand">Wolf Nutrition</span>
            <span class="sidebar-badge">ADMIN</span>
        </div>
    </div>

    <!-- Navigation Groups -->
    <div class="sidebar-nav">
        <?php foreach ($nav_groups as $group): ?>
            <div class="sidebar-group">
                <div class="sidebar-group-label"><?php echo $group['label']; ?></div>
                <?php foreach ($group['items'] as $item): ?>
                    <a href="<?php echo $item['page']; ?>"
                       class="sidebar-link <?php echo in_array($active_subpage, $item['match']) ? 'active' : ''; ?>">
                        <span class="sidebar-link-icon"><i class="<?php echo $item['icon']; ?>"></i></span>
                        <span class="sidebar-link-text"><?php echo $item['label']; ?></span>
                        <?php if (in_array($active_subpage, $item['match'])): ?>
                            <span class="sidebar-link-indicator"></span>
                        <?php endif; ?>
                    </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-link sidebar-logout">
            <span class="sidebar-link-icon"><i class="fas fa-sign-out-alt"></i></span>
            <span class="sidebar-link-text">Logout</span>
        </a>
    </div>
</aside>

<!-- Mobile Toggle Button -->
<button class="admin-mobile-toggle" id="admin-mobile-toggle" aria-label="Toggle navigation">
    <span class="toggle-bar"></span>
    <span class="toggle-bar"></span>
    <span class="toggle-bar"></span>
</button>

<style>
/* ═══════════════════════════════════════════════
   SIDEBAR
   ═══════════════════════════════════════════════ */
.admin-sidebar {
    position: sticky;
    top: 0;
    height: 100%;
    background: rgba(18, 18, 18, 0.85);
    border-right: 1px solid rgba(255, 255, 255, 0.06);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: rgba(212, 175, 55, 0.15) transparent;
}
.admin-sidebar::-webkit-scrollbar { width: 3px; }
.admin-sidebar::-webkit-scrollbar-track { background: transparent; }
.admin-sidebar::-webkit-scrollbar-thumb { background: rgba(212, 175, 55, 0.15); border-radius: 3px; }

.sidebar-logo {
    display: flex; align-items: center; gap: 12px;
    padding: 20px 16px; margin-bottom: 8px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    flex-shrink: 0;
}
.sidebar-logo-img {
    width: 38px; height: 38px; object-fit: contain; border-radius: 8px;
    background: rgba(212, 175, 55, 0.1); padding: 4px;
    border: 1px solid rgba(212, 175, 55, 0.15);
}
.sidebar-logo-text { display: flex; flex-direction: column; gap: 2px; }
.sidebar-brand { font-size: 0.95rem; font-weight: 700; color: #fff; letter-spacing: -0.3px; }
.sidebar-badge {
    font-size: 0.55rem; font-weight: 800; letter-spacing: 1.5px;
    color: #080C10; background: linear-gradient(135deg, #D4AF37, #F2D06B);
    padding: 2px 8px; border-radius: 3px; width: fit-content; text-transform: uppercase;
}

.sidebar-nav { padding: 12px 0; }

.sidebar-group { margin-bottom: 4px; }
.sidebar-group-label {
    font-size: 0.65rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 1.2px;
    color: rgba(255, 255, 255, 0.25);
    padding: 12px 20px 6px; user-select: none;
}

.sidebar-link {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 16px; margin: 1px 10px; border-radius: 8px;
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.85rem; font-weight: 500;
    font-family: 'Inter', system-ui, sans-serif;
    text-decoration: none; white-space: nowrap;
    transition: all 0.15s ease; position: relative;
}
.sidebar-link-icon {
    width: 32px; height: 32px; display: flex; align-items: center;
    justify-content: center; border-radius: 8px;
    background: rgba(255, 255, 255, 0.04); font-size: 0.85rem;
    transition: all 0.15s ease; flex-shrink: 0;
}
.sidebar-link-text { flex: 1; }
.sidebar-link-indicator {
    width: 6px; height: 6px; border-radius: 50%; background: #D4AF37;
    box-shadow: 0 0 8px rgba(212, 175, 55, 0.5); flex-shrink: 0;
}

.sidebar-link:hover {
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.05);
}
.sidebar-link:hover .sidebar-link-icon {
    background: rgba(212, 175, 55, 0.1);
    color: rgba(212, 175, 55, 0.8);
}

.sidebar-link.active {
    color: #D4AF37;
    background: rgba(212, 175, 55, 0.08);
    font-weight: 600;
}
.sidebar-link.active .sidebar-link-icon {
    background: rgba(212, 175, 55, 0.15);
    color: #D4AF37;
}

.sidebar-footer {
    padding: 10px 0; border-top: 1px solid rgba(255, 255, 255, 0.06);
    flex-shrink: 0; margin-top: auto;
}
.sidebar-logout { color: rgba(239, 68, 68, 0.7) !important; }
.sidebar-logout .sidebar-link-icon {
    background: rgba(239, 68, 68, 0.08); color: rgba(239, 68, 68, 0.7);
}
.sidebar-logout:hover {
    background: rgba(239, 68, 68, 0.08) !important; color: #ef4444 !important;
}
.sidebar-logout:hover .sidebar-link-icon {
    background: rgba(239, 68, 68, 0.15); color: #ef4444;
}

/* Mobile Toggle */
.admin-mobile-toggle {
    display: none; position: fixed; bottom: 24px; right: 24px; z-index: 200;
    width: 52px; height: 52px; border-radius: 14px;
    background: linear-gradient(135deg, #D4AF37, #F2D06B);
    border: none; cursor: pointer; flex-direction: column;
    align-items: center; justify-content: center; gap: 5px;
    box-shadow: 0 4px 20px rgba(212, 175, 55, 0.4);
}
.admin-mobile-toggle .toggle-bar {
    width: 20px; height: 2px; background: #080C10; border-radius: 2px;
}

.admin-mobile-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px); z-index: 150;
}
.admin-mobile-overlay.visible { display: block; }

@media (max-width: 1024px) {
    .admin-sidebar {
        position: fixed !important; top: 64px !important; left: 0;
        width: 260px; height: calc(100vh - 64px); z-index: 160;
        transform: translateX(-100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .admin-sidebar.mobile-open { transform: translateX(0); }
    .admin-mobile-toggle { display: flex; }
    .admin-mobile-overlay { display: block; pointer-events: none; }
    .admin-mobile-overlay.visible { pointer-events: auto; }
}
@media (max-width: 768px) {
    .admin-sidebar { width: 280px; top: 56px !important; height: calc(100vh - 56px); }
    .sidebar-logo { padding: 16px 14px; }
    .sidebar-link { padding: 10px 14px; margin: 1px 8px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('admin-sidebar');
    const toggle = document.getElementById('admin-mobile-toggle');
    const overlay = document.getElementById('admin-mobile-overlay');

    function openSidebar() {
        sidebar.classList.add('mobile-open');
        overlay.classList.add('visible');
    }
    function closeSidebar() {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('visible');
    }

    if (toggle && sidebar && overlay) {
        toggle.addEventListener('click', function() {
            sidebar.classList.contains('mobile-open') ? closeSidebar() : openSidebar();
        });
        overlay.addEventListener('click', closeSidebar);
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });
});
</script>

<main class="admin-content">
