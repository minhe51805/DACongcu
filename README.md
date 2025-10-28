# DACongcu

# CONVOI - Nền Tảng Web Đa Chức Năng

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)

CONVOI là một nền tảng web đa chức năng được xây dựng bằng PHP, tích hợp nhiều module như Blog, E-commerce, Từ thiện và AI Chatbot. Hệ thống được thiết kế với kiến trúc modular, dễ mở rộng và bảo trì.

## 🌟 Tính Năng Chính

### 🔐 Hệ Thống Xác Thực (Authentication)
- **Đăng ký/Đăng nhập** với xác thực email
- **OAuth 2.0** tích hợp với Facebook và Google
- **Quên mật khẩu** với token bảo mật
- **Bảo vệ tài khoản**: Rate limiting, account lockout
- **Quản lý phiên**: Session management với token
- **Phân quyền**: User roles (user, admin)

### 📝 Module Blog
- Tạo, chỉnh sửa và quản lý bài viết
- Giao diện đọc blog hiện đại
- Hệ thống phân loại và tìm kiếm
- Rich text editor
- Quản lý hình ảnh

### 🛒 Module E-Commerce (Shop)
- Danh sách sản phẩm với chi tiết đầy đủ
- Giỏ hàng thông minh
- Hệ thống checkout
- **Thanh toán tích hợp**:
  - MoMo Payment Gateway
  - VNPay Payment Gateway
- Quản lý đơn hàng
- Admin panel cho sản phẩm

### 💝 Module Từ Thiện (Charity)
- Quản lý sự kiện từ thiện
- Đăng ký tham gia sự kiện
- **Quyên góp trực tuyến** với MoMo và VNPay
- Theo dõi mục tiêu quyên góp
- Thống kê chi tiết
- Giao diện hiện đại và responsive

### 🤖 AI Chatbot
- Chatbot AI tích hợp với knowledge base
- Hỗ trợ khách hàng tự động
- API endpoint riêng biệt
- Dễ dàng custom và mở rộng

### 📧 Hệ Thống Email
- PHPMailer tích hợp
- Email verification
- Password reset emails
- Email templates đẹp mắt

## 🏗️ Kiến Trúc Hệ Thống

```
public_html/
├── backend/                    # Backend logic và API
│   ├── api/                   # REST API endpoints
│   │   ├── ai_chat.php       # AI Chatbot API
│   │   └── wagai_knowledge_base.json
│   ├── bootstrap.php          # Bootstrap backend
│   ├── config/                # Configuration files
│   │   ├── database.php      # Database config
│   │   ├── env.php           # Environment loader
│   │   └── mail.php          # Mail config
│   ├── controllers/           # Backend controllers
│   ├── includes/              # Auth & utilities
│   ├── libraries/             # Third-party libraries
│   │   ├── Mailer.php
│   │   └── PHPMailer/
│   ├── models/                # Data models
│   └── utils/                 # Helper utilities
│
├── frontend/                   # Frontend modules
│   ├── common/                # Shared resources
│   │   ├── assets/           # CSS, JS, Images
│   │   │   ├── css/          # Stylesheets
│   │   │   ├── js/           # JavaScript files
│   │   │   └── images/       # Image assets
│   │   └── components/       # Shared components
│   │       ├── header.php
│   │       └── footer.php
│   ├── config.php             # Frontend config
│   └── modules/               # Feature modules
│       ├── blog/              # Blog module
│       ├── charity/           # Charity module
│       ├── home/              # Home & Auth module
│       └── shop/              # E-commerce module
│
├── public/                     # Public entry point
│   └── index.php              # Main router
│
├── database.sql               # Database schema
└── README.md                  # This file
```

## 📋 Yêu Cầu Hệ Thống

### Máy Chủ
- **Web Server**: Apache 2.4+ hoặc Nginx 1.18+
- **PHP**: 7.4 hoặc cao hơn
- **Database**: MySQL 5.7+ hoặc MariaDB 10.3+
- **Extensions**: mysqli, PDO, mbstring, json, openssl, curl

### Tài Khoản & API Keys (Tùy chọn)
- Facebook App ID & Secret (cho OAuth)
- Google Client ID & Secret (cho OAuth)
- MoMo Merchant credentials (cho thanh toán)
- VNPay Merchant credentials (cho thanh toán)
- SMTP credentials (cho email)

