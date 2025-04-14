<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';
if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    header("Location: /admin/login.php");
    exit();
}

use App\Auth\Permissions;
use App\Helpers\Flash;
use App\Utils\AuditLogger;
use App\Localization\Lang;

Lang::setLanguage(LANG ?? 'de');

if (!Permissions::check(['admin', 'users.delete'])) {
    Flash::set('error', 'no-permissions');
    header("Location: /admin/users/list.php");
}

$userid = $_SESSION['userid'];

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM intra_users WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

Flash::set('user', 'deleted');
$auditLogger = new AuditLogger($pdo);
$auditLogger->log($userid, __('auditlog.user_deleted', [$id]), NULL, __('auditlog.users'), 1);
header('Location: /admin/users/list.php');
exit;
