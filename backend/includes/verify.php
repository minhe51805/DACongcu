<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$message = '';
$success = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = 'Token xác thực không hợp lệ.';
} else {
    $result = $auth->verifyEmail($token);
    $success = $result['success'];
    $message = $success ? $result['message'] : implode(', ', $result['errors']);
}

$page_title = "Xác thực email - CONVOI VinTech";
include '../includes/header.php';
?>

<div class="vintech-auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="vintech-auth-card" data-animate="fadeInUp">
                    <!-- Header -->
                    <div class="auth-header text-center mb-4">
                        <div class="auth-icon">
                            <i class="fas fa-<?= $success ? 'check-circle' : 'exclamation-circle' ?>"></i>
                        </div>
                        <h2 class="auth-title">Xác thực email</h2>
                        <p class="auth-subtitle">
                            <?= $success ? 'Tài khoản đã được kích hoạt thành công!' : 'Có lỗi xảy ra trong quá trình xác thực' ?>
                        </p>
                    </div>

                    <!-- Message -->
                    <div class="alert alert-<?= $success ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?= $success ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                    <?php if ($success): ?>
                        <!-- Success State -->
                        <div class="success-state text-center">
                            <div class="success-animation mb-4">
                                <div class="checkmark-circle">
                                    <div class="checkmark"></div>
                                </div>
                            </div>
                            
                            <h4 class="text-success mb-3">🎉 Chào mừng bạn đến với CONVOI VinTech!</h4>
                            
                            <p class="mb-4">
                                Tài khoản của bạn đã được xác thực thành công. Bây giờ bạn có thể:
                            </p>
                            
                            <div class="features-grid mb-4">
                                <div class="feature-item">
                                    <i class="fas fa-heart text-danger"></i>
                                    <span>Tham gia các hoạt động thiện nguyện</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-shopping-cart text-primary"></i>
                                    <span>Mua sắm sản phẩm công nghệ</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-donate text-warning"></i>
                                    <span>Quyên góp cho các dự án</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-users text-info"></i>
                                    <span>Kết nối với cộng đồng</span>
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="login.php" class="vintech-btn-enhanced vintech-btn-primary-enhanced me-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập ngay
                                </a>
                                <a href="<?= BASE_URL ?>" class="vintech-btn-enhanced vintech-btn-secondary-enhanced">
                                    <i class="fas fa-home me-2"></i>Khám phá trang chủ
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Error State -->
                        <div class="error-state text-center">
                            <div class="error-animation mb-4">
                                <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                            </div>
                            
                            <h4 class="text-danger mb-3">Xác thực không thành công</h4>
                            
                            <p class="mb-4">
                                Có thể do một trong những lý do sau:
                            </p>
                            
                            <div class="error-reasons mb-4">
                                <div class="reason-item">
                                    <i class="fas fa-clock text-warning"></i>
                                    <span>Link xác thực đã hết hạn</span>
                                </div>
                                <div class="reason-item">
                                    <i class="fas fa-link text-danger"></i>
                                    <span>Link xác thực không hợp lệ</span>
                                </div>
                                <div class="reason-item">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Tài khoản đã được xác thực trước đó</span>
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="resend-verification.php" class="vintech-btn-enhanced vintech-btn-primary-enhanced me-3">
                                    <i class="fas fa-paper-plane me-2"></i>Gửi lại email xác thực
                                </a>
                                <a href="login.php" class="vintech-btn-enhanced vintech-btn-secondary-enhanced me-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Thử đăng nhập
                                </a>
                                <a href="register.php" class="vintech-btn-enhanced vintech-btn-outline-enhanced">
                                    <i class="fas fa-user-plus me-2"></i>Đăng ký lại
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Help Section -->
                    <div class="help-section mt-4">
                        <h6><i class="fas fa-question-circle me-2"></i>Cần hỗ trợ?</h6>
                        <p class="help-text">
                            Nếu bạn vẫn gặp vấn đề, vui lòng liên hệ với chúng tôi tại 
                            <a href="mailto:<?= SMTP_FROM_EMAIL ?>"><?= SMTP_FROM_EMAIL ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.success-animation {
    position: relative;
}

.checkmark-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #28a745, #20c997);
    margin: 0 auto;
    position: relative;
    animation: scaleIn 0.5s ease-in-out;
}

.checkmark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 30px;
    height: 15px;
    border: 3px solid white;
    border-top: none;
    border-right: none;
    transform: translate(-50%, -60%) rotate(-45deg);
    animation: checkmarkDraw 0.5s ease-in-out 0.3s both;
}

@keyframes scaleIn {
    0% { transform: scale(0); }
    100% { transform: scale(1); }
}

@keyframes checkmarkDraw {
    0% { width: 0; height: 0; }
    100% { width: 30px; height: 15px; }
}

.features-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin: 2rem 0;
}

.feature-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: rgba(111, 187, 107, 0.1);
    border-radius: 0.75rem;
    border: 1px solid rgba(111, 187, 107, 0.2);
    font-size: 0.9rem;
}

.feature-item i {
    margin-right: 0.75rem;
    font-size: 1.2rem;
}

.error-reasons {
    text-align: left;
    max-width: 400px;
    margin: 0 auto;
}

.reason-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    background: rgba(248, 249, 250, 0.8);
    border-radius: 0.5rem;
    font-size: 0.9rem;
}

.reason-item i {
    margin-right: 0.75rem;
    width: 20px;
}

.help-section {
    background: rgba(111, 187, 107, 0.1);
    border-radius: 1rem;
    padding: 1.5rem;
    border: 1px solid rgba(111, 187, 107, 0.2);
}

.help-section h6 {
    color: var(--vintech-primary);
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.help-text {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0;
}

.help-text a {
    color: var(--vintech-accent);
    text-decoration: none;
}

.help-text a:hover {
    text-decoration: underline;
}

.alert {
    border-radius: 1rem;
    border: none;
    font-weight: 500;
}

.alert-success {
    background: rgba(40, 167, 69, 0.1);
    color: #155724;
    border: 1px solid rgba(40, 167, 69, 0.2);
}

.alert-danger {
    background: rgba(220, 53, 69, 0.1);
    color: #721c24;
    border: 1px solid rgba(220, 53, 69, 0.2);
}

.action-buttons {
    margin-top: 2rem;
}

.action-buttons .vintech-btn-enhanced {
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons .vintech-btn-enhanced {
        display: block;
        width: 100%;
        margin-bottom: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($success): ?>
    // Auto redirect to login after 10 seconds for successful verification
    setTimeout(function() {
        if (confirm('Bạn có muốn chuyển đến trang đăng nhập không?')) {
            window.location.href = 'login.php';
        }
    }, 10000);
    <?php endif; ?>
});
</script>

<?php include '../includes/footer.php'; ?>
