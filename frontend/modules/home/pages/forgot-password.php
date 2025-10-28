<?php
require_once __DIR__ . '/../../../../backend/bootstrap.php';
;

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $errors[] = 'Vui lòng nhập địa chỉ email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Địa chỉ email không hợp lệ';
    } else {
        $result = $auth->requestPasswordReset($email);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $errors = $result['errors'];
        }
    }
}

$page_title = "Quên mật khẩu - CONVOI VinTech";
include __DIR__ . '/../../../common/components/header.php';
?>

<!-- VinTech Forgot Password Page -->
<div class="vintech-auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="vintech-auth-card" data-animate="fadeInUp">
                    <!-- Logo and Title -->
                    <div class="auth-header text-center mb-4">
                        <div class="auth-logo">
                            <i class="fas fa-key"></i>
                        </div>
                        <h2 class="auth-title">Quên mật khẩu</h2>
                        <p class="auth-subtitle">Nhập email để nhận link đặt lại mật khẩu</p>
                    </div>

                    <?php if ($success): ?>
                        <!-- Success State -->
                        <div class="success-state text-center">
                            <div class="success-icon mb-3">
                                <i class="fas fa-envelope-open"></i>
                            </div>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                            </div>
                            <p class="text-muted mb-4">
                                Vui lòng kiểm tra hộp thư email của bạn và làm theo hướng dẫn để đặt lại mật khẩu.
                            </p>
                            <div class="success-actions">
                                <a href="login.php" class="vintech-btn-enhanced vintech-btn-primary-enhanced">
                                    <i class="fas fa-sign-in-alt me-2"></i>Về trang đăng nhập
                                </a>
                                <button onclick="location.reload()" class="vintech-btn-enhanced vintech-btn-outline-enhanced">
                                    <i class="fas fa-redo me-2"></i>Gửi lại email
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Form State -->
                        <form method="POST" class="vintech-auth-form" data-validate>
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <div class="vintech-form-group">
                                <label class="vintech-form-label">
                                    <i class="fas fa-envelope me-2"></i>Địa chỉ email
                                </label>
                                <input type="email" 
                                       name="email" 
                                       class="vintech-form-control" 
                                       placeholder="Nhập email đã đăng ký"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       required>
                                <small class="form-text">
                                    Nhập email bạn đã sử dụng để đăng ký tài khoản
                                </small>
                            </div>

                            <button type="submit" class="vintech-btn-enhanced vintech-btn-primary-enhanced w-100">
                                <i class="fas fa-paper-plane me-2"></i>Gửi link đặt lại mật khẩu
                            </button>
                        </form>

                        <!-- Additional Info -->
                        <div class="forgot-password-info">
                            <div class="info-box">
                                <h6><i class="fas fa-info-circle me-2"></i>Lưu ý:</h6>
                                <ul>
                                    <li>Link đặt lại mật khẩu sẽ có hiệu lực trong 1 giờ</li>
                                    <li>Kiểm tra cả thư mục spam nếu không thấy email</li>
                                    <li>Chỉ có thể gửi 1 email mỗi 5 phút</li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Footer Links -->
                    <div class="auth-footer text-center">
                        <p>Nhớ lại mật khẩu? 
                            <a href="login.php" class="register-link">Đăng nhập ngay</a>
                        </p>
                        <p>Chưa có tài khoản? 
                            <a href="register.php" class="register-link">Đăng ký tại đây</a>
                        </p>
                        <p>
                            <a href="<?= BASE_URL ?>" class="back-home-link">
                                <i class="fas fa-arrow-left me-1"></i>Về trang chủ
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced CSS for Forgot Password Page -->
<style>
.success-state {
    padding: 1rem 0;
}

.success-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #28a745, #20c997);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 2.5rem;
    color: white;
    box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
    animation: pulse 2s infinite;
}

.success-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.forgot-password-info {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #dee2e6;
}

.info-box {
    background: rgba(23, 162, 184, 0.1);
    border-radius: 0.75rem;
    padding: 1.5rem;
    border: 1px solid rgba(23, 162, 184, 0.2);
}

.info-box h6 {
    color: var(--vintech-info);
    margin-bottom: 1rem;
    font-weight: 600;
}

.info-box ul {
    margin: 0;
    padding-left: 1.5rem;
    color: #6c757d;
}

.info-box li {
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.form-text {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 15px 40px rgba(40, 167, 69, 0.4);
    }
}

@media (max-width: 768px) {
    .success-actions {
        gap: 0.75rem;
    }
    
    .info-box {
        padding: 1rem;
    }
    
    .success-icon {
        width: 70px;
        height: 70px;
        font-size: 2rem;
    }
}
</style>

<!-- VinTech Enhanced JavaScript -->
<script src="../assets/js/vintech-framework.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.vintech-auth-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const email = form.querySelector('[name="email"]').value.trim();
            
            if (!email) {
                e.preventDefault();
                if (window.VinTech) {
                    VinTech.showToast('Vui lòng nhập địa chỉ email', 'error');
                }
                return false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                if (window.VinTech) {
                    VinTech.showToast('Địa chỉ email không hợp lệ', 'error');
                }
                return false;
            }
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang gửi email...';
            submitBtn.disabled = true;
            
            // Re-enable button after 5 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
        
        // Auto-focus email input
        const emailInput = form.querySelector('input[type="email"]');
        if (emailInput) {
            emailInput.focus();
        }
    }
    
    // Show success message if email was sent
    <?php if ($success): ?>
        setTimeout(() => {
            if (window.VinTech) {
                VinTech.showToast('Email đặt lại mật khẩu đã được gửi!', 'success');
            }
        }, 1000);
    <?php endif; ?>
});
</script>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>


