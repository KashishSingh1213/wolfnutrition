<?php
require_once __DIR__ . '/includes/header.php';
try { $stmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC"); $stmt->execute(); $categories = $stmt->fetchAll(); } catch (PDOException $e) { $categories = []; }
$products_by_category = [];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare("SELECT p.*, MIN(pv.price) as max_mrp, MIN(pv.sale_price) as min_price, pv.id as default_variant_id FROM products p JOIN product_variants pv ON p.id = pv.product_id WHERE p.category_id = ? AND p.is_active = 1 AND pv.is_default = 1 GROUP BY p.id");
    $stmt->execute([$cat['id']]); $products_by_category[$cat['slug']] = $stmt->fetchAll();
}
try { $stmt = $pdo->prepare("SELECT * FROM bundles WHERE status = 1 LIMIT 1"); $stmt->execute(); $bundle = $stmt->fetch(); } catch (PDOException $e) { $bundle = null; }
$certs = get_certificates();
$testimonials = get_testimonials(false, 5);
try { $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE status = 1 ORDER BY published_at DESC"); $stmt->execute(); $blogs = $stmt->fetchAll(); if (count($blogs) > 3) $blogs = array_slice($blogs, 0, 3); } catch (PDOException $e) { $blogs = []; }
?>

<style>
#goldParticles{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0;opacity:0.35;}

/* ── Hero Rings ── */
.hero-rings{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;}
.hero-ring{position:absolute;border-radius:50%;border:1px solid rgba(212,175,55,0.06);top:50%;left:50%;}
.hero-ring:nth-child(1){width:400px;height:400px;margin:-200px 0 0 -200px;animation:ringRotate 25s linear infinite;}
.hero-ring:nth-child(2){width:550px;height:550px;margin:-275px 0 0 -275px;animation:ringRotate 35s linear infinite reverse;border-style:dashed;}
.hero-ring:nth-child(3){width:700px;height:700px;margin:-350px 0 0 -350px;animation:ringRotate 45s linear infinite;border-color:rgba(212,175,55,0.03);}
@keyframes ringRotate{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}

/* ── Hero ── */
.hero-section{position:relative;overflow:hidden;}
.hero-section .hero-slider{position:relative;}
.hero-section .hero-slide{position:absolute;width:100%;top:0;left:0;opacity:0;transition:opacity 1s ease-in-out;z-index:1;}
.hero-section .hero-slide.active{opacity:1;position:relative;z-index:2;}
.hero-section .hero-slide img{width:100%;height:auto;display:block;}

/* ── Marquee ── */
@keyframes marqueeScroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}

/* ── Statement ── */
.statement-section{text-align:center;padding:100px 0;position:relative;overflow:hidden;}
.statement-section::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:600px;height:600px;background:radial-gradient(circle,rgba(212,175,55,0.05) 0%,transparent 60%);pointer-events:none;}
.statement-text{font-size:clamp(2rem,5vw,3.8rem);font-family:var(--font-heading);font-weight:800;text-transform:uppercase;line-height:1.1;color:#fff;position:relative;z-index:2;}
.statement-text .gold{background:var(--gold-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.statement-sub{font-size:1.05rem;color:rgba(255,255,255,0.6);margin-top:20px;max-width:600px;margin-left:auto;margin-right:auto;line-height:1.8;position:relative;z-index:2;}

/* ── Counters ── */
.counter-row{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;padding:50px 0;}
.counter-item{text-align:center;padding:30px 16px;background:rgba(255,255,255,0.02);border:1px solid rgba(212,175,55,0.08);border-radius:18px;transition:all 0.4s;position:relative;overflow:hidden;}
.counter-item:hover{border-color:var(--gold-primary);transform:translateY(-4px);box-shadow:0 12px 30px rgba(8,12,16,0.4);}
.counter-item::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--gold-gradient);opacity:0;transition:opacity 0.3s;}
.counter-item:hover::before{opacity:1;}
.counter-num{font-size:2.6rem;font-weight:800;font-family:var(--font-heading);background:var(--gold-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;line-height:1;}
.counter-label{font-size:0.72rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1.2px;margin-top:8px;font-weight:600;}

/* ── Category Tiles ── */
.category-tile{position:relative;overflow:hidden;border-radius:16px;}
.category-tile::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(212,175,55,0.15) 0%,transparent 50%);opacity:0;transition:opacity 0.4s;z-index:1;}
.category-tile:hover::before{opacity:1;}
.category-tile img{transition:transform 0.6s cubic-bezier(0.25,0.8,0.25,1);}
.category-tile:hover img{transform:scale(1.1) rotate(1deg);}

/* ── Product Card ── */
.product-card{position:relative;transition:all 0.4s cubic-bezier(0.25,0.8,0.25,1);}
.product-card:hover{transform:translateY(-8px);border-color:var(--gold-primary);box-shadow:0 20px 50px rgba(8,12,16,0.5),0 0 40px rgba(212,175,55,0.08);}
.product-card::after{content:'';position:absolute;bottom:0;left:50%;transform:translateX(-50%);width:0;height:2px;background:var(--gold-gradient);transition:width 0.4s ease;border-radius:2px;}
.product-card:hover::after{width:80%;}

/* ── Diagonal Band ── */
.diagonal-band{position:relative;overflow:hidden;}
.diagonal-band::before{content:'';position:absolute;top:-50%;right:-10%;width:400px;height:200%;background:var(--gold-gradient);opacity:0.03;transform:rotate(15deg);pointer-events:none;}

/* ── Blog Card ── */
.blog-card{transition:all 0.4s;}
.blog-card:hover{transform:translateY(-6px);border-color:var(--gold-primary);}
.blog-card:hover .blog-card-image img{transform:scale(1.08);}

/* ── Social Proof ── */
.social-proof-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:50px;}
.proof-card{background:rgba(255,255,255,0.02);border:1px solid rgba(212,175,55,0.08);border-radius:18px;padding:28px 24px;transition:all 0.4s;position:relative;overflow:hidden;}
.proof-card:hover{border-color:var(--gold-primary);transform:translateY(-4px);box-shadow:0 12px 30px rgba(8,12,16,0.3);}
.proof-card::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(212,175,55,0.04) 0%,transparent 60%);opacity:0;transition:opacity 0.3s;}
.proof-card:hover::after{opacity:1;}
.proof-stars{color:var(--gold-light);font-size:0.85rem;margin-bottom:10px;}
.proof-text{font-size:0.92rem;color:rgba(255,255,255,0.7);line-height:1.6;font-style:italic;margin-bottom:16px;position:relative;z-index:1;}
.proof-author{display:flex;align-items:center;gap:10px;position:relative;z-index:1;}
.proof-avatar{width:36px;height:36px;border-radius:50%;background:var(--gold-gradient);display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:800;color:#080C10;}
.proof-name{font-size:0.82rem;color:#fff;font-weight:700;}
.proof-tag{font-size:0.68rem;color:var(--gold-primary);font-weight:600;}

/* ── Divider ── */
.divider-wave{position:relative;width:100%;overflow:hidden;line-height:0;margin-top:-1px;}
.divider-wave svg{display:block;width:100%;height:50px;}

/* ── Tilt & Spotlight ── */
.tilt-card{transform-style:preserve-3d;perspective:1000px;}
.tilt-card .tilt-shine{position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,0.06) 0%,transparent 60%);pointer-events:none;opacity:0;transition:opacity 0.3s;}
.tilt-card:hover .tilt-shine{opacity:1;}
.spotlight-card{position:relative;overflow:hidden;}
.spotlight-card::before{content:'';position:absolute;top:var(--mouse-y,50%);left:var(--mouse-x,50%);width:280px;height:280px;background:radial-gradient(circle,rgba(212,175,55,0.08) 0%,transparent 70%);transform:translate(-50%,-50%);pointer-events:none;opacity:0;transition:opacity 0.4s;z-index:0;}
.spotlight-card:hover::before{opacity:1;}

/* ── Float Badge ── */
@keyframes floatBadge{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}

@media(max-width:900px){
    .counter-row{grid-template-columns:repeat(2,1fr);}
    .social-proof-grid{grid-template-columns:1fr;}
    .hero-section{min-height:70vh;}
    .cat-card-grid{grid-template-columns:1fr !important;}
    .cat-card-inner{grid-template-columns:1fr !important;}
    .cat-card-img{width:100% !important; min-height:180px !important;}
    .combo-card-inner{grid-template-columns:1fr !important; text-align:center !important;}
    .combo-card-left,.combo-card-right{text-align:center !important;}
    .combo-card-bottom{flex-direction:column; gap:12px; text-align:center;}
    .product-grid{grid-template-columns:repeat(2,1fr) !important;}
    .feature-grid{grid-template-columns:1fr !important;}
    .blog-grid{grid-template-columns:1fr !important;}
    .footer-grid{grid-template-columns:1fr !important; gap:30px !important;}
}
@media(max-width:600px){
    .counter-row{grid-template-columns:1fr 1fr;gap:12px;}
    .counter-num{font-size:2rem;}
    .product-grid{grid-template-columns:1fr !important;}
    .hero-slider img{height:auto; max-height:50vh;}
    .cat-card-img{min-height:140px !important;}
    .cat-card-content{padding:20px !important;}
    .combo-card-inner{padding:20px !important;}
    .combo-card-bottom{padding:0 20px 20px !important;}
    .footer-grid{gap:24px !important;}
    .newsletter-form{flex-direction:column !important;}
    .newsletter-form input,.newsletter-form button{width:100% !important;}
}
</style>

