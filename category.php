<?php
require_once __DIR__ . '/includes/header.php';
$cat_slug = isset($_GET['slug']) ? trim($_GET['slug']) : 'all';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$in_stock = isset($_GET['in_stock']) ? (int)$_GET['in_stock'] : 0;
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 5000;

$category = null;
if ($cat_slug !== 'all') {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
    $stmt->execute([$cat_slug]); $category = $stmt->fetch();
    if (!$category) { $cat_slug = 'all'; }
}

$sql = "SELECT p.*, dv.price as max_mrp, dv.sale_price as min_price, dv.id as default_variant_id, SUM(pv.stock_qty) as total_stock FROM products p JOIN product_variants dv ON p.id = dv.product_id AND dv.is_default = 1 JOIN product_variants pv ON p.id = pv.product_id WHERE p.is_active = 1";
$params = [];
if ($category) { $sql .= " AND p.category_id = ? "; $params[] = $category['id']; }
$sql .= " GROUP BY p.id HAVING min_price >= ? AND min_price <= ? ";
$params[] = $min_price; $params[] = $max_price;
if ($in_stock) { $sql .= " AND total_stock > 0 "; }
switch ($sort) {
    case 'price-low': $sql .= " ORDER BY min_price ASC "; break;
    case 'price-high': $sql .= " ORDER BY min_price DESC "; break;
    case 'popularity': $sql .= " ORDER BY (SELECT COUNT(r.id) FROM reviews r WHERE r.product_id = p.id AND r.is_approved = 1) DESC "; break;
    default: $sql .= " ORDER BY p.created_at DESC "; break;
}
try { $stmt = $pdo->prepare($sql); $stmt->execute($params); $products = $stmt->fetchAll(); } catch (PDOException $e) { $products = []; }

$hero_img = 'assets/images/products/wolfpack_shoot.png';
$hero_badge = 'Wolf Nutrition Stacks';
$hero_title = $category ? htmlspecialchars($category['name']) : 'All Supplements';
$hero_desc = $category ? htmlspecialchars($category['description']) : 'Explore the full premium range of wellness stacks.';
$hero_stat1 = ['100%', 'Ayurvedic']; $hero_stat2 = ['FSSAI', 'Certified']; $hero_stat3 = ['Free', 'Consult'];

if ($cat_slug === 'liver-detox') {
    $hero_img = 'assets/images/products/wolftox.png'; $hero_badge = 'Organic Cleansing';
    $hero_title = 'Liver Support & Detox';
    $hero_stat1 = ['60', 'Capsules']; $hero_stat2 = ['Liver', 'Detox']; $hero_stat3 = ['100%', 'Veggie'];
} elseif ($cat_slug === 'vitality') {
    $hero_img = 'assets/images/products/wolfpack.png'; $hero_badge = 'Active Performance';
    $hero_stat1 = ['60', 'Capsules']; $hero_stat2 = ['T-Level', 'Booster']; $hero_stat3 = ['Shilajit', 'Pure'];
}
?>

<style>
#goldParticles{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0;opacity:0.3;}

