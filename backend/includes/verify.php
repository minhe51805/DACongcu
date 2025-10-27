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
