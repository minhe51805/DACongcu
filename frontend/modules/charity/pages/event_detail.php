<?php
session_start();
require_once __DIR__ . '/../../../../backend/bootstrap.php';

// Kiểm tra ID sự kiện
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: event_list.php');
    exit;
}

$event_id = (int)$_GET['id'];

try {
    // Lấy thông tin sự kiện
    $stmt = $pdo->prepare("
        SELECT e.*, 
               COUNT(er.id) as registration_count,
               SUM(er.quantity) as total_participants
        FROM events e
        LEFT JOIN event_registrations er ON e.id = er.event_id AND er.status != 'cancelled'
        WHERE e.id = ?
        GROUP BY e.id
    ");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        header('Location: event_list.php');
        exit;
    }

    // Lấy danh sách người đăng ký gần đây (chỉ hiển thị tên)
    $stmt = $pdo->prepare("
        SELECT name, quantity, registered_at
        FROM event_registrations
        WHERE event_id = ? AND status != 'cancelled'
        ORDER BY registered_at DESC
        LIMIT 10
    ");
    $stmt->execute([$event_id]);
    $recent_registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    header('Location: event_list.php');
    exit;
}

// Xác định trạng thái sự kiện
$today = date('Y-m-d');
$event_status = '';
$can_register = false;

if ($event['start_date'] > $today) {
    $event_status = 'Sắp diễn ra';
    $can_register = true;
} elseif ($event['start_date'] <= $today && $event['end_date'] >= $today) {
    $event_status = 'Đang diễn ra';
    $can_register = true;
} else {
    $event_status = 'Đã kết thúc';
    $can_register = false;
}

// Kiểm tra xem đã đầy chưa
$is_full = false;
if ($event['max_participants'] > 0 && ($event['total_participants'] ?? 0) >= $event['max_participants']) {
    $is_full = true;
    $can_register = false;
}

$page_title = htmlspecialchars($event['title']);
$extra_css = ['charity.css'];
include __DIR__ . '/../../../common/components/header.php';
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="index.php">Thiện nguyện</a></li>
            <li class="breadcrumb-item"><a href="event_list.php">Danh sách sự kiện</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($event['title']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Event Image -->
            <div class="mb-4">
                <img src="../assets/images/<?= htmlspecialchars($event['image']) ?>" 
                     class="img-fluid event-detail-image w-100" 
                     style="height: 400px; object-fit: cover;" 
                     alt="<?= htmlspecialchars($event['title']) ?>">
            </div>

            <!-- Event Info -->
            <div class="event-meta">
                <div class="row">
                    <div class="col-md-6">
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div>
                                <strong>Thời gian</strong><br>
                                <span class="text-muted">
                                    <?= formatDate($event['start_date']) ?>
                                    <?php if ($event['start_date'] !== $event['end_date']): ?>
                                        - <?= formatDate($event['end_date']) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <strong>Địa điểm</strong><br>
                                <span class="text-muted"><?= htmlspecialchars($event['location']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <strong>Người tham gia</strong><br>
                                <span class="text-muted">
                                    <?= number_format($event['total_participants'] ?? 0) ?> người
                                    <?php if ($event['max_participants'] > 0): ?>
                                        / <?= number_format($event['max_participants']) ?> người
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div>
                                <strong>Trạng thái</strong><br>
                                <span class="badge <?= $event_status === 'Sắp diễn ra' ? 'bg-info' : 
                                    ($event_status === 'Đang diễn ra' ? 'bg-success' : 'bg-secondary') ?>">
                                    <?= $event_status ?>
                                </span>
                                <?php if ($is_full): ?>
                                    <span class="badge bg-warning ms-1">Đã đủ người</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Description -->
            <div class="mb-4">
                <h2 class="h3 mb-3">Mô tả chi tiết</h2>
                <div class="text-justify">
                    <?= nl2br(htmlspecialchars($event['description'])) ?>
                </div>
            </div>

            <!-- Recent Registrations -->
            <?php if (!empty($recent_registrations)): ?>
                <div class="mb-4">
                    <h3 class="h4 mb-3">Người đã đăng ký gần đây</h3>
                    <div class="row">
                        <?php foreach ($recent_registrations as $reg): ?>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($reg['name']) ?></strong>
                                        <small class="d-block text-muted">
                                            <?= $reg['quantity'] ?> người - <?= formatDateTime($reg['registered_at']) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Registration Form -->
            <?php if ($can_register): ?>
                <div class="registration-form sticky-top">
                    <h3 class="h4 mb-4">Đăng ký tham gia</h3>
                    
                    <?php if (isset($_SESSION['registration_success'])): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($_SESSION['registration_success']) ?>
                        </div>
                        <?php unset($_SESSION['registration_success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['registration_error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['registration_error']) ?>
                        </div>
                        <?php unset($_SESSION['registration_error']); ?>
                    <?php endif; ?>

                    <form action="process_register.php" method="POST" id="registrationForm">
                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                        
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Họ và tên *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone" class="form-label">Số điện thoại *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="quantity" class="form-label">Số người tham gia *</label>
                            <select class="form-select" id="quantity" name="quantity" required>
                                <option value="">Chọn số người</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?> người</option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label for="message" class="form-label">Ghi chú (tuỳ chọn)</label>
                            <textarea class="form-control" id="message" name="message" rows="3" 
                                    placeholder="Chia sẻ lý do bạn muốn tham gia..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Đăng ký tham gia
                            </button>
                        </div>
                    </form>

                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            Bằng việc đăng ký, bạn đồng ý với các điều khoản và điều kiện của chúng tôi.
                        </small>
                    </div>
                </div>
            <?php else: ?>
                <div class="registration-form">
                    <h3 class="h4 mb-4">Thông tin đăng ký</h3>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php if ($is_full): ?>
                            Sự kiện này đã đủ số lượng người tham gia.
                        <?php else: ?>
                            Sự kiện này đã kết thúc hoặc không còn nhận đăng ký.
                        <?php endif; ?>
                    </div>
                    <div class="text-center">
                        <a href="event_list.php" class="btn btn-outline-primary">
                            Xem sự kiện khác
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Share Event -->
            <div class="mt-4 p-3 bg-light rounded">
                <h5 class="mb-3">Chia sẻ sự kiện</h5>
                <div class="d-flex gap-2">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                       target="_blank" class="btn btn-outline-primary btn-sm flex-fill">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($event['title']) ?>" 
                       target="_blank" class="btn btn-outline-info btn-sm flex-fill">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                </div>
            </div>

            <!-- Donation CTA -->
            <div class="mt-4 p-3 bg-primary text-white rounded">
                <h5 class="mb-2">Ủng hộ hoạt động</h5>
                <p class="mb-3 small">Ngoài tham gia sự kiện, bạn có thể ủng hộ tài chính cho các hoạt động thiện nguyện.</p>
                <a href="donate.php" class="btn btn-light btn-sm">
                    <i class="fas fa-heart me-1"></i>Quyên góp ngay
                </a>
            </div>

            <!-- Related Events -->
            <div class="mt-4">
                <h5 class="mb-3">Sự kiện khác</h5>
                <?php
                try {
                    $stmt = $pdo->prepare("
                        SELECT id, title, start_date, image
                        FROM events 
                        WHERE id != ? AND start_date >= CURDATE() AND status = 'active'
                        ORDER BY start_date ASC 
                        LIMIT 3
                    ");
                    $stmt->execute([$event_id]);
                    $related_events = $stmt->fetchAll();

                    if ($related_events):
                        foreach ($related_events as $related):
                ?>
                            <div class="card mb-2">
                                <div class="row g-0">
                                    <div class="col-4">
                                        <img src="../assets/images/<?= htmlspecialchars($related['image']) ?>" 
                                             class="img-fluid h-100" style="object-fit: cover;" 
                                             alt="<?= htmlspecialchars($related['title']) ?>">
                                    </div>
                                    <div class="col-8">
                                        <div class="card-body p-2">
                                            <h6 class="card-title mb-1" style="font-size: 0.9rem;">
                                                <?= htmlspecialchars(substr($related['title'], 0, 50)) ?>...
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= formatDate($related['start_date']) ?>
                                            </small>
                                            <div class="mt-1">
                                                <a href="event_detail.php?id=<?= $related['id'] ?>" class="btn btn-outline-primary btn-sm">
                                                    Xem
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                <?php 
                        endforeach;
                    else:
                ?>
                        <p class="text-muted small">Không có sự kiện nào khác.</p>
                <?php 
                    endif;
                } catch (PDOException $e) {
                    // Ignore error
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const quantity = document.getElementById('quantity').value;

    if (!name || !email || !phone || !quantity) {
        e.preventDefault();
        alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
        return false;
    }

    if (!validateEmail(email)) {
        e.preventDefault();
        alert('Email không hợp lệ!');
        return false;
    }

    if (!validatePhone(phone)) {
        e.preventDefault();
        alert('Số điện thoại không hợp lệ!');
        return false;
    }
});
</script>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>

