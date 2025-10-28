<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = 'Vui lòng nhập email.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email không hợp lệ.';
        $messageType = 'error';
    } else {
        try {
            // Check if user exists and is pending
            $stmt = $pdo->prepare("SELECT id, full_name, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $message = 'Email không tồn tại trong hệ thống.';
                $messageType = 'error';
            } elseif ($user['status'] === 'active') {
                $message = 'Tài khoản đã được kích hoạt. Bạn có thể đăng nhập ngay.';
                $messageType = 'info';
            } else {
                // Generate new verification token
                $newToken = bin2hex(random_bytes(32));
                
                // Update token in database
                $stmt = $pdo->prepare("UPDATE users SET email_verification_token = ? WHERE id = ?");
                $stmt->execute([$newToken, $user['id']]);
                
                // Send new verification email
                global $mailer;
                if (isset($mailer) && $mailer !== null) {
                    $emailSent = $mailer->sendVerificationEmail($email, $user['full_name'], $newToken);
                    
                    if ($emailSent) {
                        $message = 'Email xác thực mới đã được gửi! Vui lòng kiểm tra hộp thư của bạn.';
                        $messageType = 'success';
                    } else {
                        $message = 'Có lỗi khi gửi email. Vui lòng thử lại sau.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Hệ thống email tạm thời không khả dụng. Vui lòng thử lại sau.';
                    $messageType = 'error';
                }
            }
        } catch (Exception $e) {
            error_log("Resend verification error: " . $e->getMessage());
            $message = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
            $messageType = 'error';
        }
    }
}

$page_title = "Gửi lại email xác thực - CONVOI VinTech";
include '../includes/header.php';
?>

<div class="vintech-auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="vintech-auth-card" data-animate="fadeInUp">
                    <!-- Header -->
                    <div class="auth-header text-center mb-4">
                        <div class="auth-icon">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                        <h2 class="auth-title">Gửi lại email xác thực</h2>
                        <p class="auth-subtitle">
                            Nhập email của bạn để nhận lại link xác thực tài khoản
                        </p>
                    </div>

                    <!-- Message -->
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $messageType === 'success' ? 'success' : ($messageType === 'info' ? 'info' : 'danger') ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : ($messageType === 'info' ? 'info-circle' : 'exclamation-circle') ?> me-2"></i>
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST" class="vintech-form" novalidate>
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   placeholder="Nhập email của bạn"
                                   required>
                            <div class="invalid-feedback">
                                Vui lòng nhập email hợp lệ.
                            </div>
                        </div>

                        <button type="submit" class="vintech-btn-enhanced vintech-btn-primary-enhanced w-100">
                            <i class="fas fa-paper-plane me-2"></i>
                            Gửi lại email xác thực
                        </button>
                    </form>

                    <!-- Additional Links -->
                    <div class="auth-links text-center">
                        <div class="divider">
                            <span>hoặc</span>
                        </div>
                        
                        <div class="link-group">
                            <a href="login.php" class="auth-link">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                            </a>
                            <a href="register.php" class="auth-link">
                                <i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản mới
                            </a>
                            <a href="<?= BASE_URL ?>" class="auth-link">
                                <i class="fas fa-home me-2"></i>Về trang chủ
                            </a>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="help-section">
                        <h6><i class="fas fa-question-circle me-2"></i>Cần hỗ trợ?</h6>
                        <p class="help-text">
                            Nếu bạn vẫn gặp vấn đề với việc xác thực email, vui lòng liên hệ với chúng tôi tại 
                            <a href="mailto:<?= SMTP_FROM_EMAIL ?>"><?= SMTP_FROM_EMAIL ?></a>
                        </p>
                        
                        <div class="tips">
                            <h6><i class="fas fa-lightbulb me-2"></i>Mẹo:</h6>
                            <ul>
                                <li>Kiểm tra thư mục spam/junk mail</li>
                                <li>Đảm bảo email chính xác</li>
                                <li>Chờ vài phút trước khi gửi lại</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.help-section {
    background: rgba(111, 187, 107, 0.1);
    border-radius: 1rem;
    padding: 1.5rem;
    margin-top: 2rem;
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
    margin-bottom: 1rem;
}

.help-text a {
    color: var(--vintech-accent);
    text-decoration: none;
}

.help-text a:hover {
    text-decoration: underline;
}

.tips ul {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
    padding-left: 1.2rem;
}

.tips li {
    margin-bottom: 0.25rem;
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

.alert-info {
    background: rgba(23, 162, 184, 0.1);
    color: #0c5460;
    border: 1px solid rgba(23, 162, 184, 0.2);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('.vintech-form');
    const emailInput = document.getElementById('email');
    
    form.addEventListener('submit', function(e) {
        if (!emailInput.value.trim()) {
            e.preventDefault();
            emailInput.classList.add('is-invalid');
            return false;
        }
        
        if (!isValidEmail(emailInput.value)) {
            e.preventDefault();
            emailInput.classList.add('is-invalid');
            return false;
        }
        
        emailInput.classList.remove('is-invalid');
        emailInput.classList.add('is-valid');
    });
    
    emailInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            this.classList.remove('is-invalid');
        }
    });
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
</script>

<?php include '../includes/footer.php'; ?>
