<?php
require_once __DIR__ . '/includes/header.php';
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (empty($slug)) { header("Location: index.php"); exit(); }

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ? AND p.is_active = 1");
$stmt->execute([$slug]); $product = $stmt->fetch();
if (!$product) { header("Location: index.php"); exit(); }

$stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY price ASC");
$stmt->execute([$product['id']]); $variants = $stmt->fetchAll();
if (empty($variants)) { header("Location: index.php"); exit(); }

$default_variant = null;
foreach ($variants as $v) { if ($v['is_default']) { $default_variant = $v; break; } }
if (!$default_variant) $default_variant = $variants[0];

$stmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? AND is_approved = 1 ORDER BY created_at DESC");
$stmt->execute([$product['id']]); $reviews = $stmt->fetchAll();
$total_reviews = count($reviews);
$avg_rating = 0; $rating_dist = [5=>0,4=>0,3=>0,2=>0,1=>0];
if ($total_reviews > 0) { $sum = 0; foreach ($reviews as $r) { $sum += $r['rating']; if (isset($rating_dist[$r['rating']])) $rating_dist[$r['rating']]++; } $avg_rating = round($sum/$total_reviews,1); } else { $avg_rating = 5.0; }

$review_success = $review_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rn = trim($_POST['user_name'] ?? ''); $rt = trim($_POST['title'] ?? ''); $rb = trim($_POST['review_text'] ?? ''); $rs = (int)($_POST['rating'] ?? 5);
    $uid = null;
    if (is_logged_in() && isset($_SESSION['user_id'])) {
        $stmt_uid = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt_uid->execute([$_SESSION['user_id']]);
        if ($stmt_uid->fetch()) $uid = $_SESSION['user_id'];
    }
    if (empty($rn) || empty($rb)) { $review_error = "Please fill in your name and review."; }
    elseif ($rs < 1 || $rs > 5) { $review_error = "Invalid rating."; }
    else { $pdo->prepare("INSERT INTO reviews (product_id,user_id,user_name,rating,title,review_text,is_approved) VALUES (?,?,?,?, ?,?,0)")->execute([$product['id'],$uid,$rn,$rs,$rt,$rb]); $review_success = "Review submitted! Pending approval."; }
}

$stmt = $pdo->prepare("SELECT p.*, dv.price as max_mrp, dv.sale_price as min_price, dv.id as default_variant_id FROM products p JOIN product_variants dv ON p.id = dv.product_id AND dv.is_default = 1 WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 LIMIT 3");
$stmt->execute([$product['category_id'], $product['id']]); $related = $stmt->fetchAll();

$gallery = array_filter(explode(',', $product['image_gallery']));
if (empty($gallery)) $gallery = [$product['image_url']];
?>

<style>
#goldParticles{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0;opacity:0.3;}

/* ── Product Hero ── */
.pd-hero{padding:30px 0 0; position:relative; z-index:2;}
.pd-grid{display:grid; grid-template-columns:1fr 1fr; gap:50px; align-items:start;}
.pd-gallery-main{border-radius:20px; overflow:hidden; background:radial-gradient(circle at center,rgba(212,175,55,0.06) 0%,rgba(8,12,16,0.95) 80%); border:1px solid rgba(212,175,55,0.1); height:500px; display:flex; align-items:center; justify-content:center; cursor:zoom-in; position:relative;}
.pd-gallery-main img{max-height:90%; max-width:90%; object-fit:contain; transition:transform 0.3s ease; filter:drop-shadow(0 20px 40px rgba(8,12,16,0.5));}
.pd-gallery-main:hover img{transform:scale(1.08);}
.pd-gallery-thumbs{display:flex; gap:12px; margin-top:16px;}
.pd-thumb{width:80px; height:80px; border-radius:12px; border:2px solid rgba(255,255,255,0.08); overflow:hidden; cursor:pointer; transition:all 0.3s; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.02);}
.pd-thumb.active{border-color:var(--gold-primary); box-shadow:0 0 12px rgba(212,175,55,0.2);}
.pd-thumb img{width:100%; height:100%; object-fit:contain; padding:6px;}

