<?php
// about.php
require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Page Specific CSS for About Us Redesign */
    .about-hero {
        position: relative;
        height: 380px;
        background: linear-gradient(rgba(8,12,16,0.85), rgba(8,12,16,0.85)), url('assets/images/hero2.png') no-repeat center center/cover;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        border-bottom: 2px solid rgba(212, 175, 55, 0.25);
    }
    .about-hero-content h1 {
        font-size: 3.5rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 12px;
        background: var(--gold-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-family: var(--font-heading);
        font-weight: 800;
    }
    .about-hero-content p {
        font-size: 1.2rem;
        color: var(--text-secondary);
        max-width: 600px;
        margin: 0 auto;
        font-weight: 300;
        letter-spacing: 0.5px;
    }
    
    .about-grid {
        display: grid;
        grid-template-columns: 1.25fr 1fr;
        gap: 60px;
        align-items: center;
        margin: 80px 0;
    }
    .about-story-text h3 {
        font-size: 1.9rem;
        color: var(--gold-primary);
        text-transform: uppercase;
        margin-bottom: 20px;
        letter-spacing: 1px;
        font-family: var(--font-heading);
    }
    .about-story-text p {
        font-size: 1.05rem;
        line-height: 1.85;
        color: var(--text-secondary);
        margin-bottom: 20px;
    }
    
    .about-image-card {
        position: relative;
        background: rgba(255,255,255,0.01);
        border: 1px solid rgba(212,175,55,0.15);
        border-radius: 20px;
        padding: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 20px 50px rgba(8,12,16,0.5);
        overflow: hidden;
    }
    .about-image-card::after {
        content: '';
        position: absolute;
        width: 180px;
        height: 180px;
        background: var(--gold-primary);
        filter: blur(100px);
        opacity: 0.12;
        z-index: 1;
    }
    .about-image-card img {
        display: block;
        transition: transform 0.4s ease;
    }
    .about-image-card img:hover {
        transform: scale(1.06) !important;
        z-index: 10 !important;
    }
    
    @keyframes aboutFloatLeft {
        0% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-12px) rotate(-1.5deg); }
        100% { transform: translateY(0px) rotate(0deg); }
    }
    @keyframes aboutFloatRight {
        0% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(12px) rotate(2deg); }
        100% { transform: translateY(0px) rotate(0deg); }
    }
    
    .stats-counter-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
        margin: 80px 0;
        background: rgba(255,255,255,0.01);
        border: 1px solid rgba(212, 175, 55, 0.15);
        border-radius: 12px;
        padding: 45px 30px;
        text-align: center;
        box-shadow: 0 15px 35px rgba(8,12,16, 0.35);
    }
    .stat-box h4 {
        font-size: 2.6rem;
        font-weight: 800;
        margin-bottom: 8px;
        background: var(--gold-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .stat-box p {
        font-size: 0.85rem;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 1px;
        font-weight: 600;
    }
    
    .pillars-section {
        margin: 80px 0;
    }
    .pillars-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-top: 50px;
    }
    .pillar-card {
        background: rgba(18,18,18, 0.4);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(212, 175, 55, 0.12);
        border-radius: 12px;
        padding: 45px 30px;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        text-align: center;
    }
    .pillar-card:hover {
        transform: translateY(-8px);
        border-color: var(--gold-primary);
        box-shadow: 0 15px 35px rgba(212, 175, 55, 0.18);
        background: rgba(18,18,18, 0.6);
    }
    .pillar-card i {
        font-size: 2.3rem;
        margin-bottom: 20px;
        display: inline-block;
        background: var(--gold-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .pillar-card h4 {
        font-size: 1.25rem;
        color: #fff;
        text-transform: uppercase;
        margin-bottom: 12px;
        letter-spacing: 0.5px;
        font-family: var(--font-heading);
    }
    .pillar-card p {
        font-size: 0.95rem;
        color: var(--text-secondary);
        line-height: 1.65;
    }

    @media (max-width: 1024px) {
        .about-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }
        .about-image-card {
            order: -1;
        }
        .pillars-grid {
            grid-template-columns: 1fr;
        }
        .stats-counter-bar {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 600px) {
        .about-hero-content h1 {
            font-size: 2.5rem;
        }
        .stats-counter-bar {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Hero Section -->
<section class="about-hero">
    <div class="about-hero-content">
        <h1>Our Brand Story</h1>
        <p>Conquer from within: Bridging time-tested Ayurveda and modern physical performance</p>
    </div>
</section>

<div class="container" style="margin-bottom: 80px;">
    
    <!-- Narrative Grid Section -->
    <div class="about-grid">
        <div class="about-story-text">
            <h3>Ancient Wisdom Meets Modern Performance</h3>
            <p>
                At Wolf Nutrition, we believe that high performance starts from within. True physical grit, mental clarity, and physiological stamina are not built on temporary stimulants, but through deep physiological balance. 
            </p>
            <p>
                We bridge the gap between ancient, time-tested Ayurvedic botanicals and the rigorous demands of modern life. We source only the highest, gold-grade ingredients—such as pure Himalayan Shilajit, high-potency Ashwagandha, and protective Kutki extracts—to formulate performance stacks for those who refuse to settle for average.
            </p>
            <p>
                Whether you are training to hit new personal physical thresholds, navigating high-stakes business environments, or reclaiming clean vital health, Wolf Nutrition provides the raw, active tools to help you lead the pack.
            </p>
        </div>
        
        <!-- Interactive Glowing Bottle Frame -->
        <div class="about-image-card" style="height: 380px; position: relative;">
            <img src="assets/images/products/about_wolfpack.png" alt="Wolfpack Vitality" style="height: 280px; position: absolute; left: 12%; bottom: 12%; z-index: 3; filter: drop-shadow(0 15px 30px rgba(8,12,16,0.75)); animation: aboutFloatLeft 6s ease-in-out infinite;">
            <img src="assets/images/products/about_wolftox.png" alt="Wolftox Detox" style="height: 250px; position: absolute; right: 12%; top: 12%; z-index: 2; filter: drop-shadow(0 15px 30px rgba(8,12,16,0.75)); animation: aboutFloatRight 6s ease-in-out infinite;">
        </div>
    </div>

    <!-- Interactive Stats Counter Bar -->
    <div class="stats-counter-bar">
        <div class="stat-box">
            <h4>100%</h4>
            <p>Natural & Ayurvedic</p>
        </div>
        <div class="stat-box">
            <h4>No. 22126...</h4>
            <p>FSSAI Certified</p>
        </div>
        <div class="stat-box">
            <h4>25,000+</h4>
            <p>Active Customers</p>
        </div>
        <div class="stat-box">
            <h4>4.9 ★</h4>
            <p>Rated Formulations</p>
        </div>
    </div>

    <!-- The 3 Pillars Section -->
    <div class="pillars-section">
        <div class="section-header" style="margin-bottom: 20px;">
            <h2>The Wolfpack Standard</h2>
            <p>Our commitment to compounding clean, active excellence is driven by three core pillars</p>
        </div>
        
        <div class="pillars-grid">
            <!-- Pillar 1 -->
            <div class="pillar-card">
                <i class="fas fa-gem"></i>
                <h4>Uncompromising Quality</h4>
                <p>
                    We meticulously select and verify our organic ingredients at the source. From extraction purity levels to clean veggie capsule bindings, every details undergoes strict laboratory validation.
                </p>
            </div>
            
            <!-- Pillar 2 -->
            <div class="pillar-card">
                <i class="fas fa-heartbeat"></i>
                <h4>Holistic Vitality</h4>
                <p>
                    Our stacks go beyond standard single-ingredient formulas. We design complete cycles that optimize natural energy production, manage physical recovery, and detoxify organs for overall durability.
                </p>
            </div>
            
            <!-- Pillar 3 -->
            <div class="pillar-card">
                <i class="fas fa-crown"></i>
                <h4>Premium Experience</h4>
                <p>
                    We believe fitness aesthetics should match physiological performance. Our premium, minimal black-and-gold presentation brings luxury and style directly to your active wellness tray.
                </p>
            </div>
        </div>
    </div>

    <!-- Closing Call-To-Action (CTA) Banner -->
    <div class="glass-card" style="padding: 50px; border-radius: 15px; text-align: center; background: linear-gradient(135deg, rgba(212,175,55,0.06) 0%, rgba(8,12,16,0.85) 100%); margin-top: 80px; border: 1px solid rgba(212,175,55,0.15); box-shadow: 0 20px 40px rgba(8,12,16,0.4);">
        <h3 style="font-size: 1.8rem; text-transform: uppercase; margin-bottom: 15px; color: #fff; font-family: var(--font-heading); font-weight: 700; letter-spacing: 0.5px;">Take Command of Your Health</h3>
        <p style="margin-bottom: 30px; font-size: 1.05rem; color: var(--text-secondary); max-width: 700px; margin-left: auto; margin-right: auto; line-height: 1.6;">
            Explore our clinically validated formulations. From physical endurance stacks to complete toxin cleanses, select the targeted support your body deserves.
        </p>
        <a href="category.php?slug=vitality" class="btn-gold" style="padding: 14px 35px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
            Shop Our Formulations &rarr;
        </a>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
