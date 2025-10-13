<?php
require_once 'includes/config.php';

// Get comprehensive statistics
try {
    // Events statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_events,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_events,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_events
        FROM events
    ");
    $event_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Donations statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_donations,
            SUM(amount) as total_amount,
            AVG(amount) as avg_amount,
            COUNT(CASE WHEN payment_status = 'completed' THEN 1 END) as completed_donations
        FROM donations
    ");
    $donation_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Products statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_products,
            SUM(stock) as total_stock,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_products
        FROM products
    ");
    $product_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Orders statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_orders,
            SUM(total_amount) as total_revenue,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders
        FROM orders
    ");
    $order_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Recent activities
    $stmt = $pdo->query("
        SELECT 'donation' as type, donor_name as name, amount, created_at, 'Quyên góp' as action
        FROM donations 
        WHERE payment_status = 'completed'
        UNION ALL
        SELECT 'order' as type, customer_name as name, total_amount as amount, created_at, 'Mua hàng' as action
        FROM orders 
        WHERE status = 'completed'
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $event_stats = $donation_stats = $product_stats = $order_stats = [];
    $recent_activities = [];
}

$page_title = "Dashboard - XAYDUNGTUONGLAI WAG";
include 'includes/header.php';
?>

<!-- VinTech Dashboard -->
<div class="vintech-dashboard">
    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="dashboard-title">
                        <i class="fas fa-chart-line me-3"></i>
                        Dashboard VinTech
                    </h1>
                    <p class="dashboard-subtitle">Tổng quan hoạt động nền tảng CONVOI</p>
                </div>
                <div class="col-lg-6 text-end">
                    <div class="dashboard-actions">
                        <button class="vintech-btn-enhanced vintech-btn-outline-enhanced" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Làm mới
                        </button>
                        <button class="vintech-btn-enhanced vintech-btn-primary-enhanced" data-vintech-modal="export-modal">
                            <i class="fas fa-download"></i> Xuất báo cáo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-lg-6">
                <div class="vintech-stat-card-dashboard stat-primary" data-animate="fadeInUp">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" data-counter="<?= $event_stats['total_events'] ?? 0 ?>">0</div>
                        <div class="stat-label">Tổng sự kiện</div>
                        <div class="stat-sublabel"><?= $event_stats['active_events'] ?? 0 ?> đang hoạt động</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i> +12%
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6">
                <div class="vintech-stat-card-dashboard stat-success" data-animate="fadeInUp">
                    <div class="stat-icon">
                        <i class="fas fa-donate"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" data-counter="<?= $donation_stats['total_amount'] ?? 0 ?>">0</div>
                        <div class="stat-label">Tổng quyên góp (VND)</div>
                        <div class="stat-sublabel"><?= $donation_stats['total_donations'] ?? 0 ?> lượt quyên góp</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i> +25%
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6">
                <div class="vintech-stat-card-dashboard stat-warning" data-animate="fadeInUp">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" data-counter="<?= $order_stats['total_revenue'] ?? 0 ?>">0</div>
                        <div class="stat-label">Doanh thu (VND)</div>
                        <div class="stat-sublabel"><?= $order_stats['total_orders'] ?? 0 ?> đơn hàng</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i> +18%
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6">
                <div class="vintech-stat-card-dashboard stat-info" data-animate="fadeInUp">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" data-counter="<?= $product_stats['total_products'] ?? 0 ?>">0</div>
                        <div class="stat-label">Sản phẩm</div>
                        <div class="stat-sublabel"><?= $product_stats['active_products'] ?? 0 ?> đang bán</div>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i> +8%
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Activities -->
        <div class="row g-4">
            <!-- Chart Section -->
            <div class="col-lg-8">
                <div class="vintech-card-modern" data-animate="fadeInLeft">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-chart-area me-2"></i>
                            Biểu đồ hoạt động
                        </h5>
                        <div class="card-actions">
                            <button class="vintech-btn-enhanced vintech-btn-outline-enhanced btn-sm">
                                <i class="fas fa-calendar"></i> Tháng này
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="activityChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="col-lg-4">
                <div class="vintech-card-modern" data-animate="fadeInRight">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-clock me-2"></i>
                            Hoạt động gần đây
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-list">
                            <?php foreach ($recent_activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon <?= $activity['type'] ?>">
                                    <i class="fas fa-<?= $activity['type'] == 'donation' ? 'heart' : 'shopping-cart' ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title"><?= htmlspecialchars($activity['name']) ?></div>
                                    <div class="activity-desc"><?= $activity['action'] ?> - <?= number_format($activity['amount']) ?> VND</div>
                                    <div class="activity-time"><?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-4 mt-4">
            <div class="col-12">
                <div class="vintech-card-modern" data-animate="fadeInUp">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-bolt me-2"></i>
                            Thao tác nhanh
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="charity/admin.php" class="quick-action-item">
                                <div class="action-icon">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div class="action-title">Tạo sự kiện mới</div>
                                <div class="action-desc">Thêm sự kiện thiện nguyện</div>
                            </a>
                            
                            <a href="shop/admin.php" class="quick-action-item">
                                <div class="action-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div class="action-title">Thêm sản phẩm</div>
                                <div class="action-desc">Quản lý cửa hàng</div>
                            </a>
                            
                            <a href="charity/donate.php" class="quick-action-item">
                                <div class="action-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="action-title">Quyên góp</div>
                                <div class="action-desc">Ủng hộ thiện nguyện</div>
                            </a>
                            
                            <a href="#" class="quick-action-item" data-vintech-modal="settings-modal">
                                <div class="action-icon">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="action-title">Cài đặt</div>
                                <div class="action-desc">Cấu hình hệ thống</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="export-modal" class="vintech-modal">
    <div class="vintech-modal-content">
        <h4>Xuất báo cáo</h4>
        <p>Chọn loại báo cáo bạn muốn xuất:</p>
        <div class="export-options">
            <button class="vintech-btn-enhanced vintech-btn-outline-enhanced w-100 mb-2">
                <i class="fas fa-file-excel"></i> Báo cáo Excel
            </button>
            <button class="vintech-btn-enhanced vintech-btn-outline-enhanced w-100 mb-2">
                <i class="fas fa-file-pdf"></i> Báo cáo PDF
            </button>
            <button class="vintech-btn-enhanced vintech-btn-outline-enhanced w-100">
                <i class="fas fa-chart-bar"></i> Báo cáo tùy chỉnh
            </button>
        </div>
        <div class="modal-actions mt-3">
            <button class="vintech-btn-enhanced vintech-btn-outline-enhanced" data-modal-close>Hủy</button>
            <button class="vintech-btn-enhanced vintech-btn-primary-enhanced">Xuất báo cáo</button>
        </div>
    </div>
</div>

<!-- Enhanced CSS for Dashboard -->
<style>
.vintech-dashboard {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
    padding: 6rem 0;
}

.dashboard-header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--vintech-shadow-lg);
}

