CREATE DATABASE IF NOT EXISTS `wolfnutrition`;
USE `wolfnutrition`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(15) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer', 'admin') DEFAULT 'customer',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. User Addresses Table
CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `address_line1` VARCHAR(255) NOT NULL,
  `address_line2` VARCHAR(255) NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `pincode` VARCHAR(10) NOT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `is_default` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `display_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `short_description` TEXT NULL,
  `description` TEXT NULL,
  `category_id` INT NULL,
  `image_url` VARCHAR(255) NULL,
  `image_gallery` TEXT NULL, -- comma-separated list of image urls
  `benefits` TEXT NULL,
  `ingredients` TEXT NULL,
  `how_to_use` TEXT NULL,
  `disclaimer` TEXT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Product Variants Table
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `sku` VARCHAR(50) NOT NULL UNIQUE,
  `size_capsules` VARCHAR(50) NOT NULL, -- e.g. '30 Capsules', '60 Capsules'
  `price` DECIMAL(10,2) NOT NULL, -- MRP (strikethrough)
  `sale_price` DECIMAL(10,2) NOT NULL, -- Selling price
  `stock_qty` INT DEFAULT 0,
  `is_default` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bundles Table
CREATE TABLE IF NOT EXISTS `bundles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `banner_image` VARCHAR(255) NULL,
  `combo_price` DECIMAL(10,2) NOT NULL,
  `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
  `status` TINYINT(1) DEFAULT 1,
  `display_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Bundle Items Table
