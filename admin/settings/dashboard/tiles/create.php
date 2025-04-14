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
    $category = (int)($_POST['category'] ?? 0);
    $title    = trim($_POST['title'] ?? '');
    $url      = trim($_POST['url'] ?? '#');
    $icon     = trim($_POST['icon'] ?? 'external-link-alt');
    $priority = (int)($_POST['priority'] ?? 0);

    if ($category <= 0 || empty($title)) {
        Flash::set('error', 'missing-fields');
        header("Location: /admin/settings/dashboard/index.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO intra_dashboard_tiles (category, title, url, icon, priority)
            VALUES (:category, :title, :url, :icon, :priority)
        ");
        $stmt->execute([
            ':category' => $category,
            ':title'    => $title,
            ':url'      => $url,
            ':icon'     => $icon,
            ':priority' => $priority
        ]);

        Flash::set('dashboard.tile', 'created');
        $auditLogger = new AuditLogger($pdo);
        $auditLogger->log($_SESSION['userid'], __('auditlog.link_created'), __('auditlog.link_created_details', [$title]), __('auditlog.dashboard'), 1);
        header("Location: /admin/settings/dashboard/index.php");
        exit;
    } catch (PDOException $e) {
        error_log("Tile creation error: " . $e->getMessage());
        Flash::set('error', 'exception');
        header("Location: /admin/settings/dashboard/index.php");
        exit;
    }
} else {
    header("Location: /admin/settings/dashboard/index.php");
    exit;
}
