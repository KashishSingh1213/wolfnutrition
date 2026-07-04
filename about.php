<?php require_once __DIR__ . '/includes/header.php'; ?>

<style>
#goldParticles{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0;opacity:0.3;}

/* ── Hero ── */
.about-hero{position:relative;padding:100px 0 80px;overflow:hidden;background:radial-gradient(ellipse at 50% 30%,rgba(212,175,55,0.06) 0%,transparent 60%),linear-gradient(180deg,#080C10 0%,#121212 100%);text-align:center;}
.about-hero::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:500px;height:500px;border-radius:50%;border:1px solid rgba(212,175,55,0.06);animation:heroRing 20s linear infinite;}
.about-hero::after{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:350px;height:350px;border-radius:50%;border:1px solid rgba(212,175,55,0.04);animation:heroRing 15s linear infinite reverse;}
@keyframes heroRing{from{transform:translate(-50%,-50%) rotate(0deg);}to{transform:translate(-50%,-50%) rotate(360deg);}}
.hero-badge{display:inline-block;font-size:0.68rem;font-weight:800;letter-spacing:2.5px;background:var(--gold-gradient);color:#080C10;padding:6px 18px;border-radius:20px;text-transform:uppercase;margin-bottom:20px;position:relative;z-index:2;}
.about-hero h1{font-size:clamp(2.4rem,5.5vw,4.2rem);text-transform:uppercase;letter-spacing:2px;margin-bottom:16px;color:#fff;font-family:var(--font-heading);font-weight:800;line-height:1.08;text-shadow:0 3px 20px rgba(0,0,0,0.5);position:relative;z-index:2;}
.about-hero h1 .gold{background:var(--gold-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.about-hero-sub{font-size:1.1rem;color:rgba(255,255,255,0.65);max-width:580px;margin:0 auto 30px;line-height:1.7;position:relative;z-index:2;}
.hero-ctas{display:flex;gap:16px;justify-content:center;flex-wrap:wrap;position:relative;z-index:2;}

/* ── Statement ── */
.statement-section{text-align:center;padding:90px 0;position:relative;overflow:hidden;}
.statement-section::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:600px;height:600px;background:radial-gradient(circle,rgba(212,175,55,0.05) 0%,transparent 60%);pointer-events:none;}
.statement-text{font-size:clamp(1.8rem,4vw,3.2rem);font-family:var(--font-heading);font-weight:800;text-transform:uppercase;line-height:1.12;color:#fff;position:relative;z-index:2;max-width:900px;margin:0 auto;}
.statement-text .gold{background:var(--gold-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.statement-sub{font-size:1.05rem;color:rgba(255,255,255,0.6);margin-top:20px;max-width:600px;margin-left:auto;margin-right:auto;line-height:1.7;position:relative;z-index:2;}

/* ── Story ── */
.story-grid{display:grid;grid-template-columns:1.2fr 1fr;gap:60px;align-items:center;margin:80px 0;}
.story-badge{display:inline-block;font-size:0.65rem;font-weight:800;letter-spacing:2.5px;color:var(--gold-primary);text-transform:uppercase;margin-bottom:14px;background:rgba(212,175,55,0.06);border:1px solid rgba(212,175,55,0.12);padding:5px 14px;border-radius:20px;}
.story-text h2{font-size:2rem;text-transform:uppercase;color:#fff;margin-bottom:18px;font-family:var(--font-heading);font-weight:800;line-height:1.15;}
.story-text h2 .gold{background:var(--gold-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.story-text p{font-size:1rem;color:rgba(255,255,255,0.65);line-height:1.85;margin-bottom:14px;}
.story-quote{margin-top:24px;padding:20px 24px;border-left:3px solid var(--gold-primary);background:rgba(212,175,55,0.03);border-radius:0 12px 12px 0;}
.story-quote p{font-style:italic;color:rgba(255,255,255,0.75);font-size:0.95rem;margin:0;}
.story-quote cite{display:block;margin-top:8px;font-size:0.78rem;color:var(--gold-primary);font-style:normal;font-weight:700;}
.story-visual{position:relative;height:420px;display:flex;align-items:center;justify-content:center;}
.story-visual::before{content:'';position:absolute;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,var(--gold-primary) 0%,transparent 70%);opacity:0.08;filter:blur(40px);}
.story-visual::after{content:'';position:absolute;width:180px;height:180px;border-radius:50%;border:1px solid rgba(212,175,55,0.08);animation:visualRing 12s linear infinite;}
@keyframes visualRing{from{transform:rotate(0deg);}to{transform:rotate(360deg);}}
.story-img-left{position:absolute;left:5%;bottom:5%;height:270px;z-index:3;filter:drop-shadow(0 20px 40px rgba(8,12,16,0.8));animation:imgFloat 6s ease-in-out infinite;}
.story-img-right{position:absolute;right:5%;top:5%;height:240px;z-index:2;filter:drop-shadow(0 20px 40px rgba(8,12,16,0.8));animation:imgFloat 6s ease-in-out infinite 1.5s;}
@keyframes imgFloat{0%,100%{transform:translateY(0) rotate(0deg);}50%{transform:translateY(-14px) rotate(1.5deg);}}

/* ── Counters ── */
.counter-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;margin:80px 0;}
.counter-box{text-align:center;padding:32px 16px;background:rgba(255,255,255,0.02);border:1px solid rgba(212,175,55,0.08);border-radius:18px;transition:all 0.4s;position:relative;overflow:hidden;}
.counter-box:hover{border-color:var(--gold-primary);transform:translateY(-5px);box-shadow:0 15px 35px rgba(8,12,16,0.35);}
.counter-box::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--gold-gradient);opacity:0;transition:opacity 0.3s;}
.counter-box:hover::before{opacity:1;}
.counter-num{font-size:2.6rem;font-weight:800;font-family:var(--font-heading);background:var(--gold-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;line-height:1;}
.counter-label{font-size:0.72rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1.2px;margin-top:8px;font-weight:600;}

/* ── Differentiators ── */
.diff-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:50px;}
.diff-card{background:rgba(255,255,255,0.02);border:1px solid rgba(212,175,55,0.08);border-radius:18px;padding:36px 28px;transition:all 0.4s;position:relative;overflow:hidden;}
.diff-card:hover{border-color:var(--gold-primary);transform:translateY(-5px);box-shadow:0 15px 40px rgba(8,12,16,0.4);}
.diff-card::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(212,175,55,0.04) 0%,transparent 60%);opacity:0;transition:opacity 0.3s;}
.diff-card:hover::after{opacity:1;}
.diff-num{font-size:3rem;font-weight:800;font-family:var(--font-heading);background:var(--gold-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;line-height:1;margin-bottom:14px;position:relative;z-index:1;}
.diff-card h4{font-size:1.1rem;color:#fff;text-transform:uppercase;margin-bottom:8px;letter-spacing:0.5px;font-family:var(--font-heading);font-weight:700;position:relative;z-index:1;}
.diff-card p{font-size:0.88rem;color:rgba(255,255,255,0.6);line-height:1.65;position:relative;z-index:1;}

/* ── Pillars ── */
.pillars-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:26px;margin-top:50px;}
.pillar-card{background:rgba(255,255,255,0.02);border:1px solid rgba(212,175,55,0.08);border-radius:18px;padding:40px 28px;text-align:center;transition:all 0.4s;position:relative;overflow:hidden;}
.pillar-card:hover{border-color:var(--gold-primary);transform:translateY(-6px);box-shadow:0 16px 40px rgba(8,12,16,0.4);}
.pillar-card::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(212,175,55,0.04) 0%,transparent 60%);opacity:0;transition:opacity 0.3s;}
.pillar-card:hover::after{opacity:1;}
.pillar-icon{width:64px;height:64px;border-radius:16px;background:rgba(212,175,55,0.08);border:1px solid rgba(212,175,55,0.18);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;color:var(--gold-primary);font-size:1.5rem;position:relative;z-index:1;transition:all 0.3s;}
.pillar-card:hover .pillar-icon{background:rgba(212,175,55,0.12);border-color:var(--gold-primary);transform:scale(1.1);}
.pillar-card h4{font-size:1.15rem;color:#fff;text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;font-family:var(--font-heading);font-weight:700;position:relative;z-index:1;}
.pillar-card p{font-size:0.9rem;color:rgba(255,255,255,0.6);line-height:1.65;position:relative;z-index:1;}

/* ── Founder Quote ── */
.founder-section{display:grid;grid-template-columns:1fr 1.4fr;gap:50px;align-items:center;margin:80px 0;padding:50px;background:rgba(255,255,255,0.02);border:1px solid rgba(212,175,55,0.08);border-radius:22px;position:relative;overflow:hidden;}
.founder-section::before{content:'';position:absolute;top:-40px;left:-40px;width:200px;height:200px;background:radial-gradient(circle,rgba(212,175,55,0.06) 0%,transparent 70%);pointer-events:none;}
.founder-avatar{width:120px;height:120px;border-radius:50%;background:var(--gold-gradient);display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:800;color:#080C10;font-family:var(--font-heading);box-shadow:0 0 0 4px rgba(8,12,16,0.8),0 0 0 6px rgba(212,175,55,0.2);margin-bottom:20px;}
.founder-name{font-size:1.3rem;color:#fff;font-family:var(--font-heading);font-weight:700;margin-bottom:4px;}
.founder-role{font-size:0.82rem;color:var(--gold-primary);text-transform:uppercase;letter-spacing:1px;font-weight:700;}
.founder-quote{font-size:1.15rem;color:rgba(255,255,255,0.8);line-height:1.8;font-style:italic;position:relative;padding-left:24px;border-left:3px solid var(--gold-primary);}
.founder-quote::before{content:'"';position:absolute;left:-8px;top:-15px;font-size:4rem;color:var(--gold-primary);opacity:0.2;font-style:normal;font-family:var(--font-heading);}

/* ── Timeline ── */
.timeline{position:relative;padding:20px 0;margin:80px 0;}
.timeline::before{content:'';position:absolute;left:50%;top:0;bottom:0;width:2px;background:linear-gradient(to bottom,transparent,rgba(212,175,55,0.25),transparent);transform:translateX(-50%);}
.timeline-item{display:flex;align-items:center;margin-bottom:50px;position:relative;}
.timeline-item:nth-child(odd){flex-direction:row-reverse;}
.timeline-item:nth-child(odd) .timeline-content{text-align:right;padding-right:50px;padding-left:0;}
.timeline-item:nth-child(even) .timeline-content{padding-left:50px;}
.timeline-content{flex:1;}
.timeline-dot{width:16px;height:16px;border-radius:50%;background:var(--gold-gradient);border:3px solid #080C10;position:absolute;left:50%;transform:translateX(-50%);z-index:2;box-shadow:0 0 12px rgba(212,175,55,0.3);transition:transform 0.3s;}
.timeline-item:hover .timeline-dot{transform:translateX(-50%) scale(1.3);}
.timeline-year{font-size:0.65rem;font-weight:800;color:var(--gold-primary);text-transform:uppercase;letter-spacing:2px;margin-bottom:6px;background:rgba(212,175,55,0.08);border:1px solid rgba(212,175,55,0.12);padding:3px 12px;border-radius:20px;display:inline-block;}
.timeline-content h4{font-size:1.05rem;color:#fff;margin-bottom:6px;font-family:var(--font-heading);font-weight:700;text-transform:uppercase;}
.timeline-content p{font-size:0.88rem;color:rgba(255,255,255,0.6);line-height:1.6;}

/* ── CTA ── */
.cta-banner{background:linear-gradient(135deg,rgba(212,175,55,0.07) 0%,rgba(8,12,16,0.95) 50%,rgba(212,175,55,0.04) 100%);border:1px solid rgba(212,175,55,0.12);border-radius:24px;padding:60px 50px;text-align:center;position:relative;overflow:hidden;margin:80px 0;}
.cta-banner::before{content:'';position:absolute;top:-70px;right:-70px;width:250px;height:250px;background:radial-gradient(circle,rgba(212,175,55,0.08) 0%,transparent 70%);pointer-events:none;}
.cta-banner::after{content:'';position:absolute;bottom:-50px;left:-50px;width:200px;height:200px;background:radial-gradient(circle,rgba(212,175,55,0.06) 0%,transparent 70%);pointer-events:none;}
.cta-banner h3{font-size:2.1rem;text-transform:uppercase;margin-bottom:12px;color:#fff;font-family:var(--font-heading);font-weight:800;letter-spacing:0.5px;position:relative;z-index:2;}
.cta-banner p{margin-bottom:30px;font-size:1.02rem;color:rgba(255,255,255,0.65);max-width:580px;margin-left:auto;margin-right:auto;line-height:1.7;position:relative;z-index:2;}

/* ── Diagonal Divider ── */
.divider-wave{position:relative;width:100%;overflow:hidden;line-height:0;margin-top:-1px;}
.divider-wave svg{display:block;width:100%;height:50px;}

/* ── Tilt & Spotlight ── */
.tilt-card{transform-style:preserve-3d;perspective:1000px;}
.tilt-card .tilt-shine{position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,0.06) 0%,transparent 60%);pointer-events:none;opacity:0;transition:opacity 0.3s;}
.tilt-card:hover .tilt-shine{opacity:1;}
.spotlight-card{position:relative;overflow:hidden;}
.spotlight-card::before{content:'';position:absolute;top:var(--mouse-y,50%);left:var(--mouse-x,50%);width:250px;height:250px;background:radial-gradient(circle,rgba(212,175,55,0.08) 0%,transparent 70%);transform:translate(-50%,-50%);pointer-events:none;opacity:0;transition:opacity 0.4s;z-index:0;}
.spotlight-card:hover::before{opacity:1;}

@media(max-width:1024px){
    .story-grid{grid-template-columns:1fr;gap:40px;}
    .story-visual{order:-1;height:300px;}
    .diff-grid,.pillars-grid{grid-template-columns:1fr;}
    .counter-strip{grid-template-columns:repeat(2,1fr);}
    .founder-section{grid-template-columns:1fr;text-align:center;}
    .founder-avatar{margin:0 auto 20px;}
    .founder-quote{border-left:none;border-top:3px solid var(--gold-primary);padding-left:0;padding-top:20px;}
    .founder-quote::before{display:none;}
    .timeline::before{left:20px;}
    .timeline-item,.timeline-item:nth-child(odd){flex-direction:row;}
    .timeline-dot{left:20px;}
    .timeline-content,.timeline-item:nth-child(odd) .timeline-content{text-align:left;padding-left:50px;padding-right:0;}
}
@media(max-width:600px){
    .counter-strip{grid-template-columns:1fr 1fr;gap:12px;}
    .counter-num{font-size:1.8rem;}
    .about-hero{padding:70px 0 60px;}
}
</style>

<canvas id="goldParticles"></canvas>

<!-- ═══ HERO ═══ -->
<section class="about-hero">
    <div style="position:relative; z-index:2; padding:0 20px;">
        <span class="hero-badge">Est. 2023 — Jalandhar, Punjab</span>
        <h1>The Wolf <span class="gold">Nutrition</span> Story</h1>
        <p class="about-hero-sub">We don't just sell supplements. We engineer complete Ayurvedic performance systems for men who refuse to settle for average.</p>
        <div class="hero-ctas">
            <a href="category.php?slug=all" class="btn-gold" style="padding:14px 36px; font-size:0.92rem; border-radius:30px;"><i class="fas fa-shopping-bag"></i> Shop Stacks</a>
            <a href="#story" class="btn-outline-gold" style="padding:13px 36px; font-size:0.92rem; border-radius:30px;">Our Story <i class="fas fa-arrow-down" style="margin-left:6px; font-size:0.8rem;"></i></a>
        </div>
    </div>
</section>

<!-- ═══ STATEMENT ═══ -->
<section class="statement-section">
    <div class="container">
        <div class="statement-text">We Source The Highest<br><span class="gold">Gold-Grade Ingredients</span><br>From The Himalayas</div>
        <p class="statement-sub">Pure Shilajit. High-potency Ashwagandha. Protective Kutki. Every ingredient is selected at the source and validated in the lab.</p>
    </div>
</section>

<div class="container" style="position:relative; z-index:2; margin-bottom:80px;">

    <!-- ═══ STORY ═══ -->
    <div id="story" class="story-grid">
        <div class="story-text">
            <span class="story-badge">The Beginning</span>
            <h2>Ancient Wisdom Meets <span class="gold">Modern Performance</span></h2>
            <p>At Wolf Nutrition, we believe high performance starts from within. True physical grit, mental clarity, and stamina are not built on temporary stimulants — they come from deep physiological balance.</p>
            <p>We bridge the gap between time-tested Ayurvedic botanicals and the demands of modern life. We source only gold-grade ingredients to formulate performance stacks for those who refuse to settle.</p>
            <div class="story-quote">
                <p>"Ayurveda is not a shortcut. It is a daily discipline. In 90 days, your body undergoes complete cellular rejuvenation."</p>
                <cite>— Wolf Nutrition Philosophy</cite>
            </div>
            <a href="category.php?slug=all" class="btn-gold" style="padding:13px 30px; border-radius:30px; font-size:0.9rem; margin-top:24px; display:inline-flex; align-items:center; gap:8px;"><i class="fas fa-arrow-right"></i> Explore Our Stacks</a>
        </div>
        <div class="story-visual">
            <img src="assets/images/products/about_wolfpack.png" alt="Wolfpack" class="story-img-left">
            <img src="assets/images/products/about_wolftox.png" alt="Wolftox" class="story-img-right">
        </div>
    </div>

    <!-- Divider -->
    <div class="divider-wave"><svg viewBox="0 0 1200 50" preserveAspectRatio="none"><path d="M0,0 L1200,0 L1200,25 Q900,50 600,25 Q300,0 0,25 Z" fill="rgba(212,175,55,0.03)"/></svg></div>

    <!-- ═══ COUNTERS ═══ -->
    <div class="counter-strip">
        <div class="counter-box tilt-card spotlight-card"><div class="tilt-shine"></div><div class="counter-num" data-target="25000" data-suffix="+">0</div><div class="counter-label">Active Customers</div></div>
        <div class="counter-box tilt-card spotlight-card"><div class="tilt-shine"></div><div class="counter-num" data-target="100" data-suffix="%">0</div><div class="counter-label">Ayurvedic Formulas</div></div>
        <div class="counter-box tilt-card spotlight-card"><div class="tilt-shine"></div><div class="counter-num" data-target="4.9" data-decimal="true">0</div><div class="counter-label">Average Rating</div></div>
        <div class="counter-box tilt-card spotlight-card"><div class="tilt-shine"></div><div class="counter-num" data-target="50" data-suffix="+">0</div><div class="counter-label">Pincode Delivery</div></div>
    </div>

    <!-- Divider -->
    <div class="divider-wave"><svg viewBox="0 0 1200 50" preserveAspectRatio="none"><path d="M0,25 Q300,50 600,25 Q900,0 1200,25 L1200,50 L0,50 Z" fill="rgba(212,175,55,0.03)"/></svg></div>

    <!-- ═══ WHAT MAKES US DIFFERENT ═══ -->
    <div style="margin-top:60px;">
        <div class="section-header"><h2>What Makes Us Different</h2><p>We don't follow trends — we set the standard</p></div>
        <div class="diff-grid">
            <div class="diff-card tilt-card spotlight-card">
                <div class="tilt-shine"></div>
                <div class="diff-num">01</div>
                <h4>Source-Verified Ingredients</h4>
                <p>Every botanical is traceable to its origin. We test at extraction, at formulation, and before packaging. Triple-validated purity.</p>
            </div>
            <div class="diff-card tilt-card spotlight-card">
                <div class="tilt-shine"></div>
                <div class="diff-num">02</div>
                <h4>Zero Proprietary Blends</h4>
                <p>We publish full dosages on every label. No hidden ingredients, no fillers, no mystery compounds. Complete transparency.</p>
            </div>
            <div class="diff-card tilt-card spotlight-card">
                <div class="tilt-shine"></div>
                <div class="diff-num">03</div>
                <h4>Free Expert Guidance</h4>
                <p>Every customer gets access to our certified Ayurvedic nutritionists. Personalized dosage and stacking guidance, completely free.</p>
            </div>
        </div>
    </div>

    <!-- Divider -->
    <div class="divider-wave"><svg viewBox="0 0 1200 50" preserveAspectRatio="none"><path d="M0,0 L1200,0 L1200,25 Q900,50 600,25 Q300,0 0,25 Z" fill="rgba(212,175,55,0.03)"/></svg></div>

    <!-- ═══ PILLARS ═══ -->
    <div style="margin-top:60px;">
        <div class="section-header"><h2>The Wolfpack Standard</h2><p>Three pillars that drive everything we formulate</p></div>
        <div class="pillars-grid">
            <div class="pillar-card tilt-card spotlight-card">
                <div class="tilt-shine"></div>
                <div class="pillar-icon"><i class="fas fa-gem"></i></div>
                <h4>Uncompromising Quality</h4>
                <p>From extraction purity to clean veggie capsule bindings, every detail undergoes strict laboratory validation before it reaches you.</p>
            </div>
            <div class="pillar-card tilt-card spotlight-card">
                <div class="tilt-shine"></div>
                <div class="pillar-icon"><i class="fas fa-heartbeat"></i></div>
                <h4>Holistic Vitality</h4>
                <p>We design complete cycles that optimize natural energy production, manage physical recovery, and detoxify organs for total durability.</p>
            </div>
            <div class="pillar-card tilt-card spotlight-card">
                <div class="tilt-shine"></div>
                <div class="pillar-icon"><i class="fas fa-crown"></i></div>
                <h4>Premium Experience</h4>
                <p>Fitness aesthetics should match performance. Our black-and-gold presentation brings luxury directly to your active wellness tray.</p>
            </div>
        </div>
    </div>

    <!-- ═══ FOUNDER QUOTE ═══ -->
    <div class="founder-section tilt-card spotlight-card">
        <div class="tilt-shine"></div>
        <div style="position:relative; z-index:1; text-align:center;">
            <div class="founder-avatar">WN</div>
            <div class="founder-name">Wolf Nutrition</div>
            <div class="founder-role">Founded 2023</div>
        </div>
        <div style="position:relative; z-index:1;">
            <div class="founder-quote">
                We started Wolf Nutrition with one belief: that Ayurvedic wisdom, when properly formulated and honestly marketed, can compete with any modern supplement on the planet. We source the best, we formulate with science, and we deliver with integrity. That's the Wolfpack way.
            </div>
        </div>
    </div>

    <!-- ═══ TIMELINE ═══ -->
    <div style="margin-top:80px;">
        <div class="section-header"><h2>Our Journey</h2><p>From a vision to a movement — key milestones</p></div>
        <div class="timeline">
            <div class="timeline-item"><div class="timeline-dot"></div><div class="timeline-content"><span class="timeline-year">2023 — The Vision</span><h4>Founded in Jalandhar</h4><p>Born from the idea that Ayurvedic wisdom deserves premium, scientifically validated formulations.</p></div></div>
            <div class="timeline-item"><div class="timeline-dot"></div><div class="timeline-content"><span class="timeline-year">2024 — First Launch</span><h4>WOLFPACK & WOLFTOX</h4><p>Two flagship stacks released — vitality with Shilajit & Ashwagandha, liver detox with Kutki.</p></div></div>
            <div class="timeline-item"><div class="timeline-dot"></div><div class="timeline-content"><span class="timeline-year">2025 — Growth</span><h4>25,000+ Customers</h4><p>FSSAI certified, serving 50+ pincodes with a 4.9-star average rating across all platforms.</p></div></div>
            <div class="timeline-item"><div class="timeline-dot"></div><div class="timeline-content"><span class="timeline-year">2026 — Next Level</span><h4>Expanding the Pack</h4><p>New formulations, free consultations, and the Wolfpack VIP loyalty program — we're just getting started.</p></div></div>
        </div>
    </div>

    <!-- ═══ CTA ═══ -->
    <div class="cta-banner">
        <h3>Take Command of Your Health</h3>
        <p>Explore our clinically validated formulations. From physical endurance stacks to complete toxin cleanses — select the support your body deserves.</p>
        <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap; position:relative; z-index:2;">
            <a href="category.php?slug=all" class="btn-gold" style="padding:14px 36px; font-size:0.92rem; border-radius:30px;"><i class="fas fa-shopping-bag"></i> Shop Our Formulations</a>
            <a href="https://wa.me/919876543210?text=Hi%20Wolf%20Nutrition,%20I%20would%20like%20a%20free%20dietitian%20consultation." target="_blank" class="btn-outline-gold" style="padding:13px 36px; font-size:0.92rem; border-radius:30px; display:inline-flex; align-items:center; gap:8px;"><i class="fab fa-whatsapp"></i> Free Consult</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
// ── Gold Particles ──
(function(){var c=document.getElementById('goldParticles');if(!c)return;var ctx=c.getContext('2d'),p=[];function r(){c.width=window.innerWidth;c.height=window.innerHeight;}r();window.addEventListener('resize',r);for(var i=0;i<35;i++)p.push({x:Math.random()*c.width,y:Math.random()*c.height,r:Math.random()*1.8+0.4,dx:(Math.random()-0.5)*0.25,dy:(Math.random()-0.5)*0.25,o:Math.random()*0.4+0.1});function d(){ctx.clearRect(0,0,c.width,c.height);for(var i=0;i<p.length;i++){var v=p[i];ctx.beginPath();ctx.arc(v.x,v.y,v.r,0,Math.PI*2);ctx.fillStyle='rgba(212,175,55,'+v.o+')';ctx.fill();v.x+=v.dx;v.y+=v.dy;if(v.x<0||v.x>c.width)v.dx*=-1;if(v.y<0||v.y>c.height)v.dy*=-1;}requestAnimationFrame(d);}d();})();

// ── Scroll Reveal ──
(function(){var els=document.querySelectorAll('.statement-text,.story-text,.story-visual,.counter-box,.diff-card,.pillar-card,.founder-section,.timeline-item,.cta-banner');els.forEach(function(el){el.style.opacity='0';el.style.transform='translateY(24px)';el.style.transition='opacity 0.55s ease, transform 0.55s ease';});var obs=new IntersectionObserver(function(entries){entries.forEach(function(e,i){if(e.isIntersecting){setTimeout(function(){e.target.style.opacity='1';e.target.style.transform='translateY(0)';},i*60);obs.unobserve(e.target);}});},{threshold:0.1});els.forEach(function(el){obs.observe(el);});})();

// ── Counter Animation ──
(function(){var counters=document.querySelectorAll('.counter-num[data-target]');var obs=new IntersectionObserver(function(entries){entries.forEach(function(e){if(e.isIntersecting){var el=e.target,target=parseFloat(el.getAttribute('data-target')),suffix=el.getAttribute('data-suffix')||'',isDecimal=el.getAttribute('data-decimal')==='true',current=0,increment=target/50;var timer=setInterval(function(){current+=increment;if(current>=target){current=target;clearInterval(timer);}el.textContent=isDecimal?current.toFixed(1):Math.floor(current).toLocaleString();el.textContent+=suffix;},30);obs.unobserve(el);}});},{threshold:0.5});counters.forEach(function(c){obs.observe(c);});})();

// ── 3D Tilt ──
(function(){document.querySelectorAll('.tilt-card').forEach(function(card){card.addEventListener('mousemove',function(e){var rect=card.getBoundingClientRect();var x=(e.clientX-rect.left)/rect.width-0.5;var y=(e.clientY-rect.top)/rect.height-0.5;card.style.transform='rotateY('+(x*5)+'deg) rotateX('+(-y*5)+'deg) scale(1.01)';card.style.setProperty('--mouse-x',((e.clientX-rect.left)/rect.width*100)+'%');card.style.setProperty('--mouse-y',((e.clientY-rect.top)/rect.height*100)+'%');});card.addEventListener('mouseleave',function(){card.style.transform='';});});})();
</script>
