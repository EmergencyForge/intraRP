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
    'clientId'                => $_ENV['DISCORD_CLIENT_ID'],
    'clientSecret'            => $_ENV['DISCORD_CLIENT_SECRET'],
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

    $discordId = $discordUser['id'];
    $username = $discordUser['username'];
    $avatar = $discordUser['avatar'];

    $adminRoleStmt = $pdo->prepare("SELECT id FROM intra_users_roles WHERE admin = 1 LIMIT 1");
    $adminRoleStmt->execute();
    $adminRole = $adminRoleStmt->fetch();

    if (!$adminRole) {
        exit('Admin role not configured in intra_users_roles table.');
    }

    $defaultRoleStmt = $pdo->prepare("SELECT id FROM intra_users_roles WHERE `default` = 1 LIMIT 1");
    $defaultRoleStmt->execute();
    $defaultRole = $defaultRoleStmt->fetch();

    if (!$defaultRole) {
        exit('Default role not configured in intra_users_roles table.');
    }

    $checkStmt = $pdo->query("SELECT COUNT(*) FROM intra_users");
    $userCount = $checkStmt->fetchColumn();

    if ($userCount == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO intra_users (discord_id, username, fullname, role, full_admin) 
            VALUES (:discord_id, :username, NULL, :role, :full_admin)
        ");
        $stmt->execute([
            'discord_id' => $discordId,
            'username'   => $username,
            'role'       => $adminRole['id'],
            'full_admin' => 1
        ]);
    }

    $stmt = $pdo->prepare("SELECT * FROM intra_users WHERE discord_id = :discord_id");
    $stmt->execute(['discord_id' => $discordId]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['userid'] = $user['id'];
        $_SESSION['cirs_user'] = $user['fullname'];
        $_SESSION['cirs_username'] = $user['username'];
        $_SESSION['aktenid'] = $user['aktenid'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_admin'] = $user['full_admin'];

        if ($user['full_admin'] == 1) {
            $_SESSION['permissions'] = ['full_admin'];
        } else {
            $roleStmt = $pdo->prepare("SELECT permissions FROM intra_users_roles WHERE id = :role_id");
            $roleStmt->execute(['role_id' => $user['role']]);
            $role = $roleStmt->fetch();

            if ($role && isset($role['permissions'])) {
                $_SESSION['permissions'] = json_decode($role['permissions'], true) ?? [];
            } else {
                $_SESSION['permissions'] = [];
            }
        }
    } else {
        $insertStmt = $pdo->prepare("
            INSERT INTO intra_users (discord_id, username, fullname, role, full_admin) 
            VALUES (:discord_id, :username, NULL, :role, :full_admin)
        ");
        $insertStmt->execute([
            'discord_id' => $discordId,
            'username'   => $username,
            'role'       => $defaultRole['id'],
            'full_admin' => 0
        ]);

        $stmt = $pdo->prepare("SELECT * FROM intra_users WHERE discord_id = :discord_id");
        $stmt->execute(['discord_id' => $discordId]);
        $user = $stmt->fetch();

        $_SESSION['userid'] = $user['id'];
        $_SESSION['cirs_user'] = $user['fullname'];
        $_SESSION['cirs_username'] = $user['username'];
        $_SESSION['aktenid'] = $user['aktenid'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_admin'] = $user['full_admin'];
        $_SESSION['permissions'] = [];
    }

    $redirectUrl = $_SESSION['redirect_url'] ?? '/admin/index.php';
    unset($_SESSION['redirect_url']);
    header("Location: $redirectUrl");
    exit;
} catch (Exception $e) {
    echo 'Failed to get access token: ' . $e->getMessage();
    exit;
}
