<?php
require_once __DIR__ . '/../../../../backend/bootstrap.php';;

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $terms = isset($_POST['terms']);

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
        $errors[] = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    }

    if (!$terms) {
        $errors[] = 'Vui lòng đồng ý với điều khoản sử dụng';
    }

    if (empty($errors)) {
        $result = $auth->register($username, $email, $password, $fullName, $phone);

        if ($result['success']) {
            $success = $result['message'];
            // Clear form data
            $_POST = [];
        } else {
            $errors = $result['errors'];
        }
    }
}

$page_title = "Đăng ký - CONVOI VinTech";
$extra_css = ['vintech-auth.css'];
include __DIR__ . '/../../../common/components/header.php';
?>

<!-- VinTech Register Page -->
<div class="vintech-auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="vintech-auth-card" data-animate="fadeInUp">
                    <!-- Logo and Title -->
                    <div class="auth-header text-center mb-4">
                        <div class="auth-logo">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2 class="auth-title">Đăng ký tài khoản</h2>
                        <p class="auth-subtitle">Tham gia cộng đồng CONVOI VinTech</p>
                    </div>

                    <!-- Register Form -->
                    <form method="POST" class="vintech-auth-form" data-validate data-auto-save id="register-form">
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
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="vintech-form-group">
                                    <label class="vintech-form-label">
                                        <i class="fas fa-user me-2"></i>Tên đăng nhập *
                                    </label>
                                    <input type="text"
                                        name="username"
                                        class="vintech-form-control"
                                        placeholder="Nhập tên đăng nhập"
                                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                        required
                                        minlength="3">
                                    <small class="form-text">Ít nhất 3 ký tự, không có khoảng trắng</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="vintech-form-group">
                                    <label class="vintech-form-label">
                                        <i class="fas fa-envelope me-2"></i>Email *
                                    </label>
                                    <input type="email"
                                        name="email"
                                        class="vintech-form-control"
                                        placeholder="Nhập địa chỉ email"
                                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="vintech-form-group">
                            <label class="vintech-form-label">
                                <i class="fas fa-id-card me-2"></i>Họ và tên *
                            </label>
                            <input type="text"
                                name="full_name"
                                class="vintech-form-control"
                                placeholder="Nhập họ và tên đầy đủ"
                                value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                                required>
                        </div>

                        <div class="vintech-form-group">
                            <label class="vintech-form-label">
                                <i class="fas fa-phone me-2"></i>Số điện thoại
                            </label>
                            <input type="tel"
                                name="phone"
                                class="vintech-form-control"
                                placeholder="Nhập số điện thoại (tùy chọn)"
                                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="vintech-form-group">
                                    <label class="vintech-form-label">
                                        <i class="fas fa-lock me-2"></i>Mật khẩu *
                                    </label>
                                    <div class="password-input-group">
                                        <input type="password"
                                            name="password"
                                            id="password"
                                            class="vintech-form-control"
                                            placeholder="Nhập mật khẩu"
                                            required
                                            minlength="<?= PASSWORD_MIN_LENGTH ?>">
                                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                            <i class="fas fa-eye" id="password-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength" id="password-strength"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="vintech-form-group">
                                    <label class="vintech-form-label">
                                        <i class="fas fa-lock me-2"></i>Xác nhận mật khẩu *
                                    </label>
                                    <div class="password-input-group">
                                        <input type="password"
                                            name="confirm_password"
                                            id="confirm_password"
                                            class="vintech-form-control"
                                            placeholder="Nhập lại mật khẩu"
                                            required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye" id="confirm_password-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-match" id="password-match"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox"
                                name="terms"
                                id="terms"
                                class="form-check-input"
                                required
                                <?= isset($_POST['terms']) ? 'checked' : '' ?>>
                            <label for="terms" class="form-check-label">
                                Tôi đồng ý với
                                <a href="#" data-vintech-modal="terms-modal">Điều khoản sử dụng</a>
                                và
                                <a href="#" data-vintech-modal="privacy-modal">Chính sách bảo mật</a>
                            </label>
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox"
                                name="newsletter"
                                id="newsletter"
                                class="form-check-input"
                                <?= isset($_POST['newsletter']) ? 'checked' : '' ?>>
                            <label for="newsletter" class="form-check-label">
                                Nhận thông báo về các sự kiện và chương trình mới
                            </label>
                        </div>

                        <button type="submit" class="vintech-btn-enhanced vintech-btn-primary-enhanced w-100">
                            <i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản
                        </button>
                    </form>

                    <!-- Social Register -->
                    <div class="social-login">
                        <div class="divider">
                            <span>Hoặc đăng ký với</span>
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

                    <!-- Login Link -->
                    <div class="auth-footer text-center">
                        <p>Đã có tài khoản?
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

