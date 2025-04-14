<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

use App\Auth\Permissions;
use App\Helpers\Flash;
use App\Utils\AuditLogger;
use App\Localization\Lang;

Lang::setLanguage(LANG ?? 'de');

if (!Permissions::check('full_admin')) {
    Flash::set('error', 'no-permissions');
    header("Location: /admin/users/roles/index.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id <= 0) {
        Flash::set('role', 'invalid-id');
        header("Location: /admin/users/roles/index.php");
        exit;
    }

    try {
        $checkStmt = $pdo->prepare("SELECT id FROM intra_users_roles WHERE id = :id");
        $checkStmt->execute([':id' => $id]);
        if (!$checkStmt->fetch()) {
            Flash::set('role', 'not-found');
            header("Location: /admin/users/roles/index.php");
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM intra_users_roles WHERE id = :id");
        $stmt->execute([':id' => $id]);

        Flash::set('role', 'deleted');
        $auditLogger = new AuditLogger($pdo);
        $auditLogger->log($_SESSION['userid'], __('auditlog.role_deleted', [$id]), NULL, __('roles'), 1);
        header("Location: /admin/users/roles/index.php");
        exit;
    } catch (PDOException $e) {
        error_log("PDO Delete Error: " . $e->getMessage());
        Flash::set('error', 'exception');
        header("Location: /admin/users/roles/index.php");
        exit;
    }
} else {
    header("Location: /admin/users/roles/index.php");
    exit;
}