<canvas id="goldParticles"></canvas>

<!-- ═══ HERO ═══ -->
<section class="hero-section" style="min-height:auto; padding:0;">
    <div class="hero-rings">
        <div class="hero-ring"></div>
        <div class="hero-ring"></div>
        <div class="hero-ring"></div>
    </div>
    <!-- Image Slider -->
    <div class="hero-slider" style="position:relative; width:100%;">
        <div class="hero-slide active"><a href="product.php?slug=wolftox-liver-support-detox"><img src="assets/images/hero1.png" alt="WolfTox"></a></div>
        <div class="hero-slide"><a href="product.php?slug=wolfpack-unleash-the-alpha-within"><img src="assets/images/hero2.png" alt="Wolfpack"></a></div>
        <div class="hero-slide"><a href="product.php?slug=wolfpack-unleash-the-alpha-within"><img src="assets/images/hero3.png" alt="Wolfpack Performance"></a></div>
        <div class="hero-slide"><a href="product.php?slug=wolftox-liver-support-detox"><img src="assets/images/hero4.png" alt="WolfTox Detox"></a></div>
    </div>
</section>

<!-- Gold Marquee -->
<div style="overflow:hidden; background:var(--gold-gradient); padding:11px 0;">
    <div style="display:flex; white-space:nowrap; animation:marqueeScroll 25s linear infinite;">
        <?php $mq=['100% Ayurvedic','FSSAI Certified','Veggie Capsules','Free Dietitian Consult','Zero Fillers','Lab Tested','Free Shipping']; for($m=0;$m<2;$m++): foreach($mq as $item): ?>
            <span style="font-family:var(--font-heading); font-weight:800; font-size:0.78rem; color:#080C10; text-transform:uppercase; letter-spacing:2px; padding:0 32px;"><?php echo $item; ?></span>
            <span style="color:#080C10; font-size:0.55rem;">&#9670;</span>
        <?php endforeach; endfor; ?>
    </div>
</div>

<!-- ═══ BOLD STATEMENT ═══ -->
<section style="padding:80px 0 60px; position:relative; z-index:2; overflow:hidden;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:600px; height:600px; background:radial-gradient(circle,rgba(212,175,55,0.06) 0%,transparent 60%); pointer-events:none;"></div>
    <div class="container">
        <div style="text-align:center; margin-bottom:50px;">
            <span style="display:inline-block; font-size:0.68rem; font-weight:800; letter-spacing:2.5px; background:var(--gold-gradient); color:#080C10; padding:6px 18px; border-radius:20px; text-transform:uppercase; margin-bottom:22px;">Our Philosophy</span>
            <div style="font-size:clamp(2.2rem,5vw,3.8rem); font-family:var(--font-heading); font-weight:800; text-transform:uppercase; line-height:1.08; color:#fff; margin-bottom:18px;">
                Ancient Wisdom.<br>
                <span style="background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">Modern Results.</span>
            </div>
            <p style="font-size:1.05rem; color:rgba(255,255,255,0.6); max-width:620px; margin:0 auto; line-height:1.8;">We engineer complete Ayurvedic performance systems. 90 days of consistent discipline for total cellular rejuvenation.</p>
        </div>

        <!-- 3 Feature Cards -->
        <div class="feature-grid" style="display:grid; grid-template-columns:repeat(3,1fr); gap:24px; margin-bottom:40px;">
            <div class="tilt-card spotlight-card" style="background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.08); border-radius:18px; padding:30px 24px; text-align:center; position:relative; overflow:hidden; transition:all 0.4s;">
                <div class="tilt-shine"></div>
                <div style="width:56px; height:56px; border-radius:14px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.18); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; color:var(--gold-primary); font-size:1.3rem; position:relative; z-index:1;"><i class="fas fa-leaf"></i></div>
                <h4 style="font-size:1.05rem; color:#fff; margin-bottom:6px; text-transform:uppercase; font-family:var(--font-heading); font-weight:700; position:relative; z-index:1;">100% Ayurvedic</h4>
                <p style="font-size:0.85rem; color:rgba(255,255,255,0.55); line-height:1.6; position:relative; z-index:1;">Pure Himalayan botanicals. Zero synthetic compounds. Nature's strongest formulas.</p>
            </div>
            <div class="tilt-card spotlight-card" style="background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.08); border-radius:18px; padding:30px 24px; text-align:center; position:relative; overflow:hidden; transition:all 0.4s;">
                <div class="tilt-shine"></div>
                <div style="width:56px; height:56px; border-radius:14px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.18); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; color:var(--gold-primary); font-size:1.3rem; position:relative; z-index:1;"><i class="fas fa-flask"></i></div>
                <h4 style="font-size:1.05rem; color:#fff; margin-bottom:6px; text-transform:uppercase; font-family:var(--font-heading); font-weight:700; position:relative; z-index:1;">Lab Validated</h4>
                <p style="font-size:0.85rem; color:rgba(255,255,255,0.55); line-height:1.6; position:relative; z-index:1;">Triple-tested purity. Every batch verified before it reaches your doorstep.</p>
            </div>
            <div class="tilt-card spotlight-card" style="background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.08); border-radius:18px; padding:30px 24px; text-align:center; position:relative; overflow:hidden; transition:all 0.4s;">
                <div class="tilt-shine"></div>
                <div style="width:56px; height:56px; border-radius:14px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.18); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; color:var(--gold-primary); font-size:1.3rem; position:relative; z-index:1;"><i class="fas fa-user-doctor"></i></div>
                <h4 style="font-size:1.05rem; color:#fff; margin-bottom:6px; text-transform:uppercase; font-family:var(--font-heading); font-weight:700; position:relative; z-index:1;">Free Consultation</h4>
                <p style="font-size:0.85rem; color:rgba(255,255,255,0.55); line-height:1.6; position:relative; z-index:1;">Personalized guidance from certified Ayurvedic nutritionists. Always free.</p>
            </div>
        </div>

        <!-- CTA Buttons -->
        <div style="text-align:center;">
            <a href="https://wa.me/919876543210?text=Hi%20Wolf%20Nutrition,%20I%20would%20like%20to%20start%20my%2090-day%20personalized%20challenge%20program%20please." target="_blank" class="btn-gold" style="padding:15px 38px; font-weight:700; font-size:0.95rem; border-radius:30px; margin-right:12px;">Start 90-Day Challenge</a>
            <a href="about.php" class="btn-outline-gold" style="padding:14px 38px; font-weight:700; font-size:0.95rem; border-radius:30px;">Our Story</a>
        </div>
    </div>
</section>

<!-- Divider -->
<div class="divider-wave" style="position:relative; z-index:2;"><svg viewBox="0 0 1200 50" preserveAspectRatio="none"><path d="M0,0 L1200,0 L1200,25 Q900,50 600,25 Q300,0 0,25 Z" fill="rgba(212,175,55,0.03)"/></svg></div>

