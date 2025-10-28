<?php
session_start();
require_once __DIR__ . '/../../../../backend/bootstrap.php';
include __DIR__ . '/../../../common/components/header.php';

// Get parameters
$category_id = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'name';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = intval($_GET['per_page'] ?? 12);
$min_price = intval($_GET['min_price'] ?? 0);
$max_price = intval($_GET['max_price'] ?? 0);

// Build WHERE clause
$where_conditions = ["p.status = 'active'"];
$params = [];

if ($category_id) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
}

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($min_price > 0) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $min_price;
}

if ($max_price > 0) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $max_price;
}

$where_clause = implode(' AND ', $where_conditions);

// Determine sort order
$order_clause = match($sort) {
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'newest' => 'p.created_at DESC',
    'oldest' => 'p.created_at ASC',
    default => 'p.name ASC'
};

try {
    // Get total count
    $count_sql = "
        SELECT COUNT(*) as total
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where_clause
    ";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_products = $stmt->fetch()['total'];
    
    // Calculate pagination
    $total_pages = ceil($total_products / $per_page);
    $offset = ($page - 1) * $per_page;
    
    // Get products
    $sql = "
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where_clause
        ORDER BY $order_clause
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Get all categories for filter
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
    
    // Get price range
    $stmt = $pdo->prepare("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products WHERE status = 'active'");
    $stmt->execute();
    $price_range = $stmt->fetch();

} catch (PDOException $e) {
    $products = [];
    $categories = [];
    $total_products = 0;
    $total_pages = 0;
    $price_range = ['min_price' => 0, 'max_price' => 0];
}
?>

