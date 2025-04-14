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
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = (int)($_POST['id'] ?? 0);
    $category  = (int)($_POST['category'] ?? 0);
    $title     = trim($_POST['title'] ?? '');
    $url       = trim($_POST['url'] ?? '#');
    $icon      = trim($_POST['icon'] ?? 'external-link-alt');
    $priority  = (int)($_POST['priority'] ?? 0);

    if ($id <= 0 || $category <= 0 || empty($title)) {
        Flash::set('error', 'missing-fields');
        header("Location: /admin/settings/dashboard/index.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE intra_dashboard_tiles
            SET title = :title,
                url = :url,
                icon = :icon,
                priority = :priority,
                category = :category
            WHERE id = :id
        ");

        $stmt->execute([
            ':title'    => $title,
            ':url'      => $url,
            ':icon'     => $icon,
            ':priority' => $priority,
            ':category' => $category,
            ':id'       => $id,
        ]);

        Flash::set('success', 'updated');
        $auditLogger = new AuditLogger($pdo);
        $auditLogger->log($_SESSION['userid'], __('auditlog.link_edited', [$id]), NULL, __('auditlog.dashboard'), 1);
        header("Location: /admin/settings/dashboard/index.php");
        exit;
    } catch (PDOException $e) {
        error_log("Tile update failed: " . $e->getMessage());
        Flash::set('error', 'exception');
        header("Location: /admin/settings/dashboard/index.php");
        exit;
    }
} else {
    header("Location: /admin/settings/dashboard/index.php");
    exit;
}
