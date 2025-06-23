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

    $name = trim($_POST['name'] ?? '');
    $kennzeichen = trim($_POST['kennzeichen'] ?? '');
    $veh_type = trim($_POST['veh_type'] ?? '');
    $identifier = trim($_POST['identifier'] ?? '');
    $priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 0;
    $rd_type = isset($_POST['rd_type']) ? (int)$_POST['rd_type'] : 0;
    $active = isset($_POST['active']) ? 1 : 0;

    if (empty($name) || empty($veh_type) || empty($identifier)) {
        Flash::set('error', 'missing-fields');
        header("Location: " . BASE_PATH . "admin/settings/fahrzeuge/fahrzeuge/index.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO intra_fahrzeuge (name, kennzeichen, veh_type, identifier, priority, rd_type, active) VALUES (:name, :kennzeichen, :veh_type, :identifier, :priority, :rd_type, :active)");
        $stmt->execute([
            ':name' => $name,
            ':kennzeichen' => $kennzeichen,
            ':veh_type' => $veh_type,
            ':identifier' => $identifier,
            ':priority' => $priority,
            ':rd_type' => $rd_type,
            ':active' => $active
        ]);

        Flash::set('vehicle', 'created');
        $auditLogger = new AuditLogger($pdo);
        $auditLogger->log($_SESSION['userid'], 'Fahrzeug erstellt ', 'Name: ' . $name . ' | Typ: ' . $veh_type, 'Fahrzeuge', 1);
        header("Location: " . BASE_PATH . "admin/settings/fahrzeuge/fahrzeuge/index.php");
        exit;
    } catch (PDOException $e) {
        error_log("PDO Insert Error: " . $e->getMessage());
        Flash::set('error', 'exception');
        header("Location: " . BASE_PATH . "admin/settings/fahrzeuge/fahrzeuge/index.php");
        exit;
    }
} else {
    header("Location: " . BASE_PATH . "admin/settings/fahrzeuge/fahrzeuge/index.php");
    exit;
}
