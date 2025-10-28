<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Validate state
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state_fb']) || $_GET['state'] !== $_SESSION['oauth_state_fb']) {
    header('Location: ' . BASE_URL . 'auth/login.php?error=invalid_state');
    exit;
}
unset($_SESSION['oauth_state_fb']);

if (!isset($_GET['code'])) {
    header('Location: ' . BASE_URL . 'auth/login.php?error=missing_code');
    exit;
}

$code = $_GET['code'];

// Exchange code for access token
$tokenResp = httpGet('https://graph.facebook.com/v18.0/oauth/access_token', [
    'client_id' => FACEBOOK_APP_ID,
    'redirect_uri' => FACEBOOK_REDIRECT_URI,
    'client_secret' => FACEBOOK_APP_SECRET,
    'code' => $code
]);

if (!$tokenResp || !isset($tokenResp['access_token'])) {
    header('Location: ' . BASE_URL . 'auth/login.php?error=token');
    exit;
}

$accessToken = $tokenResp['access_token'];

// Get user info
$me = httpGet('https://graph.facebook.com/me', [
    'fields' => 'id,name,email,picture.type(large)',
    'access_token' => $accessToken
]);

if (!$me || !isset($me['id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php?error=profile');
    exit;
}

$fbId = $me['id'];
$email = $me['email'] ?? null;
$fullName = $me['name'] ?? 'Facebook User';
$avatar = isset($me['picture']['data']['url']) ? $me['picture']['data']['url'] : null;

// Find or create user
if ($email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (empty($user)) {
    $username = $email ? explode('@', $email)[0] : ('fb_' . substr($fbId, -6));
    $username = preg_replace('/[^a-zA-Z0-9_]/', '_', $username);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, avatar, status, email_verified, created_at) VALUES (?, ?, '', ?, ?, 'active', 1, NOW())");
    $stmt->execute([$username, $email, $fullName, $avatar]);
    $userId = (int)$pdo->lastInsertId();
} else {
    $userId = (int)$user['id'];
}

$auth->loginUserById($userId, true);

header('Location: ' . BASE_URL . 'dashboard.php');
exit;

function httpGet($url, $params) {
    $url .= '?' . http_build_query($params);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) return null;
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200) return null;
    return json_decode($resp, true);
}
?>


