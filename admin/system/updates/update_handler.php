<?php
session_start();

error_log('=== UPDATE HANDLER DEBUG ===');
error_log('Session ID: ' . session_id());
error_log('Session exists: ' . (session_id() ? 'yes' : 'no'));
error_log('POST action: ' . ($_POST['action'] ?? 'no action'));
error_log('Session userid: ' . ($_SESSION['userid'] ?? 'not set'));
error_log('Session permissions: ' . print_r($_SESSION['permissions'] ?? 'not set', true));

header('Content-Type: application/json');

if (!session_id()) {
    echo json_encode([
        'success' => false,
        'message' => 'Session konnte nicht gestartet werden',
        'debug' => 'session_start() fehlgeschlagen'
    ]);
    exit;
}

if (!isset($_SESSION['userid'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Keine userid in Session',
        'debug' => [
            'session_id' => session_id(),
            'all_session_keys' => array_keys($_SESSION),
            'session_data' => $_SESSION
        ]
    ]);
    exit;
}

if (!isset($_SESSION['permissions'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Keine permissions in Session',
        'debug' => [
            'userid' => $_SESSION['userid'],
            'session_keys' => array_keys($_SESSION)
        ]
    ]);
    exit;
}

$hasPermission = false;
if (is_array($_SESSION['permissions'])) {
    $adminRoles = ['full_admin'];
    foreach ($adminRoles as $role) {
        if (in_array($role, $_SESSION['permissions'])) {
            $hasPermission = true;
            break;
        }
    }
}

if (!$hasPermission) {
    echo json_encode([
        'success' => false,
        'message' => 'Keine Berechtigung gefunden',
        'debug' => [
            'your_permissions' => $_SESSION['permissions'],
            'checked_roles' => ['full_admin'],
            'permissions_type' => gettype($_SESSION['permissions'])
        ]
    ]);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    require_once __DIR__ . '/../../../assets/config/config.php';
    require_once __DIR__ . '/../../../assets/config/database.php';
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Config-Dateien konnten nicht geladen werden: ' . $e->getMessage()
    ]);
    exit;
}

$updateManager = null;
if (file_exists(__DIR__ . '/UpdateManager.php')) {
    try {
        require_once __DIR__ . '/UpdateManager.php';
        $githubRepo = 'intraRP/intraRP';
        $updateManager = new UpdateManager($pdo, $githubRepo);
    } catch (Exception $e) {
        error_log('UpdateManager Error: ' . $e->getMessage());
    }
}

switch ($action) {
    case 'check_updates':
        if ($updateManager) {
            try {
                $result = $updateManager->getUpdateInfo();
                echo json_encode($result);
            } catch (Exception $e) {
                echo json_encode([
                    'error' => true,
                    'message' => 'GitHub API Error: ' . $e->getMessage(),
                    'current_version' => '1.0.0',
                    'has_update' => false
                ]);
            }
        } else {
            echo json_encode([
                'error' => true,
                'message' => 'UpdateManager nicht verfügbar',
                'current_version' => '1.0.0',
                'has_update' => false,
                'debug' => 'UpdateManager.php existiert: ' . (file_exists(__DIR__ . '/UpdateManager.php') ? 'ja' : 'nein')
            ]);
        }
        break;

    case 'check_requirements':
        echo json_encode([
            'php_version' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'zip_extension' => extension_loaded('zip'),
            'curl_extension' => extension_loaded('curl') || ini_get('allow_url_fopen'),
            'writable_root' => is_writable(__DIR__ . '/../../../'),
            'git_available' => !empty(shell_exec('git --version 2>/dev/null')),
            'composer_available' => (
                file_exists(__DIR__ . '/../../../composer.phar') ||
                !empty(shell_exec('composer --version 2>/dev/null'))
            )
        ]);
        break;

    case 'perform_update':
        if ($updateManager) {
            set_time_limit(600);
            echo json_encode($updateManager->performUpdate());
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'UpdateManager nicht verfügbar'
            ]);
        }
        break;

    default:
        echo json_encode([
            'success' => true,
            'message' => 'Debug-Handler aktiv',
            'action' => $action,
            'permissions_ok' => true,
            'updatemanager_available' => $updateManager !== null,
            'github_repo' => 'intraRP/intraRP'
        ]);
}
