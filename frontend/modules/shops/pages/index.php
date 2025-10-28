<?php
session_start();
require_once __DIR__ . '/../../../../backend/bootstrap.php';
require_once '../includes/auth.php';

$page_title = "Cửa hàng - XAYDUNGTUONGLAI";
$extra_css = ['shop-modern.css'];

// Check if user is logged in
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $auth->getCurrentUser();

include __DIR__ . '/../../../common/components/header.php';

// Get featured products and categories
try {
    // Get featured products
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'active' AND p.featured = 1 
        ORDER BY p.created_at DESC 
        LIMIT 8
    ");
    $stmt->execute();
    $featured_products = $stmt->fetchAll();

    // Get categories with product count
    $stmt = $pdo->prepare("
        SELECT c.*, COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
        WHERE c.status = 'active'
        GROUP BY c.id
        ORDER BY c.name
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll();

    // Get latest products
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'active'
        ORDER BY p.created_at DESC 
        LIMIT 4
    ");
    $stmt->execute();
    $latest_products = $stmt->fetchAll();

} catch (PDOException $e) {
    $featured_products = [];
    $categories = [];
    $latest_products = [];
}

// Get cart count for display
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

// Helper function to get category icon
function getCategoryIcon($categoryName) {
    $icons = [
        'Thời trang' => 'tshirt',
        'Điện tử' => 'laptop',
        'Sách' => 'book',
        'Thể thao' => 'running',
        'Gia dụng' => 'home',
        'Mỹ phẩm' => 'spa',
        'Đồ chơi' => 'gamepad',
        'Thực phẩm' => 'utensils'
    ];
    return $icons[$categoryName] ?? 'box';
}
?>

<link rel="stylesheet" href="../assets/css/shop.css">
<!-- <link rel="stylesheet" href="../assets/css/charity-modern.css"> -->

<style>
.charity-info {
    border: 1px solid #e8f5e8;
    background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
    transition: all 0.3s ease;
    border-radius: 8px;
}

.charity-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.15);
}

.charity-info .fas.fa-heart {
    animation: heartbeat 2s ease-in-out infinite;
}