CREATE TABLE IF NOT EXISTS `bundle_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `bundle_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `variant_id` INT NOT NULL,
  FOREIGN KEY (`bundle_id`) REFERENCES `bundles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Announcements Table
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `message` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) NULL,
  `display_order` INT DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Quantity Discounts Table
CREATE TABLE IF NOT EXISTS `quantity_discounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NULL, -- NULL means store-wide tier discount
  `min_qty` INT NOT NULL,
  `discount_percent` DECIMAL(5,2) NOT NULL,
  `status` TINYINT(1) DEFAULT 1,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Blog Posts Table
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `category_tag` VARCHAR(100) DEFAULT 'Wellness',
  `article_type` VARCHAR(50) DEFAULT 'Blog',
  `cover_image` VARCHAR(255) NULL,
  `body` TEXT NOT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `author_user_id` INT NULL,
  `custom_author` VARCHAR(150) NULL,
  `editor_name` VARCHAR(150) NULL,
  `reading_time` INT DEFAULT 5,
  `excerpt` VARCHAR(300) NULL,
  `alt_text` VARCHAR(255) NULL,
  `tags` VARCHAR(255) NULL,
  `published_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Blog Categories Table
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `display_order` INT DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Blog Tags Table
CREATE TABLE IF NOT EXISTS `blog_tags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `status` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. CMS Pages Table
CREATE TABLE IF NOT EXISTS `cms_pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `title` VARCHAR(150) NOT NULL,
  `body` TEXT NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Certificates Table
CREATE TABLE IF NOT EXISTS `certificates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `image_url` VARCHAR(255) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `display_order` INT DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. WhatsApp Settings Table
CREATE TABLE IF NOT EXISTS `whatsapp_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `phone_number` VARCHAR(15) NOT NULL,
  `greeting_message` TEXT NULL,
  `status` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Coupons Table
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('percentage', 'flat') DEFAULT 'percentage',
  `value` DECIMAL(10,2) NOT NULL,
  `min_order_amount` DECIMAL(10,2) DEFAULT 0.00,
  `max_discount` DECIMAL(10,2) DEFAULT 0.00,
  `expiry_date` DATE NOT NULL,
  `usage_limit` INT DEFAULT 0,
  `used_count` INT DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Reviews Table
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id` INT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `rating` INT NOT NULL,
  `title` VARCHAR(150) NULL,
  `review_text` TEXT NOT NULL,
  `is_approved` TINYINT(1) DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `discount` DECIMAL(10,2) DEFAULT 0.00,
  `shipping` DECIMAL(10,2) DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'COD',
  `payment_status` ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
  `shipping_status` ENUM('pending', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  `tracking_number` VARCHAR(100) NULL,
  `courier_name` VARCHAR(100) NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(15) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `pincode` VARCHAR(10) NOT NULL,
  `note` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Order Items Table
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `variant_id` INT NULL,
  `bundle_id` INT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `variant_name` VARCHAR(100) NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`bundle_id`) REFERENCES `bundles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Wishlist Table
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- DATA SEEDING
-- ========================================================

-- Seed Admin User (Password is 'admin123' hashed with bcrypt)
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `is_active`) 
VALUES (1, 'Admin Wolf', 'wolfnutritionwp@gmail.com', '9999999999', '$2y$10$wNMD5mX7m6aK9fV7cR.0beSg/5E3C7s5v1a.g60.aL/c9VqXyvKLu', 'admin', 1)
ON DUPLICATE KEY UPDATE `email`=`email`;

-- Seed Customer (Password is 'customer123' hashed: $2y$10$vYqF2jJszkKux6g2gL3O2eF1Z0t4JkU1F1p9L7oZzU8rOqQ5iN6zG)
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `is_active`) 
VALUES (2, 'Yuvek Verma', 'yuvek@gmail.com', '9876543210', '$2y$10$vYqF2jJszkKux6g2gL3O2eF1Z0t4JkU1F1p9L7oZzU8rOqQ5iN6zG', 'customer', 1)
ON DUPLICATE KEY UPDATE `email`=`email`;

-- Seed Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `is_active`, `display_order`) VALUES
(1, 'Vitality', 'vitality', 'Supplements designed to boost natural performance, energy and stamina.', 1, 1),
(2, 'Liver & Detox', 'liver-detox', 'Ayurvedic formulations to cleanse, rejuvenate and support liver health.', 1, 2),
(3, 'Weight Management', 'weight-management', 'Thermo-metabolic and lean body support supplements.', 1, 3),
(4, 'Protein', 'protein', 'Premium protein isolates for muscle recovery and growth.', 1, 4);

-- Seed Products
INSERT INTO `products` (`id`, `name`, `slug`, `short_description`, `description`, `category_id`, `image_url`, `image_gallery`, `benefits`, `ingredients`, `how_to_use`, `disclaimer`, `is_active`) VALUES
(1, 
 'WOLFPACK - UNLEASH THE ALPHA WITHIN', 
 'wolfpack-unleash-the-alpha-within', 
 'Reclaim your edge. Wolfpack is a premium, all-natural male wellness supplement engineered for the modern man who demands peak performance.', 
 'Reclaim your edge. Wolfpack is a premium, all-natural male wellness supplement engineered for the modern man who demands peak performance. Combining ancient Ayurvedic wisdom with high-potency ingredients, Wolfpack is designed to support stamina, vitality, and hormonal balance. Whether you’re crushing it in the gym, leading in the boardroom, or looking to enhance your personal life, Wolfpack provides the "Raw, Natural, and Wild" energy you need to lead the pack.', 
 1, 
 'assets/images/products/wolfpack.png', 
 'assets/images/products/wolfpack.png,assets/images/products/wolfpack_label.png,assets/images/products/wolfpack_back.png', 
 '• Maximum Vitality: Formulated with Black Asphaltum (Shilajit) and Ashwagandha, known for centuries to boost energy and reduce stress.\n• Pure & Potent: A powerful 500mg blend per capsule featuring Saffron, Mucuna Pruriens, and Iron Calcined.\n• Clean Formulation: 100% Veggie Capsules. No hidden fillers. Raw, natural ingredients.\n• Holistic Support: Includes Emblica Officinalis (Amla) and Glycyrrhiza Glabra for overall wellness and recovery.', 
 'Each 500mg Veggie Capsule contains: Purified Shilajit (Asphaltum punjabianum) 150mg, Ashwagandha (Withania somnifera) 120mg, Safed Musli (Chlorophytum borivilianum) 80mg, Kaunch Beej (Mucuna pruriens) 60mg, Gokshura (Tribulus terrestris) 50mg, Kesar/Saffron (Crocus sativus) 5mg, Yashad Bhasma (Zinc oxide) 20mg, Amla (Emblica officinalis) 15mg.', 
 'Take 1-2 capsules daily after a meal with warm milk or water, or as directed by your healthcare professional. Maintain consistency for 60-90 days for peak results.', 
 'Wolfpack is for adult use only (men above 18). Consult your physician before use if you have an underlying medical condition like hypertension, diabetes, or cardiovascular issues. Do not exceed the recommended daily dose.', 
 1),
(2, 
 'WOLFTOX - LIVER SUPPORT & DETOX', 
 'wolftox-liver-support-detox', 
 'Your liver is the hardest working organ in your body, filtering toxins and managing your metabolism 24/7. Wolftox provides the ultimate shield for your internal engine.', 
 'Your liver is the hardest working organ in your body, filtering toxins and managing your metabolism 24/7. Whether you\'re pushing through an intense training cycle or just looking to optimize your health, Wolftox by Wolf Nutrition provides the ultimate support for your body’s natural filtration system. Formulated with high-potency Ayurvedic extracts like Kutki, Kalmegh, Bhringraj, and Punarnava, it maintains liver enzyme profiles, triggers deep cell cleansing, and promotes clean, long-term vitality.', 
 2, 
 'assets/images/products/wolftox.png', 
 'assets/images/products/wolftox.png,assets/images/products/wolftox_label.png', 
 '• Enzymatic Support: Expertly formulated to support healthy liver enzyme levels (SGOT, SGPT) and peak liver cell function.\n• Deep Detoxification: Helps flush out impurities, heavy metals, and metabolic waste, keeping you feeling light, clean, and energized.\n• Immune & Digestive Kickstart: A healthy liver means a healthy gut. Wolftox aids in bile production for smoother digestion, bloating relief, and boosts your natural immunity.\n• Clean Formulation: 100% Veggie Capsules. No chemical fillers, binders, or artificial coloring.', 
 'Each 500mg Veggie Capsule contains: Kutki (Picrorhiza kurroa) 120mg, Kalmegh (Andrographis paniculata) 100mg, Bhringraj (Eclipta alba) 80mg, Punarnava (Boerhavia diffusa) 80mg, Bhumi Amla (Phyllanthus niruri) 60mg, Milk Thistle extract (80% Silymarin) 40mg, Sharpunkha (Tephrosia purpurea) 20mg.', 
 'Take 1 capsule twice daily before meals, or as directed by your healthcare professional. Drink plenty of water throughout the day to support detox.', 
 'Keep out of reach of children. Not recommended for pregnant or lactating women without consulting a doctor. Store in a cool, dry place away from direct sunlight.', 
 1),
(3, 
 'WolfBurn - Thermo-Metabolic Lean Burner 60 Capsules', 
 'wolfburn-thermo-metabolic-lean-burner', 
 'Advanced thermo-metabolic fat burner with Ayurvedic thermogenic herbs for lean body composition.', 
 'WolfBurn combines ancient Ayurvedic thermogenic herbs with modern metabolic science. Formulated to support healthy metabolism, thermogenesis, and lean body composition without stimulants or harsh chemicals.', 
 3, 
 'assets/images/products/wolfburn.png', 
 'assets/images/products/wolfburn.png', 
 'Thermogenic Support: Clinically-inspired herbs like Garcinia Cambogia, Green Tea, and forskolin boost metabolic rate and calorie burning.\nLean Body Support: Helps maintain healthy body composition while preserving lean muscle mass.\nNatural Energy: Provides sustained, jitter-free energy from natural botanical sources.\nClean Formula: 100% Veggie Capsules. No artificial stimulants, fillers, or synthetic additives.', 
 'Each 500mg Veggie Capsule contains: Garcinia Cambogia Extract (60% HCA) 150mg, Green Tea Extract (50% EGCG) 100mg, Forskolin (Coleus forskohlii) 80mg, Chromium Picolinate 40mcg, Cayenne Pepper Extract 30mg, BioPerine (Black Pepper Extract) 5mg.', 
 'Take 1 capsule twice daily 30 minutes before meals with water. For best results, combine with regular exercise and a balanced diet. Do not exceed 2 capsules in a 24-hour period.', 
 'Not recommended for pregnant or lactating women. Consult your physician before use if you have heart disease, high blood pressure, or are taking any medication. Keep out of reach of children.', 
 0),
(4, 
 'WOLFPRO - Premium Protein Isolate', 
 'wolfpro-premium-protein-isolate', 
 'Ultra-pure whey protein isolate for muscle recovery, growth, and daily protein supplementation.', 
 'WOLFPRO delivers ultra-pure whey protein isolate sourced from grass-fed cows. Each serving provides a complete amino acid profile with minimal lactose, perfect for post-workout recovery and daily protein needs.', 
 4, 
 'assets/images/products/189873.png',
 'assets/images/products/189873.png',
 'Ultra-Pure Isolate: 27g protein per serving with 90%+ protein content and minimal carbs and fats.\nFast Absorption: Micro-filtered whey isolate for rapid amino acid delivery to muscles.\nMuscle Recovery: Rich in BCAAs and essential amino acids to support post-workout repair.\nClean Label: No added sugar, no artificial colors, no fillers. Informed Sport certified.', 
 'Whey Protein Isolate (from Grass-Fed Cows), Natural Cocoa Flavor (for Chocolate variant), Sunflower Lecithin (emulsifier), Steviol Glycosides (natural sweetener).', 
 'Mix 1 scoop (30g) with 200-250ml of cold water or milk. Shake or blend for 20-30 seconds. Best consumed within 30 minutes after exercise or as a meal supplement.', 
 'Not a substitute for a balanced diet. Do not exceed recommended daily intake. Keep in a cool, dry place away from direct sunlight. Once opened, consume within 60 days.', 
 0),
(5,
 'WOLFPRO - Premium Protein Isolate (2kg)',
 'wolfpro-premium-protein-isolate-2kg',
 'Ultra-pure whey protein isolate for muscle recovery, growth, and daily protein supplementation.',
 'WOLFPRO delivers ultra-pure whey protein isolate sourced from grass-fed cows. Each serving provides a complete amino acid profile with minimal lactose, perfect for post-workout recovery and daily protein needs.',
 4,
 'assets/images/products/189874.png',
 'assets/images/products/189874.png',
 'Ultra-Pure Isolate: 27g protein per serving with 90%+ protein content and minimal carbs and fats.\nFast Absorption: Micro-filtered whey isolate for rapid amino acid delivery to muscles.\nMuscle Recovery: Rich in BCAAs and essential amino acids to support post-workout repair.\nClean Label: No added sugar, no artificial colors, no fillers. Informed Sport certified.',
 'Whey Protein Isolate (from Grass-Fed Cows), Natural Cocoa Flavor (for Chocolate variant), Sunflower Lecithin (emulsifier), Steviol Glycosides (natural sweetener).',
 'Mix 1 scoop (30g) with 200-250ml of cold water or milk. Shake or blend for 20-30 seconds. Best consumed within 30 minutes after exercise or as a meal supplement.',
 'Not a substitute for a balanced diet. Do not exceed recommended daily intake. Keep in a cool, dry place away from direct sunlight. Once opened, consume within 60 days.',
 0);

-- Seed Product Variants
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `size_capsules`, `price`, `sale_price`, `stock_qty`, `is_default`) VALUES
(1, 1, 'WP30', '30 Veggie Capsules', 1697.00, 1194.00, 100, 1),
(2, 1, 'WP60', '60 Veggie Capsules', 2498.00, 1999.00, 150, 0),
(3, 2, 'WT30', '30 Veggie Capsules', 1499.00, 1275.00, 80, 1),
(4, 2, 'WT60', '60 Veggie Capsules', 1499.00, 999.00, 120, 0),
(5, 3, 'WB60', '60 Veggie Capsules', 1799.00, 1499.00, 0, 1),
(6, 4, 'WP1KG', '1kg', 2999.00, 2499.00, 0, 1),
(7, 5, 'WP2KG', '2kg', 4999.00, 4199.00, 0, 0);

-- Seed Bundles
INSERT INTO `bundles` (`id`, `title`, `slug`, `description`, `banner_image`, `combo_price`, `discount_percent`, `status`, `display_order`) VALUES
(1, 
 'Wolfpack (60 Capsules) + Wolftox (60 Capsules) Combo', 
 'wolfpack-wolftox-combo', 
 'Unleash peak performance while keeping your internal engine clean. This ultimate combination pack includes our premier vitality supplement Wolfpack (60 Caps) and our expert liver protectant Wolftox (60 Caps). Total retail price of both individual items is ₹2,998, buy them together now for ₹2,699 only and save ₹299 (10% extra savings!).', 
 'assets/images/products/wolfpack_wolftox_combo.png', 
 2699.00, 
 10.00, 
 1, 
 1);

-- Seed Bundle Items
INSERT INTO `bundle_items` (`id`, `bundle_id`, `product_id`, `variant_id`) VALUES
(1, 1, 1, 2),
(2, 1, 2, 4);

-- Seed Announcements
INSERT INTO `announcements` (`id`, `message`, `link`, `display_order`, `status`) VALUES
(1, '🔥 FREE Shipping on all prepaid orders! Limited time only.', '#', 1, 1),
(2, '⚡ Wolfpack Combo Offer: Buy 2 products together, Save 10% automatically!', '#', 2, 1),
(3, '🌿 100% Ayurvedic Sourced | FSSAI Certified | Veggie Capsules', '/certificates.php', 3, 1);

-- Seed Quantity Discounts
INSERT INTO `quantity_discounts` (`id`, `product_id`, `min_qty`, `discount_percent`, `status`) VALUES
(1, NULL, 2, 10.00, 1),
(2, NULL, 3, 15.00, 1);

-- Seed Blog Posts
INSERT INTO `blog_posts` (`id`, `title`, `slug`, `category_tag`, `cover_image`, `body`, `status`) VALUES
(1, 
 'Shilajit Demystified: The Ancient Himalayan Tonic for Elite Stamina', 
 'shilajit-demystified-himalayan-tonic-stamina', 
 'Vitality', 
 'assets/images/blog/shilajit_blog.png', 
 '<p>For thousands of years, Himalayan yogis and ancient Ayurvedic texts have extolled the virtues of a mysterious black resin known as <strong>Shilajit</strong> (Black Asphaltum). Reoccurring in high altitude rock formations, Shilajit is rich in fulvic acid, humic acid, and more than 84 minerals.</p><h3>How Shilajit Boosts Performance</h3><p>At a cellular level, Shilajit works by optimizing mitochondrial function—the powerhouses of your cells. By enhancing CoQ10 levels, it facilitates faster conversion of oxygen and nutrients into ATP (cellular energy). This results in lower muscle fatigue, quicker recovery cycles, and improved cardiorespiratory efficiency.</p><p>In Wolfpack, we utilize purified Shilajit standardized for fulvic acid content, combining it with high-potency Ashwagandha to create a synergistic vitality booster that works safely and naturally.</p>', 
 1),
(2, 
 'The Hardest Working Filter: Why Your Liver Needs a Routine Detox', 
 'why-your-liver-needs-detox', 
 'Detox', 
 'assets/images/blog/liver_blog.png', 
 '<p>Your liver performs over 500 vital functions every single day, working 24/7 to cleanse blood, synthesize protein, and store energy. From processing greasy foods and environmental pollutants to filtering cellular waste, it is your body\'s primary engine shield.</p><h3>Signs of an Overburdened Liver</h3><ul><li>Chronic fatigue and sluggish energy levels</li><li>Frequent bloating, indigestion, or heartburn</li><li>Poor skin health or sudden breakouts</li><li>Slow metabolism and weight loss plateaus</li></ul><h3>The Ayurvedic Shield: Kutki and Kalmegh</h3><p>Traditional wellness employs hepatoprotective herbs like Kutki and Kalmegh. Kutki triggers cell regeneration, improves bile production, and lowers fatty acid accumulation in liver cells. Kalmegh acts as a natural antioxidant, neutralising toxic metabolites from medications, alcohol, and stress. Wolftox brings these together alongside Milk Thistle to support peak filtration and overall gut health.</p>', 
 1);

-- Seed CMS Pages
INSERT INTO `cms_pages` (`slug`, `title`, `body`) VALUES
('shipping-policy', 'Shipping Policy', '<h3>Shipping & Delivery Timeline</h3><p>At Wolf Nutrition, we strive to deliver your orders promptly and safely. Here is our shipping framework:</p><ul><li><strong>Order Processing:</strong> All orders are processed and packaged within 24 to 48 hours of confirmation.</li><li><strong>Estimated Shipping Time:</strong> Delivery usually takes 3 to 5 business days in metro cities, and 5 to 7 business days in other regions across India.</li><li><strong>Shipping Charges:</strong> We offer FREE Shipping on all prepaid orders storewide. Cash on Delivery (COD) orders attract a flat shipping and handling fee of ₹99.</li><li><strong>Courier Partners:</strong> We partner with India\'s leading courier networks including Bluedart, Delhivery, Expressbees, and DTDC to ensure reliable tracking.</li></ul>'),
('refund-policy', 'Refund & Return Policy', '<h3>Return and Replacement Window</h3><p>We want you to be completely satisfied with your wellness stack. Our return and refund guidelines include:</p><ul><li><strong>15-Day Replacement:</strong> We offer a 15-day free replacement policy for products that arrive damaged, have leaking seals, or are incorrect.</li><li><strong>Verification requirement:</strong> To initiate a replacement or return, please share clear photos/videos of the package and invoice to wolfnutritionwp@gmail.com.</li><li><strong>Refund Processing:</strong> Once approved, refunds for prepaid orders are credited back to the original payment source within 5 to 7 business days. For COD orders, we process refunds via UPI or bank transfer after customer details are confirmed.</li><li><strong>Non-Returnable Items:</strong> Open bottles, partially consumed capsules, or items purchased during special stock clearance sales are not eligible for returns.</li></ul>'),
('terms-of-service', 'Terms of Service', '<h3>User Terms and Agreements</h3><p>Welcome to Wolf Nutrition (wolfnutrition.in). By using our website and purchasing our products, you agree to comply with the following terms:</p><ol><li><strong>Product Representation:</strong> We make every effort to represent our products and packaging colors accurately. However, actual label placements, shapes, or color hues may vary slightly based on display monitors and screen settings.</li><li><strong>Medical Disclaimer:</strong> The products and details provided on this site are not evaluated by the FDA or intended to diagnose, treat, cure, or prevent any medical condition. Please consult your physician before starting any dietary supplement.</li><li><strong>Age Restriction:</strong> Wolfpack is strictly formulated for adult male use. You must be at least 18 years of age to purchase Wolfpack.</li><li><strong>Pricing:</strong> We reserve the right to alter pricing, discounts, and combo bundle values at any time without prior notification.</li></ol>'),
('privacy-policy', 'Privacy Policy', '<h3>Your Data Safety</h3><p>At Wolf Nutrition, safeguarding your private data is a top priority. We outline our policy below:</p><ul><li><strong>Information Collection:</strong> We collect details like name, email, shipping address, phone number, and payment method when you create an account or check out.</li><li><strong>Data Usage:</strong> Your details are used exclusively to process orders, generate invoices, send tracking updates (via SMS/WhatsApp), and run customer accounts.</li><li><strong>No Third-Party Sharing:</strong> We do not lease, trade, or sell customer databases to advertising networks or external third parties.</li><li><strong>Security:</strong> All transactional exchanges and credit details are processed via secure SSL gateways. We do not store sensitive payment passwords or card numbers on our local server.</li></ul>'),
('about-us', 'Welcome to the Pack: The Wolf Nutrition Story', '<h3>Welcome to the Pack: The Wolf Nutrition Story</h3><p>At Wolf Nutrition, we believe that true performance starts from within. We are dedicated to formulating premium, high-impact supplements that empower you to conquer your daily challenges with sustained energy, focus, and vitality.</p><h4>Our Philosophy: Ancient Wisdom Meets Modern Performance</h4><p>We bridge the gap between time-tested traditions and the rigorous demands of modern life. Our core formulations are built upon the foundation of potent, authentic Ayurvedic botanicals. By harnessing the natural power of trusted ingredients like Shilajit, Ashwagandha, Kutki, and Gokshura, we create active blends designed to naturally support stamina, detoxification, and overall peak performance.</p><h4>The Wolfpack Standard</h4><ul><li><strong>Uncompromising Quality:</strong> We meticulously source our botanical ingredients to ensure you receive the highest quality, active blends in every single veggie capsule bottle.</li><li><strong>Holistic Vitality:</strong> From comprehensive liver support to targeted vitality enhancers, our products are crafted to optimize your body\'s natural systems from the ground up.</li><li><strong>A Premium Experience:</strong> We believe wellness should look as good as it feels. Our commitment to excellence extends from our carefully balanced, science-backed formulations to our sleek, minimalist design aesthetic.</li></ul><h4>Our Commitment to You</h4><p>We aren\'t just creating supplements; we are building a standard for those who demand more from themselves. Whether you are pushing through a demanding workday or striving for new personal bests, Wolf Nutrition provides the essential tools to help you lead the pack.</p>');

-- Seed Quality Certificates
INSERT INTO `certificates` (`id`, `image_url`, `title`, `display_order`, `status`) VALUES
(1, 'assets/images/certs/fssai_cert.png', 'FSSAI Registration License No. 22126022000063', 1, 1),
(2, 'assets/images/certs/ayurvedic_cert.png', '100% Pure Ayurvedic & Natural Botanicals', 2, 1),
(3, 'assets/images/certs/gmp_cert.png', 'GMP Good Manufacturing Practices Certified', 3, 1);

-- Seed WhatsApp Settings
INSERT INTO `whatsapp_settings` (`id`, `phone_number`, `greeting_message`, `status`) VALUES
(1, '+912212602200', 'Hey Wolf Nutrition! I\'m interested in your supplements. Can you help me choose?', 1);

-- Seed Coupons
INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_order_amount`, `max_discount`, `expiry_date`, `usage_limit`, `used_count`, `status`) VALUES
(1, 'WOLF10', 'percentage', 10.00, 500.00, 250.00, '2028-12-31', 500, 0, 1),
(2, 'ALPHA200', 'flat', 200.00, 1499.00, 0.00, '2028-12-31', 200, 0, 1);

-- Seed Reviews
INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `user_name`, `rating`, `title`, `review_text`, `is_approved`, `is_featured`) VALUES
(1, 1, 2, 'Yuvek Verma', 5, 'Absolute Game Changer!', 'I have been using the 60 Capsules pack of Wolfpack for over a month now. My stamina levels and gym performance have gone through the roof! It has a subtle energy release without any jitters. Highly recommended.', 1, 1),
(2, 2, NULL, 'Karan Sharma', 5, 'Highly effective detox', 'I take Wolftox daily to protect my liver from a high-protein bodybuilding diet. It keeps my digestion smooth and completely removes bloating. 100% natural, no chemical taste.', 1, 1),
(3, 1, NULL, 'Sanjay Sen', 4, 'Very good vitality supplement', 'Great ingredients. Standardized Shilajit + Ashwagandha really helps with office fatigue. Deducted 1 star because shipping took 5 days, but the product is excellent.', 1, 0);

-- 18. Newsletter Subscribers Table
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
