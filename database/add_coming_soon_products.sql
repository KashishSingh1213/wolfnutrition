-- ========================================================
-- UPDATE EXISTING COMING-SOON PRODUCTS WITH USER'S DETAILED COPY
-- ========================================================

-- Update WolfBurn (ID 3) with detailed description
UPDATE `products` SET
  `short_description` = 'Ignite your metabolism and carve out your physique with WolfBurn, our upcoming 100% natural fat burner. Staying true to our "Raw, Natural, Wild" philosophy, this thermo-metabolic lean burner is engineered to help you shed stubborn weight without the jitters.',
  `description` = 'Ignite your metabolism and carve out your physique with WolfBurn, our upcoming 100% natural fat burner. Staying true to our "Raw, Natural, Wild" philosophy, this thermo-metabolic lean burner is engineered to help you shed stubborn weight without the jitters. We''ve tapped into the power of traditional, raw botanicals to create a potent formula that accelerates fat loss, controls cravings, and fuels your daily energy naturally.\n\nWe''ve combined centuries-old Ayurvedic thermogenic wisdom with modern metabolic science. Each capsule is packed with carefully selected botanicals that work synergistically to boost your core temperature, suppress appetite, and drive your body into a fat-burning state — all without the crash or jitters of synthetic stimulants.',
  `benefits` = 'The Apex Predator Blend:\n• Vrikshamla Extract: A powerful, natural compound that helps curb appetite and block the formation of new fat cells.\n• Guggulu Purified: A revered traditional ingredient known for optimizing lipid metabolism and supporting healthy thyroid function.\n• Green Tea Extract: Packed with natural antioxidants to boost your core temperature, driving thermogenesis and fat oxidation.\n• Trikatu Extract: A natural bioavailability enhancer that ensures your body absorbs every ounce of these powerful ingredients while further stimulating metabolism.',
  `ingredients` = 'Each 500mg Veggie Capsule contains: Vrikshamla Extract (Garcinia cambogia, 60% HCA) 150mg, Guggulu Purified (Commiphora wightii) 120mg, Green Tea Extract (Camellia sinensis, 50% EGCG) 100mg, Trikatu Extract (Piper nigrum, Piper longum, Zingiber officinale) 80mg, BioPerine (Black Pepper Extract) 5mg.',
  `how_to_use` = 'Take 1 capsule twice daily 30 minutes before meals with water. For best results, combine with regular exercise and a balanced diet. Do not exceed 2 capsules in a 24-hour period.\n\nThe Details: 60 Capsules per bottle. Prepare to shred.',
  `disclaimer` = 'Not recommended for pregnant or lactating women. Consult your physician before use if you have heart disease, high blood pressure, or are taking any medication. Keep out of reach of children.'
WHERE `id` = 3;

-- Update WolfPro (ID 4) with detailed description
UPDATE `products` SET
  `short_description` = 'When you train like a beast, you need to recover like an alpha. Enter WOLFPRO, our upcoming Premium Protein Isolate designed for elite performance and rapid muscle repair.',
  `description` = 'When you train like a beast, you need to recover like an alpha. Enter WOLFPRO, our upcoming Premium Protein Isolate designed for elite performance and rapid muscle repair. Stripped of excess fats, carbs, and lactose, this ultra-pure isolate delivers the raw, fast-absorbing nutrients your muscles crave immediately after a brutal session. Engineered for those who refuse to compromise on quality, WOLFPRO provides the clean fuel necessary to build lean, dense muscle and keep you at the top of the food chain.\n\nOur micro-filtration process ensures maximum protein retention while removing unwanted macros. Every scoop delivers a complete amino acid profile with the highest biological value, meaning your body utilizes nearly every gram for muscle protein synthesis.',
  `benefits` = 'The Alpha Standard:\n• Rapid Absorption: Micro-filtered isolate digests quickly to flood your muscles with essential amino acids right when you need them most.\n• Maximum Protein Yield: Delivers a massive hit of pure protein per scoop with near-zero fillers, fats, or sugars.\n• Lean Muscle Support: The perfect catalyst for maximizing hypertrophy, enhancing recovery times, and supporting a lean, shredded physique.\n• Uncompromising Purity: Clean, easily digestible, and aligned with our commitment to high-quality, premium formulations.',
  `ingredients` = 'Whey Protein Isolate (from Grass-Fed Cows), Natural Cocoa Flavor (for Chocolate variant), Sunflower Lecithin (emulsifier), Steviol Glycosides (natural sweetener).',
  `how_to_use` = 'Mix 1 scoop (30g) with 200-250ml of cold water or milk. Shake or blend for 20-30 seconds. Best consumed within 30 minutes after exercise or as a meal supplement.\n\nThe Details: Available in 1KG - 30 Servings and 2KG - 60 Servings Tubs. Pure power is on the way.',
  `disclaimer` = 'Not a substitute for a balanced diet. Do not exceed recommended daily intake. Keep in a cool, dry place away from direct sunlight. Once opened, consume within 60 days.'
WHERE `id` = 4;

-- ========================================================
-- INSERT NEW COMING-SOON PRODUCTS
-- ========================================================

