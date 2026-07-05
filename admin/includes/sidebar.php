<?php
// admin/includes/sidebar.php
$active_subpage = basename($_SERVER['PHP_SELF']);

// Set dynamic page title for mobile menu panel indicator
$active_title = 'Menu Panel';
if ($active_subpage === 'dashboard.php') $active_title = 'Dashboard';
elseif ($active_subpage === 'products.php' || $active_subpage === 'product_add.php' || $active_subpage === 'product_edit.php') $active_title = 'Products';
elseif ($active_subpage === 'categories.php') $active_title = 'Categories';
elseif ($active_subpage === 'orders.php') $active_title = 'Order Management';
elseif ($active_subpage === 'bundles.php') $active_title = 'Bundle Stacks';
elseif ($active_subpage === 'coupons.php') $active_title = 'Coupons & Promos';
elseif ($active_subpage === 'quantity_discounts.php') $active_title = 'Quantity Discounts';
elseif ($active_subpage === 'announcements.php') $active_title = 'Announcements';
elseif ($active_subpage === 'reviews.php') $active_title = 'Reviews Moderation';
elseif ($active_subpage === 'blog.php') $active_title = 'Blog / Articles';
elseif ($active_subpage === 'cms.php') $active_title = 'Policy Pages';
elseif ($active_subpage === 'certificates.php') $active_title = 'Certificates Gallery';
elseif ($active_subpage === 'whatsapp.php') $active_title = 'WhatsApp settings';
elseif ($active_subpage === 'reports.php') $active_title = 'Financial Reports';
?>

<!-- Inline CSS for Collapsible Menu Toggle -->
<style>
    .admin-mobile-menu-header {
        display: none;
    }
    @media (max-width: 768px) {
        .admin-mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 18px;
            background-color: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            user-select: none;
            transition: background-color 0.2s;
            margin-bottom: 5px;
        }
        .admin-mobile-menu-header:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .admin-mobile-menu-header span {
            font-weight: 700;
            color: var(--gold-primary);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-mobile-menu-header .toggle-icon {
            color: var(--gold-primary);
            font-size: 0.85rem;
            transition: transform 0.3s ease;
        }
        .admin-sidebar.expanded .admin-mobile-menu-header .toggle-icon {
            transform: rotate(180deg);
        }
    }
</style>

<aside class="admin-sidebar" id="admin-sidebar-menu">
    <!-- Mobile Menu Accordion Header -->
    <div class="admin-mobile-menu-header" id="mobile-menu-toggle">
        <span>
            <i class="fas fa-bars"></i> Nav Menu: <strong style="color:#fff;"><?php echo $active_title; ?></strong>
        </span>
        <i class="fas fa-chevron-down toggle-icon"></i>
    </div>

    <!-- Sidebar Navigation Links -->
    <a href="dashboard.php" class="admin-sidebar-link <?php echo $active_subpage === 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a href="products.php" class="admin-sidebar-link <?php echo ($active_subpage === 'products.php' || $active_subpage === 'product_add.php' || $active_subpage === 'product_edit.php') ? 'active' : ''; ?>">
        <i class="fas fa-capsules"></i> Products
    </a>
    <a href="categories.php" class="admin-sidebar-link <?php echo ($active_subpage === 'categories.php' || $active_subpage === 'category_add.php' || $active_subpage === 'category_edit.php') ? 'active' : ''; ?>">
        <i class="fas fa-tags"></i> Categories
    </a>
    <a href="orders.php" class="admin-sidebar-link <?php echo $active_subpage === 'orders.php' ? 'active' : ''; ?>">
        <i class="fas fa-receipt"></i> Order Management
    </a>
    <a href="bundles.php" class="admin-sidebar-link <?php echo ($active_subpage === 'bundles.php' || $active_subpage === 'bundle_add.php' || $active_subpage === 'bundle_edit.php') ? 'active' : ''; ?>">
        <i class="fas fa-cubes"></i> Bundle Stacks
    </a>
    <a href="coupons.php" class="admin-sidebar-link <?php echo $active_subpage === 'coupons.php' ? 'active' : ''; ?>">
        <i class="fas fa-percent"></i> Coupons & Promos
    </a>
    <a href="quantity_discounts.php" class="admin-sidebar-link <?php echo $active_subpage === 'quantity_discounts.php' ? 'active' : ''; ?>">
        <i class="fas fa-layer-group"></i> Quantity Discounts
    </a>
    <a href="announcements.php" class="admin-sidebar-link <?php echo $active_subpage === 'announcements.php' ? 'active' : ''; ?>">
        <i class="fas fa-bullhorn"></i> Announcements
    </a>
    <a href="reviews.php" class="admin-sidebar-link <?php echo $active_subpage === 'reviews.php' ? 'active' : ''; ?>">
        <i class="fas fa-star"></i> Reviews Moderation
    </a>
    <a href="blog.php" class="admin-sidebar-link <?php echo $active_subpage === 'blog.php' ? 'active' : ''; ?>">
        <i class="fas fa-blog"></i> Blog / Articles
    </a>
    <a href="cms.php" class="admin-sidebar-link <?php echo $active_subpage === 'cms.php' ? 'active' : ''; ?>">
        <i class="fas fa-file-alt"></i> Policy Pages
    </a>
    <a href="certificates.php" class="admin-sidebar-link <?php echo $active_subpage === 'certificates.php' ? 'active' : ''; ?>">
        <i class="fas fa-certificate"></i> Certificates Gallery
    </a>
    <a href="whatsapp.php" class="admin-sidebar-link <?php echo $active_subpage === 'whatsapp.php' ? 'active' : ''; ?>">
        <i class="fab fa-whatsapp"></i> WhatsApp settings
    </a>
    <a href="reports.php" class="admin-sidebar-link <?php echo $active_subpage === 'reports.php' ? 'active' : ''; ?>">
        <i class="fas fa-file-csv"></i> Financial Reports
    </a>
    <a href="logout.php" class="admin-sidebar-link" style="margin-top:auto; color:var(--danger-color);">
        <i class="fas fa-sign-out-alt"></i> Exit Admin
    </a>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const sidebar = document.getElementById('admin-sidebar-menu');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('expanded');
        });
    }
});
</script>

<main class="admin-content">