## 🚀 Cài Đặt

### 1. Clone Repository

```bash
git clone <repository-url>
cd public_html
```

### 2. Cấu Hình Database

Tạo database và import schema:

```bash
mysql -u your_username -p
```

```sql
CREATE DATABASE convoi_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE convoi_app;
SOURCE database.sql;
```

### 3. Cấu Hình Environment

Tạo file `.env` trong thư mục gốc của project:

```env
# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=convoi_app
DB_USER=your_username
DB_PASS=your_password

# Application Settings
APP_NAME=CONVOI
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/public_html

# Security
APP_KEY=your-secret-key-here
SESSION_LIFETIME=120

# Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=noreply@convoi.com
MAIL_FROM_NAME=CONVOI

# OAuth - Facebook
FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_APP_SECRET=your_facebook_app_secret
FACEBOOK_REDIRECT_URI=http://localhost/public_html/public/home/oauth_facebook_callback

# OAuth - Google
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost/public_html/public/home/oauth_google_callback

# Payment - MoMo
MOMO_PARTNER_CODE=your_partner_code
MOMO_ACCESS_KEY=your_access_key
MOMO_SECRET_KEY=your_secret_key
MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create
MOMO_RETURN_URL=http://localhost/public_html/public/shop/momo_callback

# Payment - VNPay
VNPAY_TMN_CODE=your_tmn_code
VNPAY_HASH_SECRET=your_hash_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=http://localhost/public_html/public/shop/vnpay_callback

# AI Chatbot (Optional)
AI_API_KEY=your_ai_api_key
AI_MODEL=gpt-3.5-turbo
```

### 4. Cấu Hình Web Server

#### Apache (.htaccess)

Tạo file `.htaccess` trong thư mục `public_html`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /public_html/
    
    # Redirect to public/index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/index.php [L,QSA]
</IfModule>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/public_html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Cấp Quyền (Linux/Mac)

```bash
chmod -R 755 public_html
chmod -R 777 frontend/common/assets/images
chmod -R 777 backend/logs  # Nếu có thư mục logs
```

### 6. Chạy Development Server (PHP Built-in)

```bash
cd public
php -S localhost:8000
```

Truy cập: `http://localhost:8000`

## 🎯 Sử Dụng

### Routing System

Hệ thống sử dụng routing dựa trên URL pattern:

```
http://your-domain.com/public/{module}/{page}
```

**Ví dụ:**
- Trang chủ: `/public/home` hoặc `/public/home/index`
- Đăng nhập: `/public/home/login`
- Blog: `/public/blog`
- Shop: `/public/shop`
- Chi tiết sản phẩm: `/public/shop/product_detail?id=1`
- Từ thiện: `/public/charity`
- API: `/public/api/ai_chat`

### Module Development

Để tạo module mới:

1. Tạo thư mục trong `frontend/modules/your_module/`
2. Cấu trúc thư mục:
```
your_module/
├── assets/        # Module-specific assets
├── components/    # Module components
└── pages/         # Module pages
    └── index.php  # Default page
```

3. Tạo trang trong `pages/your_page.php`:
```php
<?php
require_once dirname(__DIR__, 3) . '/config.php';
require_once FRONTEND_ROOT . '/common/components/header.php';
?>

<div class="container">
    <!-- Your content here -->
</div>

<?php require_once FRONTEND_ROOT . '/common/components/footer.php'; ?>
```

### API Development

Tạo API endpoint mới trong `backend/api/`:

```php
<?php
// backend/api/your_endpoint.php
require_once dirname(__DIR__) . '/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Your API logic here
$response = [
    'success' => true,
    'data' => 'Your data'
];

echo json_encode($response);
```

Truy cập: `/public/api/your_endpoint`

## 🔒 Bảo Mật

### Best Practices Đã Áp Dụng

- ✅ **Password Hashing**: Sử dụng `password_hash()` với bcrypt
- ✅ **SQL Injection Prevention**: PDO prepared statements
- ✅ **XSS Protection**: HTML escaping với `htmlspecialchars()`
- ✅ **CSRF Protection**: Token validation (nên implement)
- ✅ **Rate Limiting**: Login attempt tracking
- ✅ **Session Security**: Secure session handling
- ✅ **Email Verification**: Xác thực email trước khi activate
- ✅ **Password Reset**: Secure token-based reset

### Khuyến Nghị Bổ Sung