@keyframes heartbeat {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.product-card:hover .charity-info {
    border-color: #28a745;
    background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
}

.charity-info small {
    font-size: 0.85rem;
}

.charity-info .text-success {
    font-size: 0.8rem;
    line-height: 1.3;
}
</style>

<!-- Modern Hero Section with Blog Style -->
<!-- Modern Hero Section -->
<section class="shop-hero">
    <div class="container">
        <div class="shop-hero-content" data-animate="fadeInUp">
            <div class="shop-hero-badge">
                <i class="fas fa-shopping-bag"></i>
                <span>XAYDUNGTUONGLAI Shop</span>
            </div>
            <h1 class="shop-hero-title">
                Mua sắm thông minh<br>
                <span style="color: #ffd700;">Góp phần thiện nguyện</span>
            </h1>
            <p class="shop-hero-subtitle">
                Khám phá hàng ngàn sản phẩm chất lượng với giá tốt nhất.
                Mỗi giao dịch đều góp phần vào các hoạt động thiện nguyện của chúng tôi.
            </p>

            <div class="shop-hero-stats">
                <div class="shop-stat">
                    <span class="shop-stat-number">1000+</span>
                    <span class="shop-stat-label">Sản phẩm chất lượng</span>
                </div>
                <div class="shop-stat">
                    <span class="shop-stat-number">500+</span>
                    <span class="shop-stat-label">Khách hàng hài lòng</span>
                </div>
                <div class="shop-stat">
                    <span class="shop-stat-number">24/7</span>
                    <span class="shop-stat-label">Hỗ trợ tận tình</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="shop-categories">
    <div class="container">
        <div class="section-header" data-animate="fadeInUp">
            <h2 class="section-title">Danh mục sản phẩm</h2>
            <p class="section-subtitle">
                Khám phá các sản phẩm chất lượng trong từng danh mục của XAYDUNGTUONGLAI Shop
            </p>
        </div>

        <?php if (!empty($categories)): ?>
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
            <div class="category-card" data-animate="fadeInUp">
                <div class="category-icon">
                    <i class="fas fa-<?= getCategoryIcon($category['name']) ?>"></i>
                </div>
                <h3 class="category-title"><?= htmlspecialchars($category['name']) ?></h3>
                <p class="category-desc"><?= htmlspecialchars($category['description'] ?? 'Khám phá sản phẩm chất lượng') ?></p>
                <span class="category-count"><?= $category['product_count'] ?> sản phẩm</span>
                <a href="product_list.php?category=<?= $category['id'] ?>" class="category-link">
                    <span>Xem sản phẩm</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Products Section -->
<?php if (!empty($featured_products)): ?>
<section class="shop-products">
    <div class="container">
        <div class="section-header" data-animate="fadeInUp">
            <h2 class="section-title">Sản phẩm nổi bật</h2>
            <p class="section-subtitle">
                Những sản phẩm được yêu thích nhất tại XAYDUNGTUONGLAI Shop
            </p>
        </div>

        <div class="products-grid">
            <?php foreach ($featured_products as $index => $product): ?>
            <div class="product-card" data-animate="fadeInUp" style="animation-delay: <?= $index * 0.1 ?>s">
                <div class="product-image">
                    <img src="<?= !empty($product['image']) ? $product['image'] : '../assets/images/default-product.jpg' ?>"
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         loading="lazy"
                         onerror="this.src='../assets/images/default-product.jpg'">

                    <?php if ($product['featured']): ?>
                    <span class="product-badge">NỔI BẬT</span>
                    <?php endif; ?>

                    <div class="product-actions">
                        <button class="product-action-btn" title="Yêu thích">
                            <i class="far fa-heart"></i>
                        </button>
                        <button class="product-action-btn" title="Xem nhanh">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="product-content">
                    <div class="product-meta">
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span><?= htmlspecialchars($product['category_name'] ?? 'Sản phẩm') ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-box"></i>
                            <span>Còn <?= $product['stock_quantity'] ?></span>
                        </div>
                    </div>

                    <h3 class="product-title">
                        <a href="product_detail.php?id=<?= $product['id'] ?>">
                            <?= htmlspecialchars($product['name']) ?>
                        </a>
                    </h3>

                    <p class="product-desc">
                        <?= htmlspecialchars(substr($product['description'] ?? '', 0, 120)) ?>...
                    </p>

                    <div class="product-price">
                        <span class="price-current"><?= number_format($product['price']) ?>đ</span>
                        <?php if ($product['price'] < ($product['price'] * 1.3)): ?>
                        <span class="price-original"><?= number_format($product['price'] * 1.3) ?>đ</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-rating">
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= 4 ? '' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-count">(4.0)</span>
                    </div>
                </div>

                <div class="product-footer">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <?php if ($isLoggedIn): ?>
                        <button class="btn-modern btn-primary-modern add-to-cart-btn"
                                data-product-id="<?= $product['id'] ?>"
                                data-product-name="<?= htmlspecialchars($product['name']) ?>">
                            <i class="fas fa-cart-plus"></i>
                            <span>Thêm vào giỏ</span>
                        </button>
                        <?php else: ?>
                        <button class="btn-modern btn-outline-modern login-required-btn"
                                data-product-id="<?= $product['id'] ?>">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Đăng nhập</span>
                        </button>
                        <?php endif; ?>
                    <?php else: ?>
                    <button class="btn-modern btn-disabled-modern" disabled>
                        <i class="fas fa-times"></i>
                        <span>Hết hàng</span>
                    </button>
                    <?php endif; ?>

                    <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn-modern btn-outline-modern">
                        <i class="fas fa-eye"></i>
                        <span>Chi tiết</span>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="product_list.php" class="btn-modern btn-primary-modern" style="padding: 16px 32px; font-size: 1.1rem;">
                <i class="fas fa-arrow-right"></i>
                <span>Xem Tất Cả Sản Phẩm</span>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Latest Products Section -->
<?php if (!empty($latest_products)): ?>
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Sản Phẩm Mới Nhất</h2>
                <p class="lead text-muted">Cập nhật những sản phẩm mới nhất</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($latest_products as $product): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card product-card h-100 border-0 shadow-sm">
                    <div class="position-relative">
                        <img src="<?= !empty($product['image']) ? $product['image'] : '../assets/images/default-product.jpg' ?>"
                             class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>"
                             onerror="this.src='../assets/images/default-product.jpg'">
                        <span class="badge bg-success position-absolute top-0 start-0 m-2">Mới</span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text text-muted small">
                            <?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...
                        </p>
                        <p class="text-primary fw-bold fs-5">
                            <?= number_format($product['price']) ?>đ
                        </p>

                        <!-- Charity contribution info -->
                        <div class="charity-info mt-2 p-2 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-heart text-danger me-2"></i>
                                <small class="text-muted">
                                    <strong>Thiện nguyện:</strong>
                                    <?= number_format($product['price'] * 0.1) ?>đ (10%)
                                </small>
                            </div>
                            <small class="text-success">
                                <i class="fas fa-info-circle me-1"></i>
                                10% giá trị sản phẩm sẽ được dành cho hoạt động thiện nguyện
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn btn-primary w-100">
                            Xem Chi Tiết
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- About Shop Section -->
<section class="py-5 bg-dark text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-4">Về XAYDUNGTUONGLAI Shop</h2>
                <p class="lead mb-4">
                    XAYDUNGTUONGLAI Shop không chỉ là nơi mua sắm mà còn là cầu nối kết nối cộng đồng. 
                    Một phần lợi nhuận từ mỗi sản phẩm sẽ được dành cho các hoạt động thiện nguyện.
                </p>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-warning">1000+</h3>
                            <p>Sản phẩm chất lượng</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-warning">500+</h3>
                            <p>Khách hàng hài lòng</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="row">
                    <div class="col-12 mb-3">
                        <i class="fas fa-shipping-fast fa-3x text-warning mb-2"></i>
                        <h5>Giao hàng nhanh chóng</h5>
                        <p class="text-light">Giao hàng toàn quốc trong 1-3 ngày</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <i class="fas fa-shield-alt fa-2x text-warning mb-2"></i>
                        <h6>Bảo hành chính hãng</h6>
                    </div>
                    <div class="col-6">
                        <i class="fas fa-heart fa-2x text-warning mb-2"></i>
                        <h6>Hỗ trợ thiện nguyện</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Shopping Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm vào giỏ hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p>Sản phẩm đã được thêm vào giỏ hàng!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tiếp tục mua</button>
                <a href="cart.php" class="btn btn-primary">Xem giỏ hàng</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle add to cart buttons (for logged in users)
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));

    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;

            // Add loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang thêm...';
            this.disabled = true;

            // AJAX request to add to cart
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count in header
                    const cartBadge = document.querySelector('.cart-count');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }

                    // Show success modal
                    cartModal.show();
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng');
            })
            .finally(() => {
                // Restore button state
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
    });

    // Handle login required buttons (for non-logged in users)
    const loginRequiredBtns = document.querySelectorAll('.login-required-btn');

    loginRequiredBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;

            // Show login modal or redirect to login page
            if (confirm('Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng. Chuyển đến trang đăng nhập?')) {
                // Redirect to login page with return URL
                const currentUrl = encodeURIComponent(window.location.href);
                window.location.href = '../auth/login.php?redirect=' + currentUrl;
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>

