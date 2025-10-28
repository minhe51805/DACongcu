<?php
require_once __DIR__ . '/../../../../backend/bootstrap.php';

// Redirect if not logged in
if (!$auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$currentUser = $auth->getCurrentUser();
$errors = [];
$success = '';

// Check for session messages
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $errors[] = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($fullName)) {
            $errors[] = 'Họ tên không được để trống';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
                $stmt->execute([$fullName, $phone, $currentUser['id']]);

                $success = 'Cập nhật thông tin thành công!';
                $currentUser['full_name'] = $fullName;
                $currentUser['phone'] = $phone;
            } catch (Exception $e) {
                $errors[] = 'Có lỗi xảy ra khi cập nhật thông tin';
            }
        }
    } elseif ($action === 'upload_avatar') {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $fileInfo = pathinfo($_FILES['avatar']['name']);
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension = strtolower($fileInfo['extension']);

            if (!in_array($fileExtension, $allowedTypes)) {
                $errors[] = 'Chỉ chấp nhận file ảnh (JPG, JPEG, PNG, GIF)';
            } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) { // 2MB limit for database storage
                $errors[] = 'File ảnh không được vượt quá 2MB';
            } else {
                // Read file content and convert to base64
                $imageData = file_get_contents($_FILES['avatar']['tmp_name']);
                $base64Image = base64_encode($imageData);
                $mimeType = $_FILES['avatar']['type'];

                // Create data URL
                $avatarDataUrl = 'data:' . $mimeType . ';base64,' . $base64Image;

                try {
                    // Save to database as base64 data URL
                    $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $stmt->execute([$avatarDataUrl, $currentUser['id']]);

                    $currentUser['avatar'] = $avatarDataUrl;
                    $success = 'Cập nhật ảnh đại diện thành công!';

                    // Don't redirect, just show success message
                    // This prevents the reload loop issue
                } catch (Exception $e) {
                    $errors[] = 'Có lỗi xảy ra khi lưu ảnh vào cơ sở dữ liệu';
                }
            }
        } else {
            $errors[] = 'Vui lòng chọn file ảnh';
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $errors[] = 'Vui lòng nhập đầy đủ thông tin';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Mật khẩu mới không khớp';
        } elseif (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Mật khẩu mới phải có ít nhất ' . PASSWORD_MIN_LENGTH . ' ký tự';
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$currentUser['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($currentPassword, $user['password'])) {
                $errors[] = 'Mật khẩu hiện tại không đúng';
            } else {
                try {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $currentUser['id']]);

                    $success = 'Đổi mật khẩu thành công!';
                } catch (Exception $e) {
                    $errors[] = 'Có lỗi xảy ra khi đổi mật khẩu';
                }
            }
        }
    }
}

$page_title = "Hồ sơ cá nhân - CONVOI VinTech";
include __DIR__ . '/../../../common/components/header.php';
?>

