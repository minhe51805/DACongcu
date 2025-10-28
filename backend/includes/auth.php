<?php

/**
 * VinTech Authentication System
 * Handles user authentication, sessions, and security
 */

require_once __DIR__ . '/mailer.php';

class VinTechAuth
{
    private $pdo;
    private $mailer;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->mailer = new VinTechMailer();
        $this->startSession();
    }

    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Register new user
     */
    public function register($username, $email, $password, $full_name, $phone = null)
    {
        try {
            // Validate input
            $errors = $this->validateRegistration($username, $email, $password, $full_name);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            // Check if user exists
            if ($this->userExists($username, $email)) {
                return ['success' => false, 'errors' => ['Username hoặc email đã tồn tại']];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));

            // Insert user
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password, full_name, phone, email_verification_token, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");

            $stmt->execute([$username, $email, $hashedPassword, $full_name, $phone, $verificationToken]);
            $userId = $this->pdo->lastInsertId();

            // Send verification email
            $emailSent = $this->sendVerificationEmail($email, $full_name, $verificationToken);

            // Log activity
            $this->logActivity($userId, 'register', 'User registered successfully');

            if ($emailSent) {
                return [
                    'success' => true,
                    'message' => 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.',
                    'user_id' => $userId,
                    'requires_verification' => true
                ];
            } else {
                // If email fails, auto-activate account
                $stmt = $this->pdo->prepare("UPDATE users SET status = 'active', email_verification_token = NULL WHERE id = ?");
                $stmt->execute([$userId]);

                return [
                    'success' => true,
                    'message' => 'Đăng ký thành công! Tài khoản đã được kích hoạt (email verification không khả dụng).',
                    'user_id' => $userId,
                    'requires_verification' => false
                ];
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Có lỗi xảy ra trong quá trình đăng ký']];
        }
    }

    /**
     * Login user
     */
    public function login($username, $password, $remember = false)
    {
        try {
            // Check login attempts
            if ($this->isAccountLocked($username)) {
                return ['success' => false, 'errors' => ['Tài khoản đã bị khóa do đăng nhập sai quá nhiều lần']];
            }

            // Get user
            $user = $this->getUserByUsernameOrEmail($username);

            if (!$user || !password_verify($password, $user['password'])) {
                $this->recordFailedLogin($username);
                return ['success' => false, 'errors' => ['Tên đăng nhập hoặc mật khẩu không đúng']];
            }

            // Check account status
            if ($user['status'] !== 'active') {
                $message = $user['status'] === 'pending' ?
                    'Tài khoản chưa được xác thực. Vui lòng kiểm tra email.' :
                    'Tài khoản đã bị vô hiệu hóa.';
                return ['success' => false, 'errors' => [$message]];
            }

            // Reset login attempts
            $this->resetLoginAttempts($user['id']);

            // Create session
            $this->createUserSession($user, $remember);

            // Update last login
            $this->updateLastLogin($user['id']);

            // Log activity
            $this->logActivity($user['id'], 'login', 'User logged in successfully');

            return [
                'success' => true,
                'message' => 'Đăng nhập thành công!',
                'user' => $this->sanitizeUserData($user)
            ];
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Có lỗi xảy ra trong quá trình đăng nhập']];
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'User logged out');

            // Remove session from database
            if (isset($_SESSION['session_token'])) {
                $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE session_token = ?");
                $stmt->execute([$_SESSION['session_token']]);
            }
        }

        // Clear session
        session_destroy();

        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        return ['success' => true, 'message' => 'Đăng xuất thành công!'];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && $this->validateSession();
    }

    /**
     * Get current user
     */
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ? $this->sanitizeUserData($user) : null;
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin()
    {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === 'admin';
    }

    /**
     * Login user by ID (used for social login flows)
     */
    public function loginUserById($userId, $remember = false)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['success' => false, 'errors' => ['Tài khoản không tồn tại']];
            }

            // Auto-activate if social login created this user
            if ($user['status'] !== 'active') {
                $upd = $this->pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                $upd->execute([$userId]);
                $user['status'] = 'active';
            }

            // Create session
            $this->createUserSession($user, $remember);
            $this->updateLastLogin($userId);
            $this->logActivity($userId, 'login_social', 'User logged in via social provider');

            return [
                'success' => true,
                'message' => 'Đăng nhập thành công!',
                'user' => $this->sanitizeUserData($user)
            ];
        } catch (Exception $e) {
            error_log('loginUserById error: ' . $e->getMessage());
            return ['success' => false, 'errors' => ['Có lỗi xảy ra trong quá trình đăng nhập']];
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail($token)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id FROM users 
                WHERE email_verification_token = ? AND status = 'pending'
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['success' => false, 'errors' => ['Token xác thực không hợp lệ']];
            }

            // Update user status
            $stmt = $this->pdo->prepare("
                UPDATE users
                SET status = 'active', email_verified = 1, email_verification_token = NULL
                WHERE id = ?
            ");
            $stmt->execute([$user['id']]);

            // Get user info for welcome email
            $stmt = $this->pdo->prepare("SELECT email, full_name FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            // Send welcome email
            if ($userInfo) {
                $this->sendWelcomeEmail($userInfo['email'], $userInfo['full_name']);
            }

            // Log activity
            $this->logActivity($user['id'], 'email_verified', 'Email verified successfully');

            return ['success' => true, 'message' => 'Email đã được xác thực thành công!'];
        } catch (Exception $e) {
            error_log("Email verification error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Có lỗi xảy ra trong quá trình xác thực']];
        }
    }

    /**
     * Send password reset email
     */
    public function requestPasswordReset($email)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, full_name FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['success' => false, 'errors' => ['Email không tồn tại trong hệ thống']];
            }

            // Generate reset token
            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Save reset token
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET password_reset_token = ?, password_reset_expires = ? 
                WHERE id = ?
            ");
            $stmt->execute([$resetToken, $expiresAt, $user['id']]);

            // Send reset email
            $this->sendPasswordResetEmail($email, $user['full_name'], $resetToken);

            // Log activity
            $this->logActivity($user['id'], 'password_reset_requested', 'Password reset requested');

            return ['success' => true, 'message' => 'Email đặt lại mật khẩu đã được gửi!'];
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Có lỗi xảy ra trong quá trình gửi email']];
        }
    }

    /**
     * Reset password
     */
    public function resetPassword($token, $newPassword)
    {
        try {
            // Validate password
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                return ['success' => false, 'errors' => ['Mật khẩu phải có ít nhất ' . PASSWORD_MIN_LENGTH . ' ký tự']];
            }

            // Check token
            $stmt = $this->pdo->prepare("
                SELECT id FROM users 
                WHERE password_reset_token = ? AND password_reset_expires > NOW()
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['success' => false, 'errors' => ['Token đặt lại mật khẩu không hợp lệ hoặc đã hết hạn']];
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET password = ?, password_reset_token = NULL, password_reset_expires = NULL 
                WHERE id = ?
            ");
            $stmt->execute([$hashedPassword, $user['id']]);

            // Log activity
            $this->logActivity($user['id'], 'password_reset', 'Password reset successfully');

            return ['success' => true, 'message' => 'Mật khẩu đã được đặt lại thành công!'];
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Có lỗi xảy ra trong quá trình đặt lại mật khẩu']];
        }
    }

    // Private helper methods
    private function validateRegistration($username, $email, $password, $full_name)
    {
        $errors = [];

        if (empty($username) || strlen($username) < 3) {
            $errors[] = 'Username phải có ít nhất 3 ký tự';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }

        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Mật khẩu phải có ít nhất ' . PASSWORD_MIN_LENGTH . ' ký tự';
        }

        if (empty($full_name)) {
            $errors[] = 'Họ tên không được để trống';
        }

        return $errors;
    }

    private function userExists($username, $email)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        return $stmt->fetch() !== false;
    }

    private function getUserByUsernameOrEmail($username)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function isAccountLocked($username)
    {
        $stmt = $this->pdo->prepare("
            SELECT locked_until FROM users 
            WHERE (username = ? OR email = ?) AND locked_until > NOW()
        ");
        $stmt->execute([$username, $username]);
        return $stmt->fetch() !== false;
    }

    private function recordFailedLogin($username)
    {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET login_attempts = login_attempts + 1,
                locked_until = CASE 
                    WHEN login_attempts + 1 >= ? THEN DATE_ADD(NOW(), INTERVAL ? SECOND)
                    ELSE locked_until 
                END
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([MAX_LOGIN_ATTEMPTS, LOCKOUT_DURATION, $username, $username]);
    }

    private function resetLoginAttempts($userId)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?");
        $stmt->execute([$userId]);
    }

    private function createUserSession($user, $remember = false)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];

        // Create session token
        $sessionToken = bin2hex(random_bytes(32));
        $_SESSION['session_token'] = $sessionToken;

        // Save session to database
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . SESSION_TIMEOUT . ' seconds'));
        $stmt = $this->pdo->prepare("
            INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user['id'],
            $sessionToken,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $expiresAt
        ]);

        // Set remember me cookie
        if ($remember) {
            $rememberToken = bin2hex(random_bytes(32));
            setcookie('remember_token', $rememberToken, time() + (30 * 24 * 3600), '/'); // 30 days

            $stmt = $this->pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$rememberToken, $user['id']]);
        }
    }

    private function validateSession()
    {
        if (!isset($_SESSION['session_token'])) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            SELECT user_id FROM user_sessions 
            WHERE session_token = ? AND expires_at > NOW()
        ");
        $stmt->execute([$_SESSION['session_token']]);

        return $stmt->fetch() !== false;
    }

    private function updateLastLogin($userId)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }

    private function sanitizeUserData($user)
    {
        unset($user['password'], $user['email_verification_token'], $user['password_reset_token']);
        return $user;
    }

    private function logActivity($userId, $type, $description)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_activities (user_id, activity_type, description, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $type,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Activity logging error: " . $e->getMessage());
        }
    }

    private function sendVerificationEmail($email, $name, $token)
    {
        try {
            require_once __DIR__ . '/mailer.php';
            $mailer = new VinTechMailer();
            $result = $mailer->sendVerificationEmail($email, $name, $token);

            if ($result) {
                error_log("Verification email sent successfully to: " . $email);
                return true;
            } else {
                error_log("Failed to send verification email to: " . $email);
                return false;
            }
        } catch (Exception $e) {
            error_log("Verification email error: " . $e->getMessage());
            return false;
        }
    }

    private function sendPasswordResetEmail($email, $name, $token)
    {
        try {
            require_once __DIR__ . '/mailer.php';
            $mailer = new VinTechMailer();
            $result = $mailer->sendPasswordResetEmail($email, $name, $token);

            if ($result) {
                error_log("Password reset email sent successfully to: " . $email);
            } else {
                error_log("Failed to send password reset email to: " . $email);
            }

            return $result;
        } catch (Exception $e) {
            error_log("Password reset email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send welcome email after successful verification
     */
    private function sendWelcomeEmail($email, $name)
    {
        try {
            require_once __DIR__ . '/mailer.php';
            $mailer = new VinTechMailer();
            $result = $mailer->sendWelcomeEmail($email, $name);

            if ($result) {
                error_log("Welcome email sent successfully to: " . $email);
            } else {
                error_log("Failed to send welcome email to: " . $email);
            }

            return $result;
        } catch (Exception $e) {
            error_log("Welcome email error: " . $e->getMessage());
            return false;
        }
    }
}

// Initialize auth instance
$auth = new VinTechAuth($pdo);
