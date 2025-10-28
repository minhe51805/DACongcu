<?php
session_start();
require_once __DIR__ . '/../../../../backend/bootstrap.php';

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Get cart products
$cart_products = [];
$total_amount = 0;

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
    error_log("Cart processing error: " . $e->getMessage());
    $cart_products = [];
}

$page_title = 'Thanh toán';
include __DIR__ . '/../../../common/components/header.php';
?>

<link rel="stylesheet" href="../assets/css/shop.css">
<link rel="stylesheet" href="../assets/css/charity.css">

<style>
/* Checkout Page Styles */
.checkout-section {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
}

.checkout-section h4 {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f8f9fa;
}

.payment-methods {
    margin-top: 1rem;
}

.payment-option {
    margin-bottom: 1rem;
}

.payment-btn {
    padding: 1rem;
    height: auto;
    min-height: 80px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    transition: all 0.3s ease;
    border: 2px solid #dee2e6;
}

.payment-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.btn-check:checked + .payment-btn {
    border-color: var(--bs-primary);
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}

.payment-info {
    border-radius: 10px;
    border: 1px solid #dee2e6;
    background: #f8f9fa;
    padding: 1rem;
}

.order-summary {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    position: sticky;
    top: 2rem;
}

.order-item {
    padding: 1rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.btn-checkout {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border: none;
    border-radius: 15px;
    padding: 1rem 2rem;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-checkout:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,123,255,0.3);
}

.btn-checkout:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-checkout:hover:before {
    left: 100%;
}

