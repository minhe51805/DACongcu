<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Redirect to Facebook OAuth
$params = [
    'client_id' => FACEBOOK_APP_ID,
    'redirect_uri' => FACEBOOK_REDIRECT_URI,
    'state' => bin2hex(random_bytes(16)),
    'response_type' => 'code',
    'scope' => 'email,public_profile'
];

$_SESSION['oauth_state_fb'] = $params['state'];

$url = 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);
header('Location: ' . $url);
exit;
?>


