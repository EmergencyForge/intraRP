<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

use App\Auth\Permissions;
use App\Helpers\Flash;
use App\Utils\AuditLogger;

if (!Permissions::check('admin')) {
    Flash::set('error', 'no-permissions');
    header("Location: /admin/enotf/management/ziele/index.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id <= 0) {
        Flash::set('target', 'invalid-id');
        header("Location: /admin/enotf/management/ziele/index.php?error=invalid-id");
        exit;
    }

    try {
        $checkStmt = $pdo->prepare("SELECT id FROM intra_edivi_ziele WHERE id = :id");
        $checkStmt->execute([':id' => $id]);
        if (!$checkStmt->fetch()) {
            Flash::set('target', 'not-found');
            header("Location: /admin/enotf/management/ziele/index.php");
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM intra_edivi_ziele WHERE id = :id");
        $stmt->execute([':id' => $id]);

        Flash::set('target', 'deleted');
        $auditLogger = new AuditLogger($pdo);
        $auditLogger->log($_SESSION['userid'], 'Ziel gelöscht [ID: ' . $id . ']', NULL, 'Ziele', 1);
        header("Location: /admin/enotf/management/ziele/index.php");
        exit;
    } catch (PDOException $e) {
        error_log("PDO Delete Error: " . $e->getMessage());
        Flash::set('error', 'exception');
        header("Location: /admin/enotf/management/ziele/index.php");
        exit;
    }
} else {
    header("Location: /admin/enotf/management/ziele/index.php");
    exit;
}
