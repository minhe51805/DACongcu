<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Redirect to Google OAuth 2.0 consent screen
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'access_type' => 'online',
    'include_granted_scopes' => 'true',
    'state' => bin2hex(random_bytes(16))
];

$_SESSION['oauth_state'] = $params['state'];

$url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $url);
exit;
?>


