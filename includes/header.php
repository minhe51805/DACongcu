<?php require_once __DIR__ . '/helpers.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>CONVOI VinTech - Nền tảng Thiện Nguyện Công Nghệ</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- VinTech CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/vintech-style.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <!-- VinTech Enhanced Features CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/vintech-enhanced.css">
    <!-- Header Fix CSS - Must be loaded last to override other styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/header-fix.css">
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- VinTech Navigation -->
    <nav class="vintech-navbar navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="vintech-brand navbar-brand" href="<?= BASE_URL ?>/">
                <div class="vintech-logo">
                    <i class="fas fa-leaf"></i>
                </div>
                <span class="vintech-brand-text">XAYDUNGTUONGLAI</span>
                <span class="vintech-brand-badge">WAG</span>
            </a>
            <button class="navbar-toggler vintech-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <!-- Main Navigation -->
                    <li class="nav-item bottom-nav-item">
                        <a class="nav-link vintech-nav-link" href="<?= BASE_URL ?>/">
                            <i class="fas fa-home"></i>
                            <span>Trang chủ</span>
                        </a>
                    </li>
                    <li class="nav-item bottom-nav-item">
                        <a class="nav-link vintech-nav-link" href="<?= BASE_URL ?>/charity/">
                            <i class="fas fa-hands-helping"></i>
                            <span>Thiện nguyện</span>
                        </a>
                    </li>
                    <li class="nav-item bottom-nav-item">
                        <a class="nav-link vintech-nav-link" href="<?= BASE_URL ?>/shop/">
                            <i class="fas fa-store"></i>
                            <span>Cửa hàng</span>
                        </a>
                    </li>
                    <li class="nav-item bottom-nav-item">
                        <a class="nav-link vintech-nav-link" href="<?= BASE_URL ?>/blog/">
                            <i class="fas fa-blog"></i>
                            <span>Blog</span>
                        </a>
                    </li>

                    <!-- Cart (only show if logged in and has items) -->
                    <?php if ($isLoggedIn && isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                        <li class="nav-item">
                            <a class="nav-link vintech-nav-link position-relative" href="<?= BASE_URL ?>/shop/cart.php">
                                <i class="fas fa-shopping-bag"></i>
                                <span class="d-none d-lg-inline">Giỏ hàng</span>
                                <span class="vintech-cart-badge">
                                    <?= count($_SESSION['cart']) ?>
                                </span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item bottom-nav-item">
                        <a class="nav-link vintech-nav-link position-relative" href="<?= BASE_URL ?>/wishlist.php">
                            <i class="fas fa-heart"></i>
                            <span class="d-none d-lg-inline">Yêu thích</span>
                            <span class="vintech-cart-badge" id="header-wishlist-count">0</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Authentication Section -->
                    <?php if ($isLoggedIn): ?>
                        <!-- User Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link vintech-nav-link dropdown-toggle user-menu-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar">
                                    <?php if (!empty($currentUser['avatar']) && strpos($currentUser['avatar'], 'data:') === 0): ?>
                                        <img src="<?= $currentUser['avatar'] ?>"
                                             alt="<?= htmlspecialchars($currentUser['full_name']) ?>">
                                    <?php else: ?>
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #007bff; display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; font-weight: bold;">
                                            <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="user-name d-none d-md-inline"><?= htmlspecialchars($currentUser['full_name']) ?></span>
                                <?php if ($isAdmin): ?>
                                    <span class="admin-badge">Admin</span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu vintech-dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li class="dropdown-header">
                                    <div class="user-info">
                                        <strong><?= htmlspecialchars($currentUser['full_name']) ?></strong>
                                        <small><?= htmlspecialchars($currentUser['email']) ?></small>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>/profile.php">
                                        <i class="fas fa-user me-2"></i>Hồ sơ cá nhân
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>/dashboard.php">
                                        <i class="fas fa-chart-line me-2"></i>Dashboard
                                    </a>
                                </li>
                                <?php if ($isAdmin): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-header">Quản trị</li>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/charity/admin.php">
                                            <i class="fas fa-hands-helping me-2"></i>Quản lý thiện nguyện
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/shop/admin.php">
                                            <i class="fas fa-store me-2"></i>Quản lý cửa hàng
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/blog.php">
                                            <i class="fas fa-blog me-2"></i>Quản lý blog
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/admin/users.php">
                                            <i class="fas fa-users me-2"></i>Quản lý người dùng
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/auth/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Guest Menu -->
                        <li class="nav-item">
                            <a class="nav-link vintech-nav-link" href="<?= BASE_URL ?>/auth/login.php">
                                <i class="fas fa-sign-in-alt"></i>
                                <span class="d-none d-sm-inline">Đăng nhập</span>
                            </a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="vintech-btn vintech-btn-outline vintech-nav-cta" href="<?= BASE_URL ?>/auth/register.php">
                                <i class="fas fa-user-plus"></i>
                                <span class="d-none d-sm-inline">Đăng ký</span>
                            </a>
                        </li> -->
                    <?php endif; ?>

                    <!-- CTA Button -->
                    <li class="nav-item">
                        <a class="vintech-btn vintech-btn-primary vintech-nav-cta" href="<?= BASE_URL ?>/charity/donate.php">
                            <i class="fas fa-donate"></i>
                            <span class="d-none d-sm-inline">Quyên góp</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Floating Bottom Navigation -->
    <div class="floating-bottom-nav" id="floatingNav">
        <div class="floating-nav-container">
            <a href="<?= BASE_URL ?>/" class="floating-nav-item" title="Trang chủ">
                <i class="fas fa-home"></i>
                <span>Trang chủ</span>
            </a>
            <a href="<?= BASE_URL ?>/charity/" class="floating-nav-item" title="Thiện nguyện">
                <i class="fas fa-hands-helping"></i>
                <span>Thiện nguyện</span>
            </a>
            <a href="<?= BASE_URL ?>/shop/" class="floating-nav-item" title="Cửa hàng">
                <i class="fas fa-store"></i>
                <span>Cửa hàng</span>
            </a>
            <a href="<?= BASE_URL ?>/blog/" class="floating-nav-item" title="Blog">
                <i class="fas fa-blog"></i>
                <span>Blog</span>
            </a>
            <?php if ($isLoggedIn && isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                <a href="<?= BASE_URL ?>/shop/cart.php" class="floating-nav-item position-relative" title="Giỏ hàng">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Giỏ hàng</span>
                    <span class="floating-cart-badge"><?= count($_SESSION['cart']) ?></span>
                </a>
            <?php endif; ?>
            <?php if ($isLoggedIn): ?>
            <a href="<?= BASE_URL ?>/wishlist.php" class="floating-nav-item position-relative" title="Yêu thích">
                <i class="fas fa-heart"></i>
                <span>Yêu thích</span>
                <span class="floating-cart-badge" id="floating-wishlist-count">0</span>
            </a>
            <?php endif; ?>
            <?php if ($isLoggedIn): ?>
                <a href="<?= BASE_URL ?>/profile.php" class="floating-nav-item" title="Hồ sơ">
                    <div class="floating-avatar">
                        <?php if (!empty($currentUser['avatar']) && strpos($currentUser['avatar'], 'data:') === 0): ?>
                            <img src="<?= $currentUser['avatar'] ?>"
                                 alt="<?= htmlspecialchars($currentUser['full_name']) ?>">
                        <?php else: ?>
                            <div style="width: 24px; height: 24px; border-radius: 50%; background: #007bff; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">
                                <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <span>Hồ sơ</span>
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/auth/login.php" class="floating-nav-item" title="Đăng nhập">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Đăng nhập</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modern Header JavaScript -->
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.vintech-navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for navigation links
        document.querySelectorAll('.vintech-nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href && href.startsWith('#')) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Mobile menu animation
        const navbarToggler = document.querySelector('.navbar-toggler');
        const navbarCollapse = document.querySelector('.navbar-collapse');

        if (navbarToggler) {
            navbarToggler.addEventListener('click', function() {
                this.classList.toggle('active');
            });
        }
    </script>

    <!-- VinTech Enhanced Navbar Styles -->
    <style>
        :root {
            --navbar-height: 70px;
            --navbar-bg: rgba(255, 255, 255, 0.98);
            --navbar-shadow: 0 4px 20px rgba(45, 90, 39, 0.08);
            --primary-green: #2d5a27;
            --accent-green: #6fbb6b;
            --text-dark: #333;
            --border-light: rgba(45, 90, 39, 0.1);
        }

        body {
            padding-top: var(--navbar-height);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            transition: padding 0.3s ease;
        }

        body.navbar-at-bottom {
            padding-top: 0;
            padding-bottom: 85px;
        }
        
        .vintech-navbar {
            background: var(--navbar-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-light);
            box-shadow: var(--navbar-shadow);
            transition: all 0.3s ease;
            z-index: 1050;
            width: 100%;
            height: var(--navbar-height);
            transform: translateY(0);
            position: relative;
        }

        .vintech-navbar.fixed-top {
            position: fixed !important;
            top: 0 !important;
            left: 0;
            right: 0;
            bottom: auto !important;
        }

        .vintech-navbar.navbar-hidden {
            transform: translateY(-100%);
        }

        .vintech-navbar.navbar-bottom {
            position: fixed !important;
            top: auto;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            transform: translateY(0);
            border: none;
            border-radius: 0;
            box-shadow: none;
            background: transparent;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            height: 85px;
            padding: 0 !important;
            margin: 0 !important;
            width: 100vw !important;
            max-width: 100vw !important;
            z-index: 1050;
            border-top: none;
        }

        /* Mobile optimizations for bottom navbar */
        @media (max-width: 991.98px) {
            .vintech-navbar.navbar-bottom {
                height: 85px;
                margin: 0;
                width: 100vw;
                border-radius: 0;
                left: 0;
                right: 0;
            }

            body.navbar-at-bottom {
                padding-bottom: 85px;
            }
        }

        /* Ensure no white space on any device */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden;
            height: 100%;
        }

        /* Remove any default browser spacing */
        * {
            box-sizing: border-box;
        }

        /* Remove any default margins/padding that could cause gaps */
        .vintech-navbar.navbar-bottom {
            margin: 0 !important;
            padding: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            max-width: 100vw !important;
            min-height: 85px !important;
            height: 85px !important;
        }

        /* Force absolute bottom positioning */
        .vintech-navbar.navbar-bottom,
        .vintech-navbar.navbar-bottom * {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        /* Remove any viewport units that might cause spacing */
        .vintech-navbar.navbar-bottom {
            transform: translateY(0) !important;
            bottom: 0px !important;
        }

        /* Ensure body has no gaps when navbar is at bottom */
        body.navbar-at-bottom {
            margin-bottom: 0 !important;
        }

        /* Force full width and remove any container constraints */
        .vintech-navbar.navbar-bottom,
        .vintech-navbar.navbar-bottom * {
            box-sizing: border-box !important;
        }

        .vintech-navbar.navbar-bottom .container-fluid {
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }

        /* Hide certain elements when navbar is at bottom */
        .vintech-navbar.navbar-bottom .navbar-brand {
            display: none;
        }

        .vintech-navbar.navbar-bottom .vintech-nav-cta {
            display: none;
        }

        .vintech-navbar.navbar-bottom .user-menu-toggle {
            display: none;
        }

        .vintech-navbar.navbar-bottom .nav-item:not(.bottom-nav-item) {
            display: none;
        }        /* Show only essential navigation items at bottom */
        .vintech-navbar.navbar-bottom .bottom-nav-item {
            display: flex !important;
            flex: 1;
            justify-content: center;
            align-items: center;
            max-width: calc(100% / 4); /* Chia đều cho 4 items */
        }.vintech-navbar.navbar-bottom .navbar-nav {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            margin: 0;
            padding: 0;
            background: transparent;
            position: relative;
            z-index: 2;
            gap: 0;
        }        .vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px;
            text-decoration: none;
            color: #ffffff;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border-radius: 0;
            position: relative;
            width: 100%;
            min-width: 60px;
            height: 60px;
            background: transparent;
            margin: 0;
            font-weight: 500;
            border: none;
            box-shadow: none;
            text-align: center;
        }

        .vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link::before {
            display: none;
        }





        .vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link i {
            font-size: 1.4rem;
            margin-bottom: 5px;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            color: inherit;
        }

        .vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link span {
            font-size: 0.75rem;
            font-weight: 600;
            display: block !important;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            text-transform: capitalize;
            letter-spacing: 0.3px;
            color: inherit;
        }

        .vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link .d-none {
            display: block !important;
        }

        .vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link:hover,
        .vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link.active {
            color: #6fbb6b;
            transform: none;
        }

        .vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link:hover i,
        .vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link.active i {
            transform: scale(1.1);
            color: #6fbb6b;
        }

        .vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link:hover span,
        .vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link.active span {
            color: #6fbb6b;
            font-weight: 600;
        }

        /* Badge styling for bottom navbar */
        .vintech-navbar.navbar-bottom .vintech-cart-badge {
            position: absolute;
            top: 5px;
            right: 10px;
            background: linear-gradient(135deg, #FF3B30, #FF2D92);
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 3px 12px rgba(255, 59, 48, 0.4);
            z-index: 10;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Hide floating nav when main navbar is at bottom */
        body.navbar-at-bottom .floating-bottom-nav {
            display: none;
        }

        /* Ensure no white space around bottom navbar */
        .vintech-navbar.navbar-bottom .container,
        .vintech-navbar.navbar-bottom .container-fluid {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            height: 100% !important;
            bottom: 0 !important;
        }

        .vintech-navbar.navbar-bottom .navbar-collapse {
            background: transparent;
            margin: 0;
            padding: 0;
        }



        /* Force navbar to stick to absolute bottom - no safe area */
        .vintech-navbar.navbar-bottom {
            bottom: 0 !important;
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
        }

        /* Override safe area for iPhone X and newer - force to bottom */
        @supports (padding-bottom: env(safe-area-inset-bottom)) {
            .vintech-navbar.navbar-bottom {
                padding-bottom: 0 !important;
                height: 85px !important;
                bottom: 0 !important;
            }

            body.navbar-at-bottom {
                padding-bottom: 85px !important;
            }
        }        /* Force full width on all containers */
        .vintech-navbar.navbar-bottom * {
            box-sizing: border-box;
        }

        .vintech-navbar.navbar-bottom .navbar-nav {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            display: flex !important;
            justify-content: space-evenly !important;
            align-items: center !important;
        }/* Ensure perfect centering */
        .vintech-navbar.navbar-bottom .container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .vintech-navbar.navbar-bottom .container-fluid {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
        }        /* Ensure navbar with navbar-bottom class always sticks to bottom */
        .vintech-navbar.fixed-top.navbar-bottom {
            position: fixed !important;
            top: auto !important;
            bottom: 0 !important;
        }

        .vintech-navbar.scrolled.navbar-bottom {
            position: fixed !important;
            top: auto !important;
            bottom: 0 !important;
        }

        /* Additional centering fixes */
        .vintech-navbar.navbar-bottom {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .vintech-navbar.navbar-bottom .navbar-collapse {
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }

        /* Override any top positioning when navbar-bottom is present */
        .navbar.navbar-bottom {
            position: fixed !important;
            top: auto !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
        }

        /* Ripple effect for bottom nav items */
        .vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link {
            overflow: hidden;
        }

        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Remove duplicate background styling */

        /* Subtle animation on load */
        .vintech-navbar.navbar-bottom .bottom-nav-item {
            animation: slideUpFade 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        .vintech-navbar.navbar-bottom .bottom-nav-item:nth-child(1) { animation-delay: 0.1s; }
        .vintech-navbar.navbar-bottom .bottom-nav-item:nth-child(2) { animation-delay: 0.2s; }
        .vintech-navbar.navbar-bottom .bottom-nav-item:nth-child(3) { animation-delay: 0.3s; }
        .vintech-navbar.navbar-bottom .bottom-nav-item:nth-child(4) { animation-delay: 0.4s; }

        @keyframes slideUpFade {
            0% {
                opacity: 0;
                transform: translateY(30px) scale(0.8);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Enhanced bottom navbar entrance animation */
        .vintech-navbar.navbar-bottom {
            animation: slideUpFromBottom 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }

        @keyframes slideUpFromBottom {
            0% {
                transform: translateY(100%);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Smooth transitions for all navbar states */
        .vintech-navbar,
        .vintech-navbar * {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Enhanced mobile menu for bottom position */
        .vintech-navbar.navbar-bottom .navbar-collapse {
            position: absolute;
            bottom: 100%;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0);
            backdrop-filter: blur(40px);
            border-radius: 20px 20px 0 0;
            margin-bottom: 1px;
            box-shadow: 0 0px 20px rgba(45, 90, 39, 0.08);
        }        .vintech-navbar .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
            height: 100%;
            display: flex;
            align-items: center;
            width: 100%;
        }
        
        .vintech-navbar.scrolled {
            background: rgba(255, 255, 255, 0.99);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            box-shadow: 0 6px 0 rgba(45, 90, 39, 0);
            border-bottom: 1px solid rgba(45, 90, 39, 0.15);
        }
        
        .vintech-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--primary-green) !important;
            transition: all 0.3s ease;
        }
        
        .vintech-brand:hover {
            transform: translateY(-1px);
            color: var(--primary-green) !important;
        }
        
        .vintech-logo {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--accent-green) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            box-shadow: 0 4px 15px rgba(45, 90, 39, 0.2);
            transition: all 0.3s ease;
        }

        .vintech-brand:hover .vintech-logo {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(45, 90, 39, 0.3);
        }
        
        .vintech-brand-text {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .vintech-brand-badge {
            background: linear-gradient(135deg, var(--accent-green) 0%, #28a745 100%);
            color: white;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-left: 6px;
            text-transform: uppercase;
            box-shadow: 0 2px 8px rgba(111, 187, 107, 0.25);
        }
        
        /* Enhanced Mobile Toggle */
        .vintech-toggler {
            border: none;
            padding: 8px;
            background: transparent;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 4px;
            position: relative;
            z-index: 1051;
        }
        
        .vintech-toggler span {
            width: 22px;
            height: 2.5px;
            background: #2d5a27;
            border-radius: 2px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: center;
        }
        
        .vintech-toggler[aria-expanded="true"] span:nth-child(1) {
            transform: rotate(45deg) translate(7px, 7px);
        }
        
        .vintech-toggler[aria-expanded="true"] span:nth-child(2) {
            opacity: 0;
            transform: scaleX(0);
        }
        
        .vintech-toggler[aria-expanded="true"] span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }
          .vintech-nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            padding: 11px 16px !important;
            border-radius: 20px;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            margin: 0 3px;
            white-space: nowrap;
            font-size: 0.95rem;
            height: auto !important;
            line-height: 1 !important;
        }
        
        /* Remove all nav link backgrounds */
        .vintech-nav-link::before {
            display: none !important;
        }
        
        .vintech-nav-link:hover {
            color: var(--primary-green) !important;
            transform: translateY(-2px);
        }
        
        .vintech-nav-link i {
            font-size: 1rem;
            transition: transform 0.3s ease;
        }
        
        .vintech-nav-link:hover i {
            transform: scale(1.1);
        }        .navbar-nav {
            align-items: center;
            gap: 6px;
            display: flex;
            flex-wrap: nowrap;
            margin-left: auto;
        }

        .navbar-nav .nav-item {
            display: flex;
            align-items: center;
        }

        .navbar-nav .nav-link,
        .navbar-nav .vintech-nav-cta {
            height: 43px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .vintech-cart-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            min-width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
            animation: pulse 2s infinite;
            padding: 0 4px;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }        .vintech-nav-cta {
            background: linear-gradient(135deg, var(--accent-green) 0%, #28a745 100%) !important;
            color: white !important;
            margin-left: 12px !important;
            padding: 11px 18px !important;
            font-weight: 600;
            border-radius: 25px !important;
            border: none;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
            position: relative;
            overflow: hidden;
            white-space: nowrap;
            font-size: 0.9rem;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            vertical-align: middle !important;
            height: auto !important;
            line-height: 1 !important;
        }
        
        .vintech-nav-cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s;
        }
        
        .vintech-nav-cta:hover::before {
            left: 100%;
        }
        
        .vintech-nav-cta:hover {
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }
        
        /* Mobile Navigation Enhancements */
        @media (max-width: 991px) {
            .navbar-collapse {
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(20px);
                border-radius: 20px;
                margin-top: 15px;
                padding: 25px;
                box-shadow: 0 10px 0 rgba(45, 90, 39, 0);
                border: 1px solid rgba(45, 90, 39, 0.1);
            }
            
            .vintech-nav-link {
                margin: 8px 0;
                padding: 15px 20px !important;
                border-radius: 15px;
                width: 100%;
                justify-content: flex-start;
            }
              .vintech-nav-cta {
                margin: 15px auto 0 auto !important;
                text-align: center;
                justify-content: center;
                width: 100%;
                max-width: 200px;
                padding: 15px 24px !important;
                display: flex !important;
                align-items: center !important;
            }
            
            /* Smooth slide animation for mobile menu */
            .navbar-collapse {
                opacity: 0;
                transform: translateY(-20px);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .navbar-collapse.show {
                opacity: 1;
                transform: translateY(0);
            }
            
            .navbar-collapse.collapsing {
                opacity: 0.5;
                transform: translateY(-10px);
            }
        }
        
        /* Scroll indicator */
        .vintech-navbar::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #6fbb6b, #28a745);
            transition: width 0.3s ease;
        }
        
        .vintech-navbar.scrolled::after {
            width: 100%;
        }
        
        /* Floating navigation on scroll */
        @media (min-width: 992px) {
            .vintech-navbar.floating {
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                width: 90%;
                max-width: 1200px;
                border-radius: 50px;
                background: rgba(255, 255, 255, 0.95);
                box-shadow: 0 10px 40px rgba(45, 90, 39, 0.15);
                animation: slideDown 0.3s ease;
            }

            .vintech-navbar.floating .container {
                max-width: none;
                padding: 0 30px;
            }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        /* User Authentication Styles */
        .user-menu-toggle {
            display: flex !important;
            align-items: center;
            gap: 8px;
            padding: 8px 16px !important;
            border-radius: 25px;
            background: rgba(111, 187, 107, 0.1);
            border: 1px solid rgba(111, 187, 107, 0.2);
            transition: all 0.3s ease;
        }

        .user-menu-toggle:hover {
            background: rgba(111, 187, 107, 0.15);
            transform: translateY(-1px);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--vintech-primary);
            display: inline-block;
            vertical-align: middle;
            flex-shrink: 0;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-name {
            font-weight: 600;
            color: var(--vintech-primary);
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-badge {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 6px;
            flex-shrink: 0;
        }

        .vintech-dropdown-menu {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(45, 90, 39, 0.1);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(45, 90, 39, 0.15);
            padding: 15px 0;
            margin-top: 10px;
            min-width: 280px;
            position: absolute;
            z-index: 1060;
        }

        .vintech-dropdown-menu .dropdown-header {
            padding: 10px 20px;
            margin-bottom: 5px;
        }

        .user-info strong {
            color: var(--vintech-primary);
            font-weight: 600;
        }

        .user-info small {
            color: #6c757d;
            font-size: 0.8rem;
        }

        .vintech-dropdown-menu .dropdown-item {
            padding: 10px 20px;
            color: #4a4a4a;
            transition: all 0.3s ease;
            border-radius: 0;
            margin: 2px 10px;
            border-radius: 8px;
        }

        .vintech-dropdown-menu .dropdown-item:hover {
            background: rgba(45, 90, 39, 0.1);
            color: var(--vintech-primary);
            transform: translateX(5px);
        }

        .vintech-dropdown-menu .dropdown-item.text-danger:hover {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .vintech-dropdown-menu .dropdown-divider {
            margin: 10px 20px;
            border-color: rgba(45, 90, 39, 0.1);
        }

        /* Guest buttons */
        .vintech-btn-outline {
            background: transparent;
            color: var(--vintech-primary);
            border: 2px solid var(--vintech-primary);
            margin-left: 10px;
        }

        .vintech-btn-outline:hover {
            background: var(--vintech-primary);
            color: white;
        }

        /* Navbar responsive fixes */
        @media (max-width: 991px) {
            .vintech-navbar {
                position: fixed !important;
                top: 0;
                left: 0;
                right: 0;
                width: 100%;
                transform: none !important;
                padding: 0.5rem 0;
            }

            .vintech-navbar .container {
                width: 100%;
                max-width: none;
                padding: 0 15px;
            }

            .navbar-nav {
                text-align: center;
                padding: 1rem 0;
                gap: 0.5rem;
            }

            .nav-item {
                margin: 0.25rem 0;
                width: 100%;
            }

            .vintech-nav-link {
                justify-content: center;
                padding: 12px 20px !important;
                margin: 0 10px;
                border-radius: 15px;
            }

            .vintech-nav-cta {
                margin: 0.5rem 10px !important;
                justify-content: center;
            }

            .user-menu-toggle {
                justify-content: center;
                margin: 0 10px;
            }

            .vintech-dropdown-menu {
                position: static;
                float: none;
                width: calc(100% - 20px);
                margin: 0.5rem 10px;
                border: 1px solid rgba(45, 90, 39, 0.1);
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(45, 90, 39, 0.1);
                background: rgba(255, 255, 255, 0.98);
            }
        }

        /* Small mobile adjustments */
        @media (max-width: 576px) {
            .vintech-nav-link span,
            .vintech-nav-cta span {
                display: inline !important;
            }

            .user-name {
                display: none !important;
            }

            .vintech-cart-badge {
                font-size: 0.7rem;
                padding: 2px 6px;
            }
        }

        /* Fix for navbar brand */
        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        /* Fix for navbar toggler */
        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        /* Floating Bottom Navigation */
        .floating-bottom-nav {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1060;
            opacity: 0;
            transform: translateY(100px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
        }

        .floating-bottom-nav.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }

        .floating-nav-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 15px;
            box-shadow: 0 10px 40px rgba(45, 90, 39, 0.2);
            border: 1px solid rgba(45, 90, 39, 0.1);
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-width: 80px;
        }

        .floating-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 12px 8px;
            border-radius: 15px;
            text-decoration: none;
            color: #4a4a4a;
            transition: all 0.3s ease;
            position: relative;
            background: transparent;
        }

        .floating-nav-item:hover {
            background: rgba(45, 90, 39, 0.1);
            color: var(--vintech-primary);
            transform: scale(1.05);
        }

        .floating-nav-item i {
            font-size: 1.2rem;
            margin-bottom: 2px;
        }

        .floating-nav-item span {
            font-size: 0.7rem;
            font-weight: 500;
            text-align: center;
            line-height: 1;
        }

        .floating-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--vintech-primary);
        }

        .floating-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .floating-cart-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-radius: 50%;
            min-width: 16px;
            height: 16px;
            font-size: 0.6rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid white;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }

        /* Hide floating nav on large screens by default */
        @media (min-width: 992px) {
            .floating-bottom-nav {
                display: none;
            }
        }

        /* Show floating nav on mobile/tablet */
        @media (max-width: 991px) {
            .floating-bottom-nav {
                display: block;
            }

            .floating-nav-container {
                flex-direction: row;
                bottom: 0;
                left: 0;
                right: 0;
                border-radius: 25px 25px 0 0;
                padding: 15px 20px;
                justify-content: space-around;
                min-width: auto;
                position: fixed;
                bottom: 0;
            }

            .floating-bottom-nav {
                bottom: 0;
                right: 0;
                left: 0;
                border-radius: 0;
            }

            .floating-nav-item {
                flex: 1;
                padding: 8px 4px;
            }

            .floating-nav-item span {
                font-size: 0.65rem;
            }

            body {
                padding-bottom: 90px;
            }
        }
    </style>

    <!-- Floating Navigation Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const floatingNav = document.getElementById('floatingNav');
            const navbar = document.querySelector('.vintech-navbar');
            let lastScrollTop = 0;
            let scrollTimeout;

            // Show floating nav after scroll
            function handleScroll() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                // Clear previous timeout
                clearTimeout(scrollTimeout);

                // Show floating nav when scrolling down past 200px
                if (scrollTop > 200) {
                    floatingNav.classList.add('show');

                    // Auto hide after 3 seconds of no scrolling (desktop only)
                    if (window.innerWidth >= 992) {
                        scrollTimeout = setTimeout(() => {
                            floatingNav.classList.remove('show');
                        }, 3000);
                    }
                } else {
                    floatingNav.classList.remove('show');
                }

                // Scrolled class is handled by the main navigation script

                lastScrollTop = scrollTop;
            }

            // Throttled scroll handler
            let ticking = false;
            function requestTick() {
                if (!ticking) {
                    requestAnimationFrame(handleScroll);
                    ticking = true;
                    setTimeout(() => { ticking = false; }, 10);
                }
            }

            window.addEventListener('scroll', requestTick);

            // Show floating nav on hover (desktop)
            if (window.innerWidth >= 992) {
                floatingNav.addEventListener('mouseenter', () => {
                    clearTimeout(scrollTimeout);
                    floatingNav.classList.add('show');
                });

                floatingNav.addEventListener('mouseleave', () => {
                    if (window.pageYOffset > 200) {
                        scrollTimeout = setTimeout(() => {
                            floatingNav.classList.remove('show');
                        }, 1000);
                    }
                });
            }

            // Update wishlist count
            function updateWishlistCount() {
                const wishlistCount = localStorage.getItem('wishlistCount') || '0';
                const headerWishlist = document.getElementById('header-wishlist-count');
                const floatingWishlist = document.getElementById('floating-wishlist-count');

                if (headerWishlist) headerWishlist.textContent = wishlistCount;
                if (floatingWishlist) floatingWishlist.textContent = wishlistCount;

                // Hide badge if count is 0
                if (wishlistCount === '0') {
                    if (headerWishlist) headerWishlist.style.display = 'none';
                    if (floatingWishlist) floatingWishlist.style.display = 'none';
                } else {
                    if (headerWishlist) headerWishlist.style.display = 'flex';
                    if (floatingWishlist) floatingWishlist.style.display = 'flex';
                }
            }

            // Initialize wishlist count
            updateWishlistCount();

            // Listen for wishlist updates
            window.addEventListener('storage', updateWishlistCount);
            window.addEventListener('wishlistUpdated', updateWishlistCount);
        });
    </script>

    <!-- Enhanced Navigation JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.querySelector('.vintech-navbar');
            const navbarToggler = document.querySelector('.vintech-toggler');
            const navbarCollapse = document.querySelector('.navbar-collapse');
            
            // Enhanced scroll effects with bottom positioning
            let lastScrollTop = 0;
            let scrollDirection = 'up';
            let isScrolling = false;
            let scrollTimer = null;
            const body = document.body;

            // Debounce scroll events
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            function handleScroll() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                console.log('Scroll position:', scrollTop); // Debug

                // Determine scroll direction
                if (scrollTop > lastScrollTop) {
                    scrollDirection = 'down';
                } else {
                    scrollDirection = 'up';
                }

                // Clear existing timer
                if (scrollTimer) {
                    clearTimeout(scrollTimer);
                }

                isScrolling = true;

                // Add scrolled class
                if (scrollTop > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }

                // Handle navbar positioning based on scroll with improved logic
                if (scrollTop > 80) {
                    console.log('Moving navbar to bottom'); // Debug
                    // Move navbar to bottom when scrolling past threshold
                    navbar.classList.remove('navbar-hidden', 'fixed-top');
                    navbar.classList.add('navbar-bottom');
                    body.classList.add('navbar-at-bottom');

                    // Add smooth transition class
                    navbar.style.transition = 'all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                } else {
                    console.log('Moving navbar to top'); // Debug
                    // Return to top when near top of page
                    navbar.classList.remove('navbar-bottom', 'navbar-hidden');
                    navbar.classList.add('fixed-top');
                    body.classList.remove('navbar-at-bottom');

                    // Add smooth transition class
                    navbar.style.transition = 'all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                }

                // Set timer to detect when scrolling stops
                scrollTimer = setTimeout(() => {
                    isScrolling = false;

                    // Ensure navbar stays visible at bottom
                    if (scrollTop > 100 && navbar.classList.contains('navbar-bottom')) {
                        navbar.classList.remove('navbar-hidden');
                    }
                }, 150);

                lastScrollTop = scrollTop;
            }

            // Use debounced scroll handler for better performance
            const debouncedHandleScroll = debounce(handleScroll, 10);

            window.addEventListener('scroll', debouncedHandleScroll, { passive: true });

            // Handle touch events for mobile swipe
            let touchStartY = 0;
            let touchEndY = 0;

            document.addEventListener('touchstart', function(e) {
                touchStartY = e.changedTouches[0].screenY;
            }, { passive: true });

            document.addEventListener('touchend', function(e) {
                touchEndY = e.changedTouches[0].screenY;
                handleSwipe();
            }, { passive: true });

            function handleSwipe() {
                const swipeThreshold = 40;
                const diff = touchStartY - touchEndY;

                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0) {
                        // Swiping up - move navbar to bottom
                        if (window.pageYOffset > 50) {
                            navbar.classList.remove('fixed-top');
                            navbar.classList.add('navbar-bottom');
                            body.classList.add('navbar-at-bottom');
                            navbar.style.transition = 'all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                        }
                    } else {
                        // Swiping down - return navbar to top if near top
                        if (window.pageYOffset < 80) {
                            navbar.classList.remove('navbar-bottom');
                            navbar.classList.add('fixed-top');
                            body.classList.remove('navbar-at-bottom');
                            navbar.style.transition = 'all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                        }
                    }
                }
            }

            // Initialize navbar position on page load
            if (window.pageYOffset > 80) {
                navbar.classList.remove('fixed-top');
                navbar.classList.add('navbar-bottom');
                body.classList.add('navbar-at-bottom');
            } else {
                navbar.classList.add('fixed-top');
                navbar.classList.remove('navbar-bottom');
                body.classList.remove('navbar-at-bottom');
            }

            // Add smooth scroll behavior for better UX
            document.documentElement.style.scrollBehavior = 'smooth';

            // Highlight active menu item in bottom navbar
            function highlightActiveMenuItem() {
                const currentPath = window.location.pathname;
                const navLinks = document.querySelectorAll('.vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link');

                navLinks.forEach(link => {
                    link.classList.remove('active');
                    const href = link.getAttribute('href');

                    if (href && (currentPath === href || currentPath.startsWith(href + '/') ||
                        (href.includes('/') && currentPath.includes(href.split('/').pop())))) {
                        link.classList.add('active');
                    }
                });
            }

            // Call highlight function when navbar moves to bottom
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        if (navbar.classList.contains('navbar-bottom')) {
                            setTimeout(highlightActiveMenuItem, 100);
                        }
                    }
                });
            });

            observer.observe(navbar, { attributes: true });

            // Initial highlight
            if (navbar.classList.contains('navbar-bottom')) {
                highlightActiveMenuItem();
            }

            // Add ripple effect to bottom nav items
            function createRipple(event) {
                const button = event.currentTarget;
                const circle = document.createElement('span');
                const diameter = Math.max(button.clientWidth, button.clientHeight);
                const radius = diameter / 2;

                circle.style.width = circle.style.height = `${diameter}px`;
                circle.style.left = `${event.clientX - button.offsetLeft - radius}px`;
                circle.style.top = `${event.clientY - button.offsetTop - radius}px`;
                circle.classList.add('ripple');

                const ripple = button.getElementsByClassName('ripple')[0];
                if (ripple) {
                    ripple.remove();
                }

                button.appendChild(circle);
            }

            // Add ripple effect to all bottom nav links
            document.addEventListener('click', function(e) {
                if (e.target.closest('.vintech-navbar.navbar-bottom .bottom-nav-item .vintech-nav-link')) {
                    createRipple(e);
                }
            });
            
            // Mobile menu close on link click
            const navLinks = document.querySelectorAll('.vintech-nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        const collapse = new bootstrap.Collapse(navbarCollapse, {
                            hide: true
                        });
                    }
                });
            });
            
            // Enhanced mobile menu animation
            navbarCollapse.addEventListener('show.bs.collapse', function() {
                navbarToggler.setAttribute('aria-expanded', 'true');
            });
            
            navbarCollapse.addEventListener('hide.bs.collapse', function() {
                navbarToggler.setAttribute('aria-expanded', 'false');
            });
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Update wishlist count in header (only for logged in users)
            function updateWishlistCount() {
                <?php if ($isLoggedIn): ?>
                const wishlist = JSON.parse(localStorage.getItem('vintech-wishlist') || '[]');
                const countElement = document.getElementById('header-wishlist-count');
                const floatingCountElement = document.getElementById('floating-wishlist-count');

                if (countElement) {
                    countElement.textContent = wishlist.length;
                    countElement.style.display = wishlist.length > 0 ? 'flex' : 'none';
                }

                if (floatingCountElement) {
                    floatingCountElement.textContent = wishlist.length;
                    floatingCountElement.style.display = wishlist.length > 0 ? 'flex' : 'none';
                }
                <?php endif; ?>
            }

            // Initial update (only for logged in users)
            <?php if ($isLoggedIn): ?>
            updateWishlistCount();

            // Listen for storage changes
            window.addEventListener('storage', updateWishlistCount);

            // Listen for custom wishlist events
            document.addEventListener('wishlistUpdated', updateWishlistCount);
            <?php endif; ?>

            // Show logout message if present
            <?php if (isset($logout_message)): ?>
                setTimeout(() => {
                    if (window.VinTech) {
                        VinTech.showToast('<?= $logout_message ?>', 'success');
                    }
                }, 1000);
            <?php endif; ?>
        });
    </script>

     <!-- VinTech AI Chatbot Widget -->
    <script src="<?= BASE_URL ?>./assets/js/ai-chatbot.js"></script>
    <script>
        // Initialize Meta AI-style chatbot
        document.addEventListener('DOMContentLoaded', function() {
            window.metaAI = new VinTechAIChatbot({
                brandName: 'Meta AI Assistant',
                assistantName: 'CONVOI VinTech AI'
            });
            
            // Global access for other scripts
            window.vinTechAI = window.metaAI;
            
            console.log('🤖 Meta AI Assistant initialized successfully');
        });
    </script>

    <!-- Base URL for JavaScript -->
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>

    <!-- Main Content -->
    <main class="vintech-main-content">
