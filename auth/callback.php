<?php
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use League\OAuth2\Client\Provider\GenericProvider;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$provider = new GenericProvider([
    'clientId'                => '1365759297841004564',
    'clientSecret'            => 'KFQ_tB_Jq7m4Q5b_s0LeOJ1UTmTA5EF6',
    'redirectUri'             => 'https://dev.intrarp.de/auth/callback',
    'urlAuthorize'            => 'https://discord.com/api/oauth2/authorize',
    'urlAccessToken'          => 'https://discord.com/api/oauth2/token',
    'urlResourceOwnerDetails' => 'https://discord.com/api/users/@me',
]);

if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
}

if (!isset($_GET['code'])) {
    exit('Authorization code not provided.');
}

try {
    $accessToken = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    echo 'Access Token: ' . $accessToken->getToken(); // Debugging
    $resourceOwner = $provider->getResourceOwner($accessToken);
    $discordUser = $resourceOwner->toArray();

    // Example: Save user to the database or start a session
    $_SESSION['userid'] = $discordUser['id'];
    $_SESSION['username'] = $discordUser['username'];
    $_SESSION['avatar'] = $discordUser['avatar'];

    header('Location: /admin/index.php');
    exit;
} catch (Exception $e) {
    echo 'Failed to get access token: ' . $e->getMessage();
    exit;
}
