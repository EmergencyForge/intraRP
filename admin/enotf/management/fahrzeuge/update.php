<?php
session_start();
require_once __DIR__ . '/../../../../assets/config/config.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require __DIR__ . '/../../../../assets/config/database.php';

use App\Auth\Permissions;
use App\Helpers\Flash;
use App\Utils\AuditLogger;

if (!Permissions::check('admin')) {
    Flash::set('error', 'no-permissions');
    header("Location: /admin/enotf/management/fahrzeuge/index.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $veh_type = trim($_POST['veh_type'] ?? '');
    $priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 0;
    $doctor = isset($_POST['doctor']) ? 1 : 0;
    $active = isset($_POST['active']) ? 1 : 0;
    $identifier = trim($_POST['identifier'] ?? '');

    if ($id <= 0 || empty($name) || empty($veh_type) || empty($identifier)) {
        Flash::set('error', 'missing-fields');
        header("Location: /admin/enotf/management/fahrzeuge/index.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE intra_edivi_fahrzeuge SET name = :name, veh_type = :veh_type, identifier = :identifier, priority = :priority, doctor = :doctor, active = :active WHERE id = :id");

        $stmt->execute([
            ':name' => $name,
            ':veh_type' => $veh_type,
            ':identifier' => $identifier,
            ':priority' => $priority,
            ':doctor' => $doctor,
            ':active' => $active,
            ':id' => $id
        ]);

        Flash::set('success', 'updated');
        $auditLogger = new AuditLogger($pdo);
        $auditLogger->log($_SESSION['userid'], 'Fahrzeug aktualisiert [ID: ' . $id . ']', NULL, 'Fahrzeuge', 1);
        header("Location: /admin/enotf/management/fahrzeuge/index.php");
        exit;
    } catch (PDOException $e) {
        error_log("PDO Error: " . $e->getMessage());
        Flash::set('error', 'exception');
        header("Location: /admin/enotf/management/fahrzeuge/index.php");
        exit;
    }
} else {
    header("Location: /admin/enotf/management/fahrzeuge/index.php");
    exit;
}
