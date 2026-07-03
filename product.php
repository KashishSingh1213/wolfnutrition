<?php
// product.php
require_once __DIR__ . '/includes/header.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: index.php");
    exit();
}

// Fetch Product Details
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.slug = ? AND p.is_active = 1
");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit();
}

// Fetch Product Variants
$stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY price ASC");
$stmt->execute([$product['id']]);
$variants = $stmt->fetchAll();

if (empty($variants)) {
    header("Location: index.php");
    exit();
}

// Separate default variant
$default_variant = null;
foreach ($variants as $v) {
    if ($v['is_default']) {
        $default_variant = $v;
        break;
    }
}
if (!$default_variant) $default_variant = $variants[0];

// Fetch Approved Reviews
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? AND is_approved = 1 ORDER BY created_at DESC");
$stmt->execute([$product['id']]);
$reviews = $stmt->fetchAll();

// Calculate review statistics
$total_reviews = count($reviews);
$avg_rating = 0;
$rating_dist = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];

if ($total_reviews > 0) {
    $sum_ratings = 0;
    foreach ($reviews as $rev) {
        $sum_ratings += $rev['rating'];
        if (isset($rating_dist[$rev['rating']])) {
            $rating_dist[$rev['rating']]++;
        }
    }
    $avg_rating = round($sum_ratings / $total_reviews, 1);
} else {
    $avg_rating = 5.0; // default view
}

// Handle Review Submission
$review_success = null;
$review_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $r_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
    $r_title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $r_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
    $r_rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;

    if (empty($r_name) || empty($r_text)) {
        $review_error = "Please fill in your name and review text.";
    } elseif ($r_rating < 1 || $r_rating > 5) {
        $review_error = "Invalid star rating selection.";
    } else {
        $stmt_i = $pdo->prepare("
            INSERT INTO reviews (product_id, user_id, user_name, rating, title, review_text, is_approved) 
            VALUES (?, ?, ?, ?, ?, ?, 0)
        ");
        $stmt_i->execute([$product['id'], $user_id, $r_name, $r_rating, $r_title, $r_text]);
        $review_success = "Your review has been submitted successfully and is pending admin approval.";
    }
}

