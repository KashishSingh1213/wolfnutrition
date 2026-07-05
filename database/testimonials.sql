CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_title` VARCHAR(150) NULL,
  `testimonial_text` TEXT NOT NULL,
  `rating` INT DEFAULT 5,
  `avatar_url` VARCHAR(255) NULL,
  `display_order` INT DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `testimonials` (`customer_name`, `customer_title`, `testimonial_text`, `rating`, `display_order`, `is_featured`, `status`) VALUES
('Rahul Sharma', 'Fitness Trainer, Mumbai', 'Wolf Nutrition supplements have completely transformed my training recovery. The Vitality Stack is a game-changer for energy and stamina.', 5, 1, 1, 1),
('Amit Verma', 'Software Engineer, Delhi', 'I was skeptical at first but after 2 weeks of using the Liver Detox supplement, I noticed a massive improvement in my energy levels and digestion.', 5, 2, 1, 1),
('Priya Singh', 'Yoga Instructor, Bangalore', 'The 100% Ayurvedic ingredients give me confidence. No side effects, just pure results. Highly recommend to anyone serious about their health.', 4, 3, 0, 1);
