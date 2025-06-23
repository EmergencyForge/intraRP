<?php
session_start();
require_once __DIR__ . '/../../../../assets/config/config.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require __DIR__ . '/../../../../assets/config/database.php';

use App\Auth\Permissions;
use App\Helpers\Flash;
use App\Utils\AuditLogger;

if (!Permissions::check(['admin', 'vehicles.manage'])) {
    Flash::set('error', 'no-permissions');
    header("Location: " . BASE_PATH . "admin/settings/fahrzeuge/fahrzeuge/index.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $kennzeichen = trim($_POST['kennzeichen'] ?? '');
    $veh_type = trim($_POST['veh_type'] ?? '');
    $priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 0;
    $rd_type = isset($_POST['rd_type']) ? (int)$_POST['rd_type'] : 0;
    $active = isset($_POST['active']) ? 1 : 0;
    $identifier = trim($_POST['identifier'] ?? '');

    if ($id <= 0 || empty($name) || empty($veh_type) || empty($identifier)) {
        Flash::set('error', 'missing-fields');
        header("Location: " . BASE_PATH . "admin/settings/fahrzeuge/fahrzeuge/index.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE intra_fahrzeuge SET name = :name, kennzeichen = :kennzeichen, veh_type = :veh_type, identifier = :identifier, priority = :priority, rd_type = :rd_type, active = :active WHERE id = :id");

        $stmt->execute([
            ':name' => $name,
            ':kennzeichen' => $kennzeichen,
            ':veh_type' => $veh_type,
            ':identifier' => $identifier,
            ':priority' => $priority,
            ':rd_type' => $rd_type,
            ':active' => $active,
            ':id' => $id
        ]);

        Flash::set('success', 'updated');
        $auditLogger = new AuditLogger($pdo);
        $auditLogger->log($_SESSION['userid'], 'Fahrzeug aktualisiert [ID: ' . $id . ']', NULL, 'Fahrzeuge', 1);
        header("Location: " . BASE_PATH . "admin/settings/fahrzeuge/fahrzeuge/index.php");
        exit;
    } catch (PDOException $e) {
        error_log("PDO Error: " . $e->getMessage());
        Flash::set('error', 'exception');
        header("Location: " . BASE_PATH . "admin/settings/fahrzeuge/fahrzeuge/index.php");
        exit;
    }
} else {
    header("Location: " . BASE_PATH . "admin/settings/fahrzeuge/fahrzeuge/index.php");
    exit;
}