<!-- VinTech Profile Page -->
<div class="vintech-profile-page">
    <div class="container">
        <div class="row">
            <!-- Profile Sidebar -->
            <div class="col-lg-4">
                <div class="profile-sidebar" data-animate="fadeInLeft">
                    <div class="profile-card">
                        <div class="profile-avatar">
                            <?php
                            // Debug: Show avatar data (commented out to prevent reload issues)
                            // echo '<div style="font-size: 12px; color: #666; margin-bottom: 10px;">';
                            // echo 'Avatar field: ' . (!empty($currentUser['avatar']) ? 'Has data (' . strlen($currentUser['avatar']) . ' chars)' : 'Empty');
                            // if (!empty($currentUser['avatar'])) {
                            //     echo '<br>Starts with: ' . substr($currentUser['avatar'], 0, 50) . '...';
                            // }
                            // echo '</div>';

                            // Get avatar from database
                            $avatarSrc = !empty($currentUser['avatar']) && strpos($currentUser['avatar'], 'data:') === 0
                                ? $currentUser['avatar']
                                : null;
                            ?>
                            <?php if ($avatarSrc): ?>
                                <img src="<?= $avatarSrc ?>"
                                    alt="<?= htmlspecialchars($currentUser['full_name']) ?>"
                                    style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                            <?php else: ?>
                                <div style="width: 150px; height: 150px; border-radius: 50%; background: #007bff; display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; font-weight: bold;">
                                    <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <button class="avatar-edit-btn" data-bs-toggle="modal" data-bs-target="#avatarModal">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                        <div class="profile-info">
                            <h4><?= htmlspecialchars($currentUser['full_name']) ?></h4>
                            <p class="profile-email"><?= htmlspecialchars($currentUser['email']) ?></p>
                            <div class="profile-badges">
                                <?php if ($currentUser['role'] === 'admin'): ?>
                                    <span class="badge badge-admin">
                                        <i class="fas fa-crown"></i> Admin
                                    </span>
                                <?php endif; ?>
                                <span class="badge badge-verified">
                                    <i class="fas fa-check-circle"></i> Đã xác thực
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="profile-stats">
                        <h6>Thống kê hoạt động</h6>
                        <div class="stat-item">
                            <i class="fas fa-heart"></i>
                            <span>Đã quyên góp: <strong>5 lần</strong></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Đã mua: <strong>12 sản phẩm</strong></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-calendar"></i>
                            <span>Tham gia: <strong><?= date('d/m/Y', strtotime($currentUser['created_at'])) ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="col-lg-8">
                <div class="profile-content" data-animate="fadeInRight">
                    <!-- Profile Tabs -->
                    <div class="profile-tabs">
                        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                                    <i class="fas fa-user"></i> Thông tin cá nhân
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                    <i class="fas fa-shield-alt"></i> Bảo mật
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                                    <i class="fas fa-history"></i> Hoạt động
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content" id="profileTabContent">
                        <!-- Personal Info Tab -->
                        <div class="tab-pane fade show active" id="info" role="tabpanel">
                            <div class="tab-header">
                                <h5>Thông tin cá nhân</h5>
                                <p>Cập nhật thông tin cá nhân của bạn</p>
                            </div>

                            <?php if (!empty($errors) && $_POST['action'] === 'update_profile'): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if ($success && $_POST['action'] === 'update_profile'): ?>
                                <div class="alert alert-success">
                                    <?= htmlspecialchars($success) ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="profile-form">
                                <input type="hidden" name="action" value="update_profile">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="vintech-form-group">
                                            <label class="vintech-form-label">Tên đăng nhập</label>
                                            <input type="text" class="vintech-form-control" value="<?= htmlspecialchars($currentUser['username']) ?>" disabled>
                                            <small class="form-text">Tên đăng nhập không thể thay đổi</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="vintech-form-group">
                                            <label class="vintech-form-label">Email</label>
                                            <input type="email" class="vintech-form-control" value="<?= htmlspecialchars($currentUser['email']) ?>" disabled>
                                            <small class="form-text">Email không thể thay đổi</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="vintech-form-group">
                                    <label class="vintech-form-label">Họ và tên *</label>
                                    <input type="text" name="full_name" class="vintech-form-control"
                                        value="<?= htmlspecialchars($currentUser['full_name']) ?>" required>
                                </div>

                                <div class="vintech-form-group">
                                    <label class="vintech-form-label">Số điện thoại</label>
                                    <input type="tel" name="phone" class="vintech-form-control"
                                        value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>">
                                </div>

                                <button type="submit" class="vintech-btn-enhanced vintech-btn-primary-enhanced">
                                    <i class="fas fa-save"></i> Cập nhật thông tin
                                </button>
                            </form>
                        </div>

                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <div class="tab-header">
                                <h5>Bảo mật tài khoản</h5>
                                <p>Quản lý mật khẩu và cài đặt bảo mật</p>
                            </div>

                            <?php if (!empty($errors) && $_POST['action'] === 'change_password'): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if ($success && $_POST['action'] === 'change_password'): ?>
                                <div class="alert alert-success">
                                    <?= htmlspecialchars($success) ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="profile-form">
                                <input type="hidden" name="action" value="change_password">

                                <div class="vintech-form-group">
                                    <label class="vintech-form-label">Mật khẩu hiện tại</label>
                                    <input type="password" name="current_password" class="vintech-form-control" required>
                                </div>

                                <div class="vintech-form-group">
                                    <label class="vintech-form-label">Mật khẩu mới</label>
                                    <input type="password" name="new_password" class="vintech-form-control"
                                        minlength="<?= PASSWORD_MIN_LENGTH ?>" required>
                                </div>

                                <div class="vintech-form-group">
                                    <label class="vintech-form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" name="confirm_password" class="vintech-form-control" required>
                                </div>

                                <button type="submit" class="vintech-btn-enhanced vintech-btn-primary-enhanced">
                                    <i class="fas fa-key"></i> Đổi mật khẩu
                                </button>
                            </form>

                            <div class="security-info mt-4">
                                <h6>Thông tin bảo mật</h6>
                                <div class="security-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Đăng nhập lần cuối: <?= $currentUser['last_login'] ? date('d/m/Y H:i', strtotime($currentUser['last_login'])) : 'Chưa có' ?></span>
                                </div>
                                <div class="security-item">
                                    <i class="fas fa-shield-check"></i>
                                    <span>Trạng thái: <strong class="text-success">Tài khoản an toàn</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Tab -->
                        <div class="tab-pane fade" id="activity" role="tabpanel">
                            <div class="tab-header">
                                <h5>Lịch sử hoạt động</h5>
                                <p>Theo dõi các hoạt động gần đây của bạn</p>
                            </div>

                            <div class="activity-list">
                                <div class="activity-item">
                                    <div class="activity-icon login">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">Đăng nhập thành công</div>
                                        <div class="activity-time">Hôm nay, 14:30</div>
                                    </div>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-icon donation">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">Quyên góp cho dự án "Xây trường học"</div>
                                        <div class="activity-time">Hôm qua, 16:45</div>
                                    </div>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-icon purchase">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">Mua sản phẩm "Gạo hữu cơ ST25"</div>
                                        <div class="activity-time">3 ngày trước, 10:20</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Avatar Upload Modal -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="avatarModalLabel">
                    <i class="fas fa-camera me-2"></i>Cập nhật ảnh đại diện
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="avatarForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="upload_avatar">

                    <?php if (!empty($errors) && $_POST['action'] === 'upload_avatar'): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success && $_POST['action'] === 'upload_avatar'): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <div class="text-center mb-4">
                        <div class="current-avatar">
                            <?php
                            // Use the same logic as above for avatar display
                            $modalAvatarSrc = null;
                            if (!empty($currentUser['avatar']) && strpos($currentUser['avatar'], 'data:') === 0) {
                                $modalAvatarSrc = $currentUser['avatar'];
                            }
                            ?>
                            <?php if ($modalAvatarSrc): ?>
                                <img src="<?= $modalAvatarSrc ?>" alt="Current Avatar"
                                    style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #007bff;">
                            <?php else: ?>
                                <div style="width: 120px; height: 120px; border-radius: 50%; background: #007bff; display: flex; align-items: center; justify-content: center; color: white; font-size: 36px; font-weight: bold; border: 3px solid #007bff;">
                                    <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="mt-2 text-muted">Ảnh đại diện hiện tại</p>
                    </div>

                    <div class="mb-3">
                        <label for="avatar" class="form-label">Chọn ảnh mới:</label>
                        <input type="file" class="form-control" id="avatar" name="avatar"
                            accept="image/jpeg,image/jpg,image/png,image/gif" required>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Chấp nhận file JPG, JPEG, PNG, GIF. Kích thước tối đa: 2MB<br>
                            <i class="fas fa-database me-1"></i>
                            Ảnh sẽ được lưu trực tiếp vào cơ sở dữ liệu
                        </div>
                    </div>

                    <div class="preview-container" style="display: none;">
                        <label class="form-label">Xem trước:</label>
                        <div class="text-center">
                            <img id="avatarPreview" src="" alt="Preview"
                                style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #28a745;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Cập nhật ảnh
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enhanced CSS for Profile Page -->
<style>
    .vintech-profile-page {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
        padding: 6rem 0;
    }

    .profile-sidebar {
        position: sticky;
        top: 120px;
    }

    .profile-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 1.5rem;
        padding: 2rem;
        text-align: center;
        box-shadow: var(--vintech-shadow-lg);
        margin-bottom: 1.5rem;
    }

    .profile-avatar {
        position: relative;
        display: inline-block;
        margin-bottom: 1rem;
    }

    .profile-avatar img,
    .profile-avatar>div {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--vintech-primary);
        display: block;
        margin: 0 auto;
    }

    .avatar-edit-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 35px;
        height: 35px;
        background: var(--vintech-primary);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        transition: all var(--vintech-transition-base);
    }

    .avatar-edit-btn:hover {
        background: var(--vintech-primary-dark);
        transform: scale(1.1);
    }

    .profile-info h4 {
        color: var(--vintech-primary);
        margin-bottom: 0.5rem;
    }

    .profile-email {
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .profile-badges {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .badge {
        padding: 0.5rem 1rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-admin {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    .badge-verified {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }

    .profile-stats {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: var(--vintech-shadow-md);
    }

    .profile-stats h6 {
        color: var(--vintech-primary);
        margin-bottom: 1rem;
        text-align: center;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }

    .stat-item:last-child {
        border-bottom: none;
    }

    .stat-item i {
        color: var(--vintech-primary);
        width: 20px;
    }

    .profile-content {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 1.5rem;
        box-shadow: var(--vintech-shadow-lg);
        overflow: hidden;
    }

    .profile-tabs .nav-tabs {
        border-bottom: 1px solid #dee2e6;
        background: rgba(45, 90, 39, 0.05);
        padding: 0 1.5rem;
    }

    .profile-tabs .nav-link {
        border: none;
        color: #6c757d;
        padding: 1rem 1.5rem;
        font-weight: 600;
        transition: all var(--vintech-transition-base);
    }

    .profile-tabs .nav-link.active {
        color: var(--vintech-primary);
        background: transparent;
        border-bottom: 3px solid var(--vintech-primary);
    }

    .tab-content {
        padding: 2rem;
    }

    .tab-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }

    .tab-header h5 {
        color: var(--vintech-primary);
        margin-bottom: 0.5rem;
    }

    .profile-form {
        margin-bottom: 2rem;
    }

    .form-text {
        color: #6c757d;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    .security-info {
        background: rgba(111, 187, 107, 0.1);
        border-radius: 1rem;
        padding: 1.5rem;
        border: 1px solid rgba(111, 187, 107, 0.2);
    }

    .security-info h6 {
        color: var(--vintech-primary);
        margin-bottom: 1rem;
    }

    .security-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .security-item i {
        color: var(--vintech-primary);
        width: 20px;
    }

    .activity-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #eee;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
    }

    .activity-icon.login {
        background: var(--vintech-info);
    }

    .activity-icon.donation {
        background: var(--vintech-success);
    }

    .activity-icon.purchase {
        background: var(--vintech-warning);
    }

    .activity-title {
        font-weight: 600;
        color: var(--vintech-primary);
    }

    .activity-time {
        color: #6c757d;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .profile-sidebar {
            position: static;
            margin-bottom: 2rem;
        }

        .profile-tabs .nav-tabs {
            padding: 0;
        }

        .profile-tabs .nav-link {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }

        .tab-content {
            padding: 1.5rem;
        }
    }
</style>

<!-- VinTech Enhanced JavaScript -->
<script src="assets/js/vintech-framework.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show success/error messages
        <?php if ($success): ?>
            setTimeout(() => {
                if (window.VinTech) {
                    VinTech.showToast('<?= addslashes($success) ?>', 'success');
                }
            }, 1000);
        <?php endif; ?>

        // Avatar preview functionality
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatarPreview');
        const previewContainer = document.querySelector('.preview-container');

        // Handle avatar form submission
        const avatarForm = document.getElementById('avatarForm');
        if (avatarForm) {
            avatarForm.addEventListener('submit', function(e) {
                // Let the form submit normally, but prevent any additional reloads
                console.log('Avatar form submitted');
            });
        }

        if (avatarInput) {
            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                if (file) {
                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Chỉ chấp nhận file ảnh (JPG, JPEG, PNG, GIF)');
                        this.value = '';
                        previewContainer.style.display = 'none';
                        return;
                    }

                    // Validate file size (2MB for database storage)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File ảnh không được vượt quá 2MB (để lưu vào cơ sở dữ liệu)');
                        this.value = '';
                        previewContainer.style.display = 'none';
                        return;
                    }

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                        previewContainer.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.style.display = 'none';
                }
            });
        }

        // Auto-open modal if there are upload errors
        <?php if (!empty($errors) && isset($_POST['action']) && $_POST['action'] === 'upload_avatar'): ?>
            const avatarModal = new bootstrap.Modal(document.getElementById('avatarModal'));
            avatarModal.show();
        <?php endif; ?>

        // Show success message if upload successful (no auto-reload)
        <?php if ($success && strpos($success, 'ảnh đại diện') !== false): ?>
            // Just show a toast or alert, don't reload
            if (window.VinTech && window.VinTech.showToast) {
                VinTech.showToast('<?= addslashes($success) ?>', 'success');
            } else {
                // Fallback alert
                setTimeout(function() {
                    alert('<?= addslashes($success) ?>');
                }, 100);
            }
        <?php endif; ?>

        // Password confirmation validation
        const passwordForm = document.querySelector('form[action*="change_password"]');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                const newPassword = this.querySelector('[name="new_password"]').value;
                const confirmPassword = this.querySelector('[name="confirm_password"]').value;

                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    if (window.VinTech) {
                        VinTech.showToast('Mật khẩu xác nhận không khớp', 'error');
                    }
                }
            });
        }
    });
</script>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>