<!-- Terms Modal -->
<div id="terms-modal" class="vintech-modal">
    <div class="vintech-modal-content">
        <h4>Điều khoản sử dụng</h4>
        <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
            <h6>1. Chấp nhận điều khoản</h6>
            <p>Bằng việc sử dụng dịch vụ CONVOI VinTech, bạn đồng ý tuân thủ các điều khoản này.</p>

            <h6>2. Sử dụng dịch vụ</h6>
            <p>Bạn cam kết sử dụng dịch vụ một cách hợp pháp và không vi phạm quyền lợi của người khác.</p>

            <h6>3. Bảo mật thông tin</h6>
            <p>Chúng tôi cam kết bảo vệ thông tin cá nhân của bạn theo chính sách bảo mật.</p>

            <h6>4. Trách nhiệm người dùng</h6>
            <p>Bạn chịu trách nhiệm về tất cả hoạt động diễn ra dưới tài khoản của mình.</p>
        </div>
        <div class="modal-actions mt-3">
            <button class="vintech-btn-enhanced vintech-btn-primary-enhanced" data-modal-close>Đồng ý</button>
        </div>
    </div>
</div>

<!-- Privacy Modal -->
<div id="privacy-modal" class="vintech-modal">
    <div class="vintech-modal-content">
        <h4>Chính sách bảo mật</h4>
        <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
            <h6>1. Thu thập thông tin</h6>
            <p>Chúng tôi chỉ thu thập thông tin cần thiết để cung cấp dịch vụ tốt nhất.</p>

            <h6>2. Sử dụng thông tin</h6>
            <p>Thông tin của bạn được sử dụng để cải thiện trải nghiệm và liên lạc khi cần thiết.</p>

            <h6>3. Chia sẻ thông tin</h6>
            <p>Chúng tôi không chia sẻ thông tin cá nhân với bên thứ ba mà không có sự đồng ý.</p>

            <h6>4. Bảo mật</h6>
            <p>Chúng tôi áp dụng các biện pháp bảo mật tiên tiến để bảo vệ dữ liệu.</p>
        </div>
        <div class="modal-actions mt-3">
            <button class="vintech-btn-enhanced vintech-btn-primary-enhanced" data-modal-close>Đã hiểu</button>
        </div>
    </div>
</div>

<!-- Additional CSS for Register Page -->
<style>
    .password-strength {
        margin-top: 0.5rem;
        font-size: 0.8rem;
    }

    .strength-weak {
        color: #dc3545;
    }

    .strength-medium {
        color: #ffc107;
    }

    .strength-strong {
        color: #28a745;
    }

    .password-match {
        margin-top: 0.5rem;
        font-size: 0.8rem;
    }

    .match-success {
        color: #28a745;
    }

    .match-error {
        color: #dc3545;
    }

    .form-text {
        color: #6c757d;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    .modal-body {
        line-height: 1.6;
    }

    .modal-body h6 {
        color: var(--vintech-primary);
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }

    .modal-body p {
        margin-bottom: 0.75rem;
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
        const form = document.getElementById('register-form');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthDiv = document.getElementById('password-strength');
        const matchDiv = document.getElementById('password-match');

        // Password strength checking
        passwordInput.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            strengthDiv.innerHTML = strength;

            // Also check match if confirm password has value
            if (confirmPasswordInput.value) {
                const match = checkPasswordMatch(this.value, confirmPasswordInput.value);
                matchDiv.innerHTML = match;
            }
        });

        // Password match checking
        confirmPasswordInput.addEventListener('input', function() {
            const match = checkPasswordMatch(passwordInput.value, this.value);
            matchDiv.innerHTML = match;
        });

        // Form submission
        form.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const terms = document.getElementById('terms').checked;

            if (password !== confirmPassword) {
                e.preventDefault();
                if (window.VinTech) {
                    VinTech.showToast('Mật khẩu xác nhận không khớp', 'error');
                }
                return false;
            }

            if (!terms) {
                e.preventDefault();
                if (window.VinTech) {
                    VinTech.showToast('Vui lòng đồng ý với điều khoản sử dụng', 'error');
                }
                return false;
            }

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang đăng ký...';
            submitBtn.disabled = true;

            // Re-enable button after 5 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });

        // Auto-focus first input
        const firstInput = form.querySelector('input');
        if (firstInput) {
            firstInput.focus();
        }
    });
</script>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>