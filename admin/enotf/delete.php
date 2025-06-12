<?php
session_start();
require_once __DIR__ . '/../../assets/config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../assets/config/database.php';
if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: " . BASE_PATH . "admin/login.php");
    exit();
}

use App\Auth\Permissions;
use App\Helpers\Flash;
use App\Utils\AuditLogger;

if (!Permissions::check(['admin', 'edivi.edit'])) {
    Flash::set('error', 'no-permissions');
    header("Location: " . BASE_PATH . "admin/enotf/list.php");
}

$userid = $_SESSION['userid'];

$id = $_GET['id'];

$stmt = $pdo->prepare("UPDATE intra_edivi SET hidden = 1, protokoll_status = 3 WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

Flash::set('edivi', 'deleted');
$auditLogger = new AuditLogger($pdo);
$auditLogger->log($userid, 'Protokoll gelöscht [ID: ' . $id . ']', NULL, 'eNOTF', 1);
header("Location: " . BASE_PATH . "admin/enotf/list.php");
exit;