<!-- ═══ COUNTERS ═══ -->
<section style="padding:50px 0; position:relative; z-index:2;">
    <div class="container">
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px;">

            <!-- Counter 1 -->
            <div class="tilt-card" style="background:linear-gradient(135deg,rgba(212,175,55,0.08) 0%,rgba(8,12,16,0.95) 100%); border:1px solid rgba(212,175,55,0.15); border-radius:20px; padding:35px 20px; text-align:center; position:relative; overflow:hidden; transition:all 0.4s;">
                <div style="position:absolute; top:-30px; right:-30px; width:100px; height:100px; background:radial-gradient(circle,rgba(212,175,55,0.12) 0%,transparent 70%); pointer-events:none;"></div>
                <div style="width:50px; height:50px; border-radius:50%; background:var(--gold-gradient); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; color:#080C10; font-size:1.1rem; box-shadow:0 8px 24px rgba(212,175,55,0.25);"><i class="fas fa-users"></i></div>
                <div style="font-size:2.6rem; font-weight:800; font-family:var(--font-heading); background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; line-height:1; margin-bottom:8px;" class="counter-num" data-target="25000">0</div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:1.5px; font-weight:600;">Happy Customers</div>
            </div>

            <!-- Counter 2 -->
            <div class="tilt-card" style="background:linear-gradient(135deg,rgba(212,175,55,0.08) 0%,rgba(8,12,16,0.95) 100%); border:1px solid rgba(212,175,55,0.15); border-radius:20px; padding:35px 20px; text-align:center; position:relative; overflow:hidden; transition:all 0.4s;">
                <div style="position:absolute; top:-30px; right:-30px; width:100px; height:100px; background:radial-gradient(circle,rgba(212,175,55,0.12) 0%,transparent 70%); pointer-events:none;"></div>
                <div style="width:50px; height:50px; border-radius:50%; background:var(--gold-gradient); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; color:#080C10; font-size:1.1rem; box-shadow:0 8px 24px rgba(212,175,55,0.25);"><i class="fas fa-star"></i></div>
                <div style="font-size:2.6rem; font-weight:800; font-family:var(--font-heading); background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; line-height:1; margin-bottom:8px;" class="counter-num" data-target="98">0</div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:1.5px; font-weight:600;">% Satisfaction</div>
            </div>

            <!-- Counter 3 -->
            <div class="tilt-card" style="background:linear-gradient(135deg,rgba(212,175,55,0.08) 0%,rgba(8,12,16,0.95) 100%); border:1px solid rgba(212,175,55,0.15); border-radius:20px; padding:35px 20px; text-align:center; position:relative; overflow:hidden; transition:all 0.4s;">
                <div style="position:absolute; top:-30px; right:-30px; width:100px; height:100px; background:radial-gradient(circle,rgba(212,175,55,0.12) 0%,transparent 70%); pointer-events:none;"></div>
                <div style="width:50px; height:50px; border-radius:50%; background:var(--gold-gradient); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; color:#080C10; font-size:1.1rem; box-shadow:0 8px 24px rgba(212,175,55,0.25);"><i class="fas fa-leaf"></i></div>
                <div style="font-size:2.6rem; font-weight:800; font-family:var(--font-heading); background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; line-height:1; margin-bottom:8px;" class="counter-num" data-target="100">0</div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:1.5px; font-weight:600;">% Ayurvedic</div>
            </div>

            <!-- Counter 4 -->
            <div class="tilt-card" style="background:linear-gradient(135deg,rgba(212,175,55,0.08) 0%,rgba(8,12,16,0.95) 100%); border:1px solid rgba(212,175,55,0.15); border-radius:20px; padding:35px 20px; text-align:center; position:relative; overflow:hidden; transition:all 0.4s;">
                <div style="position:absolute; top:-30px; right:-30px; width:100px; height:100px; background:radial-gradient(circle,rgba(212,175,55,0.12) 0%,transparent 70%); pointer-events:none;"></div>
                <div style="width:50px; height:50px; border-radius:50%; background:var(--gold-gradient); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; color:#080C10; font-size:1.1rem; box-shadow:0 8px 24px rgba(212,175,55,0.25);"><i class="fas fa-truck-fast"></i></div>
                <div style="font-size:2.6rem; font-weight:800; font-family:var(--font-heading); background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; line-height:1; margin-bottom:8px;" class="counter-num" data-target="50">0</div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:1.5px; font-weight:600;">Pincode Delivery</div>
            </div>

        </div>
    </div>
</section>

<!-- Divider -->
<div class="divider-wave"><svg viewBox="0 0 1200 50" preserveAspectRatio="none"><path d="M0,0 L1200,0 L1200,25 Q900,50 600,25 Q300,0 0,25 Z" fill="rgba(212,175,55,0.03)"/></svg></div>

<!-- ═══ CATEGORIES ═══ -->
<section style="padding:70px 0; position:relative; z-index:2; background:radial-gradient(ellipse at 50% 50%,rgba(212,175,55,0.03) 0%,transparent 60%);">
    <div class="container">
        <!-- Section Header -->
        <div style="text-align:center; margin-bottom:50px;">
            <span style="display:inline-block; font-size:0.65rem; font-weight:800; letter-spacing:2.5px; color:var(--gold-primary); text-transform:uppercase; margin-bottom:12px; background:rgba(212,175,55,0.06); border:1px solid rgba(212,175,55,0.12); padding:5px 16px; border-radius:20px;">Our Ranges</span>
            <div style="font-size:clamp(1.8rem,4vw,2.8rem); font-family:var(--font-heading); font-weight:800; color:#fff; text-transform:uppercase; margin-bottom:10px;">Shop By Health Need</div>
            <p style="font-size:0.95rem; color:rgba(255,255,255,0.5); max-width:500px; margin:0 auto;">Choose your targeted wellness solution and start your transformation.</p>
        </div>

        <!-- Category Cards - Horizontal Layout -->
        <div class="cat-card-grid" style="display:flex; flex-direction:column; gap:20px;">

            <!-- Vitality - Image Left -->
            <a href="category.php?slug=vitality" class="tilt-card" style="display:grid; grid-template-columns:auto 1fr; text-decoration:none; background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.1); border-radius:24px; overflow:hidden; transition:all 0.4s; position:relative;">
                <!-- Image Side -->
                <div class="cat-card-img" style="width:320px; min-height:220px; background:linear-gradient(135deg,rgba(212,175,55,0.1) 0%,rgba(8,12,16,0.95) 100%); display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden;">
                    <div style="position:absolute; top:0; right:0; width:120px; height:120px; background:radial-gradient(circle,rgba(212,175,55,0.15) 0%,transparent 70%); pointer-events:none;"></div>
                    <img src="assets/images/products/wolfpack.png" alt="Wolfpack Vitality" style="height:180px; object-fit:contain; filter:drop-shadow(0 20px 40px rgba(8,12,16,0.6)); transition:transform 0.5s ease; position:relative; z-index:2;">
                </div>
                <!-- Content Side -->
                <div class="cat-card-content" style="padding:35px 40px; display:flex; flex-direction:column; justify-content:center; position:relative;">
                    <div style="position:absolute; top:20px; right:20px; background:var(--gold-gradient); color:#080C10; font-size:0.6rem; font-weight:800; padding:4px 12px; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px;">Best Seller</div>
                    <div style="width:44px; height:44px; border-radius:12px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.15); display:flex; align-items:center; justify-content:center; color:var(--gold-primary); font-size:1.1rem; margin-bottom:16px;"><i class="fas fa-fire"></i></div>
                    <h3 style="color:#fff; font-family:var(--font-heading); font-size:1.5rem; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; margin:0 0 8px;">Vitality Stack</h3>
                    <p style="color:rgba(255,255,255,0.55); font-size:0.9rem; line-height:1.6; margin:0 0 20px; max-width:450px;">Premium Himalayan Shilajit, Ashwagandha, and Gokshura. Formulated for peak testosterone, endurance, and raw physical performance.</p>
                    <div style="display:flex; align-items:center; gap:20px;">
                        <span style="display:inline-flex; align-items:center; gap:8px; color:var(--gold-primary); font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Explore Stack <i class="fas fa-arrow-right"></i></span>
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.35);">From ₹1,194</span>
                    </div>
                </div>
            </a>

            <!-- Liver Detox - Image Right -->
            <a href="category.php?slug=liver-detox" class="tilt-card" style="display:grid; grid-template-columns:1fr auto; text-decoration:none; background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.1); border-radius:24px; overflow:hidden; transition:all 0.4s; position:relative;">
                <!-- Content Side -->
                <div class="cat-card-content" style="padding:35px 40px; display:flex; flex-direction:column; justify-content:center; position:relative;">
                    <div style="width:44px; height:44px; border-radius:12px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.15); display:flex; align-items:center; justify-content:center; color:var(--gold-primary); font-size:1.1rem; margin-bottom:16px;"><i class="fas fa-shield-halved"></i></div>
                    <h3 style="color:#fff; font-family:var(--font-heading); font-size:1.5rem; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; margin:0 0 8px;">Liver & Detox Stack</h3>
                    <p style="color:rgba(255,255,255,0.55); font-size:0.9rem; line-height:1.6; margin:0 0 20px; max-width:450px;">Kutki, Milk Thistle, and Kalmegh. Complete liver cleanse, toxin removal, and digestive enzyme optimization.</p>
                    <div style="display:flex; align-items:center; gap:20px;">
                        <span style="display:inline-flex; align-items:center; gap:8px; color:var(--gold-primary); font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Explore Stack <i class="fas fa-arrow-right"></i></span>
                        <span style="font-size:0.78rem; color:rgba(255,255,255,0.35);">From ₹546</span>
                    </div>
                </div>
                <!-- Image Side -->
                <div class="cat-card-img" style="width:320px; min-height:220px; background:linear-gradient(135deg,rgba(8,12,16,0.95) 0%,rgba(212,175,55,0.1) 100%); display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden;">
                    <div style="position:absolute; top:0; left:0; width:120px; height:120px; background:radial-gradient(circle,rgba(212,175,55,0.15) 0%,transparent 70%); pointer-events:none;"></div>
                    <img src="assets/images/products/wolftox.png" alt="Wolftox Detox" style="height:180px; object-fit:contain; filter:drop-shadow(0 20px 40px rgba(8,12,16,0.6)); transition:transform 0.5s ease; position:relative; z-index:2;">
                </div>
            </a>

            <!-- Combo - Image Center -->
            <a href="category.php?slug=all" class="tilt-card" style="display:block; text-decoration:none; background:linear-gradient(135deg,rgba(212,175,55,0.06) 0%,rgba(8,12,16,0.95) 50%,rgba(212,175,55,0.04) 100%); border:1px solid rgba(212,175,55,0.15); border-radius:24px; overflow:hidden; transition:all 0.4s; position:relative;">
                <div style="position:absolute; top:0; left:0; right:0; height:3px; background:var(--gold-gradient);"></div>
                <div class="combo-card-inner" style="display:grid; grid-template-columns:1fr auto 1fr; gap:30px; align-items:center; padding:40px;">
                    <!-- Left Text -->
                    <div class="combo-card-left" style="text-align:right;">
                        <h3 style="color:#fff; font-family:var(--font-heading); font-size:1.3rem; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; margin:0 0 6px;">WOLFPACK</h3>
                        <p style="color:rgba(255,255,255,0.45); font-size:0.82rem; margin:0;">Vitality + Strength</p>
                    </div>
                    <!-- Center Image -->
                    <div style="display:flex; align-items:center; gap:12px;">
                        <img src="assets/images/products/wolfpack.png" alt="Wolfpack" style="height:120px; object-fit:contain; filter:drop-shadow(0 12px 25px rgba(8,12,16,0.5));">
                        <div style="width:40px; height:40px; border-radius:50%; background:var(--gold-gradient); display:flex; align-items:center; justify-content:center; color:#080C10; font-size:1.2rem; font-weight:800; flex-shrink:0;">+</div>
                        <img src="assets/images/products/wolftox.png" alt="Wolftox" style="height:120px; object-fit:contain; filter:drop-shadow(0 12px 25px rgba(8,12,16,0.5));">
                    </div>
                    <!-- Right Text -->
                    <div class="combo-card-right">
                        <h3 style="color:#fff; font-family:var(--font-heading); font-size:1.3rem; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; margin:0 0 6px;">WOLFTOX</h3>
                        <p style="color:rgba(255,255,255,0.45); font-size:0.82rem; margin:0;">Detox + Cleanse</p>
                    </div>
                </div>
                <!-- Bottom CTA -->
                <div class="combo-card-bottom" style="padding:0 40px 30px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="display:inline-flex; align-items:center; gap:8px; color:var(--gold-primary); font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">View All Combos <i class="fas fa-arrow-right"></i></span>
                    <div style="text-align:right;">
                        <span style="font-size:0.75rem; color:rgba(255,255,255,0.35); text-decoration:line-through; margin-right:6px;">₹2,998</span>
                        <span style="font-size:1.3rem; font-weight:800; color:var(--gold-primary); font-family:var(--font-heading);">₹2,699</span>
                    </div>
                </div>
            </a>

        </div>
    </div>
