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

if (!Permissions::check('admin')) {
    Flash::set('error', 'no-permissions');
    header("Location: /admin/personal/management/dienstgrade/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $name_m = trim($_POST['name_m'] ?? '');
    $name_w = trim($_POST['name_w'] ?? '');
    $priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 0;
    $badge = isset($_POST['badge']) && trim($_POST['badge']) !== '' ? trim($_POST['badge']) : NULL;
    $archive = isset($_POST['archive']) ? 1 : 0;

    if (empty($name) || empty($name_m) || empty($name_w)) {
        Flash::set('error', 'missing-fields');
        header("Location: /admin/personal/management/dienstgrade/index.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO intra_mitarbeiter_dienstgrade 
            (name, name_m, name_w, priority, badge, archive) 
            VALUES (:name, :name_m, :name_w, :priority, :badge, :archive)");

        $stmt->execute([
            ':name' => $name,
            ':name_m' => $name_m,
            ':name_w' => $name_w,
            ':priority' => $priority,
            ':badge' => $badge,
            ':archive' => $archive
        ]);

        Flash::set('rank', 'created');
        $auditLogger = new AuditLogger($pdo);
        $auditLogger->log($_SESSION['userid'], __('auditlog.rank_created'), __('auditlog.rank_created_details', [$name]), __('auditlog.ranks'), 1);
        header("Location: /admin/personal/management/dienstgrade/index.php");
        exit;
    } catch (PDOException $e) {
        error_log("PDO Error (create dienstgrad): " . $e->getMessage());
        Flash::set('error', 'exception');
        header("Location: /admin/personal/management/dienstgrade/index.php");
        exit;
    }
} else {
    header("Location: /admin/personal/management/dienstgrade/index.php");
    exit;
}
