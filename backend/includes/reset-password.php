<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$errors = [];
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: forgot-password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirmPassword)) {
        $errors[] = 'Vui lòng nhập đầy đủ thông tin';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Mật khẩu phải có ít nhất ' . PASSWORD_MIN_LENGTH . ' ký tự';
    } else {
        $result = $auth->resetPassword($token, $password);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $errors = $result['errors'];
        }
    }
}

$page_title = "Đặt lại mật khẩu - CONVOI VinTech";
include '../includes/header.php';
?>

<!-- VinTech Reset Password Page -->
<div class="vintech-auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="vintech-auth-card" data-animate="fadeInUp">
                    <!-- Logo and Title -->
                    <div class="auth-header text-center mb-4">
                        <div class="auth-logo">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h2 class="auth-title">Đặt lại mật khẩu</h2>
                        <p class="auth-subtitle">Nhập mật khẩu mới cho tài khoản của bạn</p>
                    </div>

                    <?php if ($success): ?>
                        <!-- Success State -->
                        <div class="success-state text-center">
                            <div class="success-icon mb-3">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                            </div>
                            <p class="text-muted mb-4">
                                Mật khẩu của bạn đã được đặt lại thành công. Bạn có thể đăng nhập với mật khẩu mới.
                            </p>
                            <div class="success-actions">
                                <a href="login.php" class="vintech-btn-enhanced vintech-btn-primary-enhanced">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập ngay
                                </a>
                                <a href="<?= BASE_URL ?>" class="vintech-btn-enhanced vintech-btn-outline-enhanced">
                                    <i class="fas fa-home me-2"></i>Về trang chủ
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Form State -->
                        <form method="POST" class="vintech-auth-form" data-validate>
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                            
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
                                    <i class="fas fa-lock me-2"></i>Mật khẩu mới
                                </label>
                                <div class="password-input-group">
                                    <input type="password" 
                                           name="password" 
                                           id="password"
                                           class="vintech-form-control" 
                                           placeholder="Nhập mật khẩu mới"
                                           required
                                           minlength="<?= PASSWORD_MIN_LENGTH ?>">
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength" id="password-strength"></div>
                                <small class="form-text">
                                    Mật khẩu phải có ít nhất <?= PASSWORD_MIN_LENGTH ?> ký tự
                                </small>
                            </div>

                            <div class="vintech-form-group">
                                <label class="vintech-form-label">
                                    <i class="fas fa-lock me-2"></i>Xác nhận mật khẩu mới
                                </label>
                                <div class="password-input-group">
                                    <input type="password" 
                                           name="confirm_password" 
                                           id="confirm_password"
                                           class="vintech-form-control" 
                                           placeholder="Nhập lại mật khẩu mới"
                                           required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye" id="confirm_password-eye"></i>
                                    </button>
                                </div>
                                <div class="password-match" id="password-match"></div>
                            </div>

                            <button type="submit" class="vintech-btn-enhanced vintech-btn-primary-enhanced w-100">
                                <i class="fas fa-save me-2"></i>Đặt lại mật khẩu
                            </button>
                        </form>

                        <!-- Security Tips -->
                        <div class="security-tips">
                            <div class="tips-box">
                                <h6><i class="fas fa-shield-alt me-2"></i>Mẹo bảo mật:</h6>
                                <ul>
                                    <li>Sử dụng mật khẩu mạnh với ít nhất 8 ký tự</li>
                                    <li>Kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt</li>
                                    <li>Không sử dụng thông tin cá nhân dễ đoán</li>
                                    <li>Không chia sẻ mật khẩu với người khác</li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Footer Links -->
                    <div class="auth-footer text-center">
                        <p>Nhớ lại mật khẩu? 
                            <a href="login.php" class="register-link">Đăng nhập ngay</a>
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

<!-- Enhanced CSS for Reset Password Page -->
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
    animation: successPulse 2s infinite;
}

