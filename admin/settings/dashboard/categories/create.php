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

if (!Permissions::check(['admin', 'dashboard.manage'])) {
    Flash::set('error', 'no-permissions');
    header("Location: /admin/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $priority = (int)($_POST['priority'] ?? 0);

    if (empty($title)) {
        Flash::set('error', 'missing-fields');
        header("Location: /admin/settings/dashboard/index.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO intra_dashboard_categories (title, priority) VALUES (:title, :priority)");
        $stmt->execute([
            ':title' => $title,
            ':priority' => $priority,
        ]);

        Flash::set('dashboard.category', 'created');
        $auditLogger = new AuditLogger($pdo);
        $auditLogger->log($_SESSION['userid'], __('auditlog.category_created'), __('auditlog.category_created_details', [$title]), __('auditlog.dashboard'), 1);
        header("Location: /admin/settings/dashboard/index.php");
        exit;
    } catch (PDOException $e) {
        error_log("Category creation failed: " . $e->getMessage());
        Flash::set('error', 'exception');
        header("Location: /admin/settings/dashboard/index.php");
        exit;
    }
} else {
    header("Location: /admin/settings/dashboard/index.php");
    exit;
}
