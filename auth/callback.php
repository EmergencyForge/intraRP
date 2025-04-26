<?php
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

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

    $resourceOwner = $provider->getResourceOwner($accessToken);
    $discordUser = $resourceOwner->toArray();

    // Save user to the database or start a session
    $_SESSION['userid'] = $discordUser['id'];
    $_SESSION['username'] = $discordUser['username'];
    $_SESSION['avatar'] = $discordUser['avatar'];

    // Check if the Discord ID already exists in the database
    $stmt = $pdo->prepare("SELECT * FROM intra_users WHERE discord_id = :discord_id");
    $stmt->execute(['discord_id' => $_SESSION['userid']]);
    $user = $stmt->fetch();

    if ($user) {
        // Discord ID exists, log the user in
        $_SESSION['userid'] = $user['id'];
        $_SESSION['permissions'] = $user['permissions']; // Assuming permissions are stored in the database
    } else {
        // Discord ID does not exist, handle linking or creating a new user
        // Example: Redirect to a page to link Discord to an existing account
        header('Location: /auth/link_account.php');
        exit;
    }

    header('Location: /admin/index.php');
    exit;
} catch (Exception $e) {
    echo 'Failed to get access token: ' . $e->getMessage();
    exit;
}
