<?php
session_start();
require_once __DIR__ . '/../../../../backend/bootstrap.php';

// Phân trang
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

// Lọc sự kiện
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Xây dựng câu truy vấn
$where_conditions = [];
$params = [];

if ($status_filter !== 'all') {
    if ($status_filter === 'upcoming') {
        $where_conditions[] = "start_date >= CURDATE()";
    } elseif ($status_filter === 'ongoing') {
        $where_conditions[] = "start_date <= CURDATE() AND end_date >= CURDATE()";
    } elseif ($status_filter === 'completed') {
        $where_conditions[] = "end_date < CURDATE()";
    }
}

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ? OR location LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

try {
    // Đếm tổng số sự kiện
    $count_sql = "SELECT COUNT(*) as total FROM events $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_events = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_events / $per_page);

    // Lấy danh sách sự kiện
    $sql = "
        SELECT e.*,
               COUNT(er.id) as registration_count,
               SUM(er.quantity) as total_participants
        FROM events e
        LEFT JOIN event_registrations er ON e.id = er.event_id AND er.status != 'cancelled'
        $where_clause
        GROUP BY e.id
        ORDER BY e.start_date DESC
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Event list error: " . $e->getMessage());
    $events = [];
    $total_events = 0;
    $total_pages = 0;
}

// Debug: Show what we found
echo "<!-- DEBUG: Found " . count($events) . " events -->";
echo "<!-- DEBUG: Total events: " . $total_events . " -->";
if (count($events) > 0) {
    echo "<!-- DEBUG: First event: " . $events[0]['title'] . " -->";
}

$page_title = "Danh sách sự kiện thiện nguyện";
$extra_css = ['charity.css'];
include __DIR__ . '/../../../common/components/header.php';
?>

<div class="container my-5">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold text-primary mb-3">Sự kiện thiện nguyện</h1>
            <p class="lead">Tham gia cùng chúng tôi trong các hoạt động ý nghĩa để giúp đỡ cộng đồng</p>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-lg-8 col-md-6">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" 
                       placeholder="Tìm kiếm sự kiện..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <div class="col-lg-4 col-md-6">
            <form method="GET" onchange="this.submit()">
                <?php if (!empty($search)): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <?php endif; ?>
                <select name="status" class="form-select">
                    <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Tất cả sự kiện</option>
                    <option value="upcoming" <?= $status_filter === 'upcoming' ? 'selected' : '' ?>>Sắp diễn ra</option>
                    <option value="ongoing" <?= $status_filter === 'ongoing' ? 'selected' : '' ?>>Đang diễn ra</option>
                    <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Đã hoàn thành</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Events Grid -->
    <?php if (!empty($events)): ?>
        <div class="row">
            <?php foreach ($events as $event): ?>
                <?php
                $today = date('Y-m-d');
                $event_status = '';
                $status_class = '';
                
                if ($event['start_date'] > $today) {
                    $event_status = 'Sắp diễn ra';
                    $status_class = 'bg-info';
                } elseif ($event['start_date'] <= $today && $event['end_date'] >= $today) {
                    $event_status = 'Đang diễn ra';
                    $status_class = 'bg-success';
                } else {
                    $event_status = 'Đã kết thúc';
                    $status_class = 'bg-secondary';
                }
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 event-card">
                        <div class="position-relative">
                            <img src="../assets/images/<?= htmlspecialchars($event['image']) ?>" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;" 
                                 alt="<?= htmlspecialchars($event['title']) ?>">
                            <span class="badge <?= $status_class ?> position-absolute top-0 end-0 m-2">
                                <?= $event_status ?>
                            </span>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                            <p class="card-text"><?= substr(htmlspecialchars($event['description']), 0, 120) ?>...</p>
                            
                            <div class="event-info mt-auto">
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= formatDate($event['start_date']) ?>
                                        <?php if ($event['start_date'] !== $event['end_date']): ?>
                                            - <?= formatDate($event['end_date']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= htmlspecialchars($event['location']) ?>
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-success">
                                        <i class="fas fa-users me-1"></i>
                                        <?= number_format($event['total_participants'] ?? 0) ?> người tham gia
                                    </small>
                                </div>

                                <!-- Progress bar if max participants is set -->
                                <?php if ($event['max_participants'] > 0): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted">Đăng ký</small>
                                            <small class="text-muted">
                                                <?= $event['total_participants'] ?? 0 ?>/<?= $event['max_participants'] ?>
                                            </small>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <?php 
                                            $progress = $event['max_participants'] > 0 
                                                ? min(100, (($event['total_participants'] ?? 0) / $event['max_participants']) * 100)
                                                : 0;
                                            ?>
                                            <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-grid">
                                    <a href="event_detail.php?id=<?= $event['id'] ?>" class="btn btn-primary">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Event pagination" class="mt-5">
                <ul class="pagination justify-content-center">
                    <!-- Previous -->
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $status_filter !== 'all' ? '&status=' . $status_filter : '' ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Page numbers -->
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $status_filter !== 'all' ? '&status=' . $status_filter : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next -->
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $status_filter !== 'all' ? '&status=' . $status_filter : '' ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="text-center text-muted">
                Hiển thị <?= ($offset + 1) ?> - <?= min($offset + $per_page, $total_events) ?> 
                trong tổng số <?= number_format($total_events) ?> sự kiện
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- No events found -->
        <div class="row">
            <div class="col-12 text-center">
                <div class="alert alert-info py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h4>Không tìm thấy sự kiện nào</h4>
                    <?php if (!empty($search) || $status_filter !== 'all'): ?>
                        <p>Không có sự kiện nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                        <a href="event_list.php" class="btn btn-primary">Xem tất cả sự kiện</a>
                    <?php else: ?>
                        <p>Hiện tại chưa có sự kiện thiện nguyện nào. Vui lòng quay lại sau!</p>
                        <div class="mt-3">
                            <a href="donate.php" class="btn btn-success me-2">Quyên góp ngay</a>
                            <a href="../" class="btn btn-outline-primary">Về trang chủ</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Call to Action -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="bg-primary text-white rounded p-4 text-center">
                <h3 class="mb-3">Bạn muốn đóng góp cho cộng đồng?</h3>
                <p class="mb-3">Ngoài tham gia sự kiện, bạn cũng có thể ủng hộ tài chính cho các hoạt động thiện nguyện của chúng tôi.</p>
                <a href="donate.php" class="btn btn-light btn-lg">
                    <i class="fas fa-heart me-2"></i>Quyên góp ngay
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>