/* ── Hero ── */
.cat-hero{position:relative;padding:70px 60px;border-radius:24px;margin-top:20px;margin-bottom:50px;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:40px;min-height:320px;background:linear-gradient(135deg,rgba(212,175,55,0.08) 0%,#080C10 50%,rgba(212,175,55,0.05) 100%);border:1px solid rgba(212,175,55,0.12);box-shadow:0 25px 60px rgba(8,12,16,0.6);}
.cat-hero::before{content:'';position:absolute;top:-80px;right:-80px;width:300px;height:300px;background:radial-gradient(circle,rgba(212,175,55,0.1) 0%,transparent 70%);pointer-events:none;}
.cat-hero::after{content:'';position:absolute;bottom:-60px;left:-60px;width:200px;height:200px;background:radial-gradient(circle,rgba(212,175,55,0.08) 0%,transparent 70%);pointer-events:none;}
.cat-hero-badge{display:inline-block;font-size:0.68rem;font-weight:800;letter-spacing:2.5px;background:var(--gold-gradient);color:#080C10;padding:6px 18px;border-radius:20px;text-transform:uppercase;margin-bottom:16px;}
.cat-hero-title{font-size:clamp(2.2rem,4.5vw,3.4rem);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:14px;color:#fff;font-family:var(--font-heading);font-weight:800;line-height:1.08;text-shadow:0 2px 12px rgba(0,0,0,0.5);}
.cat-hero-desc{font-size:1.05rem;color:rgba(255,255,255,0.7);max-width:520px;line-height:1.7;margin:0;}
.cat-hero-stats{display:flex;gap:16px;margin-top:28px;}
.cat-hero-stat{text-align:center;padding:14px 20px;background:rgba(255,255,255,0.04);border:1px solid rgba(212,175,55,0.12);border-radius:14px;min-width:85px;}
.cat-hero-stat-num{font-size:1.3rem;font-weight:800;color:var(--gold-primary);font-family:var(--font-heading);line-height:1;}
.cat-hero-stat-label{font-size:0.6rem;color:rgba(255,255,255,0.45);text-transform:uppercase;letter-spacing:1px;margin-top:4px;font-weight:600;}
.cat-hero-visual{position:relative;width:260px;height:260px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.cat-hero-visual::before{content:'';position:absolute;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,var(--gold-primary) 0%,transparent 70%);opacity:0.1;filter:blur(30px);z-index:1;}
.cat-hero-visual img{height:100%;max-height:240px;object-fit:contain;z-index:2;filter:drop-shadow(0 20px 40px rgba(8,12,16,0.7));animation:catFloat 5s ease-in-out infinite;mix-blend-mode:multiply;}
@keyframes catFloat{0%,100%{transform:translateY(0) rotate(0deg)}50%{transform:translateY(-12px) rotate(1.5deg)}}

/* ── Benefits ── */
.cat-benefits{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:40px;}
.cat-benefit{background:rgba(255,255,255,0.02);border:1px solid rgba(212,175,55,0.1);border-radius:18px;padding:26px 24px;display:flex;align-items:center;gap:16px;transition:all 0.3s;position:relative;overflow:hidden;}
.cat-benefit:hover{border-color:var(--gold-primary);transform:translateY(-3px);box-shadow:0 10px 25px rgba(8,12,16,0.3);}
.cat-benefit::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--gold-gradient);border-radius:0 3px 3px 0;}
.cat-benefit-icon{width:46px;height:46px;border-radius:13px;background:rgba(212,175,55,0.08);border:1px solid rgba(212,175,55,0.18);display:flex;align-items:center;justify-content:center;color:var(--gold-primary);font-size:1.1rem;flex-shrink:0;}
.cat-benefit h5{font-size:0.95rem;color:#fff;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;font-family:var(--font-heading);font-weight:700;}
.cat-benefit p{font-size:0.82rem;color:rgba(255,255,255,0.6);margin:0;line-height:1.5;}

/* ── Filter Sidebar ── */
.filter-sidebar{background:rgba(15,16,20,0.7);backdrop-filter:blur(16px);border:1px solid rgba(212,175,55,0.1);border-radius:18px;padding:28px 24px;position:sticky;top:100px;}
.filter-sidebar h3{font-size:1.1rem;text-transform:uppercase;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(255,255,255,0.06);font-family:var(--font-heading);font-weight:800;color:#fff;}
.filter-label{display:block;font-size:0.72rem;font-weight:700;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:7px;}
.filter-input{width:100%;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:12px 14px;color:#fff;font-size:0.9rem;outline:none;transition:border-color 0.25s;font-family:var(--font-body);}
.filter-input:focus{border-color:rgba(212,175,55,0.4);box-shadow:0 0 0 3px rgba(212,175,55,0.08);}
.filter-input::placeholder{color:var(--text-muted);}
.filter-check{display:flex;align-items:center;gap:10px;cursor:pointer;font-size:0.88rem;color:rgba(255,255,255,0.6);margin:18px 0;}
.filter-check input{accent-color:var(--gold-primary);width:17px;height:17px;}
.filter-divider{height:1px;background:rgba(255,255,255,0.05);margin:16px 0;}

/* ── Product Cards ── */
.product-card{position:relative;transition:all 0.4s cubic-bezier(0.25,0.8,0.25,1);background:rgba(255,255,255,0.03);border:1px solid rgba(212,175,55,0.08);border-radius:20px;overflow:hidden;}
.product-card:hover{transform:translateY(-8px);border-color:var(--gold-primary);box-shadow:0 20px 50px rgba(8,12,16,0.5),0 0 40px rgba(212,175,55,0.08);}
.product-card::after{content:'';position:absolute;bottom:0;left:50%;transform:translateX(-50%);width:0;height:2px;background:var(--gold-gradient);transition:width 0.4s ease;border-radius:2px;}
.product-card:hover::after{width:80%;}

/* ── 3D Tilt ── */
.tilt-card{transform-style:preserve-3d;perspective:1000px;}
.tilt-card .tilt-shine{position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,0.06) 0%,transparent 60%);pointer-events:none;opacity:0;transition:opacity 0.3s;}
.tilt-card:hover .tilt-shine{opacity:1;}

/* ── Spotlight ── */
.spotlight-card{position:relative;overflow:hidden;}
.spotlight-card::before{content:'';position:absolute;top:var(--mouse-y,50%);left:var(--mouse-x,50%);width:250px;height:250px;background:radial-gradient(circle,rgba(212,175,55,0.1) 0%,transparent 70%);transform:translate(-50%,-50%);pointer-events:none;opacity:0;transition:opacity 0.4s;z-index:0;}
.spotlight-card:hover::before{opacity:1;}

/* ── Divider ── */
.divider-wave{position:relative;width:100%;overflow:hidden;line-height:0;margin-top:-1px;}
.divider-wave svg{display:block;width:100%;height:50px;}

/* ── Empty State ── */
.empty-state{background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:20px;padding:60px 40px;text-align:center;}
.empty-state-icon{width:80px;height:80px;border-radius:50%;background:rgba(212,175,55,0.06);border:1px solid rgba(212,175,55,0.15);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;color:var(--gold-primary);font-size:2rem;}

@media(max-width:1024px){
    .cat-hero{flex-direction:column;text-align:center;padding:45px 30px;}
    .cat-hero-desc{max-width:100%;}
    .cat-hero-stats{justify-content:center;}
    .cat-benefits{grid-template-columns:1fr;}
}
@media(max-width:600px){
    #goldParticles{display:none !important;}
    .cat-hero{padding:30px 16px;margin-top:10px;margin-bottom:30px;min-height:auto;}
    .cat-hero-badge{font-size:0.6rem;letter-spacing:1.5px;padding:5px 14px;}
    .cat-hero-title{font-size:1.6rem;letter-spacing:0.5px;}
    .cat-hero-desc{font-size:0.82rem;line-height:1.55;}
    .cat-hero-stats{gap:10px;margin-top:20px;}
    .cat-hero-stat{padding:10px 14px;min-width:70px;}
    .cat-hero-stat-num{font-size:1.1rem;}
    .cat-hero-stat-label{font-size:0.52rem;}
    .cat-hero-visual{width:140px;height:140px;}
    .cat-hero-visual img{max-height:130px;}
    .cat-benefits{grid-template-columns:1fr !important;gap:12px;margin-bottom:24px;}
    .cat-benefit{padding:16px 14px;gap:12px;}
    .cat-benefit-icon{width:38px;height:38px;font-size:0.9rem;border-radius:10px;}
    .cat-benefit h5{font-size:0.78rem;}
    .cat-benefit p{font-size:0.68rem;line-height:1.4;}
    .filter-sidebar{position:static;padding:20px 16px;border-radius:14px;}
    .filter-sidebar h3{font-size:0.95rem;margin-bottom:14px;}
    .filter-input{padding:10px 12px;font-size:0.82rem;}
    .filter-check{font-size:0.8rem;margin:12px 0;}
    .product-grid{grid-template-columns:1fr !important;gap:16px !important;}
    .product-card-image{height:180px !important;}
    .product-card-info{padding:14px !important;}
    .product-card-info h3{font-size:0.88rem !important;}
    .empty-state{padding:40px 20px;}
    .container > div[style*="grid-template-columns:260px"]{grid-template-columns:1fr !important;gap:16px !important;}
}
</style>

<canvas id="goldParticles"></canvas>

<div class="container" style="margin-top:20px; margin-bottom:60px; position:relative; z-index:2;">

    <!-- ═══ HERO ═══ -->
    <div class="cat-hero">
        <div style="position:relative; z-index:2; flex:1;">
            <span class="cat-hero-badge"><?php echo $hero_badge; ?></span>
            <h1 class="cat-hero-title"><?php echo $hero_title; ?></h1>
            <p class="cat-hero-desc"><?php echo $hero_desc; ?></p>
            <div class="cat-hero-stats">
                <div class="cat-hero-stat"><div class="cat-hero-stat-num"><?php echo $hero_stat1[0]; ?></div><div class="cat-hero-stat-label"><?php echo $hero_stat1[1]; ?></div></div>
                <div class="cat-hero-stat"><div class="cat-hero-stat-num"><?php echo $hero_stat2[0]; ?></div><div class="cat-hero-stat-label"><?php echo $hero_stat2[1]; ?></div></div>
                <div class="cat-hero-stat"><div class="cat-hero-stat-num"><?php echo $hero_stat3[0]; ?></div><div class="cat-hero-stat-label"><?php echo $hero_stat3[1]; ?></div></div>
            </div>
        </div>
        <div class="cat-hero-visual"><img src="<?php echo $hero_img; ?>" alt="<?php echo $hero_title; ?>"></div>
    </div>

    <!-- ═══ BENEFITS ═══ -->
    <div class="cat-benefits">
        <?php if ($cat_slug === 'vitality'): ?>
            <div class="cat-benefit tilt-card spotlight-card"><div class="tilt-shine"></div><div class="cat-benefit-icon"><i class="fas fa-fire"></i></div><div><h5>T-Level Optimizer</h5><p>Premium purified Shilajit sustains natural vitality levels.</p></div></div>
            <div class="cat-benefit tilt-card spotlight-card"><div class="tilt-shine"></div><div class="cat-benefit-icon"><i class="fas fa-bolt"></i></div><div><h5>Endurance Boost</h5><p>Peak muscle oxygenation and daily cellular energy.</p></div></div>
            <div class="cat-benefit tilt-card spotlight-card"><div class="tilt-shine"></div><div class="cat-benefit-icon"><i class="fas fa-dumbbell"></i></div><div><h5>Adaptogenic Recovery</h5><p>Reduces cortisol, optimizes stress recovery and focus.</p></div></div>
        <?php elseif ($cat_slug === 'liver-detox'): ?>
            <div class="cat-benefit tilt-card spotlight-card"><div class="tilt-shine"></div><div class="cat-benefit-icon"><i class="fas fa-shield-halved"></i></div><div><h5>Hepatic Cleanse</h5><p>Optimizes detox enzymes, purges heavy metals.</p></div></div>
            <div class="cat-benefit tilt-card spotlight-card"><div class="tilt-shine"></div><div class="cat-benefit-icon"><i class="fas fa-apple-whole"></i></div><div><h5>Digestive Booster</h5><p>Enhances bile secretion, eases bloating.</p></div></div>
            <div class="cat-benefit tilt-card spotlight-card"><div class="tilt-shine"></div><div class="cat-benefit-icon"><i class="fas fa-filter"></i></div><div><h5>Cellular Shield</h5><p>Protects liver tissue using Kutki and Kasani.</p></div></div>
        <?php else: ?>
            <div class="cat-benefit tilt-card spotlight-card"><div class="tilt-shine"></div><div class="cat-benefit-icon"><i class="fas fa-certificate"></i></div><div><h5>Certified Pure</h5><p>FSSAI, GMP manufacturing laboratory seals.</p></div></div>
            <div class="cat-benefit tilt-card spotlight-card"><div class="tilt-shine"></div><div class="cat-benefit-icon"><i class="fas fa-leaf"></i></div><div><h5>100% Organic</h5><p>Raw extracts in clean vegan capsules.</p></div></div>
            <div class="cat-benefit tilt-card spotlight-card"><div class="tilt-shine"></div><div class="cat-benefit-icon"><i class="fas fa-flask"></i></div><div><h5>Validated Ratios</h5><p>Matched to clinical research parameters.</p></div></div>
        <?php endif; ?>
    </div>

    <!-- ═══ DIVIDER ═══ -->
    <div class="divider-wave"><svg viewBox="0 0 1200 50" preserveAspectRatio="none"><path d="M0,0 L1200,0 L1200,25 Q900,50 600,25 Q300,0 0,25 Z" fill="rgba(212,175,55,0.03)"/></svg></div>

    <!-- ═══ PRODUCT GRID + FILTER ═══ -->
    <div style="display:grid; grid-template-columns:260px 1fr; gap:28px; align-items:start; margin-top:10px;">

        <!-- Filter Sidebar -->
        <aside class="filter-sidebar">
            <h3><i class="fas fa-sliders" style="color:var(--gold-primary); margin-right:8px;"></i> Filters</h3>
            <form action="category.php" method="GET">
                <input type="hidden" name="slug" value="<?php echo htmlspecialchars($cat_slug); ?>">
                <label class="filter-label">Sort By</label>
                <select name="sort" class="filter-input" onchange="this.form.submit()">
                    <option value="newest" <?php echo $sort==='newest'?'selected':''; ?>>Newest First</option>
                    <option value="price-low" <?php echo $sort==='price-low'?'selected':''; ?>>Price: Low to High</option>
                    <option value="price-high" <?php echo $sort==='price-high'?'selected':''; ?>>Price: High to Low</option>
                    <option value="popularity" <?php echo $sort==='popularity'?'selected':''; ?>>Popularity</option>
                </select>
                <div class="filter-divider"></div>
                <label class="filter-check">
                    <input type="checkbox" name="in_stock" value="1" <?php echo $in_stock?'checked':''; ?> onchange="this.form.submit()">
                    <span>In Stock Only</span>
                </label>
                <div class="filter-divider"></div>
                <label class="filter-label">Price Range (₹)</label>
                <div style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
                    <input type="number" name="min_price" value="<?php echo $min_price; ?>" class="filter-input" placeholder="Min" style="flex:1;">
                    <span style="color:rgba(255,255,255,0.35); font-size:0.8rem;">to</span>
                    <input type="number" name="max_price" value="<?php echo $max_price; ?>" class="filter-input" placeholder="Max" style="flex:1;">
                </div>
                <button type="submit" class="btn-gold" style="width:100%; margin-top:18px; padding:12px; font-size:0.85rem; border-radius:12px;"><i class="fas fa-check"></i> Apply Filters</button>
                <a href="category.php?slug=<?php echo htmlspecialchars($cat_slug); ?>" style="display:block; text-align:center; margin-top:12px; font-size:0.82rem; color:rgba(255,255,255,0.4); text-decoration:none; transition:color 0.2s;">Reset All</a>
            </form>
        </aside>

        <!-- Products -->
        <div>
            <?php if (!empty($products)): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; padding:14px 20px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:12px;">
                    <p style="font-size:0.92rem; color:rgba(255,255,255,0.6); margin:0;">Showing <strong style="color:var(--gold-primary); font-size:1.05rem;"><?php echo count($products); ?></strong> product<?php echo count($products)>1?'s':''; ?></p>
                </div>
                <div class="product-grid">
                    <?php foreach ($products as $prod):
                        $dp = $prod['max_mrp']>0 ? round((($prod['max_mrp']-$prod['min_price'])/$prod['max_mrp'])*100) : 0;
                        $sr = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(id) as cnt FROM reviews WHERE product_id=? AND is_approved=1");
                        $sr->execute([$prod['id']]); $ri=$sr->fetch(); $ar=$ri['avg_rating']?round($ri['avg_rating'],1):5.0;
                    ?>
                        <div class="product-card tilt-card spotlight-card">
                            <?php if($dp>0): ?><span class="badge-discount">-<?php echo $dp; ?>% OFF</span><?php endif; ?>
                            <?php if($prod['total_stock']<=0): ?><span class="badge-soldout">Sold Out</span><?php endif; ?>
                            <div class="tilt-shine"></div>
                            <div class="product-card-image" style="height:240px; background:radial-gradient(circle at center,rgba(212,175,55,0.06) 0%,rgba(8,12,16,0.95) 80%); padding:20px; display:flex; align-items:center; justify-content:center;">
                                <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" style="max-height:100%; max-width:100%; object-fit:contain; filter:drop-shadow(0 12px 25px rgba(8,12,16,0.5)); transition:transform 0.4s ease; mix-blend-mode:multiply;">
                            </div>
                            <div style="padding:20px;">
                                <a href="product.php?slug=<?php echo $prod['slug']; ?>" style="text-decoration:none;">
                                    <h3 style="font-size:1rem; color:#fff; margin-bottom:8px; font-family:var(--font-heading); font-weight:700; line-height:1.3;"><?php echo htmlspecialchars($prod['name']); ?></h3>
                                </a>
                                <div style="display:flex; align-items:center; gap:6px; margin-bottom:12px;">
                                    <?php for($s=1;$s<=5;$s++):?><i class="<?php echo $s<=round($ar)?'fas':'far';?> fa-star" style="color:var(--gold-light); font-size:0.75rem;"></i><?php endfor;?>
                                    <span style="font-size:0.75rem; color:rgba(255,255,255,0.4);">(<?php echo $ri['cnt']; ?>)</span>
                                </div>
                                <div style="display:flex; align-items:baseline; gap:10px; margin-bottom:16px;">
                                    <span style="font-size:1.25rem; font-weight:800; color:var(--gold-primary); font-family:var(--font-heading);">₹<?php echo number_format($prod['min_price'],2); ?></span>
                                    <span style="font-size:0.82rem; color:rgba(255,255,255,0.35); text-decoration:line-through;">MRP ₹<?php echo number_format($prod['max_mrp'],2); ?></span>
                                </div>
                                <?php if($prod['total_stock']>0): ?>
                                    <button class="btn-gold quick-add-btn" style="width:100%; padding:11px; font-size:0.82rem; border-radius:12px; font-weight:700;" data-product-id="<?php echo $prod['id']; ?>" data-variant-id="<?php echo $prod['default_variant_id']; ?>" data-csrf="<?php echo generate_csrf_token(); ?>"><i class="fas fa-shopping-cart"></i> Quick Add</button>
                                <?php else: ?>
                                    <button class="btn-gold" style="width:100%; padding:11px; font-size:0.82rem; border-radius:12px; background:rgba(255,255,255,0.1); cursor:not-allowed; box-shadow:none; color:rgba(255,255,255,0.4);" disabled>Out of Stock</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-search"></i></div>
                    <h3 style="color:#fff; margin-bottom:8px;">No products found</h3>
                    <p style="color:rgba(255,255,255,0.5); font-size:0.92rem; margin-bottom:22px;">Try adjusting your filters or reset to see all products.</p>
                    <a href="category.php?slug=<?php echo htmlspecialchars($cat_slug); ?>" class="btn-gold" style="padding:12px 30px; border-radius:30px; font-size:0.88rem;">Clear All Filters</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
// ── Gold Particles ──
(function(){var c=document.getElementById('goldParticles');if(!c)return;var ctx=c.getContext('2d'),p=[];function r(){c.width=window.innerWidth;c.height=window.innerHeight;}r();window.addEventListener('resize',r);for(var i=0;i<35;i++)p.push({x:Math.random()*c.width,y:Math.random()*c.height,r:Math.random()*1.8+0.4,dx:(Math.random()-0.5)*0.25,dy:(Math.random()-0.5)*0.25,o:Math.random()*0.4+0.1});function d(){ctx.clearRect(0,0,c.width,c.height);for(var i=0;i<p.length;i++){var v=p[i];ctx.beginPath();ctx.arc(v.x,v.y,v.r,0,Math.PI*2);ctx.fillStyle='rgba(212,175,55,'+v.o+')';ctx.fill();v.x+=v.dx;v.y+=v.dy;if(v.x<0||v.x>c.width)v.dx*=-1;if(v.y<0||v.y>c.height)v.dy*=-1;}requestAnimationFrame(d);}d();})();

// ── Scroll Reveal ──
(function(){var els=document.querySelectorAll('.product-card');els.forEach(function(el){el.style.opacity='0';el.style.transform='translateY(24px)';el.style.transition='opacity 0.5s ease, transform 0.5s ease';});var obs=new IntersectionObserver(function(entries){entries.forEach(function(e,i){if(e.isIntersecting){setTimeout(function(){e.target.style.opacity='1';e.target.style.transform='translateY(0)';},i*60);obs.unobserve(e.target);}});},{threshold:0.1});els.forEach(function(el){obs.observe(el);});})();

// ── 3D Tilt ──
(function(){document.querySelectorAll('.tilt-card').forEach(function(card){card.addEventListener('mousemove',function(e){var rect=card.getBoundingClientRect();var x=(e.clientX-rect.left)/rect.width-0.5;var y=(e.clientY-rect.top)/rect.height-0.5;card.style.transform='rotateY('+(x*5)+'deg) rotateX('+(-y*5)+'deg) scale(1.015)';card.style.setProperty('--mouse-x',((e.clientX-rect.left)/rect.width*100)+'%');card.style.setProperty('--mouse-y',((e.clientY-rect.top)/rect.height*100)+'%');});card.addEventListener('mouseleave',function(){card.style.transform='';});});})();
</script>