-- Wolfgain (ID 5)
INSERT INTO `products` (`id`, `name`, `slug`, `short_description`, `description`, `category_id`, `image_url`, `image_gallery`, `benefits`, `ingredients`, `how_to_use`, `disclaimer`, `is_active`) VALUES
(5,
 'Wolfgain - Advanced Mass Gainer Formula',
 'wolfgain-advanced-mass-gainer',
 'Size matters when you''re hunting for serious gains. Prepare for the arrival of Wolfgain, our Advanced Mass Gainer Formula built for those who demand uncompromising bulk and power.',
 'Size matters when you''re hunting for serious gains. Prepare for the arrival of Wolfgain, our Advanced Mass Gainer Formula built for those who demand uncompromising bulk and power. Formulated with a high-quality protein blend and multi-sourced carbs, Wolfgain is designed to replenish depleted glycogen stores, trigger rapid muscle recovery, and pack on dense mass after your most brutal workouts.\n\nWhether you''re a hardgainer struggling to pack on size or an athlete looking to maximize caloric intake during a bulking phase, Wolfgain delivers the concentrated calories and premium macronutrients your body needs to grow. Every serving is engineered for optimal absorption, ensuring minimal fat gain and maximum muscle hypertrophy.',
 4,
 'assets/images/products/wolfgain.png',
 'assets/images/products/wolfgain.png',
 'Why Wolfgain Will Dominate:\n• Multi-Sourced Carbs: Provides a sustained release of energy to fuel heavy lifts and prevent muscle breakdown.\n• High-Quality Blend: Premium macronutrients engineered for optimal absorption and maximum muscle hypertrophy.\n• Incredible Taste: Launching in our signature Choco Fury flavor—because fueling your gains should taste as good as it feels.',
 'Multi-Source Carbohydrate Blend (Oat Flour, Maltodextrin, Waxy Maize) 50g, Whey Protein Concentrate 15g, Milk Protein Isolate 10g, Natural Cocoa Flavor, Medium Chain Triglycerides (MCT Oil) 3g, Digestive Enzyme Blend (Amylase, Protease) 50mg, Sunflower Lecithin.',
 'Mix 1 heaping scoop (100g) with 300-400ml of cold water or milk. Blend or shake vigorously for 30-45 seconds. Best consumed post-workout or between meals to maintain caloric surplus.\n\nThe Details: 1KG Tub | 15 Heavy-Hitting Servings. The alpha of mass gainers is almost here.',
 'Not a substitute for a balanced diet. Do not exceed recommended daily intake. Keep in a cool, dry place away from direct sunlight. Once opened, consume within 60 days.',
 0);

-- Wolf-AG (ID 6)
INSERT INTO `products` (`id`, `name`, `slug`, `short_description`, `description`, `category_id`, `image_url`, `image_gallery`, `benefits`, `ingredients`, `how_to_use`, `disclaimer`, `is_active`) VALUES
(6,
 'WOLF-AG - Premium Ashwagandha Extract Capsules',
 'wolf-ag-premium-ashwagandha',
 'Conquer stress, reclaim your focus, and unlock your true physical potential with premium Ashwagandha extract capsules.',
 'Conquer stress, reclaim your focus, and unlock your true physical potential with Wolf-AG, our upcoming premium Ashwagandha extract capsules. Rooted in ancient botanical wisdom and refined for the modern alpha, Wolf-AG delivers a highly potent, standardized dose of pure Ashwagandha. Designed to naturally lower cortisol, boost vitality, and accelerate recovery, this powerful adaptogen helps you maintain an untamed edge in both the gym and daily life. Stay calm under pressure, recover faster, and dominate your territory.\n\nAshwagandha (Withania somnifera) has been used for over 3,000 years in Ayurvedic medicine as a rasayana (rejuvenator). Modern clinical research confirms its ability to reduce cortisol levels by up to 30%, improve VO2 max, and enhance strength and recovery in resistance-trained athletes.',
 1,
 'assets/images/products/wolfag.png',
 'assets/images/products/wolfag.png',
 'The Adaptogenic Edge:\n• Cortisol & Stress Control: Helps the body adapt to intense physical and mental stress, keeping your mind sharp and your focus locked.\n• Strength & Vitality Support: Formulated to naturally aid in boosting endurance, muscular strength, and overall vitality.\n• Deep Physical Recovery: Optimizes nighttime recovery and reduces muscle soreness, ensuring you wake up ready to hunt your goals.\n• 100% Pure Botanicals: True to our raw philosophy, delivering a clean, high-potency herbal extract with zero unnecessary fillers.',
 'Each 500mg Veggie Capsule contains: Ashwagandha Root Extract (Withania somnifera, standardized to 5% Withanolides) 500mg, BioPerine (Black Pepper Extract) 5mg.',
 'Take 1 capsule twice daily after meals with water or warm milk, or as directed by your healthcare professional. Maintain consistency for 60-90 days for optimal results.\n\nThe Details: 60 Units (Capsules) per bottle. Natural resilience is on the way.',
 'Not recommended for pregnant or lactating women. Consult your physician before use if you have autoimmune conditions or thyroid disorders. Keep out of reach of children.',
 0);

-- ========================================================
-- INSERT PRODUCT VARIANTS FOR NEW PRODUCTS
-- ========================================================

-- Wolfgain variants
INSERT INTO `product_variants` (`product_id`, `sku`, `size_capsules`, `price`, `sale_price`, `stock_qty`, `is_default`) VALUES
(5, 'WG1KG', '1KG - 15 Servings', 2999.00, 2499.00, 0, 1),
(5, 'WG2KG', '2KG - 30 Servings', 4999.00, 4199.00, 0, 0);

-- Wolf-AG variant
INSERT INTO `product_variants` (`product_id`, `sku`, `size_capsules`, `price`, `sale_price`, `stock_qty`, `is_default`) VALUES
(6, 'WAG60', '60 Veggie Capsules', 1499.00, 1199.00, 0, 1);
