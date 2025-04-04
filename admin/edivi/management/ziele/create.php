<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

use App\Auth\Permissions;
use App\Helpers\Flash;

if (!Permissions::check('admin')) {
    Flash::set('error', 'no-permissions');
    header("Location: /admin/edivi/management/ziele/index.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $identifier = trim($_POST['identifier'] ?? '');
    $priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 0;
    $transport = isset($_POST['transport']) ? 1 : 0;
    $active = isset($_POST['active']) ? 1 : 0;

    if (empty($name) || empty($identifier)) {
        Flash::set('error', 'missing-fields');
        header("Location: /admin/edivi/management/ziele/index.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO intra_edivi_ziele (name, identifier, priority, transport, active) VALUES (:name, :identifier, :priority, :transport, :active)");
        $stmt->execute([
            ':name' => $name,
            ':identifier' => $identifier,
            ':priority' => $priority,
            ':transport' => $transport,
            ':active' => $active
        ]);

        Flash::set('target', 'created');
        header("Location: /admin/edivi/management/ziele/index.php");
        exit;
    } catch (PDOException $e) {
        error_log("PDO Insert Error: " . $e->getMessage());
        Flash::set('error', 'exception');
        header("Location: /admin/edivi/management/ziele/index.php");
        exit;
    }
} else {
    header("Location: /admin/edivi/management/ziele/index.php");
    exit;
}