/* Responsive fixes */
@media (max-width: 768px) {
    .checkout-section {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .order-summary {
        margin-top: 2rem;
        position: static;
    }

    .payment-btn {
        min-height: 70px;
        padding: 0.75rem;
    }
}

/* Container fixes */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

/* Prevent horizontal overflow */
body {
    overflow-x: hidden;
}

.row {
    margin-left: -15px;
    margin-right: -15px;
}

.col-lg-8, .col-lg-4 {
    padding-left: 15px;
    padding-right: 15px;
}
</style>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="cart.php">Giỏ hàng</a></li>
                    <li class="breadcrumb-item active">Thanh toán</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Order Form -->
        <div class="col-lg-8">
            <form id="checkoutForm" method="POST" action="process_order.php">
                <!-- Customer Information -->
                <div class="checkout-section">
                    <h4><i class="fas fa-user me-2"></i>Thông tin khách hàng</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Họ và tên *</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Số điện thoại *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Information -->
                <div class="checkout-section">
                    <h4><i class="fas fa-truck me-2"></i>Thông tin giao hàng</h4>
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">Địa chỉ giao hàng *</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" 
                                        placeholder="Nhập địa chỉ chi tiết..." required></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="city" class="form-label">Tỉnh/Thành phố *</label>
                                <select class="form-select" id="city" name="city" required>
                                    <option value="">Chọn tỉnh/thành phố</option>
                                    <option value="hanoi">Hà Nội</option>
                                    <option value="hcm">TP. Hồ Chí Minh</option>
                                    <option value="danang">Đà Nẵng</option>
                                    <option value="haiphong">Hải Phòng</option>
                                    <option value="cantho">Cần Thơ</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="district" class="form-label">Quận/Huyện *</label>
                                <select class="form-select" id="district" name="district" required>
                                    <option value="">Chọn quận/huyện</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="ward" class="form-label">Phường/Xã</label>
                                <select class="form-select" id="ward" name="ward">
                                    <option value="">Chọn phường/xã</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú đơn hàng</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" 
                                placeholder="Ghi chú về đơn hàng, thời gian giao hàng..."></textarea>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="checkout-section">
                    <h4><i class="fas fa-credit-card me-2"></i>Phương thức thanh toán</h4>
                    <div class="payment-methods">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="payment-option">
                                    <input type="radio" class="btn-check" name="payment_method" value="cod" id="payment_cod" checked>
                                    <label class="btn btn-outline-success payment-btn w-100" for="payment_cod">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fas fa-money-bill-wave me-2"></i>
                                            <span>Thanh toán khi nhận hàng</span>
                                        </div>
                                        <small class="d-block text-muted mt-1">Thanh toán bằng tiền mặt khi nhận hàng</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="payment-option">
                                    <input type="radio" class="btn-check" name="payment_method" value="momo" id="payment_momo">
                                    <label class="btn btn-outline-danger payment-btn w-100" for="payment_momo">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fas fa-mobile-alt me-2"></i>
                                            <span>Ví MoMo</span>
                                        </div>
                                        <small class="d-block text-muted mt-1">Thanh toán qua ví điện tử MoMo</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="payment-option">
                                    <input type="radio" class="btn-check" name="payment_method" value="vnpay" id="payment_vnpay">
                                    <label class="btn btn-outline-warning payment-btn w-100" for="payment_vnpay">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fas fa-credit-card me-2"></i>
                                            <span>VNPay</span>
                                        </div>
                                        <small class="d-block text-muted mt-1">Thanh toán qua VNPay</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="payment-option">
                                    <input type="radio" class="btn-check" name="payment_method" value="bank" id="payment_bank">
                                    <label class="btn btn-outline-info payment-btn w-100" for="payment_bank">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fas fa-university me-2"></i>
                                            <span>Chuyển khoản ngân hàng</span>
                                        </div>
                                        <small class="d-block text-muted mt-1">Chuyển khoản trước khi giao hàng</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Details -->
                    <div id="payment_cod_info" class="payment-info mt-3" style="display: block;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Bạn sẽ thanh toán bằng tiền mặt khi nhận hàng. Vui lòng chuẩn bị đủ tiền theo đúng số tiền của đơn hàng.
                        </div>
                    </div>

                    <div id="payment_bank_info" class="payment-info mt-3" style="display: none;">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-university me-2"></i>Thông tin chuyển khoản:</h6>
                            <p class="mb-2"><strong>Ngân hàng:</strong> Vietcombank</p>
                            <p class="mb-2"><strong>Số tài khoản:</strong> 1234567890</p>
                            <p class="mb-2"><strong>Chủ tài khoản:</strong> CONVOI SHOP</p>
                            <p class="mb-0"><strong>Nội dung:</strong> [Mã đơn hàng] [Số điện thoại]</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-primary btn-lg btn-checkout">
                        <span class="spinner-border spinner-border-sm d-none me-2"></span>
                        <span class="btn-text">Đặt hàng</span>
                        <span class="ms-2">(<?= number_format($total_amount) ?>đ)</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="order-summary">
                <h5 class="mb-3"><i class="fas fa-receipt me-2"></i>Đơn hàng của bạn</h5>
                
                <!-- Cart Items -->
                <?php foreach ($cart_products as $item): ?>
                <div class="order-item">
                    <div class="d-flex align-items-center">
                        <?php
                        $image_src = '../assets/images/default-product.jpg';
                        if (!empty($item['product']['image'])) {
                            if (filter_var($item['product']['image'], FILTER_VALIDATE_URL)) {
                                $image_src = $item['product']['image'];
                            } else {
                                if (file_exists('../assets/images/' . ltrim($item['product']['image'], '/'))) {
                                    $image_src = '../assets/images/' . ltrim($item['product']['image'], '/');
                                } elseif (file_exists('../uploads/' . ltrim($item['product']['image'], '/'))) {
                                    $image_src = '../uploads/' . ltrim($item['product']['image'], '/');
                                }
                            }
                        }
                        ?>
                        <img src="<?= $image_src ?>"
                             class="order-item-image me-3" alt="<?= htmlspecialchars($item['product']['name']) ?>"
                             onerror="this.src='../assets/images/default-product.jpg'"
                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?= htmlspecialchars($item['product']['name']) ?></h6>
                            <small class="text-muted">Số lượng: <?= $item['quantity'] ?></small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold"><?= number_format($item['subtotal']) ?>đ</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <hr>

                <!-- Order Totals -->
                <div class="d-flex justify-content-between mb-2">
                    <span>Tạm tính:</span>
                    <span><?= number_format($total_amount) ?>đ</span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Phí vận chuyển:</span>
                    <span class="text-success">Miễn phí</span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Thuế VAT:</span>
                    <span>0đ</span>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between">
                    <strong>Tổng cộng:</strong>
                    <strong class="text-primary"><?= number_format($total_amount) ?>đ</strong>
                </div>

                <!-- Security Badge -->
                <div class="mt-4 text-center">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Thanh toán an toàn & bảo mật
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // City/District/Ward data
    const locationData = {
        'hanoi': {
            name: 'Hà Nội',
            districts: {
                'ba_dinh': { name: 'Ba Đình', wards: ['Phúc Xá', 'Trúc Bạch', 'Vĩnh Phúc', 'Cống Vị', 'Liễu Giai'] },
                'hoan_kiem': { name: 'Hoàn Kiếm', wards: ['Phúc Tấn', 'Đồng Xuân', 'Hàng Mã', 'Hàng Buồm', 'Hàng Đào'] },
                'dong_da': { name: 'Đống Đa', wards: ['Cát Linh', 'Văn Miếu', 'Quốc Tử Giám', 'Láng Thượng', 'Ô Chợ Dừa'] },
                'hai_ba_trung': { name: 'Hai Bà Trưng', wards: ['Nguyễn Du', 'Bạch Đằng', 'Phạm Đình Hổ', 'Lê Đại Hành', 'Đống Mác'] }
            }
        },
        'hcm': {
            name: 'TP. Hồ Chí Minh',
            districts: {
                'quan_1': { name: 'Quận 1', wards: ['Tân Định', 'Đa Kao', 'Bến Nghé', 'Bến Thành', 'Nguyễn Thái Bình'] },
                'quan_3': { name: 'Quận 3', wards: ['Võ Thị Sáu', 'Đa Kao', 'Nguyễn Thái Bình', 'Phạm Ngũ Lão', 'Nguyễn Cư Trinh'] },
                'quan_7': { name: 'Quận 7', wards: ['Tân Thuận Đông', 'Tân Thuận Tây', 'Tân Kiểng', 'Tân Hưng', 'Bình Thuận'] },
                'thu_duc': { name: 'Thủ Đức', wards: ['Linh Xuân', 'Bình Chiểu', 'Linh Trung', 'Tam Bình', 'Tam Phú'] }
            }
        },
        'danang': {
            name: 'Đà Nẵng',
            districts: {
                'hai_chau': { name: 'Hải Châu', wards: ['Thạch Thang', 'Hải Châu I', 'Hải Châu II', 'Phước Ninh', 'Hòa Thuận Tây'] },
                'thanh_khe': { name: 'Thanh Khê', wards: ['Tam Thuận', 'Thanh Khê Tây', 'Thanh Khê Đông', 'Xuân Hà', 'Tân Chính'] },
                'son_tra': { name: 'Sơn Trà', wards: ['Thọ Quang', 'Nại Hiên Đông', 'Mân Thái', 'An Hải Bắc', 'An Hải Đông'] }
            }
        }
    };

    // Handle city selection
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    const wardSelect = document.getElementById('ward');

    citySelect.addEventListener('change', function() {
        const selectedCity = this.value;

        // Clear district and ward options
        districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
        wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';

        if (selectedCity && locationData[selectedCity]) {
            const districts = locationData[selectedCity].districts;

            Object.keys(districts).forEach(districtKey => {
                const option = document.createElement('option');
                option.value = districtKey;
                option.textContent = districts[districtKey].name;
                districtSelect.appendChild(option);
            });
        }
    });

    // Handle district selection
    districtSelect.addEventListener('change', function() {
        const selectedCity = citySelect.value;
        const selectedDistrict = this.value;

        // Clear ward options
        wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';

        if (selectedCity && selectedDistrict && locationData[selectedCity] && locationData[selectedCity].districts[selectedDistrict]) {
            const wards = locationData[selectedCity].districts[selectedDistrict].wards;

            wards.forEach(ward => {
                const option = document.createElement('option');
                option.value = ward;
                option.textContent = ward;
                wardSelect.appendChild(option);
            });
        }
    });

    // Handle payment method selection
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const paymentInfos = document.querySelectorAll('.payment-info');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Hide all payment info sections
            paymentInfos.forEach(info => {
                info.style.display = 'none';
            });
            
            // Show selected payment method info
            const selectedInfo = document.getElementById(`payment_${this.value}_info`);
            if (selectedInfo) {
                selectedInfo.style.display = 'block';
            }
            
            // Update submit button text
            updateSubmitButton();
        });
    });
    
    // Update submit button text based on payment method
    function updateSubmitButton() {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        const btnText = document.querySelector('.btn-text');
        
        if (selectedPayment) {
            switch (selectedPayment.value) {
                case 'cod':
                    btnText.textContent = 'Đặt hàng - COD';
                    break;
                case 'momo':
                    btnText.textContent = 'Thanh toán MoMo';
                    break;
                case 'vnpay':
                    btnText.textContent = 'Thanh toán VNPay';
                    break;
                case 'bank':
                    btnText.textContent = 'Đặt hàng - Chuyển khoản';
                    break;
                default:
                    btnText.textContent = 'Đặt hàng';
            }
        }
    }
    
    // Form validation and submission
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        
        // Validate required fields
        const requiredFields = ['customer_name', 'phone', 'email', 'shipping_address', 'city', 'district'];
        let isValid = true;
        
        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showAlert('Vui lòng điền đầy đủ thông tin bắt buộc.', 'warning');
            return;
        }
        
        // Validate email format
        const emailField = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value)) {
            e.preventDefault();
            emailField.classList.add('is-invalid');
            showAlert('Vui lòng nhập địa chỉ email hợp lệ.', 'warning');
            return;
        }
        
        // Validate phone format
        const phoneField = document.getElementById('phone');
        const phoneRegex = /^[0-9]{10,11}$/;
        if (!phoneRegex.test(phoneField.value.replace(/\s/g, ''))) {
            e.preventDefault();
            phoneField.classList.add('is-invalid');
            showAlert('Vui lòng nhập số điện thoại hợp lệ (10-11 số).', 'warning');
            return;
        }
        
        // Show loading state
        const submitBtn = document.querySelector('.btn-checkout');
        const spinner = submitBtn.querySelector('.spinner-border');
        const btnText = submitBtn.querySelector('.btn-text');
        
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Đang xử lý...';
        
        // For bank transfer, handle offline processing
        if (selectedPayment.value === 'bank') {
            e.preventDefault();
            handleOfflineOrder();
            
            // Reset button
            setTimeout(() => {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                updateSubmitButton();
            }, 1000);
        }
    });
    
    // Handle offline order (bank transfer)
    function handleOfflineOrder() {
        const formData = new FormData(document.getElementById('checkoutForm'));
        
        fetch('process_order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPaymentInstructions(data);
            } else {
                showAlert(data.message || 'Có lỗi xảy ra', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Có lỗi xảy ra trong quá trình xử lý đơn hàng', 'danger');
        });
    }
    
    // Show payment instructions modal
    function showPaymentInstructions(data) {
        const modalHtml = `
            <div class="modal fade" id="paymentModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-university me-2"></i>Thông tin chuyển khoản
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Đơn hàng <strong>#${data.order_id}</strong> đã được tạo thành công!
                            </div>
                            
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Thông tin chuyển khoản:</h6>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Ngân hàng:</strong></div>
                                        <div class="col-sm-8">Vietcombank</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Số tài khoản:</strong></div>
                                        <div class="col-sm-8">1234567890</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Chủ tài khoản:</strong></div>
                                        <div class="col-sm-8">CONVOI SHOP</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Số tiền:</strong></div>
                                        <div class="col-sm-8 text-danger fw-bold">${data.amount}đ</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Nội dung:</strong></div>
                                        <div class="col-sm-8"><code>${data.order_id} ${data.phone}</code></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Lưu ý:</strong> Vui lòng chuyển khoản đúng nội dung để đơn hàng được xử lý nhanh chóng.
                                Chúng tôi sẽ liên hệ xác nhận sau khi nhận được thanh toán.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <a href="index.php" class="btn btn-primary">Về trang chủ</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal
        const existingModal = document.getElementById('paymentModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add new modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    }
    
    // Alert function
    function showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert:last-of-type');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
});
</script>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>