.dashboard-title {
    color: var(--vintech-primary);
    font-weight: 700;
    margin: 0;
}

.dashboard-subtitle {
    color: #6c757d;
    margin: 0;
}

.vintech-stat-card-dashboard {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: var(--vintech-shadow-md);
    transition: all var(--vintech-transition-base);
    position: relative;
    overflow: hidden;
}

.vintech-stat-card-dashboard:hover {
    transform: translateY(-5px);
    box-shadow: var(--vintech-shadow-xl);
}

.stat-trend {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--vintech-success);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.chart-container {
    height: 300px;
    position: relative;
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.activity-icon.donation {
    background: var(--vintech-success);
    color: white;
}

.activity-icon.order {
    background: var(--vintech-warning);
    color: white;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.quick-action-item {
    background: rgba(255, 255, 255, 0.8);
    border-radius: 0.75rem;
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
    transition: all var(--vintech-transition-base);
    border: 2px solid transparent;
}

.quick-action-item:hover {
    background: rgba(255, 255, 255, 1);
    border-color: var(--vintech-primary);
    transform: translateY(-3px);
    color: inherit;
}

.action-icon {
    width: 50px;
    height: 50px;
    background: var(--vintech-gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.action-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.action-desc {
    color: #6c757d;
    font-size: 0.9rem;
}
</style>

<!-- VinTech Enhanced JavaScript -->
<script src="assets/js/vintech-framework.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize chart (placeholder)
    const ctx = document.getElementById('activityChart');
    if (ctx) {
        // This would integrate with Chart.js or similar library
        ctx.style.background = 'linear-gradient(45deg, #e3f2fd, #f3e5f5)';
        ctx.style.borderRadius = '8px';
        
        // Add placeholder text
        const placeholder = document.createElement('div');
        placeholder.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666;"><i class="fas fa-chart-area me-2"></i>Biểu đồ sẽ được hiển thị tại đây</div>';
        ctx.parentNode.appendChild(placeholder);
        ctx.style.display = 'none';
    }
    
    // Add notification for demo
    setTimeout(() => {
        if (window.VinTech) {
            VinTech.showToast('Dashboard đã được tải thành công!', 'success');
        }
    }, 1000);
});
</script>

<?php include 'includes/footer.php'; ?>