1. Sử dụng HTTPS trong production
2. Implement CSRF tokens cho forms
3. Enable security headers (CSP, X-Frame-Options, etc.)
4. Regular security audits
5. Keep dependencies updated

## 📊 Database Schema

### Core Tables

- **users**: Thông tin người dùng và authentication
- **user_sessions**: Quản lý phiên đăng nhập
- **user_activities**: Log hoạt động người dùng

### Blog Module
- **blog_posts**: Bài viết blog
- **blog_categories**: Danh mục blog

### Shop Module
- **products**: Sản phẩm
- **categories**: Danh mục sản phẩm
- **cart**: Giỏ hàng
- **orders**: Đơn hàng
- **order_items**: Chi tiết đơn hàng

### Charity Module
- **events**: Sự kiện từ thiện
- **event_registrations**: Đăng ký tham gia
- **donations**: Quyên góp

Xem file `database.sql` để biết chi tiết schema đầy đủ.

## 🛠️ Công Nghệ Sử Dụng

### Backend
- **PHP 7.4+**: Server-side programming
- **MySQL/MariaDB**: Database
- **PDO**: Database abstraction
- **PHPMailer**: Email sending

### Frontend
- **HTML5/CSS3**: Markup & styling
- **JavaScript (Vanilla)**: Client-side interactivity
- **VinTech Framework**: Custom lightweight JS framework
- **Responsive Design**: Mobile-first approach

### Payment Gateways
- **MoMo**: E-wallet payment
- **VNPay**: Banking payment

### Third-party Services
- **Facebook OAuth**: Social login
- **Google OAuth**: Social login
- **AI Chatbot API**: Customer support

## 🧪 Testing

### Manual Testing

1. **Authentication Flow**:
```bash
# Test registration
curl -X POST http://localhost/public_html/public/home/register \
  -d "username=testuser&email=test@example.com&password=Test123!"

# Test login
curl -X POST http://localhost/public_html/public/home/login \
  -d "username=testuser&password=Test123!"
```

2. **API Testing**:
```bash
# Test AI Chatbot
curl -X POST http://localhost/public_html/public/api/ai_chat \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello"}'
```

3. **Payment Testing**: Sử dụng sandbox credentials của MoMo và VNPay

## 📝 TODO & Roadmap

### Phase 1 - Core Features ✅
- [x] Authentication system
- [x] OAuth integration
- [x] Email system
- [x] Basic modules (Blog, Shop, Charity)

### Phase 2 - Enhancements 🚧
- [ ] Admin dashboard với analytics
- [ ] Image optimization & upload
- [ ] Advanced search functionality
- [ ] Wishlist & product comparison
- [ ] Product reviews & ratings
- [ ] Multi-language support

### Phase 3 - Advanced Features 📋
- [ ] Real-time notifications
- [ ] WebSocket integration
- [ ] Advanced reporting & analytics
- [ ] API documentation (Swagger/OpenAPI)
- [ ] Mobile app (React Native/Flutter)
- [ ] Progressive Web App (PWA)

### Technical Debt 🔧
- [ ] Implement CSRF protection
- [ ] Add comprehensive unit tests
- [ ] Implement caching layer (Redis)
- [ ] Add logging system (Monolog)
- [ ] Database migrations system
- [ ] API rate limiting
- [ ] Docker containerization

## 🤝 Đóng Góp

Mọi đóng góp đều được chào đón! Vui lòng:

1. Fork repository
2. Tạo branch mới (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

### Coding Standards

- Sử dụng PSR-12 coding style cho PHP
- Comment code một cách rõ ràng
- Viết commit messages có ý nghĩa
- Test kỹ trước khi submit PR

## 📄 License

Project này được phân phối dưới giấy phép MIT. Xem file `LICENSE` để biết thêm chi tiết.

## 👥 Tác Giả

**CONVOI Team**

## 📞 Liên Hệ & Hỗ Trợ

- **Email**: support@convoi.com
- **Website**: https://convoi.com
- **Issues**: Sử dụng GitHub Issues để báo cáo bugs

## 🙏 Acknowledgments

- PHPMailer team
- MoMo & VNPay payment gateways
- Facebook & Google OAuth services
- Open source community

---

**⭐ Nếu project này hữu ích, hãy cho chúng tôi một star!**

Made with ❤️ by CONVOI Team