<link rel="stylesheet" href="../assets/css/shop.css">

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="index.php">Cửa hàng</a></li>
            <li class="breadcrumb-item active">Sản phẩm</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold">
                <?php if ($category_id): ?>
                    <?php
                    $current_category = array_filter($categories, fn($c) => $c['id'] == $category_id);
                    echo $current_category ? reset($current_category)['name'] : 'Sản phẩm';
                    ?>
                <?php elseif ($search): ?>
                    Kết quả tìm kiếm: "<?= htmlspecialchars($search) ?>"
                <?php else: ?>
                    Tất cả sản phẩm
                <?php endif; ?>
            </h1>
            <p class="text-muted">Tìm thấy <?= $total_products ?> sản phẩm</p>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="filter-sidebar">
                <h4 class="mb-3"><i class="fas fa-filter me-2"></i>Bộ lọc</h4>
                
                <form method="GET" id="filterForm">
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>
                    
                    <!-- Category Filter -->
                    <div class="filter-section">
                        <h6>Danh mục</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category" value="" id="cat_all" 
                                   <?= !$category_id ? 'checked' : '' ?>>
                            <label class="form-check-label" for="cat_all">
                                Tất cả danh mục
                            </label>
                        </div>
                        <?php foreach ($categories as $category): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category" 
                                   value="<?= $category['id'] ?>" id="cat_<?= $category['id'] ?>"
                                   <?= $category_id == $category['id'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="cat_<?= $category['id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                                <small class="text-muted">(<?= $category['product_count'] ?>)</small>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Price Filter -->
                    <div class="filter-section">
                        <h6>Khoảng giá</h6>
                        <div class="price-range-container">
                            <div class="price-display">
                                <span id="price-display">
                                    <?= number_format($min_price ?: $price_range['min_price']) ?>đ - 
                                    <?= number_format($max_price ?: $price_range['max_price']) ?>đ
                                </span>
                            </div>
                            <div class="mb-3">
                                <label for="min_price" class="form-label">Giá từ:</label>
                                <input type="number" class="form-control" name="min_price" id="min_price" 
                                       value="<?= $min_price ?>" min="0" step="1000">
                            </div>
                            <div class="mb-3">
                                <label for="max_price" class="form-label">Giá đến:</label>
                                <input type="number" class="form-control" name="max_price" id="max_price" 
                                       value="<?= $max_price ?>" min="0" step="1000">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Áp dụng
                        </button>
                        <a href="product_list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Xóa bộ lọc
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products -->
        <div class="col-lg-9">
            <!-- Sort and View Options -->
            <div class="row mb-3 align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <label for="sort" class="form-label me-2 mb-0">Sắp xếp:</label>
                        <select class="form-select sort-dropdown" id="sort" name="sort" onchange="updateSort()">
                            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Tên A-Z</option>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option>
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center justify-content-md-end">
                        <label for="per_page" class="form-label me-2 mb-0">Hiển thị:</label>
                        <select class="form-select" id="per_page" name="per_page" onchange="updatePerPage()" style="width: auto;">
                            <option value="12" <?= $per_page === 12 ? 'selected' : '' ?>>12</option>
                            <option value="24" <?= $per_page === 24 ? 'selected' : '' ?>>24</option>
                            <option value="48" <?= $per_page === 48 ? 'selected' : '' ?>>48</option>
                        </select>
                        <span class="ms-2 text-muted">sản phẩm</span>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <?php if (!empty($products)): ?>
            <div class="row product-grid">
                <?php foreach ($products as $product): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card product-card h-100 border-0 shadow-sm">
                        <div class="position-relative">
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
                                 class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>"
                                 onerror="this.src='../assets/images/default-product.jpg'"
                                 style="height: 250px; object-fit: cover;">
                            <?php if ($product['featured']): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">Nổi bật</span>
                            <?php endif; ?>
                            <?php if ($product['stock_quantity'] <= 0): ?>
                            <span class="badge bg-secondary position-absolute top-0 end-0 m-2">Hết hàng</span>
                            <?php elseif ($product['stock_quantity'] <= 5): ?>
                            <span class="badge bg-warning position-absolute top-0 end-0 m-2">Sắp hết</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="product_detail.php?id=<?= $product['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small">
                                <?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...
                            </p>
                            <p class="text-primary fw-bold fs-5 mb-2">
                                <?= number_format($product['price']) ?>đ
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?= htmlspecialchars($product['category_name']) ?>
                                </small>
                                <small class="text-muted">
                                    Còn: <?= $product['stock_quantity'] ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex gap-2">
                                <a href="product_detail.php?id=<?= $product['id'] ?>" 
                                   class="btn btn-outline-primary flex-fill">
                                    Chi tiết
                                </a>
                                <?php if ($product['stock_quantity'] > 0): ?>
                                <button class="btn btn-primary flex-fill add-to-cart-btn" 
                                        data-product-id="<?= $product['id'] ?>"
                                        data-product-name="<?= htmlspecialchars($product['name']) ?>">
                                    <i class="fas fa-cart-plus me-1"></i>Thêm vào giỏ
                                </button>
                                <?php else: ?>
                                <button class="btn btn-secondary flex-fill" disabled>
                                    Hết hàng
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Product pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="fas fa-chevron-left"></i> Trước
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    ?>
                    
                    <?php if ($start_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                    </li>
                    <?php if ($start_page > 2): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>"><?= $total_pages ?></a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            Sau <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <!-- No Products Found -->
            <div class="text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h3>Không tìm thấy sản phẩm</h3>
                <p class="text-muted">Vui lòng thử lại với từ khóa khác hoặc bỏ bớt bộ lọc.</p>
                <a href="product_list.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Xem tất cả sản phẩm
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add to Cart Modal -->
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
function updateSort() {
    const sort = document.getElementById('sort').value;
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('sort', sort);
    urlParams.delete('page'); // Reset to first page
    window.location.search = urlParams.toString();
}

function updatePerPage() {
    const perPage = document.getElementById('per_page').value;
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('per_page', perPage);
    urlParams.delete('page'); // Reset to first page
    window.location.search = urlParams.toString();
}


</script>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>

