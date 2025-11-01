<div align="center">

# 🚀 CONVOI Platform

### Modern Multi-Purpose Web Application

[![PHP Version](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=for-the-badge)](http://makeapullrequest.com)

*Nền tảng web đa chức năng với kiến trúc modular hiện đại, tích hợp Blog, E-Commerce, Charity và AI Chatbot*

[Features](#-tính-năng-nổi-bật) • [Installation](#-cài-đặt) • [Documentation](#-tài-liệu) • [Contributing](#-đóng-góp)

</div>

---

## 📖 Giới Thiệu

**CONVOI** là một nền tảng web đa chức năng được xây dựng hoàn toàn bằng PHP với kiến trúc modular linh hoạt. Hệ thống được thiết kế để dễ dàng mở rộng, bảo trì và tích hợp các module mới.

### 🎯 Mục Tiêu Dự Án

- ✨ Cung cấp nền tảng web toàn diện cho doanh nghiệp
- 🔐 Bảo mật cao với OAuth 2.0 và xác thực đa lớp
- 💳 Tích hợp thanh toán trực tuyến (MoMo, VNPay)
- 🤖 Hỗ trợ khách hàng tự động với AI Chatbot
- 📱 Responsive design, tương thích mọi thiết bị

---

## ✨ Tính Năng Nổi Bật

<table>
<tr>
<td width="50%">

### 🔐 Authentication & Authorization

- **Đăng ký/Đăng nhập** với xác thực email
- **OAuth 2.0** (Facebook & Google)
- **Quên mật khẩu** với token bảo mật
- **Rate limiting** chống brute force
- **Session management** an toàn
- **Phân quyền** (User/Admin)
- **Account lockout** sau nhiều lần đăng nhập sai

</td>
<td width="50%">

### 📝 Blog Module

- Tạo & quản lý bài viết
- Rich text editor
- Phân loại & tìm kiếm
- Quản lý hình ảnh
- SEO friendly URLs
- Responsive layout
- Admin management

</td>
</tr>
<tr>
<td width="50%">

### 🛒 E-Commerce (Shop)

- Danh mục & sản phẩm chi tiết
- Giỏ hàng thông minh
- **Thanh toán trực tuyến**:
  - 💰 MoMo E-Wallet
  - 🏦 VNPay Banking
- Quản lý đơn hàng
- Order tracking
- Admin product management

</td>
<td width="50%">

### 💝 Charity Module

- Quản lý sự kiện từ thiện
- Đăng ký tham gia
- **Quyên góp trực tuyến** (MoMo/VNPay)
- Theo dõi mục tiêu
- Thống kê real-time
- Transparent donation tracking

</td>
</tr>
<tr>
<td width="50%">

### 🤖 AI Chatbot

- Knowledge base tích hợp
- Hỗ trợ khách hàng tự động
- REST API endpoint
- Easy customization
- Context-aware responses

</td>
<td width="50%">

### 📧 Email System

- PHPMailer integration
- Email verification
- Password reset
- Beautiful HTML templates
- SMTP support
- Queue system ready

</td>
</tr>
</table>

---

## 🏗️ Kiến Trúc Hệ Thống

```
DACongcu/
│
├── 📁 backend/                     # Backend Logic & API
│   ├── controllers/                # Business logic controllers
│   │   ├── dashboard.php          # Admin dashboard
│   │   ├── users.php              # User management
│   │   ├── blog-add.php           # Blog CRUD operations
│   │   └── blog-manage.php
│   │
│   └── includes/                   # Authentication & Utilities
│       ├── auth.php               # Core authentication
│       ├── login.php              # Login handler
│       ├── register.php           # Registration handler
│       ├── verify.php             # Email verification
│       ├── forgot-password.php    # Password reset
│       ├── oauth_facebook.php     # Facebook OAuth
│       └── oauth_google.php       # Google OAuth
│
├── 📁 frontend/                    # Frontend Modules
│   └── modules/                   # Feature modules
│       ├── home/                  # Home & Auth pages
│       │   └── pages/
│       │       ├── login.php
│       │       ├── register.php
│       │       ├── dashboard.php
│       │       ├── profile.php
│       │       └── verify.php
│       │
│       ├── blog/                  # Blog module
│       │   └── pages/
│       │       ├── index.php
│       │       ├── post.php
│       │       └── simple.php
│       │
│       ├── shop/                  # E-commerce module
│       │   └── pages/
│       │       ├── index.php
│       │       ├── product_list.php
│       │       └── product_detail.php
│       │
│       ├── shops/                 # Shopping cart & checkout
│       │   └── pages/
│       │       ├── cart.php
│       │       ├── checkout.php
│       │       └── add_to_cart.php
│       │
│       └── charity/               # Charity module
│           └── pages/
│               ├── index.php
│               ├── event_list.php
│               └── event_detail.php
│
├── 📁 public/                      # Public entry point
│   └── index.php                  # Main router
│
├── 📄 database.sql                # Database schema & migrations
├── 📄 .gitignore                  # Git ignore rules
└── 📄 README.md                   # This file
```

### 🎨 Design Principles

- **Modular Architecture**: Mỗi feature là một module độc lập
- **Separation of Concerns**: Backend/Frontend tách biệt rõ ràng
- **Security First**: Authentication & validation ở mọi layer
- **Scalability**: Dễ dàng thêm module mới
- **Maintainability**: Code structure rõ ràng, dễ bảo trì

---

## 📋 Yêu Cầu Hệ Thống

### Server Requirements

| Component | Minimum Version | Recommended |
|-----------|----------------|-------------|
| **PHP** | 7.4 | 8.0+ |
| **MySQL** | 5.7 | 8.0+ |
| **Web Server** | Apache 2.4 / Nginx 1.18 | Latest |
| **Memory** | 256 MB | 512 MB+ |
| **Disk Space** | 100 MB | 500 MB+ |

### PHP Extensions Required

```bash
✓ mysqli / PDO         # Database connectivity
✓ mbstring            # Multi-byte string support
✓ json                # JSON encoding/decoding
✓ openssl             # Encryption & security
✓ curl                # HTTP requests
✓ gd / imagick        # Image processing (optional)
✓ zip                 # File compression (optional)
```

### External Services (Optional)

- 🔑 **Facebook App** credentials (OAuth login)
- 🔑 **Google OAuth** credentials
- 💳 **MoMo Merchant** account (payment)
- 🏦 **VNPay Merchant** account (payment)
- 📧 **SMTP Server** (email sending)

---

## 🚀 Cài Đặt

### Method 1: Quick Start (Development)

```bash
# 1. Clone repository
git clone <repository-url>
cd DACongcu

# 2. Import database
mysql -u root -p < database.sql

# 3. Configure database connection
# Edit backend/includes/auth.php or create config file

# 4. Start PHP built-in server
cd public
php -S localhost:8000

# 5. Open browser
# http://localhost:8000
```

### Method 2: Production Setup

#### Step 1: Database Setup

```bash
# Login to MySQL
mysql -u your_username -p

# Create database
CREATE DATABASE convoi_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Import schema
USE convoi_app;
SOURCE database.sql;

# Verify tables
SHOW TABLES;
```

#### Step 2: Configure Environment

Create `.env` file in project root:

```env
# ============================================
# DATABASE CONFIGURATION
# ============================================
DB_HOST=localhost
DB_PORT=3306
DB_NAME=convoi_app
DB_USER=your_username
DB_PASS=your_password

# ============================================
# APPLICATION SETTINGS
# ============================================
APP_NAME=CONVOI
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=your-32-character-secret-key-here

# ============================================
# SECURITY SETTINGS
# ============================================
SESSION_LIFETIME=120
SESSION_SECURE=true
SESSION_HTTP_ONLY=true
MAX_LOGIN_ATTEMPTS=5
ACCOUNT_LOCKOUT_MINUTES=30

# ============================================
# EMAIL CONFIGURATION
# ============================================
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=noreply@convoi.com
MAIL_FROM_NAME=CONVOI
MAIL_ENCRYPTION=tls

# ============================================
# OAUTH - FACEBOOK
# ============================================
FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_APP_SECRET=your_facebook_app_secret
FACEBOOK_REDIRECT_URI=https://yourdomain.com/public/home/oauth_facebook_callback

# ============================================
# OAUTH - GOOGLE
# ============================================
GOOGLE_CLIENT_ID=your_google_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/public/home/oauth_google_callback

# ============================================
# PAYMENT - MOMO
# ============================================
MOMO_PARTNER_CODE=your_partner_code
MOMO_ACCESS_KEY=your_access_key
MOMO_SECRET_KEY=your_secret_key
MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create
MOMO_RETURN_URL=https://yourdomain.com/public/shop/momo_callback
MOMO_IPN_URL=https://yourdomain.com/api/momo_ipn

# ============================================
# PAYMENT - VNPAY
# ============================================
VNPAY_TMN_CODE=your_tmn_code
VNPAY_HASH_SECRET=your_hash_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=https://yourdomain.com/public/shop/vnpay_callback
VNPAY_VERSION=2.1.0

# ============================================
# AI CHATBOT (OPTIONAL)
# ============================================
AI_API_KEY=your_ai_api_key
AI_MODEL=gpt-3.5-turbo
AI_MAX_TOKENS=500
AI_TEMPERATURE=0.7
```

#### Step 3: Web Server Configuration

**Apache (.htaccess)**

Create `public/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirect to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [L,QSA]
    
    # Security headers
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable directory listing
Options -Indexes

# PHP settings
php_value upload_max_filesize 20M
php_value post_max_size 25M
php_value max_execution_time 300
```

**Nginx Configuration**

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/DACongcu/public;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/convoi_access.log;
    error_log /var/log/nginx/convoi_error.log;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~* \.(env|sql|md)$ {
        deny all;
    }
}
```

#### Step 4: File Permissions (Linux/Mac)

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/html/DACongcu

# Set permissions
sudo find /var/www/html/DACongcu -type d -exec chmod 755 {} \;
sudo find /var/www/html/DACongcu -type f -exec chmod 644 {} \;

# Writable directories
sudo chmod -R 775 frontend/common/assets/images
sudo chmod -R 775 backend/logs
sudo chmod -R 775 backend/uploads

# Secure sensitive files
sudo chmod 600 .env
```

#### Step 5: SSL Certificate (Production)

```bash
# Using Let's Encrypt
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

---

## 📚 Tài Liệu

### 🔗 URL Routing

Hệ thống sử dụng URL pattern:

```
https://yourdomain.com/public/{module}/{page}?{parameters}
```

#### Common Routes

| Route | Description | Example |
|-------|-------------|---------|
| `/public/home` | Trang chủ | `https://yourdomain.com/public/home` |
| `/public/home/login` | Đăng nhập | Login page |
| `/public/home/register` | Đăng ký | Registration page |
| `/public/home/dashboard` | Dashboard | User dashboard |
| `/public/blog` | Blog listing | All blog posts |
| `/public/blog/post?id=1` | Blog detail | Single post |
| `/public/shop` | Shop listing | All products |
| `/public/shop/product_detail?id=1` | Product detail | Single product |
| `/public/shops/cart` | Shopping cart | Cart page |
| `/public/shops/checkout` | Checkout | Payment page |
| `/public/charity` | Charity events | Event listing |
| `/public/charity/event_detail?id=1` | Event detail | Single event |

### 🔧 Module Development

#### Creating a New Module

```bash
# 1. Create module structure
mkdir -p frontend/modules/your_module/pages
mkdir -p frontend/modules/your_module/assets/{css,js,images}
mkdir -p frontend/modules/your_module/components
```

#### Module Structure

```
your_module/
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
├── components/
│   ├── sidebar.php
│   └── card.php
└── pages/
    ├── index.php          # Default page
    ├── detail.php
    └── list.php
```

#### Example Page Template

```php
<?php
// frontend/modules/your_module/pages/index.php

// Include configuration
require_once dirname(__DIR__, 3) . '/config.php';

// Include header
require_once FRONTEND_ROOT . '/common/components/header.php';
?>

<!-- Your content here -->
<div class="container my-5">
    <h1>Your Module Title</h1>
    <p>Your content goes here...</p>
</div>

<?php
// Include footer
require_once FRONTEND_ROOT . '/common/components/footer.php';
?>
```

### 🔌 API Development

#### Creating API Endpoint

```php
<?php
// backend/api/your_endpoint.php

// Include authentication and utilities
require_once dirname(__DIR__) . '/includes/auth.php';

// Set JSON header
header('Content-Type: application/json');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['data'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Missing required parameter: data'
    ]);
    exit;
}

// Your business logic here
try {
    $result = processData($input['data']);
    
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

#### API Authentication

```php
<?php
// Verify API token
function verifyApiToken() {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized: Missing token'
        ]);
        exit;
    }
    
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    
    // Verify token against database
    // ...
}
```

---

## 🔒 Bảo Mật

### Security Features Implemented

| Feature | Implementation | Status |
|---------|----------------|--------|
| Password Hashing | `password_hash()` with bcrypt | ✅ |
| SQL Injection Protection | PDO Prepared Statements | ✅ |
| XSS Prevention | `htmlspecialchars()` | ✅ |
| CSRF Protection | Token validation | ⚠️ Implement |
| Rate Limiting | Login attempt tracking | ✅ |
| Session Security | Secure session handling | ✅ |
| Email Verification | Token-based verification | ✅ |
| Password Reset | Secure token system | ✅ |
| Account Lockout | After N failed attempts | ✅ |
| OAuth 2.0 | Facebook & Google | ✅ |

### Security Best Practices

#### 1. Password Policy

```php
// Minimum requirements
- Minimum length: 8 characters
- At least 1 uppercase letter
- At least 1 lowercase letter
- At least 1 number
- At least 1 special character
```

#### 2. Input Validation

```php
// Always validate and sanitize user input
$username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email format');
}
```

#### 3. Prepared Statements

```php
// NEVER use direct SQL queries
// BAD: $query = "SELECT * FROM users WHERE id = " . $_GET['id'];

