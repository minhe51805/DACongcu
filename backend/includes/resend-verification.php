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