</section>

<!-- Divider -->
<div class="divider-wave"><svg viewBox="0 0 1200 50" preserveAspectRatio="none"><path d="M0,25 Q300,50 600,25 Q900,0 1200,25 L1200,50 L0,50 Z" fill="rgba(212,175,55,0.03)"/></svg></div>

<!-- ═══ PRODUCTS ═══ -->
<section style="padding:50px 0 60px; position:relative; z-index:2; background:linear-gradient(180deg,rgba(212,175,55,0.02) 0%,rgba(212,175,55,0.04) 50%,rgba(212,175,55,0.02) 100%);">
    <div class="container">
        <!-- Section Header -->
        <div style="text-align:center; margin-bottom:35px;">
            <span style="display:inline-block; font-size:0.65rem; font-weight:800; letter-spacing:2.5px; color:var(--gold-primary); text-transform:uppercase; margin-bottom:12px; background:rgba(212,175,55,0.06); border:1px solid rgba(212,175,55,0.12); padding:5px 16px; border-radius:20px;">Our Range</span>
            <div style="font-size:clamp(1.8rem,4vw,2.8rem); font-family:var(--font-heading); font-weight:800; color:#fff; text-transform:uppercase; margin-bottom:10px;">Our Products</div>
            <p style="font-size:0.95rem; color:rgba(255,255,255,0.5); max-width:450px; margin:0 auto;">Ayurvedic powerhouses for peak performance</p>
        </div>

        <!-- Quick Features Strip -->
        <div style="display:flex; justify-content:center; gap:12px; margin-bottom:40px; flex-wrap:wrap;">
            <?php foreach([['fa-leaf','100% Ayurvedic'],['fa-truck-fast','Free Shipping'],['fa-shield-halved','FSSAI Certified'],['fa-user-doctor','Free Consult']] as $f): ?>
            <div style="display:flex; align-items:center; gap:8px; font-size:0.78rem; color:rgba(255,255,255,0.6); background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); padding:8px 16px; border-radius:30px;">
                <div style="width:26px; height:26px; border-radius:50%; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center; color:var(--gold-primary); font-size:0.7rem;"><i class="fas <?php echo $f[0]; ?>"></i></div>
                <span style="font-weight:600;"><?php echo $f[1]; ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Tabs -->
        <div style="display:flex; justify-content:center; gap:8px; margin-bottom:40px;">
            <?php foreach ($categories as $i => $cat): ?>
                <button class="tab-btn <?php echo $i===0?'active':''; ?>" data-target="cat-<?php echo $cat['slug']; ?>" style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); color:rgba(255,255,255,0.6); padding:10px 24px; font-family:var(--font-heading); font-weight:700; font-size:0.82rem; text-transform:uppercase; letter-spacing:1px; cursor:pointer; border-radius:30px; transition:all 0.3s;">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Product Tabs -->
        <?php foreach ($categories as $i => $cat): ?>
                <div id="cat-<?php echo $cat['slug']; ?>" class="tab-pane <?php echo $i===0?'active':''; ?>">
                <div class="product-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px,1fr)); gap:24px;">
                    <?php
                    $prods = $products_by_category[$cat['slug']] ?? [];
                    if (!empty($prods)): foreach ($prods as $prod):
                        $dp = $prod['max_mrp']>0 ? round((($prod['max_mrp']-$prod['min_price'])/$prod['max_mrp'])*100) : 0;
                        $sr = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(id) as cnt FROM reviews WHERE product_id=? AND is_approved=1");
                        $sr->execute([$prod['id']]); $ri=$sr->fetch(); $ar=$ri['avg_rating']?round($ri['avg_rating'],1):5.0;
                    ?>
                        <div class="product-card glass-card tilt-card spotlight-card" style="background:rgba(255,255,255,0.03); border:1px solid rgba(212,175,55,0.08); border-radius:20px; overflow:hidden;">
                            <?php if($dp>0): ?><span class="badge-discount">-<?php echo $dp; ?>% OFF</span><?php endif; ?>
                            <div class="tilt-shine"></div>
                            <div class="product-card-image" style="height:240px; background:radial-gradient(circle at center,rgba(212,175,55,0.08) 0%,rgba(8,12,16,0.95) 80%); padding:20px; display:flex; align-items:center; justify-content:center;">
                                <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" style="max-height:100%; max-width:100%; object-fit:contain; filter:drop-shadow(0 12px 25px rgba(8,12,16,0.5)); transition:transform 0.4s ease;">
                            </div>
                            <div class="product-card-info" style="padding:20px;">
                                <a href="product.php?slug=<?php echo $prod['slug']; ?>" style="text-decoration:none;">
                                    <h3 class="product-card-title" style="font-size:1rem; color:#fff; margin-bottom:8px; font-family:var(--font-heading); font-weight:700; line-height:1.3;"><?php echo htmlspecialchars($prod['name']); ?></h3>
                                </a>
                                <div style="display:flex; align-items:center; gap:6px; margin-bottom:12px;">
                                    <?php for($s=1;$s<=5;$s++):?><i class="<?php echo $s<=round($ar)?'fas':'far';?> fa-star" style="color:var(--gold-light); font-size:0.75rem;"></i><?php endfor;?>
                                    <span style="font-size:0.75rem; color:rgba(255,255,255,0.4);">(<?php echo $ri['cnt']; ?>)</span>
                                </div>
                                <div style="display:flex; align-items:baseline; gap:10px; margin-bottom:16px;">
                                    <span style="font-size:1.25rem; font-weight:800; color:var(--gold-primary); font-family:var(--font-heading);">₹<?php echo number_format($prod['min_price'],2); ?></span>
                                    <span style="font-size:0.82rem; color:rgba(255,255,255,0.35); text-decoration:line-through;">MRP ₹<?php echo number_format($prod['max_mrp'],2); ?></span>
                                </div>
                                <button class="btn-gold quick-add-btn" style="width:100%; padding:11px; font-size:0.82rem; border-radius:12px; font-weight:700;" data-product-id="<?php echo $prod['id']; ?>" data-variant-id="<?php echo $prod['default_variant_id']; ?>"><i class="fas fa-shopping-cart"></i> Quick Add</button>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <p style="text-align:center; grid-column:1/-1; color:rgba(255,255,255,0.4);">No products found.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ═══ SOCIAL PROOF ═══ -->
