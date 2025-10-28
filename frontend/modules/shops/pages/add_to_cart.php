<?php
session_start();
require_once __DIR__ . '/../../../../backend/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Thông tin sản phẩm không hợp lệ']);
    exit();
}

try {
    // Check if product exists and has sufficient stock
    $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit();
    }
    
    if ($product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho']);
        exit();
    }
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add to cart or update quantity
    if (isset($_SESSION['cart'][$product_id])) {
        $new_quantity = $_SESSION['cart'][$product_id] + $quantity;
        if ($new_quantity > $product['stock_quantity']) {
            echo json_encode(['success' => false, 'message' => 'Không thể thêm quá số lượng tồn kho']);
            exit();
        }
        $_SESSION['cart'][$product_id] = $new_quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    // Calculate total cart count
    $cart_count = array_sum($_SESSION['cart']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã thêm sản phẩm vào giỏ hàng',
        'cart_count' => $cart_count,
        'product_name' => $product['name']
    ]);

} catch (PDOException $e) {
    error_log("Add to cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại.']);
}
?>