// GOOD: Use prepared statements
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
```

#### 4. Security Headers

```php
// Set security headers
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000");
```

### Security Checklist for Production

- [ ] Change all default passwords
- [ ] Enable HTTPS (SSL certificate)
- [ ] Implement CSRF tokens
- [ ] Set up firewall rules
- [ ] Enable security headers
- [ ] Regular security audits
- [ ] Keep PHP & dependencies updated
- [ ] Disable error display (`display_errors = Off`)
- [ ] Enable error logging
- [ ] Backup database regularly
- [ ] Implement API rate limiting
- [ ] Use environment variables for secrets
- [ ] Restrict file upload types & sizes
- [ ] Implement Content Security Policy (CSP)

---

## 💾 Database Schema

### Core Tables Overview

#### Users & Authentication

```sql
users                    # User accounts
├── id (PK)
├── username (UNIQUE)
├── email (UNIQUE)
├── password (HASHED)
├── role (user/admin)
├── email_verified
└── created_at

user_sessions           # Active sessions
├── id (PK)
├── user_id (FK → users)
├── session_token (UNIQUE)
├── expires_at
└── created_at

user_activities         # Activity logs
├── id (PK)
├── user_id (FK → users)
├── activity_type
├── ip_address
└── created_at
```

#### E-Commerce Module

```sql
products                # Product catalog
├── id (PK)
├── name
├── price
├── stock
└── category_id (FK)

