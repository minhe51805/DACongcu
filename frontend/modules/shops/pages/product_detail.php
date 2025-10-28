<?php
session_start();
require_once __DIR__ . '/../../../../backend/bootstrap.php';

$product_id = intval($_GET['id'] ?? 0);

if ($product_id <= 0) {
    header('Location: product_list.php');
    exit();
}

try {
    // Get product details
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ? AND p.status = 'active'
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: product_list.php');
        exit();
    }
    
    // Get related products from same category
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.category_id = ? AND p.id != ? AND p.status = 'active'
        ORDER BY RAND()
        LIMIT 4
    ");
    $stmt->execute([$product['category_id'], $product_id]);
    $related_products = $stmt->fetchAll();

} catch (PDOException $e) {
    header('Location: product_list.php');
    exit();
}

$page_title = $product['name'];
include __DIR__ . '/../../../common/components/header.php';
?>

<link rel="stylesheet" href="../assets/css/shop.css">

<style>
/* Modern Product Detail Styles */
.product-detail-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 2rem 0;
}

.product-detail-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.product-images {
    padding: 2rem;
}

.product-detail-image {
    border-radius: 15px;
    transition: transform 0.3s ease;
    max-height: 500px;
    object-fit: cover;
    width: 100%;
}

.product-detail-image:hover {
    transform: scale(1.05);
}

.product-thumbnails {
    display: flex;
    gap: 10px;
    margin-top: 1rem;
}

.product-thumbnail {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    object-fit: cover;
}

.product-thumbnail:hover,
.product-thumbnail.active {
    border-color: #007bff;
    transform: scale(1.1);
}

.product-info {
    padding: 2rem;
}

.product-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
    line-height: 1.2;
}

.product-price {
    font-size: 2.2rem;
    font-weight: 800;
    color: #e74c3c;
    margin: 1.5rem 0;
    padding: 1rem;
    background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 100%);
    border-radius: 15px;
    border-left: 5px solid #e74c3c;
}

.product-meta {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 2rem;
}

.product-meta strong {
    color: #495057;
    font-weight: 600;
}

.quantity-selector {
    margin-bottom: 2rem;
}

.quantity-selector .d-flex {
    max-width: 200px;
}

