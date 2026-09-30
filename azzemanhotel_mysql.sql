-- Azzeman Hotel - MySQL Database Schema
-- Generated from Prisma schema conversion
-- Engine: InnoDB, Collation: utf8mb4_unicode_ci

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Create database (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS `azzeman_hotel` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `azzeman_hotel`;

-- --------------------------------------------------------
-- Table: AdminUser
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: Room
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(255) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 0,
  `facilities` TEXT NOT NULL,
  `booked` INT(11) NOT NULL DEFAULT 0,
  `price_per_night` DECIMAL(10, 2) NOT NULL DEFAULT 100.00,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `max_guests` INT(11) NOT NULL DEFAULT 2,
  `price_per_adult` DECIMAL(10, 2) NOT NULL DEFAULT 100.00,
  `price_additional_adult` DECIMAL(10, 2) NOT NULL DEFAULT 50.00,
  `price_per_child` DECIMAL(10, 2) NOT NULL DEFAULT 50.00,
  `image_path` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `room_images` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `room_id` INT(11) NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_room_images_room_id` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: RoomPriceTiers (per-guest-count pricing)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `room_price_tiers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `room_id` INT(11) NOT NULL,
  `guest_count` INT(11) NOT NULL,
  `price_per_night` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_guest` (`room_id`, `guest_count`, `currency`),
  KEY `idx_room_id` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: BookingRoom
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `booking_rooms` (
  `id` VARCHAR(255) NOT NULL,
  `guest_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone_number` VARCHAR(50) NOT NULL,
  `check_in_date` DATETIME NOT NULL,
  `check_out_date` DATETIME NOT NULL,
  `room_type` VARCHAR(255) NOT NULL,
  `number_of_guests` INT(11) NOT NULL DEFAULT 1,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `price_per_night` DECIMAL(10, 2) DEFAULT NULL,
  `price_additional_adult` DECIMAL(10, 2) DEFAULT NULL,
  `price_per_child` DECIMAL(10, 2) DEFAULT NULL,
  `currency` VARCHAR(10) DEFAULT NULL,
  `total_price` DECIMAL(10, 2) DEFAULT NULL,
  `guests_count` INT(11) DEFAULT NULL,
  `rooms_count` INT(11) NOT NULL DEFAULT 1,
  `adults_count` INT(11) NOT NULL DEFAULT 1,
  `children_count` INT(11) NOT NULL DEFAULT 0,
  `room_details` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_room_type` (`room_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: SpaBooking
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `spa_services` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `spa_service_prices` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `service_id` INT(11) NOT NULL,
  `currency` VARCHAR(10) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_currency` (`service_id`, `currency`),
  KEY `idx_service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `meeting_venues` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `capacity_note` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `meeting_venue_prices` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `venue_id` INT(11) NOT NULL,
  `currency` VARCHAR(10) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `venue_currency` (`venue_id`, `currency`),
  KEY `idx_venue_id` (`venue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: SpaBooking
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `spa_bookings` (
  `id` VARCHAR(255) NOT NULL,
  `guest_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone_number` VARCHAR(50) NOT NULL,
  `service` VARCHAR(255) NOT NULL,
  `date` DATETIME NOT NULL,
  `time` VARCHAR(10) NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `total_price` DECIMAL(10, 2) DEFAULT NULL,
  `currency` VARCHAR(10) DEFAULT NULL,
  `payment_status` VARCHAR(50) NOT NULL DEFAULT 'unpaid',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_date` (`date`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: MeetingBooking
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `meeting_bookings` (
  `id` VARCHAR(255) NOT NULL,
  `contact_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone_number` VARCHAR(50) NOT NULL,
  `company_name` VARCHAR(255) DEFAULT NULL,
  `venue_name` VARCHAR(255) NOT NULL,
  `date` DATETIME NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `total_price` DECIMAL(10, 2) DEFAULT NULL,
  `currency` VARCHAR(10) DEFAULT NULL,
  `payment_status` VARCHAR(50) NOT NULL DEFAULT 'unpaid',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_date` (`date`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: GalleryCategory
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery_categories` (
  `id` VARCHAR(100) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_gallery_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: GalleryImage
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `image_path` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: ImageBlog
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `image_blogs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `image_id` INT(11) NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `content` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_image_blog_image` (`image_id`),
  CONSTRAINT `fk_image_blog_image` FOREIGN KEY (`image_id`) REFERENCES `gallery_images` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------
-- Table: SiteImage
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_images` (
  `key` VARCHAR(255) NOT NULL,
  `src` TEXT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: VirtualTour
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `virtual_tours` (
  `id` INT(11) NOT NULL DEFAULT 1,
  `image_url` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: Hotspot
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `hotspots` (
  `id` VARCHAR(255) NOT NULL,
  `pitch` DECIMAL(10, 2) NOT NULL,
  `yaw` DECIMAL(10, 2) NOT NULL,
  `text` TEXT NOT NULL,
  `tour_id` INT(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_tour_id` (`tour_id`),
  CONSTRAINT `fk_hotspot_tour` FOREIGN KEY (`tour_id`) REFERENCES `virtual_tours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: currencies
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `currencies` (
  `code` VARCHAR(10) NOT NULL,
  `symbol` VARCHAR(10) NOT NULL DEFAULT '',
  `name` VARCHAR(50) NOT NULL DEFAULT '',
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: room_currency_prices
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `room_currency_prices` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `room_id` INT(11) NOT NULL,
  `currency` VARCHAR(10) NOT NULL,
  `price_per_adult` DECIMAL(10, 2) NOT NULL DEFAULT 100.00,
  `price_additional_adult` DECIMAL(10, 2) NOT NULL DEFAULT 50.00,
  `price_per_child` DECIMAL(10, 2) NOT NULL DEFAULT 40.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_currency` (`room_id`, `currency`),
  KEY `idx_room_id` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed Data
-- --------------------------------------------------------

-- Default Admin User (password: SecurePass!2024)
-- Password hash generated with: password_hash('SecurePass!2024', PASSWORD_DEFAULT)
INSERT INTO `admin_users` (`username`, `password_hash`) VALUES
('azzeman_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE `username` = `username`;

-- Default Currencies
INSERT INTO `currencies` (`code`, `symbol`, `name`, `is_default`) VALUES
('USD', '$', 'US Dollar', 1),
('ETB', 'Br', 'Ethiopian Birr', 0),
('EUR', '€', 'Euro', 0),
('GBP', '£', 'British Pound', 0)
ON DUPLICATE KEY UPDATE `is_default` = VALUES(`is_default`);

-- Default Rooms
INSERT INTO `rooms` (`type`, `quantity`, `facilities`, `booked`, `price_per_night`, `currency`, `max_guests`, `price_per_adult`, `price_additional_adult`, `price_per_child`) VALUES
('King room', 52, 'King size bed, Mini bar, writing table, coffee table, Kettle, Bottle of water, safe box', 0, 150.00, 'USD', 2, 150.00, 75.00, 50.00),
('Twin', 11, 'Double bed, Mini bar, writing table, coffee table, cattle, Bottle of water, safe box', 0, 120.00, 'USD', 2, 120.00, 60.00, 40.00),
('Deluxe', 9, 'King size bed, Mini bar, writing table, coffee table, Kettle, Bottle of water, safe box, Iron and ironing board, Sofa bed, carpeted room', 0, 180.00, 'USD', 2, 180.00, 90.00, 60.00),
('Executive Suite', 5, 'King size bed, Mini bar, writing table, coffee table, Kettle, Bottle of water, safe box, Kitchen, Steam bath, sofa bed, Iron and ironing board, Private Steam', 0, 250.00, 'USD', 4, 250.00, 125.00, 80.00),
('Junior suite', 2, 'King size bed, Mini bar, writing table, coffee table, Kettle, Bottle of water, safe box, Iron and ironing board, Sofa.', 0, 200.00, 'USD', 3, 200.00, 100.00, 70.00)
ON DUPLICATE KEY UPDATE `type` = VALUES(`type`), `price_per_night` = VALUES(`price_per_night`), `currency` = VALUES(`currency`), `max_guests` = VALUES(`max_guests`), `price_per_adult` = VALUES(`price_per_adult`), `price_additional_adult` = VALUES(`price_additional_adult`), `price_per_child` = VALUES(`price_per_child`);

-- Seed default room currency prices
INSERT INTO `room_currency_prices` (room_id, currency, price_per_adult, price_additional_adult, price_per_child)
SELECT id, 'USD', price_per_adult, price_additional_adult, price_per_child FROM rooms
ON DUPLICATE KEY UPDATE price_per_adult=VALUES(price_per_adult), price_additional_adult=VALUES(price_additional_adult), price_per_child=VALUES(price_per_child);

INSERT INTO `room_currency_prices` (room_id, currency, price_per_adult, price_additional_adult, price_per_child)
SELECT id, 'ETB', price_per_adult * 100, price_additional_adult * 100, price_per_child * 100 FROM rooms
ON DUPLICATE KEY UPDATE price_per_adult=VALUES(price_per_adult), price_additional_adult=VALUES(price_additional_adult), price_per_child=VALUES(price_per_child);

INSERT INTO `room_currency_prices` (room_id, currency, price_per_adult, price_additional_adult, price_per_child)
SELECT id, 'EUR', price_per_adult * 0.9, price_additional_adult * 0.9, price_per_child * 0.9 FROM rooms
ON DUPLICATE KEY UPDATE price_per_adult=VALUES(price_per_adult), price_additional_adult=VALUES(price_additional_adult), price_per_child=VALUES(price_per_child);

INSERT INTO `room_currency_prices` (room_id, currency, price_per_adult, price_additional_adult, price_per_child)
SELECT id, 'GBP', price_per_adult * 0.8, price_additional_adult * 0.8, price_per_child * 0.8 FROM rooms
ON DUPLICATE KEY UPDATE price_per_adult=VALUES(price_per_adult), price_additional_adult=VALUES(price_additional_adult), price_per_child=VALUES(price_per_child);

-- Default Gallery Images & Blogs
INSERT INTO `gallery_categories` (`id`, `name`, `description`) VALUES
('rooms', 'Rooms', 'Stories from our luxurious rooms'),
('hotel', 'Hotel', 'Scenes from around the hotel grounds'),
('amenities', 'Amenities', 'Highlights of premium amenities')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

-- Default Gallery Images & Blogs
INSERT INTO `gallery_images` (`image_path`, `category`) VALUES
('uploads/gallery/sample-room.jpg', 'Rooms'),
('uploads/gallery/sample-lobby.jpg', 'Hotel'),
('uploads/gallery/sample-amenity.jpg', 'Amenities')
ON DUPLICATE KEY UPDATE `image_path` = VALUES(`image_path`);

INSERT INTO `image_blogs` (`image_id`, `title`, `content`) VALUES
((SELECT id FROM gallery_images WHERE image_path = 'uploads/gallery/sample-room.jpg' LIMIT 1), 'Serene King Suite', '<p>Wake up to panoramic city views in our signature king suite, curated with contemporary Ethiopian art and plush comfort.</p>'),
((SELECT id FROM gallery_images WHERE image_path = 'uploads/gallery/sample-lobby.jpg' LIMIT 1), 'A Grand Arrival', '<p>The lobby is a living tapestry of Azzeman hospitality. Soft jazz, warm lighting, and attentive concierge services set the tone for your stay.</p>'),
((SELECT id FROM gallery_images WHERE image_path = 'uploads/gallery/sample-amenity.jpg' LIMIT 1), 'Relaxation, Elevated', '<p>Unwind at our rooftop spa where curated treatments meet breathtaking sunsets.</p>')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `content` = VALUES(`content`);

-- Default Site Images
INSERT INTO `site_images` (`key`, `src`) VALUES
('hero_background', 'https://images.unsplash.com/photo-1582719508461-905c673771fd?q=80&w=2070&auto=format&fit=crop'),
('about_image', 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=2070&auto=format&fit=crop'),
('rooms_image', 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=1200&auto=format&fit=crop'),
('meetings_image', 'https://images.unsplash.com/photo-1543269865-cbf427effbad?q=80&w=2070&auto=format&fit=crop'),
('spa_image_1', 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?q=80&w=800&auto=format&fit=crop'),
('spa_image_2', 'https://images.unsplash.com/photo-1519824145371-296894a0d72b?q=80&w=800&auto=format&fit=crop'),
('spa_image_3', 'https://images.unsplash.com/photo-1597015552392-4916a6953258?q=80&w=800&auto=format&fit=crop'),
('spa_image_4', 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=800&auto=format&fit=crop'),
('gallery_page_fallback', 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?q=80&w=1200&auto=format&fit=crop'),
('og_image', 'https://images.unsplash.com/photo-1582719508461-905c673771fd?q=80&w=1200&auto=format&fit=crop')
ON DUPLICATE KEY UPDATE `src` = VALUES(`src`);

-- Default Virtual Tour
INSERT INTO `virtual_tours` (`id`, `image_url`) VALUES
(1, 'https://pannellum.org/images/alma.jpg')
ON DUPLICATE KEY UPDATE `image_url` = VALUES(`image_url`);

-- Default Hotspots
INSERT INTO `hotspots` (`id`, `pitch`, `yaw`, `text`, `tour_id`) VALUES
('hs-1', -5.00, 10.00, 'This is one of the 66 high-precision antennas that make up the ALMA array.', 1),
('hs-2', 2.00, -130.00, 'The Atacama Desert provides the perfect dry, high-altitude conditions for astronomical observation.', 1),
('hs-3', -2.00, 150.00, 'The correlator, a powerful supercomputer, combines the signals from all antennas.', 1)
ON DUPLICATE KEY UPDATE `text` = VALUES(`text`);