// Fetch Related Products (in the same category, excluding current product)
$stmt = $pdo->prepare("
    SELECT p.*, MIN(pv.price) as max_mrp, MIN(pv.sale_price) as min_price, pv.id as default_variant_id
    FROM products p
    JOIN product_variants pv ON p.id = pv.product_id
    WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
    GROUP BY p.id
    LIMIT 3
");
$stmt->execute([$product['category_id'], $product['id']]);
$related_products = $stmt->fetchAll();

// Gallery images array
$gallery = array_filter(explode(',', $product['image_gallery']));
if (empty($gallery)) {
    $gallery = [$product['image_url']];
}
?>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <!-- Breadcrumbs -->
        <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:20px;">
            <a href="index.php">Home</a> &nbsp;/&nbsp; 
            <a href="category.php?slug=<?php echo $product['category_slug']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a> &nbsp;/&nbsp; 
            <span style="color:var(--text-primary);"><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <?php if ($review_success): ?>
            <div class="quantity-discount-widget" style="background-color:rgba(212,175,55,0.05); border-color:rgba(212,175,55,0.3); color:var(--success-color); margin-bottom:20px;">
                ✅ <?php echo htmlspecialchars($review_success); ?>
            </div>
        <?php endif; ?>
        <?php if ($review_error): ?>
            <div class="quantity-discount-widget" style="background-color:rgba(255,255,255,0.05); border-color:rgba(255,255,255,0.15); color:var(--danger-color); margin-bottom:20px;">
                ❌ <?php echo htmlspecialchars($review_error); ?>
            </div>
        <?php endif; ?>

        <!-- Product Grid Detail Layout -->
        <div class="product-detail-grid">
            <!-- Left Side: Image Gallery -->
            <div class="product-gallery">
                <div class="gallery-main" id="gallery-zoom-container">
                    <img id="main-product-image" src="<?php echo htmlspecialchars($gallery[0]); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                
                <?php if (count($gallery) > 1): ?>
                    <div class="gallery-thumbs">
                        <?php foreach ($gallery as $index => $img): ?>
                            <div class="gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>" onclick="changeMainImage(this, '<?php echo htmlspecialchars($img); ?>')">
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="Thumbnail Image">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Side: Details and Purchasing -->
            <div class="product-detail-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-detail-rating">
                    <div style="color:var(--gold-light);">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <i class="<?php echo $i <= round($avg_rating) ? 'fas' : 'far'; ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <span><?php echo $avg_rating; ?>/5.0 Stars (<?php echo $total_reviews; ?> customer reviews)</span>
                </div>

                <!-- Prices -->
                <div class="product-detail-price">
                    <span class="sale-price" id="main-price-sale">₹<?php echo number_format($default_variant['sale_price'], 2); ?></span>
                    <span class="mrp" id="main-price-mrp">MRP ₹<?php echo number_format($default_variant['price'], 2); ?></span>
                    <?php $saved = $default_variant['price'] - $default_variant['sale_price']; ?>
                    <span class="product-save-amount" id="main-save-amount">
                        <?php echo $saved > 0 ? "You save ₹" . number_format($saved, 2) : ""; ?>
                    </span>
                </div>

                <div class="product-description-short">
                    <p><?php echo htmlspecialchars($product['short_description']); ?></p>
                </div>

                <!-- Add to Cart Form -->
                <form id="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <!-- Variant Selector -->
                    <div class="variant-selector">
                        <h4>Choose Pack Size</h4>
                        <div class="variant-options">
                            <?php foreach ($variants as $index => $v): ?>
                                <input type="radio" 
                                       name="variant_id" 
                                       id="v-<?php echo $v['id']; ?>" 
                                       class="variant-option-radio" 
                                       value="<?php echo $v['id']; ?>" 
                                       data-sale-price="<?php echo $v['sale_price']; ?>" 
                                       data-mrp="<?php echo $v['price']; ?>"
                                       <?php echo $v['id'] == $default_variant['id'] ? 'checked' : ''; ?>>
                                <label for="v-<?php echo $v['id']; ?>" class="variant-option-label">
                                    <?php echo htmlspecialchars($v['size_capsules']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Quantity Discount Banner -->
                    <div class="quantity-discount-widget">
                        <i class="fas fa-percentage"></i>
                        <span>Automatic Stack Savings: Buy 2 items save 10% | Buy 3+ items save 15% storewide</span>
                    </div>

                    <!-- Quantity selection and CTA buttons -->
                    <div class="purchase-actions">
                        <div class="detail-qty-picker">
                            <button type="button" class="detail-qty-btn detail-qty-minus">-</button>
                            <input type="text" name="quantity" class="detail-qty-input" value="1" readonly>
                            <button type="button" class="detail-qty-btn detail-qty-plus">+</button>
                        </div>
                        
                        <?php 
                        $total_stock = array_sum(array_column($variants, 'stock_qty'));
                        if ($total_stock > 0): 
                        ?>
                            <button type="submit" class="btn-gold btn">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn" style="background:rgba(255,255,255,0.2); color:rgba(255,255,255,0.4); border:none; cursor:not-allowed;" disabled>
                                Out of Stock
                            </button>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Pincode Shipping Estimator -->
                <div style="border: 1px solid var(--border-color); border-radius:4px; padding:15px; background:var(--bg-card); margin-bottom:30px;">
                    <h5 style="margin-bottom:8px; font-size:0.9rem; text-transform:uppercase; color:var(--gold-muted);">Delivery Pincode Check</h5>
                    <div class="pincode-estimator">
                        <input type="text" id="pincode-input" class="form-control" placeholder="Enter Pincode (e.g. 110001)" maxlength="6">
                        <button id="pincode-check-btn" class="btn-gold" style="padding:10px 15px; font-size:0.85rem;">Check</button>
                    </div>
                    <div id="pincode-result" class="pincode-result"></div>
                </div>

                <!-- Adult Use Disclaimer (For Vitality supplements) -->
                <?php if ($product['category_slug'] === 'vitality'): ?>
                    <div class="disclaimer-box">
                        <h5><i class="fas fa-exclamation-triangle"></i> ADULT USE DISCLAIMER</h5>
                        <p style="font-size: 0.8rem; line-height: 1.4;">
                            Wolfpack is strictly for adult men (above 18 years). Shilajit and high-potency Ashwagandha are natural performance botanicals. Consult your healthcare practitioner before use if you have hypertension, cardiovascular conditions, or chronic illnesses.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Tabs (Description / Ingredients / How to Use) -->
        <section class="product-tabs">
            <div class="product-tabs-header">
                <button class="product-tab-btn active" data-target="tab-desc">Description</button>
                <button class="product-tab-btn" data-target="tab-benefits">Key Benefits</button>
                <button class="product-tab-btn" data-target="tab-ingred">Ingredients</button>
                <button class="product-tab-btn" data-target="tab-usage">How to Use</button>
            </div>
            
            <div id="tab-desc" class="product-tab-pane active">
                <div style="line-height:1.7;"><?php echo nl2br($product['description']); ?></div>
            </div>
            <div id="tab-benefits" class="product-tab-pane">
                <div style="line-height:1.7;"><?php echo nl2br($product['benefits']); ?></div>
            </div>
            <div id="tab-ingred" class="product-tab-pane">
                <div style="line-height:1.7;"><?php echo nl2br($product['ingredients']); ?></div>
            </div>
            <div id="tab-usage" class="product-tab-pane">
                <div style="line-height:1.7;"><?php echo nl2br($product['how_to_use']); ?></div>
            </div>
        </section>

        <!-- Moderated Reviews Section -->
        <section class="reviews-section" style="margin-top: 60px;">
            <div class="section-header" style="text-align:left;">
                <h2>Customer Reviews</h2>
            </div>
            
            <div class="reviews-summary">
                <div class="avg-rating-box">
                    <div class="avg-rating-num"><?php echo $avg_rating; ?></div>
                    <div class="avg-rating-stars">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <i class="<?php echo $i <= round($avg_rating) ? 'fas' : 'far'; ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <div style="color:var(--text-muted); font-size:0.9rem;">Based on <?php echo $total_reviews; ?> approved ratings</div>
                </div>

                <div class="rating-bars">
                    <?php 
                    for ($stars = 5; $stars >= 1; $stars--): 
                        $pct = 0;
                        if ($total_reviews > 0) {
                            $pct = round(($rating_dist[$stars] / $total_reviews) * 100);
                        }
                    ?>
                        <div class="rating-bar-row">
                            <span style="width: 50px; text-align:right; font-weight:600;"><?php echo $stars; ?> Star</span>
                            <div class="rating-bar-fill-wrapper">
                                <div class="rating-bar-fill" style="width: <?php echo $pct; ?>%;"></div>
                            </div>
                            <span style="width: 40px; color:var(--text-muted);"><?php echo $pct; ?>%</span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- List of Reviews -->
            <div class="reviews-list-container">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <span class="review-author"><?php echo htmlspecialchars($rev['user_name']); ?></span>
                                <div class="review-stars">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="<?php echo $i <= $rev['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="review-title"><?php echo htmlspecialchars($rev['title']); ?></div>
                            <p style="margin: 10px 0;"><?php echo nl2br(htmlspecialchars($rev['review_text'])); ?></p>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span class="review-date"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                                <span class="review-verified-badge"><i class="fas fa-check-circle"></i> Verified Buyer</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:var(--text-muted); text-align:center; padding: 20px 0;">No reviews yet. Be the first to review this product!</p>
                <?php endif; ?>
            </div>

            <!-- Submit Review Form -->
            <div class="submit-review-form">
                <h3 style="margin-bottom: 20px; font-size:1.25rem;">Write A Review</h3>
                <form action="product.php?slug=<?php echo htmlspecialchars($slug); ?>" method="POST">
                    <div class="form-group">
                        <label>Your Rating</label>
                        <div class="rating-select-stars">
                            <i class="fas fa-star active" data-value="1"></i>
                            <i class="fas fa-star active" data-value="2"></i>
                            <i class="fas fa-star active" data-value="3"></i>
                            <i class="fas fa-star active" data-value="4"></i>
                            <i class="fas fa-star active" data-value="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="review-rating-input" value="5">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="user_name">Reviewer Name</label>
                            <input type="text" name="user_name" id="user_name" class="form-control" required placeholder="e.g. Yuvek Verma">
                        </div>
                        <div class="form-group">
                            <label for="title">Review Title</label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Highly Recommended">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="review_text">Review Body</label>
                        <textarea name="review_text" id="review_text" rows="5" class="form-control" required placeholder="Describe your experience with this supplement..."></textarea>
                    </div>

                    <button type="submit" name="submit_review" class="btn-gold" style="padding:12px 30px;">Submit Review</button>
                </form>
            </div>
        </section>

        <!-- Related Products / Frequently Bought Together -->
        <?php if (!empty($related_products)): ?>
            <section style="margin-top: 60px; border-top:1px solid var(--border-color); padding-top:40px;">
                <div class="section-header" style="text-align:left; margin-bottom:30px;">
                    <h2>Related Supplements</h2>
                </div>
                <div class="product-grid">
                    <?php foreach ($related_products as $rel): 
                        // Fetch ratings
                        $stmt_r = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(id) as cnt FROM reviews WHERE product_id = ? AND is_approved = 1");
                        $stmt_r->execute([$rel['id']]);
                        $rel_r = $stmt_r->fetch();
                        $rel_avg = $rel_r['avg_rating'] ? round($rel_r['avg_rating'], 1) : 5.0;
                    ?>
                        <div class="product-card glass-card">
                            <div class="product-card-image">
                                <img src="<?php echo htmlspecialchars($rel['image_url']); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>">
                            </div>
                            <div class="product-card-info">
                                <a href="product.php?slug=<?php echo $rel['slug']; ?>">
                                    <h3 class="product-card-title"><?php echo htmlspecialchars($rel['name']); ?></h3>
                                </a>
                                <div class="product-card-rating">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="<?php echo $i <= round($rel_avg) ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                    <span>(<?php echo $rel_r['cnt']; ?>)</span>
                                </div>
                                <div class="product-card-prices">
                                    <span class="price-sale">₹<?php echo number_format($rel['min_price'], 2); ?></span>
                                    <span class="price-regular">MRP ₹<?php echo number_format($rel['max_mrp'], 2); ?></span>
                                </div>
                                <div class="product-card-action">
                                    <a href="product.php?slug=<?php echo $rel['slug']; ?>" class="btn-gold" style="width: 100%; text-align:center;">
                                        Select Options
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <!-- Thumbnail Switcher & Gallery Zoom Script -->
    <script>
        function changeMainImage(thumbElement, imgPath) {
            // Remove active from all thumbs
            document.querySelectorAll('.gallery-thumb').forEach(el => el.classList.remove('active'));
            // Add active to current
            thumbElement.classList.add('active');
            // Change main image path
            document.getElementById('main-product-image').src = imgPath;
        }

        // Simple hover zoom effect on main image
        const zoomContainer = document.getElementById('gallery-zoom-container');
        const mainImage = document.getElementById('main-product-image');
        
        if (zoomContainer && mainImage) {
            zoomContainer.addEventListener('mousemove', function(e) {
                const rect = e.target.getBoundingClientRect();
                const x = e.clientX - rect.left; //x position within the element.
                const y = e.clientY - rect.top;  //y position within the element.
                
                mainImage.style.transformOrigin = `${x}px ${y}px`;
                mainImage.style.transform = "scale(1.5)";
            });

            zoomContainer.addEventListener('mouseleave', function() {
                mainImage.style.transform = "scale(1)";
                mainImage.style.transformOrigin = "center center";
            });
        }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