.success-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.password-strength {
    margin-top: 0.5rem;
    font-size: 0.8rem;
}

.strength-weak { color: #dc3545; }
.strength-medium { color: #ffc107; }
.strength-strong { color: #28a745; }

.password-match {
    margin-top: 0.5rem;
    font-size: 0.8rem;
}

.match-success { color: #28a745; }
.match-error { color: #dc3545; }

.security-tips {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #dee2e6;
}

.tips-box {
    background: rgba(111, 187, 107, 0.1);
    border-radius: 0.75rem;
    padding: 1.5rem;
    border: 1px solid rgba(111, 187, 107, 0.2);
}

.tips-box h6 {
    color: var(--vintech-primary);
    margin-bottom: 1rem;
    font-weight: 600;
}

.tips-box ul {
    margin: 0;
    padding-left: 1.5rem;
    color: #6c757d;
}

.tips-box li {
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.form-text {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

@keyframes successPulse {
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
    
    .tips-box {
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
// Password toggle functionality
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(inputId + '-eye');
    
    if (input.type === 'password') {
        input.type = 'text';
        eye.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        eye.className = 'fas fa-eye';
    }
}

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    let feedback = '';
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    switch (strength) {
        case 0:
        case 1:
            feedback = '<span class="strength-weak">Yếu</span>';
            break;
        case 2:
        case 3:
            feedback = '<span class="strength-medium">Trung bình</span>';
            break;
        case 4:
        case 5:
            feedback = '<span class="strength-strong">Mạnh</span>';
            break;
    }
    
    return feedback;
}

// Password match checker
function checkPasswordMatch(password, confirmPassword) {
    if (confirmPassword === '') return '';
    
    if (password === confirmPassword) {
        return '<span class="match-success"><i class="fas fa-check"></i> Mật khẩu khớp</span>';
    } else {
        return '<span class="match-error"><i class="fas fa-times"></i> Mật khẩu không khớp</span>';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.vintech-auth-form');
    
    if (form) {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthDiv = document.getElementById('password-strength');
        const matchDiv = document.getElementById('password-match');
        
        // Password strength checking
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const strength = checkPasswordStrength(this.value);
                strengthDiv.innerHTML = strength;
                
                // Also check match if confirm password has value
                if (confirmPasswordInput.value) {
                    const match = checkPasswordMatch(this.value, confirmPasswordInput.value);
                    matchDiv.innerHTML = match;
                }
            });
        }
        
        // Password match checking
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                const match = checkPasswordMatch(passwordInput.value, this.value);
                matchDiv.innerHTML = match;
            });
        }
        
        // Form submission
        form.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                if (window.VinTech) {
                    VinTech.showToast('Mật khẩu xác nhận không khớp', 'error');
                }
                return false;
            }
            
            if (password.length < <?= PASSWORD_MIN_LENGTH ?>) {
                e.preventDefault();
                if (window.VinTech) {
                    VinTech.showToast('Mật khẩu phải có ít nhất <?= PASSWORD_MIN_LENGTH ?> ký tự', 'error');
                }
                return false;
            }
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang đặt lại...';
            submitBtn.disabled = true;
            
            // Re-enable button after 5 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
        
        // Auto-focus first input
        const firstInput = form.querySelector('input[type="password"]');
        if (firstInput) {
            firstInput.focus();
        }
    }
    
    // Show success message if password was reset
    <?php if ($success): ?>
        setTimeout(() => {
            if (window.VinTech) {
                VinTech.showToast('Mật khẩu đã được đặt lại thành công!', 'success');
            }
        }, 1000);
        
        // Auto redirect to login after 5 seconds
        setTimeout(() => {
            if (confirm('Bạn có muốn chuyển đến trang đăng nhập không?')) {
                window.location.href = 'login.php';
            }
        }, 5000);
    <?php endif; ?>
});
</script>

<?php include '../includes/footer.php'; ?>