cart                    # Shopping cart
├── id (PK)
├── user_id (FK → users)
├── product_id (FK → products)
└── quantity

orders                  # Order management
├── id (PK)
├── user_id (FK → users)
├── total_amount
├── status
└── created_at

order_items            # Order details
├── id (PK)
├── order_id (FK → orders)
├── product_id (FK → products)
├── quantity
└── price
```

#### Blog Module

```sql
blog_posts             # Blog articles
├── id (PK)
├── author_id (FK → users)
├── title
├── content
├── status
└── created_at

blog_categories        # Blog categories
├── id (PK)
├── name
└── slug
```

#### Charity Module

```sql
events                 # Charity events
├── id (PK)
├── title
├── goal_amount
├── current_amount
└── end_date

event_registrations    # Event sign-ups
├── id (PK)
├── event_id (FK → events)
├── user_id (FK → users)
└── created_at

donations              # Donations
├── id (PK)
├── event_id (FK → events)
├── user_id (FK → users)
├── amount
└── created_at
```

### Database Migration

```bash
# Full schema import
mysql -u root -p convoi_app < database.sql

# Backup database
mysqldump -u root -p convoi_app > backup_$(date +%Y%m%d).sql

# Restore from backup
mysql -u root -p convoi_app < backup_20241101.sql
```

---

## 🧪 Testing

### Manual Testing

#### 1. Authentication Tests

```bash
# Test registration
curl -X POST http://localhost:8000/home/register \
  -d "username=testuser&email=test@example.com&password=Test123!&full_name=Test User"

