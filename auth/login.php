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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        $errors[] = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        $result = $auth->login($username, $password, $remember);
        
        if ($result['success']) {
            $success = $result['message'];
            // Redirect after successful login
            $redirectUrl = $_GET['redirect'] ?? BASE_URL . '/dashboard.php';
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            $errors = $result['errors'];
        }
    }
}

$page_title = "Đăng nhập - CONVOI VinTech";
include '../includes/header.php';
?>

<!-- VinTech Login Page -->
<div class="vintech-auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="vintech-auth-card" data-animate="fadeInUp">
                    <!-- Logo and Title -->
                    <div class="auth-header text-center mb-4">
                        <div class="auth-logo">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h2 class="auth-title">Đăng nhập</h2>
                        <p class="auth-subtitle">Chào mừng trở lại với CONVOI VinTech</p>
                    </div>

                    <!-- Login Form -->
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

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>

                        <div class="vintech-form-group">
                            <label class="vintech-form-label">
                                <i class="fas fa-user me-2"></i>Tên đăng nhập hoặc Email
                            </label>
                            <input type="text" 
                                   name="username" 
                                   class="vintech-form-control" 
                                   placeholder="Nhập username hoặc email"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="vintech-form-group">
                            <label class="vintech-form-label">
                                <i class="fas fa-lock me-2"></i>Mật khẩu
                            </label>
                            <div class="password-input-group">
                                <input type="password" 
                                       name="password" 
                                       id="password"
                                       class="vintech-form-control" 
                                       placeholder="Nhập mật khẩu"
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="password-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <div class="form-check">
                                <input type="checkbox" 
                                       name="remember" 
                                       id="remember" 
                                       class="form-check-input"
                                       <?= isset($_POST['remember']) ? 'checked' : '' ?>>
                                <label for="remember" class="form-check-label">
                                    Ghi nhớ đăng nhập
                                </label>
                            </div>
                            <a href="forgot-password.php" class="forgot-password-link">
                                Quên mật khẩu?
                            </a>
                        </div>

                        <button type="submit" class="vintech-btn-enhanced vintech-btn-primary-enhanced w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                        </button>
                    </form>

                    <!-- Social Login -->
                    <div class="social-login">
                        <div class="divider">
                            <span>Hoặc đăng nhập với</span>
                        </div>
                        <div class="social-buttons">
                            <a class="social-btn google-btn" href="<?= BASE_URL ?>auth/oauth_google.php">
                                <i class="fab fa-google"></i>
                                Google
                            </a>
                            <a class="social-btn facebook-btn" href="<?= BASE_URL ?>auth/oauth_facebook.php">
                                <i class="fab fa-facebook-f"></i>
                                Facebook
                            </a>
                        </div>
                    </div>

                    <!-- Register Link -->
                    <div class="auth-footer text-center">
                        <p>Chưa có tài khoản? 
                            <a href="register.php" class="register-link">Đăng ký ngay</a>
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

<!-- Enhanced CSS for Auth Pages -->
<style>
.vintech-auth-page {
    background: linear-gradient(135deg, #2d5a27 0%, #4a7c59 50%, #6fbb6b 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 2rem 0;
    position: relative;
}

.vintech-auth-page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.vintech-auth-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 1.5rem;
    padding: 3rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    z-index: 1;
}

.auth-header {
    margin-bottom: 2rem;
}

.auth-logo {
    width: 80px;
    height: 80px;
    background: var(--vintech-gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem;
    color: white;
    box-shadow: var(--vintech-shadow-lg);
}

.auth-title {
    color: var(--vintech-primary);
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.auth-subtitle {
    color: #6c757d;
    margin: 0;
}

.vintech-auth-form {
    margin-bottom: 2rem;
}

.password-input-group {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 5px;
    transition: color var(--vintech-transition-base);
}

.password-toggle:hover {
    color: var(--vintech-primary);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-check-input {
    margin: 0;
}

.forgot-password-link {
    color: var(--vintech-primary);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color var(--vintech-transition-base);
}

.forgot-password-link:hover {
    color: var(--vintech-primary-dark);
    text-decoration: underline;
}

.social-login {
    margin-bottom: 2rem;
}

.divider {
    text-align: center;
    margin: 1.5rem 0;
    position: relative;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #dee2e6;
}

.divider span {
    background: rgba(255, 255, 255, 0.95);
    padding: 0 1rem;
    color: #6c757d;
    font-size: 0.9rem;
}

.social-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.social-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem;
    border: 2px solid #dee2e6;
    border-radius: var(--vintech-radius-lg);
    background: white;
    color: #495057;
    text-decoration: none;
    font-weight: 600;
    transition: all var(--vintech-transition-base);
    cursor: pointer;
}

.google-btn:hover {
    border-color: #db4437;
    color: #db4437;
    transform: translateY(-2px);
}

.facebook-btn:hover {
    border-color: #4267B2;
    color: #4267B2;
    transform: translateY(-2px);
}

.auth-footer {
    border-top: 1px solid #dee2e6;
    padding-top: 1.5rem;
}

.register-link {
    color: var(--vintech-primary);
    text-decoration: none;
    font-weight: 600;
}

.register-link:hover {
    color: var(--vintech-primary-dark);
    text-decoration: underline;
}

.back-home-link {
    color: #6c757d;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color var(--vintech-transition-base);
}

.back-home-link:hover {
    color: var(--vintech-primary);
}

@media (max-width: 768px) {
    .vintech-auth-card {
        padding: 2rem;
        margin: 1rem;
    }
    
    .social-buttons {
        grid-template-columns: 1fr;
    }
    
    .form-options {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
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

// Enhanced form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.vintech-auth-form');
    
    form.addEventListener('submit', function(e) {
        const username = form.querySelector('[name="username"]').value.trim();
        const password = form.querySelector('[name="password"]').value;
        
        if (!username || !password) {
            e.preventDefault();
            if (window.VinTech) {
                VinTech.showToast('Vui lòng nhập đầy đủ thông tin', 'error');
            }
            return false;
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang đăng nhập...';
        submitBtn.disabled = true;
        
        // Re-enable button after 3 seconds (in case of error)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 3000);
    });
    
    // Auto-focus first input
    const firstInput = form.querySelector('input');
    if (firstInput) {
        firstInput.focus();
    }
    
    // Demo notification
    setTimeout(() => {
        if (window.VinTech) {
            VinTech.showToast('Demo: admin/123456 hoặc user1/123456', 'info', 8000);
        }
    }, 1000);
});
</script>

<?php include '../includes/footer.php'; ?>