/* ── Product Info ── */
.pd-badge{display:inline-block; font-size:0.65rem; font-weight:800; letter-spacing:2px; background:var(--gold-gradient); color:#080C10; padding:5px 14px; border-radius:20px; text-transform:uppercase; margin-bottom:14px;}
.pd-title{font-size:1.8rem; font-family:var(--font-heading); font-weight:800; color:#fff; text-transform:uppercase; letter-spacing:0.5px; line-height:1.2; margin-bottom:12px;}
.pd-rating{display:flex; align-items:center; gap:8px; margin-bottom:18px;}
.pd-rating-stars{color:var(--gold-light);}
.pd-rating-text{font-size:0.88rem; color:rgba(255,255,255,0.6);}
.pd-price-row{display:flex; align-items:baseline; gap:12px; margin-bottom:6px;}
.pd-price-sale{font-size:2rem; font-weight:800; color:var(--gold-primary); font-family:var(--font-heading);}
.pd-price-mrp{font-size:1.05rem; color:rgba(255,255,255,0.35); text-decoration:line-through;}
.pd-save{font-size:0.85rem; color:#2ecc71; font-weight:700; background:rgba(46,204,113,0.08); padding:4px 10px; border-radius:6px; border:1px solid rgba(46,204,113,0.15);}
.pd-desc{font-size:0.95rem; color:rgba(255,255,255,0.65); line-height:1.7; margin:18px 0 24px; padding-bottom:24px; border-bottom:1px solid rgba(255,255,255,0.06);}

/* ── Variant Selector ── */
.pd-variant-label{font-size:0.78rem; font-weight:700; color:rgba(255,255,255,0.6); text-transform:uppercase; letter-spacing:1px; margin-bottom:10px;}
.pd-variants{display:flex; gap:10px; margin-bottom:24px;}
.pd-variant{background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:10px 20px; cursor:pointer; transition:all 0.3s; font-size:0.88rem; color:rgba(255,255,255,0.6); font-weight:600;}
.pd-variant:hover{border-color:rgba(212,175,55,0.3); color:#fff;}
.pd-variant.active{background:rgba(212,175,55,0.1); border-color:var(--gold-primary); color:var(--gold-primary);}

/* ── Quantity & Cart ── */
.pd-actions{display:flex; gap:14px; margin-bottom:20px;}
.pd-qty{display:flex; align-items:center; border:1px solid rgba(255,255,255,0.1); border-radius:12px; overflow:hidden;}
.pd-qty button{width:42px; height:44px; background:transparent; border:none; color:#fff; font-size:1.2rem; cursor:pointer; transition:background 0.2s;}
.pd-qty button:hover{background:rgba(212,175,55,0.1);}
.pd-qty input{width:44px; text-align:center; background:transparent; border:none; color:#fff; font-size:1rem; font-weight:700; outline:none;}

/* ── Benefits Strip ── */
.pd-benefits{display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin:20px 0; padding:18px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:14px;}
.pd-benefit{display:flex; align-items:center; gap:8px; font-size:0.82rem; color:rgba(255,255,255,0.6);}
.pd-benefit i{color:var(--gold-primary); font-size:0.85rem;}

/* ── Pincode ── */
.pd-pincode{display:flex; gap:10px; margin-bottom:20px;}
.pd-pincode input{flex:1; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:12px 14px; color:#fff; font-size:0.9rem; outline:none;}
.pd-pincode input:focus{border-color:rgba(212,175,55,0.4);}

/* ── Tabs ── */
.pd-tabs{margin-top:60px; position:relative; z-index:2;}
.pd-tab-nav{display:flex; gap:4px; border-bottom:1px solid rgba(255,255,255,0.08); margin-bottom:30px; overflow-x:auto;}
.pd-tab-btn{background:transparent; border:none; color:rgba(255,255,255,0.4); font-family:var(--font-heading); font-weight:700; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.5px; padding:14px 24px; cursor:pointer; border-bottom:2px solid transparent; transition:all 0.3s; white-space:nowrap;}
.pd-tab-btn:hover{color:rgba(255,255,255,0.7);}
.pd-tab-btn.active{color:var(--gold-primary); border-bottom-color:var(--gold-primary);}
.pd-tab-pane{display:none; font-size:0.95rem; color:rgba(255,255,255,0.65); line-height:1.8;}
.pd-tab-pane.active{display:block; animation:fadeIn 0.3s ease;}

/* ── Reviews ── */
.pd-reviews{margin-top:60px; position:relative; z-index:2;}
.review-summary{display:grid; grid-template-columns:auto 1fr; gap:30px; padding:30px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:18px; margin-bottom:30px;}
.review-avg{text-align:center; padding:20px 30px; border-right:1px solid rgba(255,255,255,0.06);}
.review-avg-num{font-size:3rem; font-weight:800; color:var(--gold-primary); font-family:var(--font-heading); line-height:1;}
.review-avg-stars{color:var(--gold-light); font-size:1rem; margin:8px 0;}
.review-avg-text{font-size:0.82rem; color:rgba(255,255,255,0.4);}
.review-bars{display:flex; flex-direction:column; gap:8px; justify-content:center;}
.review-bar-row{display:flex; align-items:center; gap:12px; font-size:0.85rem;}
.review-bar-fill-bg{flex:1; height:8px; background:rgba(255,255,255,0.06); border-radius:4px; overflow:hidden;}
.review-bar-fill{height:100%; background:var(--gold-gradient); border-radius:4px; transition:width 0.5s;}
.review-item{padding:24px 0; border-bottom:1px solid rgba(255,255,255,0.05);}
.review-item:last-child{border-bottom:none;}
.review-stars{color:var(--gold-light); font-size:0.85rem; margin-bottom:6px;}
.review-author{font-weight:700; color:#fff; font-size:0.92rem;}
.review-date{font-size:0.78rem; color:rgba(255,255,255,0.35);}
.review-text{font-size:0.9rem; color:rgba(255,255,255,0.6); line-height:1.6; margin-top:8px;}
.review-verified{display:inline-flex; align-items:center; gap:5px; font-size:0.75rem; color:var(--gold-primary); font-weight:600;}

/* ── Write Review ── */
.pd-write-review{background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:18px; padding:30px; margin-top:30px;}
.pd-write-review input,.pd-write-review textarea{width:100%; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:12px 14px; color:#fff; font-size:0.9rem; outline:none; font-family:var(--font-body);}
.pd-write-review input:focus,.pd-write-review textarea:focus{border-color:rgba(212,175,55,0.4);}
.pd-write-review label{display:block; font-size:0.78rem; font-weight:700; color:rgba(255,255,255,0.6); text-transform:uppercase; letter-spacing:0.8px; margin-bottom:7px;}
.rating-select-stars{display:flex; gap:8px; font-size:1.3rem; color:rgba(255,255,255,0.2); cursor:pointer;}
.rating-select-stars i.active{color:var(--gold-primary);}

/* ── Related ── */
.pd-related{margin-top:70px; padding:60px 0; border-top:1px solid rgba(255,255,255,0.06); position:relative; z-index:2;}

/* ── Disclaimer ── */
.pd-disclaimer{background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:14px; padding:18px 20px; margin-top:20px; display:flex; gap:12px; align-items:flex-start;}
.pd-disclaimer i{color:var(--gold-primary); font-size:1rem; margin-top:2px;}
.pd-disclaimer p{font-size:0.82rem; color:rgba(255,255,255,0.5); line-height:1.5; margin:0;}

@keyframes fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
@keyframes slideInToast{from{transform:translateX(100px);opacity:0;}to{transform:translateX(0);opacity:1;}}
@keyframes slideOutToast{from{transform:translateX(0);opacity:1;}to{transform:translateX(100px);opacity:0;}}
@media(max-width:900px){
    body{overflow-x:hidden;}
    #goldParticles{display:none !important;}
    .pd-grid{grid-template-columns:1fr; gap:24px;}
    .pd-gallery-main{height:320px;}
    .pd-title{font-size:1.5rem;}
    .pd-desc{font-size:0.88rem;}
    .review-summary{grid-template-columns:1fr;}
    .review-avg{border-right:none; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:20px;}
    .pd-variants{flex-wrap:wrap;}
    .pd-actions{flex-direction:row;}
    .pd-qty{justify-content:center;}
    .pd-benefits{grid-template-columns:1fr;}
    .pd-tab-nav{overflow-x:auto; -webkit-overflow-scrolling:touch;}
    .pd-tab-btn{padding:12px 16px; font-size:0.82rem;}
    .pd-disclaimer{flex-direction:row;}
    .pd-related{padding:40px 0;}
    .product-grid{grid-template-columns:repeat(2,1fr) !important; gap:14px !important;}
}
@media(max-width:600px){
    .pd-gallery-main{height:260px; border-radius:14px;}
    .pd-title{font-size:1.25rem; line-height:1.15;}
    .pd-price-sale{font-size:1.5rem;}
    .pd-price-mrp{font-size:0.9rem;}
    .pd-gallery-thumbs{gap:8px;}
    .pd-thumb{width:56px; height:56px;}
    .pd-variant{padding:8px 14px; font-size:0.82rem;}
    .pd-actions{gap:10px;}
    .pd-qty button{width:36px; height:38px;}
    .pd-qty input{width:36px; font-size:0.9rem;}
    .pd-benefit{font-size:0.75rem;}
    .pd-tab-btn{padding:10px 12px; font-size:0.75rem;}
    .pd-disclaimer{flex-direction:column; gap:8px;}
    .review-summary{padding:18px;}
    .review-avg-num{font-size:2rem;}
    .review-item{padding:18px 0;}
    .review-text{font-size:0.82rem;}
    .pd-write-review{padding:18px;}
    .pd-write-review > div[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr !important;}
    .pd-related{margin-top:40px;}
    .product-grid{grid-template-columns:1fr !important; gap:14px !important;}
}
</style>

<canvas id="goldParticles"></canvas>

<div class="container pd-hero">
    <!-- Breadcrumb -->
    <div style="font-size:0.82rem; color:rgba(255,255,255,0.4); margin-bottom:24px;">
        <a href="index.php" style="color:rgba(255,255,255,0.4); text-decoration:none;">Home</a>
        <span style="margin:0 8px;">/</span>
        <a href="category.php?slug=<?php echo $product['category_slug']; ?>" style="color:rgba(255,255,255,0.4); text-decoration:none;"><?php echo htmlspecialchars($product['category_name']); ?></a>
        <span style="margin:0 8px;">/</span>
        <span style="color:var(--gold-primary);"><?php echo htmlspecialchars($product['name']); ?></span>
    </div>

    <!-- Alerts -->
    <?php if ($review_success): ?><div style="background:rgba(46,204,113,0.06); border:1px solid rgba(46,204,113,0.15); color:#2ecc71; padding:14px 18px; border-radius:12px; margin-bottom:20px; font-size:0.9rem; font-weight:600; display:flex; align-items:center; gap:10px;"><i class="fas fa-check-circle"></i> <?php echo $review_success; ?></div><?php endif; ?>
    <?php if ($review_error): ?><div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); color:rgba(255,255,255,0.7); padding:14px 18px; border-radius:12px; margin-bottom:20px; font-size:0.9rem; font-weight:600; display:flex; align-items:center; gap:10px;"><i class="fas fa-exclamation-circle"></i> <?php echo $review_error; ?></div><?php endif; ?>

    <div class="pd-grid">
        <!-- LEFT: Gallery -->
        <div>
            <div class="pd-gallery-main">
                <img id="main-product-image" src="<?php echo htmlspecialchars($gallery[0]); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <?php if (count($gallery) > 1): ?>
                <div class="pd-gallery-thumbs">
                    <?php foreach ($gallery as $i => $img): ?>
                        <div class="pd-thumb <?php echo $i===0?'active':''; ?>" onclick="switchImg(this,'<?php echo htmlspecialchars($img); ?>')"><img src="<?php echo htmlspecialchars($img); ?>" alt="Thumbnail"></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Info -->
        <div>
            <span class="pd-badge"><?php echo htmlspecialchars($product['category_name']); ?></span>
            <h1 class="pd-title"><?php echo htmlspecialchars($product['name']); ?></h1>

            <div class="pd-rating">
                <div class="pd-rating-stars"><?php for($i=1;$i<=5;$i++):?><i class="<?php echo $i<=round($avg_rating)?'fas':'far';?> fa-star"></i><?php endfor;?></div>
                <span class="pd-rating-text"><?php echo $avg_rating; ?>/5.0 (<?php echo $total_reviews; ?> reviews)</span>
            </div>

            <div class="pd-price-row">
                <span class="pd-price-sale" id="main-sale">₹<?php echo number_format($default_variant['sale_price'],2); ?></span>
                <span class="pd-price-mrp" id="main-mrp">MRP ₹<?php echo number_format($default_variant['price'],2); ?></span>
                <?php $saved=$default_variant['price']-$default_variant['sale_price']; if($saved>0): ?>
                    <span class="pd-save">Save ₹<?php echo number_format($saved,0); ?></span>
                <?php endif; ?>
            </div>

            <p class="pd-desc"><?php echo htmlspecialchars($product['short_description']); ?></p>

            <!-- Variants -->
            <div class="pd-variant-label">Select Pack Size</div>
            <div class="pd-variants">
                <?php foreach ($variants as $v): ?>
                    <div class="pd-variant <?php echo $v['id']==$default_variant['id']?'active':''; ?>" onclick="selectVariant(this,<?php echo $v['id']; ?>,<?php echo $v['sale_price']; ?>,<?php echo $v['price']; ?>)" data-vid="<?php echo $v['id']; ?>">
                        <?php echo htmlspecialchars($v['size_capsules']); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Quantity & Add to Cart -->
            <div class="pd-actions">
                <div class="pd-qty">
                    <button type="button" onclick="changeQty(-1)">-</button>
                    <input type="text" id="pd-qty-input" value="1" readonly>
                    <button type="button" onclick="changeQty(1)">+</button>
                </div>
                <?php $ts=array_sum(array_column($variants,'stock_qty')); if($ts>0): ?>
                    <button class="btn-gold" style="flex:1; border-radius:12px; font-size:0.95rem; font-weight:700;" onclick="addToCart()"><i class="fas fa-shopping-cart"></i> Add to Cart</button>
                <?php else: ?>
                    <button style="flex:1; background:rgba(255,255,255,0.1); color:rgba(255,255,255,0.4); border:none; border-radius:12px; font-size:0.95rem; font-weight:700; cursor:not-allowed;" disabled>Out of Stock</button>
                <?php endif; ?>
            </div>

            <!-- Benefits -->
            <div class="pd-benefits">
                <div class="pd-benefit"><i class="fas fa-leaf"></i> 100% Ayurvedic</div>
                <div class="pd-benefit"><i class="fas fa-truck-fast"></i> Free Shipping</div>
                <div class="pd-benefit"><i class="fas fa-shield-halved"></i> FSSAI Certified</div>
                <div class="pd-benefit"><i class="fas fa-rotate-left"></i> Easy Returns</div>
            </div>

            <!-- Disclaimer -->
            <?php if ($product['category_slug'] === 'vitality'): ?>
                <div class="pd-disclaimer">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p><strong>Adult Use Only.</strong> For men above 18 years. Consult your healthcare practitioner before use if you have hypertension or chronic conditions.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══ TABS ═══ -->
    <div class="pd-tabs">
        <div class="pd-tab-nav">
            <button class="pd-tab-btn active" onclick="switchTab(this,'tab-desc')">Description</button>
            <button class="pd-tab-btn" onclick="switchTab(this,'tab-benefits')">Key Benefits</button>
            <button class="pd-tab-btn" onclick="switchTab(this,'tab-ingred')">Ingredients</button>
            <button class="pd-tab-btn" onclick="switchTab(this,'tab-usage')">How to Use</button>
        </div>
        <div id="tab-desc" class="pd-tab-pane active"><?php echo nl2br($product['description']); ?></div>
        <div id="tab-benefits" class="pd-tab-pane"><?php echo nl2br($product['benefits']); ?></div>
        <div id="tab-ingred" class="pd-tab-pane"><?php echo nl2br($product['ingredients']); ?></div>
        <div id="tab-usage" class="pd-tab-pane"><?php echo nl2br($product['how_to_use']); ?></div>
    </div>

    <!-- ═══ REVIEWS ═══ -->
    <div class="pd-reviews">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:30px;">
            <div style="width:42px; height:42px; border-radius:12px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.15); display:flex; align-items:center; justify-content:center; color:var(--gold-primary);"><i class="fas fa-comments"></i></div>
            <div><h2 style="font-size:1.5rem; margin:0;">Customer Reviews</h2><p style="font-size:0.82rem; color:rgba(255,255,255,0.4); margin:0;">Real feedback from verified buyers</p></div>
        </div>

        <div class="review-summary">
            <div class="review-avg">
                <div class="review-avg-num"><?php echo $avg_rating; ?></div>
                <div class="review-avg-stars"><?php for($i=1;$i<=5;$i++):?><i class="<?php echo $i<=round($avg_rating)?'fas':'far';?> fa-star"></i><?php endfor;?></div>
                <div class="review-avg-text">Based on <?php echo $total_reviews; ?> ratings</div>
            </div>
            <div class="review-bars">
                <?php for($s=5;$s>=1;$s--): $pct=$total_reviews>0?round(($rating_dist[$s]/$total_reviews)*100):0; ?>
                    <div class="review-bar-row">
                        <span style="width:40px; text-align:right; font-weight:600; color:rgba(255,255,255,0.6);"><?php echo $s; ?> ★</span>
                        <div class="review-bar-fill-bg"><div class="review-bar-fill" style="width:<?php echo $pct; ?>%"></div></div>
                        <span style="width:35px; color:rgba(255,255,255,0.35); font-size:0.82rem;"><?php echo $pct; ?>%</span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Reviews List -->
        <?php if (!empty($reviews)): foreach ($reviews as $rev): ?>
            <div class="review-item">
                <div class="review-stars"><?php for($i=1;$i<=5;$i++):?><i class="<?php echo $i<=$rev['rating']?'fas':'far';?> fa-star"></i><?php endfor;?></div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span class="review-author"><?php echo htmlspecialchars($rev['user_name']); ?></span>
                    <span class="review-date"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                </div>
                <?php if($rev['title']): ?><div style="font-weight:700; color:#fff; margin-bottom:4px;"><?php echo htmlspecialchars($rev['title']); ?></div><?php endif; ?>
                <p class="review-text"><?php echo nl2br(htmlspecialchars($rev['review_text'])); ?></p>
                <span class="review-verified"><i class="fas fa-check-circle"></i> Verified Buyer</span>
            </div>
        <?php endforeach; else: ?>
            <p style="color:rgba(255,255,255,0.4); text-align:center; padding:30px 0;">No reviews yet. Be the first to review!</p>
        <?php endif; ?>

        <!-- Write Review -->
        <div class="pd-write-review">
            <h3 style="font-size:1.2rem; margin-bottom:20px; color:#fff;">Write A Review</h3>
            <form action="product.php?slug=<?php echo htmlspecialchars($slug); ?>" method="POST">
                <label>Your Rating</label>
                <div class="rating-select-stars" id="review-stars">
                    <i class="fas fa-star active" data-value="1"></i>
                    <i class="fas fa-star active" data-value="2"></i>
                    <i class="fas fa-star active" data-value="3"></i>
                    <i class="fas fa-star active" data-value="4"></i>
                    <i class="fas fa-star active" data-value="5"></i>
                </div>
                <input type="hidden" name="rating" id="review-rating-input" value="5">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin:20px 0;">
                    <div><label>Your Name *</label><input type="text" name="user_name" required placeholder="e.g. Yuvek Verma"></div>
                    <div><label>Review Title</label><input type="text" name="title" placeholder="e.g. Highly Recommended"></div>
                </div>
                <div style="margin-bottom:20px;"><label>Your Review *</label><textarea name="review_text" rows="4" required placeholder="Share your experience..."></textarea></div>
                <button type="submit" name="submit_review" class="btn-gold" style="padding:13px 30px; border-radius:12px;"><i class="fas fa-paper-plane"></i> Submit Review</button>
            </form>
        </div>
    </div>

    <!-- ═══ RELATED ═══ -->
    <?php if (!empty($related)): ?>
    <div class="pd-related">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:30px;">
            <div style="width:42px; height:42px; border-radius:12px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.15); display:flex; align-items:center; justify-content:center; color:var(--gold-primary);"><i class="fas fa-layer-group"></i></div>
            <h2 style="font-size:1.5rem; margin:0;">Related Supplements</h2>
        </div>
        <div class="product-grid">
            <?php foreach ($related as $rel): $sr=$pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(id) as cnt FROM reviews WHERE product_id=? AND is_approved=1"); $sr->execute([$rel['id']]); $rr=$sr->fetch(); $ra=$rr['avg_rating']?round($rr['avg_rating'],1):5.0; ?>
                <div class="product-card glass-card tilt-card spotlight-card">
                    <?php $dp=$rel['max_mrp']>0?round((($rel['max_mrp']-$rel['min_price'])/$rel['max_mrp'])*100):0; if($dp>0):?><span class="badge-discount">-<?php echo $dp; ?>% OFF</span><?php endif;?>
                    <div class="tilt-shine"></div>
                    <div class="product-card-image" style="height:220px; background:radial-gradient(circle at center,rgba(212,175,55,0.06) 0%,rgba(8,12,16,0.95) 80%); padding:16px; display:flex; align-items:center; justify-content:center;">
                        <img src="<?php echo htmlspecialchars($rel['image_url']); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>" style="max-height:100%; max-width:100%; object-fit:contain; filter:drop-shadow(0 10px 20px rgba(8,12,16,0.5));">
                    </div>
                    <div style="padding:18px;">
                        <a href="product.php?slug=<?php echo $rel['slug']; ?>" style="text-decoration:none;"><h3 style="font-size:0.95rem; color:#fff; margin-bottom:6px; font-family:var(--font-heading); font-weight:700; line-height:1.3;"><?php echo htmlspecialchars($rel['name']); ?></h3></a>
                        <div style="display:flex; align-items:center; gap:5px; margin-bottom:10px;">
                            <?php for($s=1;$s<=5;$s++):?><i class="<?php echo $s<=round($ra)?'fas':'far';?> fa-star" style="color:var(--gold-light); font-size:0.72rem;"></i><?php endfor;?>
                            <span style="font-size:0.72rem; color:rgba(255,255,255,0.35);">(<?php echo $rr['cnt']; ?>)</span>
                        </div>
                        <div style="display:flex; align-items:baseline; gap:8px; margin-bottom:14px;">
                            <span style="font-size:1.15rem; font-weight:800; color:var(--gold-primary); font-family:var(--font-heading);">₹<?php echo number_format($rel['min_price'],2); ?></span>
                            <span style="font-size:0.78rem; color:rgba(255,255,255,0.3); text-decoration:line-through;">₹<?php echo number_format($rel['max_mrp'],2); ?></span>
                        </div>
                        <a href="product.php?slug=<?php echo $rel['slug']; ?>" class="btn-gold" style="width:100%; padding:10px; font-size:0.82rem; border-radius:10px; text-align:center;">View Product</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
// Gold Particles
(function(){var c=document.getElementById('goldParticles');if(!c)return;var ctx=c.getContext('2d'),p=[];function r(){c.width=window.innerWidth;c.height=window.innerHeight;}r();window.addEventListener('resize',r);for(var i=0;i<30;i++)p.push({x:Math.random()*c.width,y:Math.random()*c.height,r:Math.random()*1.8+0.4,dx:(Math.random()-0.5)*0.25,dy:(Math.random()-0.5)*0.25,o:Math.random()*0.4+0.1});function d(){ctx.clearRect(0,0,c.width,c.height);for(var i=0;i<p.length;i++){var v=p[i];ctx.beginPath();ctx.arc(v.x,v.y,v.r,0,Math.PI*2);ctx.fillStyle='rgba(212,175,55,'+v.o+')';ctx.fill();v.x+=v.dx;v.y+=v.dy;if(v.x<0||v.x>c.width)v.dx*=-1;if(v.y<0||v.y>c.height)v.dy*=-1;}requestAnimationFrame(d);}d();})();

// Gallery
function switchImg(el,src){document.querySelectorAll('.pd-thumb').forEach(t=>t.classList.remove('active'));el.classList.add('active');document.getElementById('main-product-image').src=src;}
document.getElementById('main-product-image').addEventListener('mousemove',function(e){var r=this.getBoundingClientRect();this.style.transformOrigin=((e.clientX-r.left)/r.width*100)+'% '+((e.clientY-r.top)/r.height*100)+'%';this.style.transform='scale(1.5)';});
document.getElementById('main-product-image').addEventListener('mouseleave',function(){this.style.transform='scale(1)';});

// Variant
var currentVariantId=<?php echo $default_variant['id']; ?>;
function selectVariant(el,vid,sale,mrp){document.querySelectorAll('.pd-variant').forEach(v=>v.classList.remove('active'));el.classList.add('active');currentVariantId=vid;document.getElementById('main-sale').textContent='₹'+Number(sale).toLocaleString('en-IN',{minimumFractionDigits:2});document.getElementById('main-mrp').textContent='MRP ₹'+Number(mrp).toLocaleString('en-IN',{minimumFractionDigits:2});}

// Qty
function changeQty(d){var inp=document.getElementById('pd-qty-input');var v=parseInt(inp.value)+d;if(v>=1&&v<=10)inp.value=v;}

// Tabs
function switchTab(btn,id){document.querySelectorAll('.pd-tab-btn').forEach(b=>b.classList.remove('active'));document.querySelectorAll('.pd-tab-pane').forEach(p=>p.classList.remove('active'));btn.classList.add('active');document.getElementById(id).classList.add('active');}

// Add to Cart Toast
function showAddToCartToast(){
    var existing=document.getElementById('cart-toast');
    if(existing)existing.remove();
    var toast=document.createElement('div');
    toast.id='cart-toast';
    toast.style.cssText='position:fixed;bottom:30px;right:30px;z-index:9999;background:linear-gradient(135deg,#D4AF37,#F2D06B);color:#080C10;padding:16px 24px;border-radius:12px;font-weight:700;font-size:0.9rem;display:flex;align-items:center;gap:10px;box-shadow:0 8px 30px rgba(212,175,55,0.3);animation:slideInToast 0.3s ease;';
    toast.innerHTML='<i class="fas fa-check-circle"></i> Added to cart!';
    document.body.appendChild(toast);
    setTimeout(function(){toast.style.animation='slideOutToast 0.3s ease forwards';setTimeout(function(){toast.remove();},300);},2000);
}

// Add to Cart
var csrfToken='<?php echo generate_csrf_token(); ?>';
function addToCart(){var qty=document.getElementById('pd-qty-input').value;var fd=new URLSearchParams();fd.append('action','add');fd.append('product_id','<?php echo $product["id"];?>');fd.append('variant_id',currentVariantId);fd.append('quantity',qty);fd.append('csrf_token',csrfToken);fetch('cart_api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:fd.toString()}).then(function(r){return r.json();}).then(function(d){if(d.success){showAddToCartToast();setTimeout(function(){location.reload();},1500);}else{alert(d.message||'Failed to add to cart');}});}

// Review Stars
document.getElementById('review-stars').addEventListener('click',function(e){var star=e.target.closest('i');if(!star)return;var val=parseInt(star.dataset.value);document.getElementById('review-rating-input').value=val;this.querySelectorAll('i').forEach(function(s,i){s.classList.toggle('active',i<val);});});
</script>
