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
    $discordId = $discordUser['id'];
    $username = $discordUser['username'];
    $avatar = $discordUser['avatar'];

    // Check if any users exist in the database
    $checkStmt = $pdo->query("SELECT COUNT(*) FROM intra_users");
    $userCount = $checkStmt->fetchColumn();

    if ($userCount == 0) {
        // No users exist, create the first admin user
        $stmt = $pdo->prepare("
            INSERT INTO intra_users (discord_id, username, fullname, passwort, role, full_admin) 
            VALUES (:discord_id, :username, NULL, NULL, :role, :full_admin)
        ");
        $stmt->execute([
            'discord_id' => $discordId,
            'username'   => $username,
            'role'       => 0, // Admin role
            'full_admin' => 1  // Full admin privileges
        ]);
    }

    // Check if the Discord ID already exists in the database
    $stmt = $pdo->prepare("SELECT * FROM intra_users WHERE discord_id = :discord_id");
    $stmt->execute(['discord_id' => $discordId]);
    $user = $stmt->fetch();

    if ($user) {
        // Discord ID exists, log the user in
        $_SESSION['userid'] = $user['id'];
        $_SESSION['cirs_user'] = $user['fullname'];
        $_SESSION['cirs_username'] = $user['username'];
        $_SESSION['aktenid'] = $user['aktenid'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_admin'] = $user['full_admin'];
    } else {
        // Discord ID does not exist, create a new user
        $insertStmt = $pdo->prepare("
            INSERT INTO intra_users (discord_id, username, fullname, role, full_admin) 
            VALUES (:discord_id, :username, NULL, :role, :full_admin)
        ");
        $insertStmt->execute([
            'discord_id' => $discordId,
            'username'   => $username,
            'role'       => 7, // Default role for new users
            'full_admin' => 0  // Default full_admin value
        ]);

        // Fetch the newly created user to set session variables
        $stmt = $pdo->prepare("SELECT * FROM intra_users WHERE discord_id = :discord_id");
        $stmt->execute(['discord_id' => $discordId]);
        $user = $stmt->fetch();

        $_SESSION['userid'] = $user['id'];
        $_SESSION['cirs_user'] = $user['fullname'];
        $_SESSION['cirs_username'] = $user['username'];
        $_SESSION['aktenid'] = $user['aktenid'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_admin'] = $user['full_admin'];
    }

    // Redirect to the admin dashboard or the originally requested page
    $redirectUrl = $_SESSION['redirect_url'] ?? '/admin/index.php';
    unset($_SESSION['redirect_url']);
    // Debugging: Check if session variables are set
    var_dump($_SESSION);
    exit;
    header("Location: $redirectUrl");
    exit;
} catch (Exception $e) {
    echo 'Failed to get access token: ' . $e->getMessage();
    exit;
}
