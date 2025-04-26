<?php
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use League\OAuth2\Client\Provider\GenericProvider;

session_start();

$provider = new GenericProvider([
    'clientId'                => 'YOUR_CLIENT_ID',
    'clientSecret'            => 'YOUR_CLIENT_SECRET',
    'redirectUri'             => 'https://yourdomain.com/auth/discord/callback',
    'urlAuthorize'            => 'https://discord.com/api/oauth2/authorize',
    'urlAccessToken'          => 'https://discord.com/api/oauth2/token',
    'urlResourceOwnerDetails' => 'https://discord.com/api/users/@me',
]);

if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
}

try {
    $accessToken = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    $resourceOwner = $provider->getResourceOwner($accessToken);
    $discordUser = $resourceOwner->toArray();

    // Example: Save user to the database or start a session
    $_SESSION['userid'] = $discordUser['id'];
    $_SESSION['username'] = $discordUser['username'];
    $_SESSION['avatar'] = $discordUser['avatar'];

    header('Location: /admin/index.php');
    exit;
} catch (Exception $e) {
    exit('Failed to get access token: ' . $e->getMessage());
}