<?php if (!empty($testimonials)): ?>
<?php
$featured = null;
$others = [];
foreach ($testimonials as $t) {
    if (!$featured && $t['is_featured']) {
        $featured = $t;
    } else {
        $others[] = $t;
    }
}
if (!$featured && !empty($testimonials)) {
    $featured = $testimonials[0];
    $others = array_slice($testimonials, 1);
}
?>
<section style="padding:80px 0; position:relative; z-index:2; background:radial-gradient(ellipse at 50% 50%,rgba(212,175,55,0.03) 0%,transparent 60%);">
    <div class="container">
        <!-- Section Header -->
        <div style="text-align:center; margin-bottom:50px;">
            <span style="display:inline-block; font-size:0.65rem; font-weight:800; letter-spacing:2.5px; color:var(--gold-primary); text-transform:uppercase; margin-bottom:12px; background:rgba(212,175,55,0.06); border:1px solid rgba(212,175,55,0.12); padding:5px 16px; border-radius:20px;">Testimonials</span>
            <div style="font-size:clamp(2rem,4.5vw,3rem); font-family:var(--font-heading); font-weight:800; color:#fff; text-transform:uppercase; margin-bottom:10px;">What The Pack Says</div>
            <p style="font-size:1rem; color:rgba(255,255,255,0.5);">Real reviews from real customers who transformed their lives</p>
        </div>

        <?php if ($featured): ?>
        <!-- Featured Review (Big Card) -->
        <div class="tilt-card" style="background:linear-gradient(135deg,rgba(212,175,55,0.06) 0%,rgba(8,12,16,0.95) 40%); border:1px solid rgba(212,175,55,0.15); border-radius:24px; padding:45px 50px; margin-bottom:24px; position:relative; overflow:hidden;">
            <div style="position:absolute; top:-50px; right:-50px; width:200px; height:200px; background:radial-gradient(circle,rgba(212,175,55,0.1) 0%,transparent 70%); pointer-events:none;"></div>
            <div style="display:grid; grid-template-columns:1fr auto; gap:40px; align-items:center;">
                <div>
                    <div style="font-size:4rem; line-height:1; color:rgba(212,175,55,0.12); font-family:Georgia,serif; margin-bottom:16px;">"</div>
                    <div style="display:flex; gap:4px; margin-bottom:18px;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star" style="color:var(--gold-primary); font-size:1rem;"></i>
                        <?php endfor; ?>
                    </div>
                    <p style="font-size:1.1rem; color:rgba(255,255,255,0.8); line-height:1.75; margin-bottom:24px; font-style:italic;"><?php echo htmlspecialchars($featured['testimonial_text']); ?></p>
                    <div style="display:flex; align-items:center; gap:16px;">
                        <div style="width:52px; height:52px; border-radius:50%; background:var(--gold-gradient); display:flex; align-items:center; justify-content:center; overflow:hidden;">
                            <?php if (!empty($featured['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($featured['avatar_url']); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <span style="font-size:0.9rem; font-weight:800; color:#080C10;"><?php echo strtoupper(substr($featured['customer_name'], 0, 2)); ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div style="font-size:1rem; color:#fff; font-weight:700;"><?php echo htmlspecialchars($featured['customer_name']); ?></div>
                            <div style="font-size:0.78rem; color:var(--gold-primary); font-weight:600;"><?php echo htmlspecialchars($featured['customer_title'] ?: 'Verified Buyer'); ?></div>
                        </div>
                    </div>
                </div>
                <!-- Product Image -->
                <div style="width:180px; height:180px; display:flex; align-items:center; justify-content:center; position:relative;">
                    <div style="position:absolute; width:160px; height:160px; border-radius:50%; background:radial-gradient(circle,rgba(212,175,55,0.12) 0%,transparent 70%);"></div>
                    <img src="assets/images/products/wolfpack.png" alt="WOLFPACK" style="height:150px; object-fit:contain; filter:drop-shadow(0 15px 30px rgba(8,12,16,0.5)); position:relative; z-index:2;">
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($others)): ?>
        <!-- Smaller Review Cards -->
        <div style="display:grid; grid-template-columns:repeat(<?php echo count($others) > 3 ? 3 : count($others); ?>,1fr); gap:20px;">
            <?php foreach (array_slice($others, 0, 3) as $t): ?>
            <div class="tilt-card" style="background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.08); border-radius:20px; padding:28px 24px; position:relative; overflow:hidden; transition:all 0.4s;">
                <div style="position:absolute; top:0; left:0; right:0; height:2px; background:var(--gold-gradient); opacity:0; transition:opacity 0.3s;"></div>
                <div style="display:flex; gap:4px; margin-bottom:14px;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star" style="color:<?php echo $i <= $t['rating'] ? 'var(--gold-primary)' : 'rgba(255,255,255,0.1)'; ?>; font-size:0.8rem;"></i>
                    <?php endfor; ?>
                </div>
                <p style="font-size:0.88rem; color:rgba(255,255,255,0.65); line-height:1.6; margin-bottom:20px; font-style:italic;">"<?php echo htmlspecialchars($t['testimonial_text']); ?>"</p>
                <div style="display:flex; align-items:center; gap:12px; padding-top:14px; border-top:1px solid rgba(255,255,255,0.05);">
                    <div style="width:38px; height:38px; border-radius:50%; background:var(--gold-gradient); display:flex; align-items:center; justify-content:center; overflow:hidden;">
                        <?php if (!empty($t['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($t['avatar_url']); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <span style="font-size:0.7rem; font-weight:800; color:#080C10;"><?php echo strtoupper(substr($t['customer_name'], 0, 2)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div style="font-size:0.85rem; color:#fff; font-weight:700;"><?php echo htmlspecialchars($t['customer_name']); ?></div>
                        <div style="font-size:0.68rem; color:var(--gold-primary); font-weight:600;"><?php echo htmlspecialchars($t['customer_title'] ?: 'Verified Buyer'); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Trust Stats -->
        <div style="display:flex; justify-content:center; gap:50px; margin-top:45px; padding-top:35px; border-top:1px solid rgba(255,255,255,0.05);">
            <div style="text-align:center;">
                <div style="font-size:1.6rem; font-weight:800; color:var(--gold-primary); font-family:var(--font-heading);">4.9/5</div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:1px; font-weight:600;">Average Rating</div>
            </div>
            <div style="width:1px; background:rgba(255,255,255,0.06);"></div>
            <div style="text-align:center;">
                <div style="font-size:1.6rem; font-weight:800; color:var(--gold-primary); font-family:var(--font-heading);">500+</div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:1px; font-weight:600;">5-Star Reviews</div>
            </div>
            <div style="width:1px; background:rgba(255,255,255,0.06);"></div>
            <div style="text-align:center;">
                <div style="font-size:1.6rem; font-weight:800; color:var(--gold-primary); font-family:var(--font-heading);">25K+</div>
                <div style="font-size:0.7rem; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:1px; font-weight:600;">Happy Customers</div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══ COMBO BUNDLE ═══ -->
<?php if ($bundle): ?>
<section style="padding:60px 0; position:relative; z-index:2; background:linear-gradient(180deg,rgba(212,175,55,0.04) 0%,rgba(212,175,55,0.02) 100%);">
    <div class="container">
        <!-- Section Header -->
        <div style="text-align:center; margin-bottom:45px;">
            <span style="display:inline-block; font-size:0.65rem; font-weight:800; letter-spacing:2.5px; color:var(--gold-primary); text-transform:uppercase; margin-bottom:12px; background:rgba(212,175,55,0.06); border:1px solid rgba(212,175,55,0.12); padding:5px 16px; border-radius:20px;">Combo Offer</span>
            <div style="font-size:clamp(1.8rem,4vw,2.8rem); font-family:var(--font-heading); font-weight:800; color:#fff; text-transform:uppercase; margin-bottom:10px;">Build Your Wellness Stack</div>
            <p style="font-size:1rem; color:rgba(255,255,255,0.5);">Combined power for peak testosterone & total liver detox</p>
        </div>

        <!-- Bundle Grid -->
        <div style="display:grid; grid-template-columns:1fr auto 1fr auto 1.3fr; gap:24px; align-items:stretch;">

            <!-- Product 1 -->
            <div class="tilt-card" style="background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.1); border-radius:20px; padding:32px 24px; text-align:center; position:relative; overflow:hidden; transition:all 0.4s;">
                <div style="display:flex; justify-content:center; margin-bottom:18px;">
                    <img src="assets/images/products/wolfpack.png" alt="Wolfpack" style="height:170px; object-fit:contain; filter:drop-shadow(0 12px 25px rgba(8,12,16,0.5));">
                </div>
                <h3 style="color:#fff; font-size:1.15rem; font-weight:800; text-transform:uppercase; font-family:var(--font-heading); margin-bottom:6px;">WOLFPACK</h3>
                <p style="color:var(--text-muted); font-size:0.82rem; margin-bottom:4px;">Vitality & Strength</p>
                <p style="color:var(--gold-primary); font-size:0.8rem; font-weight:600;">60 Veggie Capsules</p>
            </div>

            <!-- Plus Connector -->
            <div style="display:flex; align-items:center; justify-content:center;">
                <div style="width:50px; height:50px; border-radius:50%; background:var(--gold-gradient); display:flex; align-items:center; justify-content:center; color:#080C10; font-size:1.5rem; font-weight:800; box-shadow:0 8px 20px rgba(212,175,55,0.25);">+</div>
            </div>

            <!-- Product 2 -->
            <div class="tilt-card" style="background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.1); border-radius:20px; padding:32px 24px; text-align:center; position:relative; overflow:hidden; transition:all 0.4s;">
                <div style="display:flex; justify-content:center; margin-bottom:18px;">
                    <img src="assets/images/products/wolftox.png" alt="Wolftox" style="height:170px; object-fit:contain; filter:drop-shadow(0 12px 25px rgba(8,12,16,0.5));">
                </div>
                <h3 style="color:#fff; font-size:1.15rem; font-weight:800; text-transform:uppercase; font-family:var(--font-heading); margin-bottom:6px;">WOLFTOX</h3>
                <p style="color:var(--text-muted); font-size:0.82rem; margin-bottom:4px;">Liver Support & Detox</p>
                <p style="color:var(--gold-primary); font-size:0.8rem; font-weight:600;">60 Veggie Capsules</p>
            </div>

            <!-- Equals Connector -->
            <div style="display:flex; align-items:center; justify-content:center;">
                <div style="width:50px; height:50px; border-radius:50%; background:var(--gold-gradient); display:flex; align-items:center; justify-content:center; color:#080C10; font-size:1.5rem; font-weight:800; box-shadow:0 8px 20px rgba(212,175,55,0.25);">=</div>
            </div>

            <!-- Combo Result -->
            <div class="tilt-card" style="background:linear-gradient(135deg,rgba(212,175,55,0.08) 0%,rgba(8,12,16,0.95) 100%); border:1px solid rgba(212,175,55,0.2); border-radius:20px; padding:32px 28px; text-align:center; position:relative; overflow:hidden; transition:all 0.4s;">
                <div style="position:absolute; top:-30px; right:-30px; width:120px; height:120px; background:radial-gradient(circle,rgba(212,175,55,0.12) 0%,transparent 70%); pointer-events:none;"></div>
                <div style="position:absolute; top:0; left:0; right:0; height:3px; background:var(--gold-gradient);"></div>
                <span style="display:inline-block; background:var(--gold-gradient); color:#080C10; font-size:0.65rem; font-weight:800; padding:4px 14px; border-radius:16px; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:14px;">Save ₹299 (10% OFF)</span>
                <h3 style="color:#fff; font-size:1.2rem; font-weight:800; text-transform:uppercase; font-family:var(--font-heading); margin-bottom:12px;">Wolf Stack Combo</h3>
                <div style="margin-bottom:16px;">
                    <span style="text-decoration:line-through; font-size:0.95rem; color:rgba(255,255,255,0.35); margin-right:6px;">₹2,998</span>
                    <span style="font-size:2rem; font-weight:800; color:var(--gold-primary); font-family:var(--font-heading);">₹<?php echo number_format($bundle['combo_price'],2); ?></span>
                </div>
                <p style="font-size:0.82rem; color:rgba(255,255,255,0.5); margin-bottom:20px;">Full 30-day program. Both formulas, synergized.</p>
                <button class="btn-gold" id="add-bundle-btn" data-bundle-id="<?php echo $bundle['id']; ?>" style="width:100%; padding:13px; border-radius:12px; font-size:0.88rem; font-weight:700;"><i class="fas fa-cubes"></i> Add Stack to Cart</button>
            </div>

        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══ WHY SHOP WITH US ═══ -->
<section style="padding:100px 0; background:radial-gradient(circle at 75% 50%,rgba(212,175,55,0.04) 0%,transparent 70%); overflow:hidden; position:relative; z-index:2;">
    <div class="container">
        <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:70px; align-items:center;">
            <!-- Phone Mockup -->
            <div style="position:relative; width:400px; margin:0 auto; display:flex; justify-content:center;">
                <div style="position:absolute; bottom:-15px; left:-10px; width:250px; height:250px; background:var(--gold-gradient); opacity:0.06; border-radius:30px;"></div>
                <div style="width:320px; height:640px; background:#080C10; border:5px solid #121212; padding:8px; border-radius:44px; box-shadow:0 40px 80px -20px rgba(8,12,16,0.9), 0 0 30px rgba(212,175,55,0.06); position:relative; overflow:hidden; display:flex; flex-direction:column; z-index:5;">
                    <div style="position:absolute; top:12px; left:50%; transform:translateX(-50%); width:100px; height:24px; background:#080C10; border-radius:12px; z-index:10; display:flex; align-items:center; justify-content:center; gap:6px;"><div style="width:30px; height:3px; background:#121212; border-radius:2px;"></div><div style="width:5px; height:5px; background:radial-gradient(circle at 35% 35%,rgba(212,175,55,0.5),#080C10); border-radius:50%;"></div></div>
                    <div style="position:absolute; inset:0; background:linear-gradient(135deg,rgba(255,255,255,0.06) 0%,rgba(255,255,255,0.02) 45%,transparent 46%); pointer-events:none; z-index:9; border-radius:40px;"></div>
                    <div style="flex:1; display:flex; flex-direction:column; padding:38px 14px 14px 14px;">
                        <div style="display:flex; justify-content:space-between; padding:2px 16px; font-size:0.58rem; color:rgba(255,255,255,0.3); font-weight:700; font-family:sans-serif; margin-bottom:10px;"><span>09:41</span><div style="display:flex; gap:5px; align-items:center;"><i class="fas fa-signal"></i><i class="fas fa-wifi"></i><i class="fas fa-battery-full"></i></div></div>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:0 14px 8px; border-bottom:1px solid rgba(255,255,255,0.03); margin-bottom:12px;"><i class="fas fa-bars" style="color:var(--gold-primary); font-size:0.8rem;"></i><span style="font-size:0.82rem; font-weight:800; color:#fff; font-family:var(--font-heading);">WOLF <span style="color:var(--gold-primary);">NUTRITION</span></span><div style="position:relative;"><i class="fas fa-shopping-bag" style="color:#fff; font-size:0.8rem;"></i><span style="position:absolute; top:-5px; right:-5px; background:var(--gold-primary); color:#080C10; font-size:0.45rem; width:11px; height:11px; border-radius:50%; display:flex; justify-content:center; align-items:center; font-weight:800;">2</span></div></div>
                        <div style="height:190px; background:radial-gradient(circle at 50% 50%,rgba(212,175,55,0.12) 0%,transparent 80%); border:1px solid rgba(255,255,255,0.03); border-radius:16px; margin:0 14px 10px; position:relative; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center;"><span style="position:absolute; top:10px; left:10px; background:var(--gold-gradient); color:#080C10; font-size:0.5rem; font-weight:800; padding:2px 7px; border-radius:8px; text-transform:uppercase; font-family:var(--font-heading);">Best Seller</span><img src="assets/images/products/wolfpack.png" alt="Wolfpack" style="height:110px; object-fit:contain; filter:drop-shadow(0 12px 20px rgba(8,12,16,0.5)); animation:phoneProductFloat 4s ease-in-out infinite;"><span style="font-size:0.78rem; font-weight:700; color:#fff; margin-top:6px; font-family:var(--font-heading);">WOLFPACK Vitality</span><div style="position:absolute; bottom:8px; display:flex; gap:3px;"><div style="width:10px; height:3px; border-radius:2px; background:var(--gold-primary);"></div><div style="width:4px; height:4px; border-radius:50%; background:rgba(255,255,255,0.2);"></div><div style="width:4px; height:4px; border-radius:50%; background:rgba(255,255,255,0.2);"></div></div></div>
                        <div style="font-size:0.65rem; font-weight:800; color:#fff; text-align:left; margin:4px 14px 8px; text-transform:uppercase; letter-spacing:0.5px;">Shop Range</div>
                        <div style="display:flex; justify-content:space-between; padding:0 14px; gap:8px;">
                            <?php foreach([['wolfpack.png','Vitality'],['wolftox.png','Detox'],['wolfpack_wolftox_combo.png','Combos']] as $mc): ?>
                            <div style="display:flex; flex-direction:column; align-items:center; gap:4px;"><div style="width:46px; height:46px; background:rgba(255,255,255,0.02); border:1px solid rgba(212,175,55,0.12); border-radius:50%; display:flex; align-items:center; justify-content:center; padding:5px;"><img src="assets/images/products/<?php echo $mc[0]; ?>" alt="<?php echo $mc[1]; ?>" style="width:100%; height:100%; object-fit:contain;"></div><span style="font-size:0.5rem; color:rgba(255,255,255,0.5); font-weight:600;"><?php echo $mc[1]; ?></span></div>
                            <?php endforeach; ?>
                        </div>
                        <div style="display:grid; grid-template-columns:repeat(4,1fr); padding:10px 8px; border-top:1px solid rgba(255,255,255,0.03); background:#080C10; margin-top:auto; text-align:center;">
                            <div style="color:var(--gold-primary);"><i class="fas fa-home" style="font-size:0.75rem;"></i><br><span style="font-size:0.45rem; font-weight:700;">Home</span></div>
                            <div style="color:rgba(255,255,255,0.35);"><i class="fas fa-capsules" style="font-size:0.75rem;"></i><br><span style="font-size:0.45rem; font-weight:700;">Shop</span></div>
                            <div style="color:rgba(255,255,255,0.35);"><i class="fas fa-user-doctor" style="font-size:0.75rem;"></i><br><span style="font-size:0.45rem; font-weight:700;">Consult</span></div>
                            <div style="color:rgba(255,255,255,0.35);"><i class="fas fa-user" style="font-size:0.75rem;"></i><br><span style="font-size:0.45rem; font-weight:700;">Profile</span></div>
                        </div>
                    </div>
                </div>
                <!-- Floating Card 1 -->
                <div style="position:absolute; top:70px; right:-80px; width:210px; background:rgba(18,18,18,0.95); backdrop-filter:blur(16px); border:1px solid var(--gold-primary); border-radius:14px; padding:14px; box-shadow:0 25px 50px rgba(8,12,16,0.8); z-index:15; animation:floatBadge 5s ease-in-out infinite;">
                    <span style="display:inline-block; background:rgba(212,175,55,0.12); color:var(--gold-primary); font-size:0.55rem; font-weight:800; padding:2px 7px; border-radius:16px; text-transform:uppercase; border:1px solid rgba(212,175,55,0.15); margin-bottom:8px;">Ultimate Offer</span>
                    <div style="display:flex; gap:10px; align-items:center; margin-bottom:10px;"><img src="assets/images/products/wolftox.png" alt="Wolftox" style="width:50px; height:50px; object-fit:contain; filter:drop-shadow(0 6px 10px rgba(8,12,16,0.3));"><div><div style="font-size:0.75rem; font-weight:700; color:#fff;">WOLFTOX Detox</div><div style="color:var(--gold-light); font-size:0.6rem; margin-top:2px;"><i class="fas fa-star"></i> 4.9 (24)</div></div></div>
                    <div style="display:flex; align-items:baseline; gap:5px; margin-bottom:8px;"><span style="font-size:1rem; font-weight:800; color:var(--gold-primary);">₹899</span><span style="font-size:0.7rem; text-decoration:line-through; color:var(--text-muted);">₹999</span></div>
                    <button class="btn-gold" style="width:100%; padding:7px; font-size:0.7rem; font-weight:700; border-radius:8px;" onclick="location.href='product.php?slug=wolftox-liver-support-detox'">Add to Cart</button>
                </div>
                <!-- Floating Card 2 -->
                <div style="position:absolute; bottom:60px; left:-80px; width:220px; background:rgba(18,18,18,0.95); backdrop-filter:blur(16px); border:1px solid rgba(212,175,55,0.25); border-radius:14px; padding:14px; box-shadow:0 25px 50px rgba(8,12,16,0.8); z-index:15; display:flex; flex-direction:column; gap:8px; animation:floatBadge 6s ease-in-out infinite;">
                    <span style="font-size:0.7rem; font-weight:700; color:var(--gold-primary); text-transform:uppercase; letter-spacing:0.5px;">Goal-focused Plans</span>
                    <span style="font-size:0.78rem; font-weight:600; color:#fff; line-height:1.3;">Get your first wellness call free!</span>
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:3px;"><img src="assets/images/dietitian_avatar.png" alt="Dietitian" style="width:32px; height:32px; border-radius:50%; border:1.5px solid var(--gold-primary); object-fit:cover;"><div><div style="font-size:0.7rem; color:#fff; font-weight:700;">Shalini Sen</div><div style="font-size:0.55rem; color:var(--text-muted);">Certified Dietitian</div></div></div>
                    <a href="https://wa.me/919876543210?text=Hi%20Wolf%20Nutrition,%20I%20would%20like%20to%20book%20a%20free%20dietitian%20consultation%20please." target="_blank" class="btn-outline-gold" style="width:100%; text-align:center; padding:7px; font-size:0.7rem; font-weight:700; border-radius:8px; display:block;">Consult Free</a>
                </div>
            </div>
            <!-- Right Column -->
            <div>
                <span style="display:inline-block; font-size:0.85rem; color:var(--gold-primary); text-transform:uppercase; letter-spacing:2px; margin-bottom:10px; font-weight:700;">Designed for High Performance</span>
                <h2 style="font-size:2.6rem; text-transform:uppercase; margin-bottom:22px; line-height:1.12; font-weight:800; font-family:var(--font-heading);">Unleash The Power Of Pure Wellness</h2>
                <p style="font-size:1.02rem; color:rgba(255,255,255,0.65); line-height:1.7; margin-bottom:35px;">We combine ancient Ayurvedic secrets with modern sports science to deliver daily stacks that fuel strength and detox your liver.</p>
                <div style="display:flex; flex-direction:column; gap:28px; margin-bottom:35px;">
                    <?php foreach([['fa-flask','100% Transparent Formulations','No hidden ingredients, zero fillers. Full disclosure of every premium extract.'],['fa-user-doctor','Free Certified Expert Guidance','Consult 1-on-1 with our certified health coaches for a personalized regimen.'],['fa-truck-fast','Prepaid Rewards & Fast Delivery','Free express shipping and additional prepaid cashbacks on all orders.']] as $b): ?>
                    <div style="display:flex; gap:22px; align-items:flex-start;">
                        <div style="width:52px; height:52px; background:rgba(212,175,55,0.06); border:1px solid rgba(212,175,55,0.18); border-radius:14px; display:flex; align-items:center; justify-content:center; color:var(--gold-primary); font-size:1.3rem; flex-shrink:0; box-shadow:var(--gold-glow); transition:all 0.3s;"><i class="fas <?php echo $b[0]; ?>"></i></div>
                        <div><h4 style="font-size:1.15rem; color:#fff; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.5px;"><?php echo $b[1]; ?></h4><p style="font-size:0.92rem; color:rgba(255,255,255,0.6); line-height:1.6;"><?php echo $b[2]; ?></p></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex; gap:18px; flex-wrap:wrap;">
                    <a href="category.php?slug=all" class="btn-gold" style="padding:15px 34px; font-size:0.92rem; font-weight:700;">Explore Products</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══ TRUST ═══ -->
<section class="container" style="margin-top:80px; position:relative; z-index:2;">
    <div class="trust-strip" style="border-radius:20px; padding:42px 28px;">
        <?php foreach([['fa-certificate','FSSAI Certified','License No. 22126022000063'],['fa-leaf','100% Ayurvedic','Pure Himalayan botanicals'],['fa-shield-halved','Veggie Capsules','100% clean, zero fillers']] as $t): ?>
        <div class="trust-item tilt-card spotlight-card">
            <div class="tilt-shine"></div>
            <div style="width:62px; height:62px; border-radius:50%; background:var(--gold-gradient); display:flex; align-items:center; justify-content:center; margin-bottom:16px; box-shadow:0 8px 22px rgba(212,175,55,0.25); position:relative; z-index:1;"><i class="fas <?php echo $t[0]; ?>" style="font-size:1.4rem; color:#080C10;"></i></div>
            <h4 style="font-size:1.1rem; margin-bottom:5px; position:relative; z-index:1;"><?php echo $t[1]; ?></h4>
            <p style="font-size:0.82rem; position:relative; z-index:1;"><?php echo $t[2]; ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ═══ BRAND PHILOSOPHY ═══ -->
<section class="container" style="margin:70px auto; position:relative; z-index:2;">
    <div style="display:grid; grid-template-columns:1fr 1fr; align-items:center; border-radius:22px; overflow:hidden; border:1px solid rgba(212,175,55,0.1); box-shadow:0 20px 50px rgba(8,12,16,0.5); background:#121212;">
        <div style="padding:48px 42px;">
            <span style="display:inline-block; font-size:0.68rem; font-weight:800; color:var(--gold-primary); text-transform:uppercase; letter-spacing:2px; margin-bottom:14px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.12); padding:5px 13px; border-radius:20px;">Our Philosophy</span>
            <h2 style="font-size:2.1rem; text-transform:uppercase; margin-bottom:16px; background:var(--gold-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; line-height:1.2; font-family:var(--font-heading);">Bridging Ancient Wisdom & Modern Science</h2>
            <p style="margin-bottom:24px; font-size:1.02rem; color:rgba(255,255,255,0.65); line-height:1.7;">We source the highest grade Shilajit, Ashwagandha, Kutki and Gokshura to formulate active wellness stacks for those who refuse to settle.</p>
            <a href="about.php" class="btn-gold" style="padding:13px 30px; font-size:0.88rem; border-radius:30px;"><i class="fas fa-arrow-right"></i> Know Our Story</a>
        </div>
        <div style="height:100%; min-height:310px; background-image:url('assets/images/logo.png'); background-size:contain; background-position:center; background-repeat:no-repeat; background-color:#080C10; border-left:1px solid rgba(212,175,55,0.06); position:relative;">
            <div style="position:absolute; inset:0; background:radial-gradient(circle at center,rgba(212,175,55,0.05) 0%,transparent 70%);"></div>
        </div>
    </div>
</section>

<!-- ═══ CERTIFICATES ═══ -->
<?php if (!empty($certs)): ?>
<section class="container" style="margin-bottom:60px; position:relative; z-index:2;">
    <div class="section-header"><h2>Quality Certificates</h2><p>Our quality and safety registrations</p></div>
    <div class="cert-gallery" style="<?php echo count($certs) === 1 ? 'display:flex; justify-content:center;' : ''; ?>">
        <?php foreach ($certs as $cert):
            $cert_is_pdf = strtolower(pathinfo($cert['image_url'], PATHINFO_EXTENSION)) === 'pdf';
        ?>
            <div class="cert-item tilt-card spotlight-card" style="<?php echo count($certs) === 1 ? 'max-width:380px; width:100%;' : ''; ?>"><div class="tilt-shine"></div>
                <a href="<?php echo $cert_is_pdf ? htmlspecialchars($cert['image_url']) : 'certificates.php'; ?>" <?php echo $cert_is_pdf ? 'target="_blank"' : ''; ?> style="text-decoration:none;">
                    <?php if ($cert_is_pdf): ?>
                        <div style="width:100%; height:180px; background:linear-gradient(135deg, rgba(212,175,55,0.08) 0%, rgba(212,175,55,0.02) 100%); border:1px solid rgba(212,175,55,0.2); border-radius:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:12px; transition:all 0.3s;" onmouseover="this.style.borderColor='rgba(212,175,55,0.4)'" onmouseout="this.style.borderColor='rgba(212,175,55,0.2)'">
                            <div style="width:60px; height:60px; border-radius:50%; background:rgba(212,175,55,0.1); display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-file-pdf" style="font-size:1.8rem; color:#D4AF37;"></i>
                            </div>
                            <span style="font-size:0.8rem; color:rgba(255,255,255,0.6); font-weight:500;">Click to view certificate</span>
                        </div>
                    <?php else: ?>
                        <img src="<?php echo htmlspecialchars($cert['image_url']); ?>" alt="<?php echo htmlspecialchars($cert['title']); ?>">
                    <?php endif; ?>
                </a>
                <h4 style="margin-top:15px; text-align:center;"><?php echo htmlspecialchars($cert['title']); ?></h4>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ═══ BLOG ═══ -->
<?php if (!empty($blogs)): ?>
<section class="container" style="margin-bottom:60px; position:relative; z-index:2;">
    <div class="section-header"><h2>The Wellness Pack Blog</h2><p>Scientific insights & Ayurvedic guides</p></div>
    <div class="blog-grid">
        <?php foreach ($blogs as $blog): ?>
            <div class="blog-card tilt-card spotlight-card"><div class="tilt-shine"></div>
                <div class="blog-card-image"><img src="<?php echo htmlspecialchars($blog['cover_image'] ?: 'assets/images/blog/default.png'); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>"><span class="blog-card-badge"><?php echo htmlspecialchars($blog['category_tag']); ?></span></div>
                <div class="blog-card-content"><div class="blog-card-date"><?php echo date('M d, Y', strtotime($blog['published_at'])); ?></div><a href="blog-post.php?slug=<?php echo $blog['slug']; ?>"><h3 class="blog-card-title"><?php echo htmlspecialchars($blog['title']); ?></h3></a><p class="blog-card-excerpt"><?php $text=strip_tags($blog['body']); echo htmlspecialchars(strlen($text)>100?substr($text,0,97).'...':$text); ?></p><a href="blog-post.php?slug=<?php echo $blog['slug']; ?>" class="blog-card-link">Read Article <i class="fas fa-arrow-right"></i></a></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ═══ NEWSLETTER ═══ -->
<section class="container" style="margin-bottom:60px; position:relative; z-index:2;">
    <div style="background:linear-gradient(135deg,rgba(212,175,55,0.07) 0%,rgba(8,12,16,0.95) 50%,rgba(212,175,55,0.04) 100%); border:1px solid rgba(212,175,55,0.12); border-radius:22px; padding:55px 50px; text-align:center; position:relative; overflow:hidden;">
        <div style="position:absolute; top:-60px; right:-60px; width:200px; height:200px; background:radial-gradient(circle,rgba(212,175,55,0.08) 0%,transparent 70%); pointer-events:none;"></div>
        <div style="position:absolute; bottom:-60px; left:-60px; width:200px; height:200px; background:radial-gradient(circle,rgba(212,175,55,0.08) 0%,transparent 70%); pointer-events:none;"></div>
        <h2 style="font-size:2rem; text-transform:uppercase; margin-bottom:8px; font-family:var(--font-heading); position:relative; z-index:2;">Join the Wolf Pack</h2>
        <p style="color:rgba(255,255,255,0.65); font-size:0.95rem; margin-bottom:28px; max-width:460px; margin-left:auto; margin-right:auto; position:relative; z-index:2;">Exclusive discounts, stack guides, and early access. No spam, only gains.</p>

        <?php if (is_logged_in()): ?>
            <?php
                $nl_user = get_logged_in_user();
                $nl_email = $nl_user ? htmlspecialchars($nl_user['email']) : '';
            ?>
            <form class="newsletter-form" id="newsletter-form" onsubmit="return handleNewsletterSubmit(event);" style="display:flex; gap:10px; max-width:440px; margin:0 auto; position:relative; z-index:2;">
                <input type="email" name="email" id="nl-email" value="<?php echo $nl_email; ?>" readonly required style="flex:1; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:30px; padding:13px 20px; color:#fff; font-size:0.9rem; outline:none; font-family:var(--font-body); cursor:not-allowed; opacity:0.8;">
                <button type="submit" id="nl-btn" class="btn-gold" style="border-radius:30px; padding:13px 26px; white-space:nowrap; font-size:0.88rem;"><i class="fas fa-paper-plane"></i> Subscribe</button>
            </form>
        <?php else: ?>
            <div style="max-width:440px; margin:0 auto; position:relative; z-index:2;">
                <p style="color:rgba(255,255,255,0.5); font-size:0.88rem; margin-bottom:14px;">Please log in to subscribe with your verified email.</p>
                <a href="login.php?redirect=home" class="btn-gold" style="border-radius:30px; padding:13px 30px; font-size:0.88rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fas fa-sign-in-alt"></i> Login to Subscribe
                </a>
            </div>
        <?php endif; ?>

        <div id="nl-message" style="margin-top:12px; font-size:0.85rem; display:none; position:relative; z-index:2;"></div>
    </div>
</section>

<script>
function handleNewsletterSubmit(e) {
    e.preventDefault();
    var email = document.getElementById('nl-email').value.trim();
    var btn = document.getElementById('nl-btn');
    var msg = document.getElementById('nl-message');

    if (!email) return false;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';

    var fd = new FormData();
    fd.append('email', email);

    fetch('newsletter_subscribe.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            msg.style.display = 'block';
            msg.style.color = data.success ? '#4ade80' : '#ef4444';
            msg.textContent = data.message;
            if (data.success) {
                btn.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
                btn.style.background = 'linear-gradient(135deg, #4ade80, #22c55e)';
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Subscribe';
            }
            setTimeout(function() { msg.style.display = 'none'; }, 5000);
        })
        .catch(function() {
            msg.style.display = 'block';
            msg.style.color = '#ef4444';
            msg.textContent = 'Something went wrong. Please try again.';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Subscribe';
        });

    return false;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
// ── Gold Particles ──
(function(){var c=document.getElementById('goldParticles');if(!c)return;var ctx=c.getContext('2d'),p=[];function r(){c.width=window.innerWidth;c.height=window.innerHeight;}r();window.addEventListener('resize',r);for(var i=0;i<40;i++)p.push({x:Math.random()*c.width,y:Math.random()*c.height,r:Math.random()*2+0.5,dx:(Math.random()-0.5)*0.3,dy:(Math.random()-0.5)*0.3,o:Math.random()*0.5+0.1});function d(){ctx.clearRect(0,0,c.width,c.height);for(var i=0;i<p.length;i++){var v=p[i];ctx.beginPath();ctx.arc(v.x,v.y,v.r,0,Math.PI*2);ctx.fillStyle='rgba(212,175,55,'+v.o+')';ctx.fill();v.x+=v.dx;v.y+=v.dy;if(v.x<0||v.x>c.width)v.dx*=-1;if(v.y<0||v.y>c.height)v.dy*=-1;}requestAnimationFrame(d);}d();})();

// ── Scroll Reveal ──
(function(){var els=document.querySelectorAll('.section-header,.category-tile,.product-card,.trust-item,.blog-card,.counter-item,.proof-card,.tilt-card');els.forEach(function(el){el.style.opacity='0';el.style.transform='translateY(24px)';el.style.transition='opacity 0.55s ease, transform 0.55s ease';});var obs=new IntersectionObserver(function(entries){entries.forEach(function(e,i){if(e.isIntersecting){setTimeout(function(){e.target.style.opacity='1';e.target.style.transform='translateY(0)';},i*60);obs.unobserve(e.target);}});},{threshold:0.1});els.forEach(function(el){obs.observe(el);});})();

// ── Counter Animation ──
(function(){var counters=document.querySelectorAll('.counter-num[data-target]');var obs=new IntersectionObserver(function(entries){entries.forEach(function(e){if(e.isIntersecting){var el=e.target,target=parseInt(el.getAttribute('data-target')),current=0,increment=target/50;var timer=setInterval(function(){current+=increment;if(current>=target){current=target;clearInterval(timer);}el.textContent=Math.floor(current).toLocaleString()+(target>=1000?'+':'');},30);obs.unobserve(el);}});},{threshold:0.5});counters.forEach(function(c){obs.observe(c);});})();

// ── 3D Tilt ──
(function(){document.querySelectorAll('.tilt-card').forEach(function(card){card.addEventListener('mousemove',function(e){var rect=card.getBoundingClientRect();var x=(e.clientX-rect.left)/rect.width-0.5;var y=(e.clientY-rect.top)/rect.height-0.5;card.style.transform='rotateY('+(x*6)+'deg) rotateX('+(-y*6)+'deg) scale(1.015)';card.style.setProperty('--mouse-x',((e.clientX-rect.left)/rect.width*100)+'%');card.style.setProperty('--mouse-y',((e.clientY-rect.top)/rect.height*100)+'%');});card.addEventListener('mouseleave',function(){card.style.transform='';});});})();
</script>
