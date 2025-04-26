<?php
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use League\OAuth2\Client\Provider\GenericProvider;

$provider = new GenericProvider([
    'clientId'                => '1365759297841004564',
    'clientSecret'            => 'KFQ_tB_Jq7m4Q5b_s0LeOJ1UTmTA5EF6',
    'redirectUri'             => 'https://dev.intrarp.de/auth/discord/callback',
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
