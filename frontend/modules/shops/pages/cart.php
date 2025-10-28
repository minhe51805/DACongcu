<?php
session_start();
require_once __DIR__ . '/../../../../backend/bootstrap.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $product_id = intval($_POST['product_id'] ?? 0);
        
        switch ($_POST['action']) {
            case 'update':
                $quantity = intval($_POST['quantity'] ?? 0);
                if ($quantity > 0) {
                    $_SESSION['cart'][$product_id] = $quantity;
                } else {
                    unset($_SESSION['cart'][$product_id]);
                }
                break;
                
            case 'remove':
                unset($_SESSION['cart'][$product_id]);
                break;
                
            case 'clear':
                $_SESSION['cart'] = [];
                break;
        }
        
        // Redirect to prevent form resubmission
        header('Location: cart.php');
        exit();
    }
}

// Debug: Check session cart
echo '<pre>Session Cart: '; print_r($_SESSION['cart']); echo '</pre>';

// Get cart products
$cart_products = [];
$total_amount = 0;

if (!empty($_SESSION['cart'])) {
    try {
        $product_ids = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        
        $stmt = $pdo->prepare("
            SELECT id, name, price, stock_quantity, image
            FROM products
            WHERE id IN ($placeholders) AND status = 'active'
        ");
        $stmt->execute($product_ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($products as $product) {
            $quantity = $_SESSION['cart'][$product['id']];
            $subtotal = $product['price'] * $quantity;
            
            $cart_products[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
            
            $total_amount += $subtotal;
        }
        
    } catch (PDOException $e) {
        $cart_products = [];
    }
}

$page_title = 'Giỏ hàng';
include __DIR__ . '/../../../common/components/header.php';
?>

<link rel="stylesheet" href="../assets/css/shop.css">

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="index.php">Cửa hàng</a></li>
            <li class="breadcrumb-item active">Giỏ hàng</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold">
                <i class="fas fa-shopping-cart me-2"></i>Giỏ Hàng Của Bạn
            </h1>
        </div>
    </div>

    <?php if (empty($cart_products)): ?>
    <!-- Empty Cart -->
    <div class="row">
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                <h3>Giỏ hàng của bạn đang trống</h3>
                <p class="text-muted mb-4">Hãy khám phá các sản phẩm tuyệt vời của chúng tôi!</p>
                <a href="product_list.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag me-2"></i>Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Cart Items -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sản phẩm trong giỏ (<?= count($cart_products) ?>)</h5>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                onclick="return confirm('Bạn có chắc muốn xóa tất cả sản phẩm?')">
                            <i class="fas fa-trash me-1"></i>Xóa tất cả
                        </button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($cart_products as $item): ?>
                    <div class="cart-item">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php
                                $image_src = '../assets/images/default-product.jpg'; // Default image
                                if (!empty($item['product']['image'])) {
                                    // Check if it's a full URL or relative path
                                    if (filter_var($item['product']['image'], FILTER_VALIDATE_URL)) {
                                        $image_src = $item['product']['image'];
                                    } else {
                                        // Check different possible paths
                                        if (file_exists('../assets/images/' . ltrim($item['product']['image'], '/'))) {
                                            $image_src = '../assets/images/' . ltrim($item['product']['image'], '/');
                                        } elseif (file_exists('../uploads/' . ltrim($item['product']['image'], '/'))) {
                                            $image_src = '../uploads/' . ltrim($item['product']['image'], '/');
                                        } else {
                                            $image_src = '../assets/images/default-product.jpg';
                                        }
                                    }
                                }
                                ?>
                                <img src="<?= $image_src ?>"
                                     class="cart-item-image" alt="<?= htmlspecialchars($item['product']['name']) ?>"
                                     onerror="this.src='../assets/images/default-product.jpg'"
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            </div>
                            <div class="col-md-4">
                                <div class="cart-item-info">
                                    <h6 class="mb-1">
                                        <a href="product_detail.php?id=<?= $item['product']['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($item['product']['name']) ?>
                                        </a>
                                    </h6>
                                    <p class="cart-item-price mb-0">
                                        <?= number_format($item['product']['price']) ?>đ
                                    </p>
                                    <small class="text-muted">
                                        Còn: <?= $item['product']['stock_quantity'] ?>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <form method="POST" class="d-flex align-items-center">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                                    <div class="quantity-selector">
                                        <button type="button" class="quantity-btn" 
                                                onclick="updateQuantity(<?= $item['product']['id'] ?>, -1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" name="quantity" 
                                               value="<?= $item['quantity'] ?>" 
                                               min="1" max="<?= $item['product']['stock_quantity'] ?>"
                                               class="quantity-input" 
                                               id="qty_<?= $item['product']['id'] ?>"
                                               onchange="updateCartItem(<?= $item['product']['id'] ?>)">
                                        <button type="button" class="quantity-btn" 
                                                onclick="updateQuantity(<?= $item['product']['id'] ?>, 1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <strong class="text-primary">
                                        <?= number_format($item['subtotal']) ?>đ
                                    </strong>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" 
                                            onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Continue Shopping -->
            <div class="mt-3">
                <a href="product_list.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
                </a>
            </div>
        </div>
        
        <!-- Cart Summary -->
        <div class="col-lg-4">
            <div class="cart-summary">
                <h5 class="mb-3">Tóm tắt đơn hàng</h5>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Tạm tính:</span>
                    <span><?= number_format($total_amount) ?>đ</span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Phí vận chuyển:</span>
                    <span class="text-success">Miễn phí</span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Giảm giá:</span>
                    <span>0đ</span>
                </div>
                
                <hr>
                
                <div class="cart-total d-flex justify-content-between">
                    <strong>Tổng cộng:</strong>
                    <strong><?= number_format($total_amount) ?>đ</strong>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="checkout.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-credit-card me-2"></i>Thanh toán
                    </a>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#discountModal">
                        <i class="fas fa-tag me-2"></i>Áp dụng mã giảm giá
                    </button>
                </div>
                
                <!-- Payment Security -->
                <div class="mt-4 text-center">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Thanh toán an toàn & bảo mật
                    </small>
                    <div class="mt-2">
                        <img src="../assets/images/momo-logo.png" alt="MoMo" style="height: 30px;" class="me-2">
                        <i class="fab fa-cc-visa fa-2x text-primary me-1"></i>
                        <i class="fab fa-cc-mastercard fa-2x text-warning"></i>
                    </div>
                </div>
                
                <!-- Charity Message -->
                <div class="alert alert-info mt-3">
                    <i class="fas fa-heart text-primary me-2"></i>
                    <small>
                        Một phần lợi nhuận từ đơn hàng này sẽ được dành cho các hoạt động thiện nguyện.
                        Cảm ơn bạn đã góp phần tạo ra những thay đổi tích cực!
                    </small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Discount Modal -->
<div class="modal fade" id="discountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mã giảm giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="discountCode" class="form-label">Nhập mã giảm giá:</label>
                        <input type="text" class="form-control" id="discountCode" placeholder="Ví dụ: WELCOME10">
                    </div>
                    <div class="alert alert-info">
                        <strong>Mã giảm giá hiện có:</strong><br>
                        <code>WELCOME10</code> - Giảm 10% cho đơn hàng đầu tiên<br>
                        <code>CHARITY5</code> - Giảm 5% và tặng thêm 5% cho thiện nguyện
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="applyDiscount()">Áp dụng</button>
            </div>
        </div>
    </div>
</div>

<script>
function updateQuantity(productId, delta) {
    const input = document.getElementById(`qty_${productId}`);
    const currentValue = parseInt(input.value);
    const newValue = currentValue + delta;
    const maxValue = parseInt(input.max);
    
    if (newValue >= 1 && newValue <= maxValue) {
        input.value = newValue;
        updateCartItem(productId);
    }
}

function updateCartItem(productId) {
    const quantity = document.getElementById(`qty_${productId}`).value;
    
    // Create and submit form
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="product_id" value="${productId}">
        <input type="hidden" name="quantity" value="${quantity}">
    `;
    
    document.body.appendChild(form);
    form.submit();
}

function applyDiscount() {
    const code = document.getElementById('discountCode').value.trim().toUpperCase();
    
    if (!code) {
        alert('Vui lòng nhập mã giảm giá');
        return;
    }
    
    // Simple discount validation (in real app, this should be server-side)
    const validCodes = {
        'WELCOME10': 10,
        'CHARITY5': 5
    };
    
    if (validCodes[code]) {
        alert(`Đã áp dụng mã giảm giá ${code} - Giảm ${validCodes[code]}%`);
        // In real implementation, this would update the cart total
        const modal = bootstrap.Modal.getInstance(document.getElementById('discountModal'));
        modal.hide();
    } else {
        alert('Mã giảm giá không hợp lệ hoặc đã hết hạn');
    }
}

// Auto-save cart when quantity changes
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(input => {
        let timeout;
        
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const productId = this.id.split('_')[1];
                updateCartItem(productId);
            }, 1000); // Auto-save after 1 second of no typing
        });
    });
});
</script>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>