.quantity-btn {
    background: #007bff;
    color: white;
    border: none;
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.quantity-btn:hover {
    background: #0056b3;
    transform: scale(1.1);
}

.quantity-input {
    width: 80px;
    height: 45px;
    text-align: center;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    margin: 0 10px;
    font-weight: 600;
    font-size: 1.1rem;
}

.btn-modern {
    padding: 15px 30px;
    border-radius: 15px;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    border: none;
    position: relative;
    overflow: hidden;
}

.btn-modern:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-modern:hover:before {
    left: 100%;
}

.btn-primary-modern {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

.btn-success-modern {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    color: white;
}

.product-features {
    background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
    padding: 2rem;
    border-radius: 15px;
    margin-top: 2rem;
}

.product-features h5 {
    color: #155724;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.product-features li {
    padding: 0.5rem 0;
    font-size: 1.05rem;
}

.charity-highlight {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 2px solid #ffc107;
    border-radius: 15px;
    padding: 1.5rem;
    margin: 2rem 0;
    text-align: center;
}

.charity-highlight h6 {
    color: #856404;
    font-weight: 700;
    margin-bottom: 1rem;
}

.charity-amount {
    font-size: 1.5rem;
    font-weight: 800;
    color: #dc3545;
    margin: 0.5rem 0;
}

.tabs-modern .nav-tabs {
    border: none;
    background: #f8f9fa;
    border-radius: 15px;
    padding: 0.5rem;
}

.tabs-modern .nav-link {
    border: none;
    border-radius: 10px;
    font-weight: 600;
    color: #6c757d;
    transition: all 0.3s ease;
}

.tabs-modern .nav-link.active {
    background: white;
    color: #007bff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.related-products-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

@media (max-width: 768px) {
    .product-title {
        font-size: 2rem;
    }

    .product-price {
        font-size: 1.8rem;
    }

    .product-info {
        padding: 1rem;
    }
}
</style>

<div class="product-detail-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white rounded-pill px-4 py-2 shadow-sm">
                <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">🏠 Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">🛍️ Cửa hàng</a></li>
                <li class="breadcrumb-item"><a href="product_list.php" class="text-decoration-none">📦 Sản phẩm</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>

        <!-- Product Detail -->
        <div class="product-detail-card">
            <div class="row g-0">
                <div class="col-lg-6">
            <!-- Product Images -->
            <div class="product-images">
                <?php
                $image_src = '../assets/images/default-product.jpg'; // Default image
                if (!empty($product['image'])) {
                    // Check if it's a full URL or relative path
                    if (filter_var($product['image'], FILTER_VALIDATE_URL)) {
                        $image_src = $product['image'];
                    } else {
                        // Check different possible paths
                        if (file_exists('../assets/images/' . ltrim($product['image'], '/'))) {
                            $image_src = '../assets/images/' . ltrim($product['image'], '/');
                        } elseif (file_exists('../uploads/' . ltrim($product['image'], '/'))) {
                            $image_src = '../uploads/' . ltrim($product['image'], '/');
                        } else {
                            $image_src = '../assets/images/default-product.jpg';
                        }
                    }
                }
                ?>
                <img src="<?= $image_src ?>"
                     class="img-fluid product-detail-image" id="mainImage"
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     onerror="this.src='../assets/images/default-product.jpg'">

                <!-- Thumbnail images (placeholder for multiple images) -->
                <div class="product-thumbnails mt-3">
                    <img src="<?= $image_src ?>"
                         class="product-thumbnail active" onclick="changeMainImage(this.src)"
                         onerror="this.src='../assets/images/default-product.jpg'">
                    <!-- Add more thumbnails here if you have multiple product images -->
                </div>
                </div>
            </div>

            <div class="col-lg-6">
            <div class="product-info">
                <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
                
                <div class="product-meta mb-3">
                    <div class="row">
                        <div class="col-6">
                            <strong>Danh mục:</strong> 
                            <a href="product_list.php?category=<?= $product['category_id'] ?>" class="text-decoration-none">
                                <?= htmlspecialchars($product['category_name']) ?>
                            </a>
                        </div>
                        <div class="col-6">
                            <strong>Mã sản phẩm:</strong> SP<?= str_pad($product['id'], 4, '0', STR_PAD_LEFT) ?>
                        </div>
                        <div class="col-6 mt-2">
                            <strong>Tình trạng:</strong> 
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span class="text-success">Còn hàng (<?= $product['stock_quantity'] ?>)</span>
                            <?php else: ?>
                                <span class="text-danger">Hết hàng</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-6 mt-2">
                            <strong>Thương hiệu:</strong> CONVOI
                        </div>
                    </div>
                </div>
                
                <div class="product-price">
                    💰 <?= number_format($product['price']) ?>đ
                </div>

                <!-- Charity Contribution Highlight -->
                <div class="charity-highlight">
                    <h6>💝 Đóng góp thiện nguyện</h6>
                    <div class="charity-amount">
                        <?= number_format($product['price'] * 0.1) ?>đ
                    </div>
                    <p class="mb-0">
                        <i class="fas fa-heart text-danger me-2"></i>
                        10% giá trị sản phẩm (<?= number_format($product['price'] * 0.1) ?>đ) sẽ được dành cho các hoạt động thiện nguyện
                    </p>
                </div>
                
                <div class="product-description mb-4">
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                
                <?php if ($product['stock_quantity'] > 0): ?>
                <form id="addToCartForm" class="mb-4">
                    <div class="quantity-selector">
                        <label for="quantity" class="form-label">Số lượng:</label>
                        <div class="d-flex align-items-center">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="quantity-input" id="quantity" name="quantity" 
                                   value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-3 d-md-flex">
                        <button type="submit" class="btn btn-modern btn-primary-modern flex-fill">
                            <i class="fas fa-cart-plus me-2"></i>🛒 Thêm vào giỏ hàng
                        </button>
                        <button type="button" class="btn btn-modern btn-success-modern" onclick="buyNow()">
                            <i class="fas fa-bolt me-2"></i>⚡ Mua ngay
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Sản phẩm hiện đã hết hàng. Vui lòng quay lại sau.
                </div>
                <?php endif; ?>
                
                <!-- Product Features -->
                <div class="product-features">
                    <h5>Đặc điểm nổi bật:</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Chất lượng cao, được kiểm định</li>
                        <li><i class="fas fa-check text-success me-2"></i>Bảo hành chính hãng</li>
                        <li><i class="fas fa-check text-success me-2"></i>Giao hàng nhanh chóng</li>
                        <li><i class="fas fa-check text-success me-2"></i>Hỗ trợ đổi trả trong 7 ngày</li>
                        <li><i class="fas fa-heart text-primary me-2"></i>Một phần lợi nhuận sẽ được dành cho thiện nguyện</li>
                    </ul>
                </div>
            </div>
            </div>
        </div>
    </div>

        <!-- Product Details Tabs -->
        <div class="tabs-modern mb-5">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-transparent border-0">
                    <ul class="nav nav-tabs" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                                    data-bs-target="#description" type="button" role="tab">
                                Mô tả chi tiết
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" 
                                    data-bs-target="#specifications" type="button" role="tab">
                                Thông số kỹ thuật
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" 
                                    data-bs-target="#reviews" type="button" role="tab">
                                Đánh giá (0)
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="productTabsContent">
                        <div class="tab-pane fade show active" id="description" role="tabpanel">
                            <div class="p-3">
                                <h5>Mô tả sản phẩm</h5>
                                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                                
                                <h6 class="mt-4">Cam kết chất lượng</h6>
                                <p>
                                    Tại CONVOI Shop, chúng tôi cam kết mang đến những sản phẩm chất lượng cao nhất 
                                    với giá cả hợp lý. Mỗi sản phẩm đều được kiểm tra kỹ lưỡng trước khi đến tay khách hàng.
                                </p>
                                
                                <h6 class="mt-4">Hỗ trợ thiện nguyện</h6>
                                <p class="text-primary">
                                    <i class="fas fa-heart me-2"></i>
                                    Khi bạn mua sản phẩm này, một phần lợi nhuận sẽ được dành cho các hoạt động thiện nguyện 
                                    của tổ chức CONVOI. Cảm ơn bạn đã góp phần tạo ra những thay đổi tích cực cho cộng đồng!
                                </p>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="specifications" role="tabpanel">
                            <div class="p-3">
                                <h5>Thông số kỹ thuật</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="30%">Tên sản phẩm</th>
                                            <td><?= htmlspecialchars($product['name']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Danh mục</th>
                                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Mã sản phẩm</th>
                                            <td>SP<?= str_pad($product['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Giá</th>
                                            <td class="text-primary fw-bold"><?= number_format($product['price']) ?>đ</td>
                                        </tr>
                                        <tr>
                                            <th>Tình trạng kho</th>
                                            <td>
                                                <?php if ($product['stock_quantity'] > 0): ?>
                                                    <span class="text-success">Còn <?= $product['stock_quantity'] ?> sản phẩm</span>
                                                <?php else: ?>
                                                    <span class="text-danger">Hết hàng</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Thương hiệu</th>
                                            <td>CONVOI</td>
                                        </tr>
                                        <tr>
                                            <th>Bảo hành</th>
                                            <td>12 tháng</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="reviews" role="tabpanel">
                            <div class="p-3">
                                <h5>Đánh giá sản phẩm</h5>
                                <div class="text-center py-4">
                                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
                                    <p class="text-muted">Hãy là người đầu tiên đánh giá!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div class="related-products-section">
            <div class="text-center mb-4">
                <h3 class="fw-bold">🔗 Sản phẩm liên quan</h3>
                <p class="text-muted">Khám phá thêm những sản phẩm tương tự</p>
            </div>
            <div class="row">
                <?php foreach ($related_products as $related): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card product-card h-100 border-0 shadow-sm">
                        <div class="position-relative">
                            <?php
                            $related_image_src = '../assets/images/default-product.jpg';
                            if (!empty($related['image'])) {
                                if (filter_var($related['image'], FILTER_VALIDATE_URL)) {
                                    $related_image_src = $related['image'];
                                } else {
                                    if (file_exists('../assets/images/' . ltrim($related['image'], '/'))) {
                                        $related_image_src = '../assets/images/' . ltrim($related['image'], '/');
                                    } elseif (file_exists('../uploads/' . ltrim($related['image'], '/'))) {
                                        $related_image_src = '../uploads/' . ltrim($related['image'], '/');
                                    }
                                }
                            }
                            ?>
                            <img src="<?= $related_image_src ?>"
                                 class="card-img-top" alt="<?= htmlspecialchars($related['name']) ?>"
                                 onerror="this.src='../assets/images/default-product.jpg'">
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">
                                <a href="product_detail.php?id=<?= $related['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($related['name']) ?>
                                </a>
                            </h6>
                            <p class="text-primary fw-bold">
                                <?= number_format($related['price']) ?>đ
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="product_detail.php?id=<?= $related['id'] ?>" class="btn btn-outline-primary w-100">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thành công!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p id="modalMessage">Sản phẩm đã được thêm vào giỏ hàng!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tiếp tục mua</button>
                <a href="cart.php" class="btn btn-primary">Xem giỏ hàng</a>
            </div>
        </div>
    </div>
</div>

<script>
function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
    
    // Update active thumbnail
    document.querySelectorAll('.product-thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    event.target.classList.add('active');
}

function changeQuantity(delta) {
    const quantityInput = document.getElementById('quantity');
    const currentValue = parseInt(quantityInput.value);
    const newValue = currentValue + delta;
    const maxValue = parseInt(quantityInput.max);
    
    if (newValue >= 1 && newValue <= maxValue) {
        quantityInput.value = newValue;
    }
}

function buyNow() {
    const quantity = document.getElementById('quantity').value;
    const productId = <?= $product_id ?>;
    
    // Add to cart first, then redirect to checkout
    addToCart(productId, quantity, true);
}

function addToCart(productId, quantity, redirectToCheckout = false) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count in header
            const cartBadge = document.querySelector('.badge');
            if (cartBadge) {
                cartBadge.textContent = data.cart_count;
            }
            
            if (redirectToCheckout) {
                window.location.href = 'cart.php';
            } else {
                // Show success modal
                document.getElementById('modalMessage').textContent = data.message;
                const modal = new bootstrap.Modal(document.getElementById('successModal'));
                modal.show();
            }
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle add to cart form
    document.getElementById('addToCartForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const quantity = document.getElementById('quantity').value;
        const productId = <?= $product_id ?>;
        
        addToCart(productId, quantity);
    });
    
    // Handle quantity input changes
    document.getElementById('quantity').addEventListener('change', function() {
        const value = parseInt(this.value);
        const max = parseInt(this.max);
        
        if (value < 1) {
            this.value = 1;
        } else if (value > max) {
            this.value = max;
        }
    });
});
</script>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>