# Test login
curl -X POST http://localhost:8000/home/login \
  -d "username=testuser&password=Test123!"

# Test OAuth (Open in browser)
# http://localhost:8000/home/oauth_facebook
# http://localhost:8000/home/oauth_google
```

#### 2. API Tests

```bash
# Test AI Chatbot
curl -X POST http://localhost:8000/api/ai_chat \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello, how can I buy products?"}'
```

#### 3. Payment Testing

- **MoMo Sandbox**: Use test credentials
- **VNPay Sandbox**: Use test cards provided

### Automated Testing (Future)

```bash
# PHPUnit setup (planned)
composer require --dev phpunit/phpunit

# Run tests
./vendor/bin/phpunit tests/
```

---

## 🛠️ Công Nghệ & Stack

### Backend Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 7.4+ | Server-side language |
| **MySQL** | 5.7+ | Database |
| **PDO/MySQLi** | - | Database abstraction |
| **PHPMailer** | 6.x | Email sending |
| **OAuth 2.0** | - | Social authentication |

### Frontend Stack

| Technology | Purpose |
|------------|---------|
| **HTML5** | Markup |
| **CSS3** | Styling |
| **JavaScript** | Client-side logic |
| **Bootstrap** | UI framework |
| **jQuery** | DOM manipulation |

### Payment Gateways

- 💰 **MoMo**: E-wallet payment (Vietnam)
- 🏦 **VNPay**: Banking payment (Vietnam)

### Third-party Services

- 📘 **Facebook OAuth**: Social login
- 🔴 **Google OAuth**: Social login
- 🤖 **AI API**: Chatbot integration

---

## 📈 Roadmap & TODO

### ✅ Phase 1: Core Features (Completed)

- [x] User authentication system
- [x] OAuth integration (Facebook/Google)
- [x] Email verification & password reset
- [x] Basic CRUD operations
- [x] Module structure (Blog, Shop, Charity)
- [x] Payment gateway integration
- [x] AI Chatbot foundation

### 🚧 Phase 2: Enhancements (In Progress)

- [ ] Admin dashboard with analytics
- [ ] Advanced product management
- [ ] Order tracking system
- [ ] Wishlist & product comparison
- [ ] Product reviews & ratings
- [ ] Search functionality (full-text search)
- [ ] Image optimization & CDN
- [ ] Multi-language support (i18n)
- [ ] Export/Import features

### 📋 Phase 3: Advanced Features (Planned)

- [ ] Real-time notifications (WebSocket)
- [ ] Progressive Web App (PWA)
- [ ] Mobile app (React Native/Flutter)
- [ ] Advanced analytics & reporting
- [ ] Marketing automation
- [ ] Newsletter system
- [ ] Loyalty program
- [ ] Affiliate management
- [ ] API documentation (Swagger/OpenAPI)
- [ ] GraphQL API

### 🔧 Technical Improvements

- [ ] **Testing**: Unit tests, integration tests
- [ ] **Caching**: Redis/Memcached implementation
- [ ] **Logging**: Monolog integration
- [ ] **Queue**: Job queue system
- [ ] **CSRF Protection**: Complete implementation
- [ ] **API Rate Limiting**: Advanced throttling
- [ ] **Database Migrations**: Version control
- [ ] **Docker**: Containerization
- [ ] **CI/CD**: Automated deployment
- [ ] **Performance**: Optimization & profiling
- [ ] **Documentation**: Complete API docs

---

## 🤝 Đóng Góp

Chúng tôi rất hoan nghênh mọi đóng góp! 🎉

### How to Contribute

1. **Fork** repository
2. **Create** feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** changes (`git commit -m 'Add some AmazingFeature'`)
4. **Push** to branch (`git push origin feature/AmazingFeature`)
5. **Open** Pull Request

### Coding Standards

#### PHP (PSR-12)

```php
<?php

