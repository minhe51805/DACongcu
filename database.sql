-- Tạo database và tables cho hệ thống CONVOI
-- Chạy script này trong phpMyAdmin hoặc MySQL command line

-- Tạo database (nếu chưa có)
CREATE DATABASE IF NOT EXISTS convoi_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE convoi_app;

-- Table users (Người dùng) - Authentication system
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default-avatar.jpg',
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','inactive','pending','banned') DEFAULT 'pending',
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verification_token` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int DEFAULT '0',
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_username` (`username`),
  UNIQUE KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`),
  KEY `idx_email_verified` (`email_verified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table user_sessions (Phiên đăng nhập)
CREATE TABLE `user_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_session_token` (`session_token`),
  KEY `fk_user_session` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `fk_user_session` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table user_activities (Hoạt động người dùng)
CREATE TABLE `user_activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_user_activity` (`user_id`),
  KEY `idx_activity_type` (`activity_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_user_activity` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table events (Sự kiện thiện nguyện) - Updated with goal amount for donations
CREATE TABLE `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(255) DEFAULT 'default-event.jpg',
  `event_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `max_participants` int DEFAULT 0,
  `current_participants` int DEFAULT 0,
  `goal_amount` decimal(12,2) DEFAULT '0.00',
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table event_registrations (Đăng ký tham gia sự kiện)
CREATE TABLE `event_registrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `message` text,
  `status` enum('registered','confirmed','cancelled') DEFAULT 'registered',
  `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_event` (`event_id`),
  KEY `idx_email` (`email`),
  CONSTRAINT `fk_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table donations (Ủng hộ thiện nguyện) - Updated for multiple payment methods
CREATE TABLE `donations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int DEFAULT NULL,
  `donor_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `message` text,
  `is_anonymous` tinyint(1) DEFAULT '0',
  `payment_method` enum('momo','vnpay','bank','cash') DEFAULT 'momo',
  `status` enum('pending','completed','failed','awaiting_confirmation','confirmed') DEFAULT 'pending',
  `momo_order_id` varchar(100) DEFAULT NULL,
  `momo_request_id` varchar(100) DEFAULT NULL,
  `momo_trans_id` varchar(100) DEFAULT NULL,
  `vnpay_txn_ref` varchar(100) DEFAULT NULL,
  `vnpay_transaction_no` varchar(100) DEFAULT NULL,
  `vnpay_bank_code` varchar(20) DEFAULT NULL,
  `vnpay_pay_date` varchar(14) DEFAULT NULL,
  `bank_transfer_proof` varchar(255) DEFAULT NULL,
  `admin_notes` text,
  `confirmed_by` varchar(100) DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `donation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_donation_event` (`event_id`),
  KEY `idx_payment_method` (`payment_method`),
  KEY `idx_status` (`status`),
  KEY `idx_donation_date` (`donation_date`),
  KEY `idx_momo_order` (`momo_order_id`),
  KEY `idx_vnpay_txn` (`vnpay_txn_ref`),
  CONSTRAINT `fk_donation_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table categories (Danh mục sản phẩm)
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table products (Sản phẩm)
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT 'default-product.jpg',
  `gallery` json DEFAULT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `stock` int NOT NULL DEFAULT '0', -- Keep for backward compatibility
  `sku` varchar(100) DEFAULT NULL,
  `weight` decimal(8,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT '0',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_sku` (`sku`),
  KEY `fk_category` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_featured` (`featured`),
  KEY `idx_price` (`price`),
  CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table orders (Đơn hàng)
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `notes` text,
  `total_amount` decimal(12,2) NOT NULL,
  `payment_method` enum('cod','momo','vnpay','bank') DEFAULT 'cod',
  `status` enum('pending','confirmed','paid','shipped','delivered','cancelled','failed','awaiting_payment') DEFAULT 'pending',
  `momo_request_id` varchar(100) DEFAULT NULL,
  `momo_trans_id` varchar(100) DEFAULT NULL,
  `momo_response` json DEFAULT NULL,
  `vnpay_txn_ref` varchar(100) DEFAULT NULL,
  `vnpay_response` json DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `shipped_date` timestamp NULL DEFAULT NULL,
  `delivered_date` timestamp NULL DEFAULT NULL,
  `admin_notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_order_id` (`order_id`),
  KEY `idx_payment_method` (`payment_method`),
  KEY `idx_status` (`status`),
  KEY `idx_email` (`email`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_momo_request` (`momo_request_id`),
  KEY `idx_vnpay_txn` (`vnpay_txn_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table order_items (Chi tiết đơn hàng)
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_order_items_order` (`order_id`),
  KEY `fk_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data

-- Sample users (password: 123456 - hashed with password_hash)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `phone`, `role`, `status`, `email_verified`) VALUES
('admin', 'admin@convoi.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '0901234567', 'admin', 'active', 1),
('user1', 'user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn An', '0912345678', 'user', 'active', 1),
('user2', 'user2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị Bình', '0923456789', 'user', 'active', 1),
('moderator', 'mod@convoi.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Văn Cường', '0934567890', 'admin', 'active', 1);

-- Sample categories
INSERT INTO `categories` (`name`, `description`) VALUES
('Thực phẩm hữu cơ', 'Các sản phẩm thực phẩm tự nhiên, hữu cơ'),
('Đồ thủ công', 'Sản phẩm handmade từ các làng nghề truyền thống'),
('Quần áo', 'Trang phục thời trang bền vững'),
('Phụ kiện', 'Các phụ kiện thời trang và đồ dùng');

-- Sample events
INSERT INTO `events` (`title`, `description`, `image_url`, `event_date`, `end_date`, `location`, `max_participants`) VALUES
('Chăm sóc người già neo đơn', 'Hoạt động thăm hỏi và chăm sóc người già tại viện dưyang lão', 'event1.jpg', '2025-06-15', '2025-06-15', 'Viện dưỡng lão Hạnh Phúc, Q.Bình Thạnh', 50),
('Xây dựng nhà tình thương', 'Chương trình xây dựng nhà cho hộ nghèo vùng cao', 'event2.jpg', '2025-07-01', '2025-07-03', 'Xã Tà Van, Sa Pa, Lào Cai', 30),
('Phát cơm từ thiện', 'Nấu và phát cơm miễn phí cho người vô gia cư', 'event3.jpg', '2025-06-20', '2025-06-20', 'Công viên 23/9, Q.1, TP.HCM', 100);

-- Sample products
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `image`, `stock_quantity`, `stock`, `sku`) VALUES
(1, 'Gạo hữu cơ ST25', 'Gạo hữu cơ chất lượng cao, không thuốc trừ sâu', 150000, 'product1.jpg', 100, 100, 'ST25-001'),
(1, 'Mật ong rừng nguyên chất', 'Mật ong thu hoạch từ rừng tự nhiên, không pha trộn', 250000, 'product2.jpg', 50, 50, 'HONEY-001'),
(2, 'Túi xách cói', 'Túi xách thủ công từ cói tự nhiên', 180000, 'product3.jpg', 75, 75, 'BAG-001'),
(3, 'Áo len handmade', 'Áo len đan thủ công từ len tự nhiên', 350000, 'product4.jpg', 25, 25, 'SWEATER-001'),
(4, 'Vòng tay gỗ trầm hương', 'Vòng tay gỗ trầm hương tự nhiên', 120000, 'product5.jpg', 200, 200, 'BRACELET-001');

-- Sample orders
INSERT INTO `orders` (`order_id`, `customer_name`, `email`, `phone`, `shipping_address`, `total_amount`, `payment_method`, `status`, `notes`, `created_at`) VALUES
('ORD20250605001', 'Nguyễn Văn An', 'an@example.com', '0901234567', '123 Nguyễn Huệ, P.Bến Nghé, Q.1, TP.HCM', 330000, 'cod', 'confirmed', 'Giao hàng giờ hành chính', '2025-06-01 10:30:00'),
('ORD20250605002', 'Trần Thị Bình', 'binh@example.com', '0912345678', '456 Lê Lợi, P.Phường 1, Q.Gò Vấp, TP.HCM', 250000, 'momo', 'paid', '', '2025-06-02 14:15:00'),
('ORD20250605003', 'Lê Văn Cường', 'cuong@example.com', '0923456789', '789 Trần Hưng Đạo, P.5, Q.5, TP.HCM', 530000, 'bank', 'awaiting_payment', 'Khách yêu cầu giao vào cuối tuần', '2025-06-03 16:45:00'),
('ORD20250605004', 'Phạm Thị Dung', 'dung@example.com', '0934567890', '321 Võ Văn Tần, P.6, Q.3, TP.HCM', 470000, 'vnpay', 'paid', '', '2025-06-04 09:20:00'),
('ORD20250605005', 'Hoàng Văn Em', 'em@example.com', '0945678901', '654 Cách Mạng Tháng 8, P.11, Q.10, TP.HCM', 300000, 'cod', 'shipped', 'Khách đặt hàng gấp', '2025-06-05 11:00:00');

-- Sample order items
INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES
-- Order 1: Gạo ST25 (1) + Túi xách cói (1)
(1, 1, 1, 150000, 150000),
(1, 3, 1, 180000, 180000),
-- Order 2: Mật ong (1)
(2, 2, 1, 250000, 250000),
-- Order 3: Gạo ST25 (2) + Vòng tay (1) + Túi xách (1)
(3, 1, 2, 150000, 300000),
(3, 5, 1, 120000, 120000),
(3, 3, 1, 180000, 180000),
-- Order 4: Áo len (1) + Vòng tay (1)
(4, 4, 1, 350000, 350000),
(4, 5, 1, 120000, 120000),
-- Order 5: Mật ong (1) + Gạo ST25 (1)
(5, 2, 1, 250000, 250000),
(5, 1, 1, 150000, 150000);

-- Insert sample events with goal amounts
INSERT INTO `events` (`title`, `description`, `image_url`, `event_date`, `end_date`, `location`, `max_participants`, `goal_amount`, `status`) VALUES
('Thiện nguyện vùng lũ', 'Hỗ trợ người dân vùng lũ miền Trung với nhu yếu phẩm thiết yếu', 'event1.jpg', '2025-07-15', '2025-08-15', 'Miền Trung', 0, 500000000.00, 'active'),
('Xây trường học sạch vùng cao', 'Xây dựng trường học sạch sẽ, hiện đại cho trẻ em vùng cao Tây Bắc. Dự án bao gồm xây dựng 5 phòng học, nhà vệ sinh sạch, sân chơi an toàn và hệ thống nước sạch.', 'school-project.jpg', '2025-08-01', '2025-12-31', 'Xã Tà Van, Huyện Sa Pa, Tỉnh Lào Cai', 0, 1000000000.00, 'active'),
('Tặng quà trẻ em mồ côi', 'Tặng quà và học bổng cho trẻ em mồ côi', 'event3.jpg', '2025-06-20', '2025-07-20', 'TP.HCM', 100, 200000000.00, 'active'),
('Phẫu thuật tim miễn phí', 'Chương trình phẫu thuật tim miễn phí cho trẻ em nghèo', 'event1.jpg', '2025-09-01', '2025-11-30', 'Bệnh viện Nhi Đồng', 0, 2000000000.00, 'active'),
('Cứu trợ đồng bào miền núi', 'Hỗ trợ đồng bào dân tộc thiểu số vùng sâu vùng xa', 'event2.jpg', '2025-10-15', '2025-12-15', 'Hà Giang', 0, 300000000.00, 'active');

-- Insert sample donations with different payment methods
INSERT INTO `donations` (`event_id`, `donor_name`, `email`, `phone`, `amount`, `message`, `is_anonymous`, `payment_method`, `status`, `momo_order_id`, `donation_date`) VALUES
(1, 'Nguyễn Văn An', 'an@example.com', '0901234567', 500000, 'Mong muốn giúp đỡ bà con vùng lũ', 0, 'momo', 'completed', 'DONATE_1234567890_1234', '2025-06-01 10:30:00'),
(1, 'Trần Thị Bình', 'binh@example.com', '0912345678', 1000000, 'Ủng hộ đồng bào miền Trung', 0, 'vnpay', 'completed', 'DONATE_1234567891_1235', '2025-06-02 14:15:00'),
(2, 'Lê Văn Cường', 'cuong@example.com', '0923456789', 2000000, 'Hy vọng các em có trường học đẹp', 1, 'bank', 'confirmed', 'DONATE_1234567892_1236', '2025-06-03 16:45:00'),
(2, 'Phạm Thị Mai', 'mai@example.com', '0934567890', 5000000, 'Ủng hộ xây trường cho các em nhỏ vùng cao', 0, 'vnpay', 'completed', 'DONATE_1234567893_1237', '2025-06-04 09:20:00'),
(2, 'Hoàng Văn Nam', 'nam@example.com', '0945678901', 3000000, 'Mong các em có môi trường học tập tốt', 0, 'momo', 'completed', 'DONATE_1234567894_1238', '2025-06-05 11:00:00'),
(2, 'Nguyễn Thị Lan', 'lan@example.com', '0956789012', 1500000, 'Giáo dục là tương lai của đất nước', 0, 'bank', 'confirmed', 'DONATE_1234567895_1239', '2025-06-06 14:30:00'),
(2, 'Trần Văn Đức', 'duc@example.com', '0967890123', 10000000, 'Ủng hộ dự án xây trường học', 0, 'vnpay', 'completed', 'DONATE_1234567896_1240', '2025-06-07 16:20:00'),
(3, 'Phạm Thị Dung', 'dung@example.com', '0934567890', 300000, 'Hy vọng các em có Tết vui vẻ', 0, 'cash', 'confirmed', 'DONATE_1234567897_1241', '2025-06-04 09:20:00'),
(NULL, 'Hồ Văn Em', 'em@example.com', '0945678901', 800000, 'Quyên góp chung cho tổ chức', 0, 'momo', 'completed', 'DONATE_1234567898_1242', '2025-06-05 11:00:00');

-- Create blog posts table
CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `author` varchar(100) DEFAULT 'CONVOI Team',
  `author_id` int DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `is_featured` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `beneficiaries` int(11) DEFAULT 0,
  `locations` int(11) DEFAULT 0,
  `budget` decimal(15,2) DEFAULT 0.00,
  `donors` int(11) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `published_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  KEY `category` (`category`),
  KEY `published_at` (`published_at`),
  KEY `view_count` (`view_count`),
  KEY `is_featured` (`is_featured`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `fk_blog_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample blog posts
INSERT INTO `blog_posts` (`title`, `subtitle`, `slug`, `content`, `excerpt`, `featured_image`, `category`, `author`, `status`, `is_featured`, `view_count`, `beneficiaries`, `locations`, `budget`, `donors`, `published_at`) VALUES
('Chương trình "Tết ấm cho em"', 'Trao quà Tết cho 500 trẻ em vùng cao', 'tet-am-cho-em',
'<h2>Hành trình chuẩn bị</h2>
<p>Từ tháng 11/2024, đội ngũ CONVOI đã bắt đầu lên kế hoạch chi tiết cho chương trình. Chúng tôi đã khảo sát thực tế tại các vùng cao thuộc tỉnh Lào Cai, Hà Giang để hiểu rõ nhu cầu và hoàn cảnh của các em nhỏ nơi đây.</p>

<img src="assets/images/blog/tet-am-1.jpg" alt="Đội ngũ CONVOI khảo sát thực tế tại vùng cao" class="blog-content-image">

<h2>Những món quà ý nghĩa</h2>
<p>Mỗi phần quà Tết bao gồm: áo ấm mùa đông, giày dép, đồ dùng học tập, bánh kẹo và một khoản tiền mặt nhỏ để các em có thể mua sắm những gì cần thiết. Tổng giá trị mỗi phần quà là 500.000 VNĐ.</p>

<ul>
<li>Áo ấm chất lượng cao phù hợp với thời tiết vùng cao</li>
<li>Giày dép bền chắc cho việc đi lại trên địa hình khó khăn</li>
<li>Bộ đồ dùng học tập đầy đủ: sách vở, bút, thước kẻ</li>
<li>Bánh kẹo và thực phẩm dinh dưỡng</li>
<li>Tiền mặt 100.000 VNĐ cho mỗi em</li>
</ul>

<h2>Ngày trao quà đáng nhớ</h2>
<p>Ngày 15/01/2025, đoàn thiện nguyện CONVOI gồm 20 thành viên đã lên đường đến các xã vùng cao. Dù thời tiết lạnh giá và đường xá khó khăn, nhưng nụ cười hạnh phúc của các em nhỏ khi nhận quà đã xóa tan mọi mệt mỏi.</p>

<img src="assets/images/blog/tet-am-2.jpg" alt="Khoảnh khắc trao quà cho các em nhỏ vùng cao" class="blog-content-image">

<blockquote>
"Em rất vui khi nhận được những món quà này. Em sẽ cố gắng học thật giỏi để sau này có thể giúp đỡ những người khác như các anh chị đã giúp em."
<cite>- Bé Mùa, 8 tuổi, xã Tà Van, Sa Pa</cite>
</blockquote>

<h2>Kết quả đạt được</h2>
<p>Chương trình đã thành công trao quà cho 500 trẻ em tại 10 xã vùng cao thuộc 3 tỉnh: Lào Cai, Hà Giang và Cao Bằng. Tổng kinh phí thực hiện là 250 triệu VNĐ, được quyên góp từ 1.200 nhà hảo tâm trên toàn quốc.</p>

<h2>Lời cảm ơn</h2>
<p>CONVOI xin chân thành cảm ơn tất cả các nhà hảo tâm đã đồng hành cùng chúng tôi trong chương trình này. Sự ủng hộ của các bạn đã giúp chúng tôi mang lại niềm vui và hy vọng cho các em nhỏ vùng cao.</p>',

'Chương trình "Tết ấm cho em" là một trong những hoạt động thiện nguyện ý nghĩa nhất mà CONVOI đã tổ chức trong năm qua. Với mục tiêu mang lại một cái Tết ấm áp và đầy ý nghĩa cho các em nhỏ vùng cao.',
'event1.jpg', 'Hoạt động thiện nguyện', 'CONVOI Team', 'published', 1, 2547, 500, 10, 250000000.00, 1200, '2025-01-15 10:00:00'),

('Xây dựng cầu dân sinh', 'Kết nối hai bờ sông cho bà con vùng sâu', 'xay-dung-cau-dan-sinh',
'<h2>Hoàn cảnh trước khi có cầu</h2>
<p>Trước đây, để qua sông, bà con phải sử dụng thúng chai hoặc đi vòng hơn 10km. Mùa mưa lũ, việc qua sông trở nên vô cùng nguy hiểm, nhiều lần có người bị nước cuốn. Trẻ em phải nghỉ học vì không thể đến trường.</p>

<img src="assets/images/blog/cau-dan-sinh-1.jpg" alt="Bà con phải dùng thúng chai để qua sông trước khi có cầu" class="blog-content-image">

<h2>Quá trình xây dựng</h2>
<p>Dự án được khởi công vào tháng 6/2024 với tổng kinh phí 800 triệu VNĐ. Toàn bộ vật liệu xây dựng đều được vận chuyển bằng đường bộ và đường thủy. Bà con địa phương đã tích cực tham gia, đóng góp công sức để dự án hoàn thành đúng tiến độ.</p>

<img src="assets/images/blog/cau-dan-sinh-2.jpg" alt="Quá trình thi công cầu dân sinh" class="blog-content-image">

<h2>Ý nghĩa của dự án</h2>
<p>Cây cầu dài 45m, rộng 3.5m này đã kết nối hai bờ sông, giúp bà con dễ dàng đi lại, vận chuyển hàng hóa và đưa con em đến trường. Đây không chỉ là một công trình giao thông mà còn là cầu nối của hy vọng và tương lai.</p>

<blockquote>
"Có cầu rồi, con cháu chúng tôi đi học dễ dàng hơn nhiều. Cảm ơn các anh chị đã giúp đỡ bà con chúng tôi."
<cite>- Ông Vàng Seo Su, 65 tuổi, xã Nậm Lúc</cite>
</blockquote>',

'Dự án xây dựng cầu dân sinh tại xã Nậm Lúc, huyện Nậm Nhùn, tỉnh Lai Châu đã hoàn thành sau 6 tháng thi công, kết nối hai bờ sông và mang lại cuộc sống tốt đẹp hơn cho bà con địa phương.',
'event2.jpg', 'Xây dựng cơ sở hạ tầng', 'CONVOI Team', 'published', 1, 1834, 1200, 3, 800000000.00, 2500, '2024-12-20 14:00:00'),

('Khám bệnh miễn phí', 'Chăm sóc sức khỏe cho người dân vùng xa', 'kham-benh-mien-phi',
'<h2>Chương trình khám bệnh miễn phí</h2>
<p>Chương trình khám bệnh miễn phí "Sức khỏe đến từng nhà" đã được tổ chức tại 5 xã vùng sâu, vùng xa thuộc tỉnh Điện Biên. Với sự tham gia của 15 bác sĩ chuyên khoa và 20 tình nguyện viên, chương trình đã khám và điều trị miễn phí cho hơn 800 bệnh nhân.</p>

<img src="assets/images/blog/kham-benh-1.jpg" alt="Bác sĩ khám bệnh miễn phí cho bà con vùng xa" class="blog-content-image">

<h2>Các dịch vụ y tế</h2>
<ul>
<li>Khám tổng quát và tư vấn sức khỏe</li>
<li>Khám chuyên khoa: tim mạch, tiêu hóa, hô hấp</li>
<li>Cấp phát thuốc miễn phí</li>
<li>Tầm soát các bệnh mãn tính</li>
<li>Tư vấn dinh dưỡng và chăm sóc sức khỏe</li>
</ul>

<h2>Tác động tích cực</h2>
<p>Chương trình không chỉ giúp phát hiện sớm các bệnh lý mà còn nâng cao nhận thức của người dân về chăm sóc sức khỏe. Nhiều trường hợp bệnh được phát hiện và điều trị kịp thời.</p>

<blockquote>
"Cảm ơn các bác sĩ đã đến tận nhà khám bệnh cho chúng tôi. Nhờ có chương trình này mà bệnh của tôi được phát hiện sớm."
<cite>- Bà Lò Thị May, 58 tuổi, xã Mường Phăng</cite>
</blockquote>',

'Chương trình khám bệnh miễn phí "Sức khỏe đến từng nhà" mang dịch vụ y tế chất lượng đến tận các vùng sâu, vùng xa, giúp bà con có cơ hội được chăm sóc sức khỏe tốt nhất.',
'event3.jpg', 'Y tế cộng đồng', 'CONVOI Team', 'published', 0, 1256, 800, 5, 150000000.00, 800, '2024-11-10 09:00:00');
