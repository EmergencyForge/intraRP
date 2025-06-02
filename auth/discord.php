<?php
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use League\OAuth2\Client\Provider\GenericProvider;

$provider = new GenericProvider([
    'clientId'                => $_ENV['DISCORD_CLIENT_ID'],
    'clientSecret'            => $_ENV['DISCORD_CLIENT_SECRET'],
    'redirectUri' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
        '://' . $_SERVER['HTTP_HOST'] . '/auth/callback',
    'urlAuthorize'            => 'https://discord.com/api/oauth2/authorize',
    'urlAccessToken'          => 'https://discord.com/api/oauth2/token',
    'urlResourceOwnerDetails' => 'https://discord.com/api/users/@me',
]);

// Add the required scopes (e.g., 'identify' to get basic user info)
$authorizationUrl = $provider->getAuthorizationUrl([
    'scope' => ['identify'] // Add other scopes if needed, e.g., 'email', 'guilds'
]);

session_start();
$_SESSION['oauth2state'] = $provider->getState();

header('Location: ' . $authorizationUrl);
exit;
