<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Validate state
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    header('Location: ' . BASE_URL . 'auth/login.php?error=invalid_state');
    exit;
}

unset($_SESSION['oauth_state']);

// Exchange code for tokens
if (!isset($_GET['code'])) {
    header('Location: ' . BASE_URL . 'auth/login.php?error=missing_code');
    exit;
}

$code = $_GET['code'];

$tokenResponse = httpPost('https://oauth2.googleapis.com/token', [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
]);

if (!$tokenResponse || !isset($tokenResponse['id_token'])) {
    header('Location: ' . BASE_URL . 'auth/login.php?error=token');
    exit;
}

$idToken = $tokenResponse['id_token'];
$payload = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $idToken)[1]))), true);

if (!$payload || !isset($payload['sub'])) {
    header('Location: ' . BASE_URL . 'auth/login.php?error=payload');
    exit;
}

$googleId = $payload['sub'];
$email = $payload['email'] ?? null;
$fullName = $payload['name'] ?? 'Google User';
$avatar = $payload['picture'] ?? null;

// Find or create user
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Create username from email
    $username = $email ? explode('@', $email)[0] : ('gg_' . substr($googleId, -6));
    $username = preg_replace('/[^a-zA-Z0-9_]/', '_', $username);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, avatar, status, email_verified, created_at) VALUES (?, ?, '', ?, ?, 'active', 1, NOW())");
    $stmt->execute([$username, $email, $fullName, $avatar]);
    $userId = (int)$pdo->lastInsertId();
} else {
    $userId = (int)$user['id'];
}

// Log the user in
$auth->loginUserById($userId, true);

header('Location: ' . BASE_URL . 'dashboard.php');
exit;

function httpPost($url, $data) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) return null;
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200) return null;
    return json_decode($resp, true);
}
?>