namespace App\Controllers;

class UserController
{
    public function index()
    {
        // Use 4 spaces for indentation
        $users = $this->getAllUsers();
        
        return view('users.index', compact('users'));
    }
}
```

#### Commit Messages

```bash
# Format: <type>(<scope>): <subject>

# Examples:
feat(auth): add password reset functionality
fix(shop): resolve cart calculation bug
docs(readme): update installation instructions
style(frontend): improve responsive layout
refactor(api): optimize database queries
test(auth): add unit tests for login
chore(deps): update PHPMailer to v6.5
```

#### Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Code style (formatting)
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

### Code Review Process

1. All PRs require at least 1 approval
2. Ensure all tests pass
3. Update documentation if needed
4. Follow coding standards
5. Add meaningful commit messages

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2024 CONVOI Team

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction...
```

---

## 👥 Team & Credits

### Development Team

- **Project Lead**: CONVOI Team
- **Backend Developers**: PHP Team
- **Frontend Developers**: UI/UX Team
- **DevOps**: Infrastructure Team

### Contributors

Thanks to all contributors who helped make this project better! 🙏

### Acknowledgments

- [PHPMailer](https://github.com/PHPMailer/PHPMailer) - Email sending library
- [MoMo Payment Gateway](https://developers.momo.vn/) - E-wallet integration
- [VNPay](https://vnpay.vn/) - Banking payment integration
- [Facebook Developers](https://developers.facebook.com/) - OAuth integration
- [Google Developers](https://developers.google.com/) - OAuth integration
- Open Source Community ❤️

---

## 📞 Support & Contact

### Get Help

- 📧 **Email**: support@convoi.com
- 🌐 **Website**: https://convoi.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/your-repo/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/your-repo/discussions)

### Documentation

- 📚 **Wiki**: [Project Wiki](https://github.com/your-repo/wiki)
- 📖 **API Docs**: Coming soon
- 🎥 **Video Tutorials**: Coming soon

### Social Media

- 📘 Facebook: [@convoi](https://facebook.com/convoi)
- 🐦 Twitter: [@convoi](https://twitter.com/convoi)
- 📸 Instagram: [@convoi](https://instagram.com/convoi)
- 💼 LinkedIn: [CONVOI](https://linkedin.com/company/convoi)

---

## 📊 Project Stats

![GitHub repo size](https://img.shields.io/github/repo-size/your-repo/convoi?style=flat-square)
![GitHub issues](https://img.shields.io/github/issues/your-repo/convoi?style=flat-square)
![GitHub pull requests](https://img.shields.io/github/issues-pr/your-repo/convoi?style=flat-square)
![GitHub last commit](https://img.shields.io/github/last-commit/your-repo/convoi?style=flat-square)
![GitHub contributors](https://img.shields.io/github/contributors/your-repo/convoi?style=flat-square)

---

## 🌟 Show Your Support

Give a ⭐️ if this project helped you!

[![Star History Chart](https://api.star-history.com/svg?repos=your-repo/convoi&type=Date)](https://star-history.com/#your-repo/convoi&Date)

---

<div align="center">

### Made with ❤️ by CONVOI Team

**[⬆ Back to Top](#-convoi-platform)**

</div>
