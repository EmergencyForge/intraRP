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

if (!Permissions::check(['admin', 'application.edit'])) {
    Flash::set('error', 'no-permissions');
    header("Location: " . BASE_PATH . "admin/index.php");
}

$caseid = $_GET['antrag'];

// MYSQL QUERY
$stmt = $pdo->prepare("SELECT * FROM intra_antrag_bef WHERE uniqueid = :caseid");
$stmt->bindParam(':caseid', $caseid);
$stmt->execute();
$row = $stmt->fetch();

if (isset($_POST['new']) && $_POST['new'] == 1) {
    $id = $_REQUEST['case_id'];
    $cirs_manager = $_SESSION['cirs_user'] ?? "Fehler Fehler";
    $cirs_status = $_REQUEST['cirs_status'];
    $cirs_text = $_REQUEST['cirs_text'];
    $jetzt = date("Y-m-d H:i:s");

    $auditLogger = new AuditLogger($pdo);

    if ($row['cirs_manager'] != $cirs_manager) {
        $auditLogger->log($_SESSION['userid'], 'Bearbeiter geändert [ID: ' . $id . ']', $cirs_manager, 'Anträge',  1);
    }
    if ($row['cirs_status'] != $cirs_status) {
        $auditLogger->log($_SESSION['userid'], 'Status geändert [ID: ' . $id . ']', 'Neuer Status: ' . $cirs_status, 'Anträge',  1);
    }
    if ($row['cirs_text'] != $cirs_text) {
        $auditLogger->log($_SESSION['userid'], 'Bemerkung geändert [ID: ' . $id . ']', '"' . $cirs_text . '"', 'Anträge',  1);
    }

    $stmt = $pdo->prepare("UPDATE intra_antrag_bef SET cirs_manager = :cirs_manager, cirs_status = :cirs_status, cirs_text = :cirs_text, cirs_time = :jetzt WHERE id = :id");
    $stmt->execute([
        ':cirs_manager' => $cirs_manager,
        ':cirs_status' => $cirs_status,
        ':cirs_text' => $cirs_text,
        ':jetzt' => $jetzt,
        ':id' => $id
    ]);

    header("Refresh:0");
}

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Anträge &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/style.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/admin.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <script src="<?= BASE_PATH ?>vendor/components/jquery/jquery.min.js"></script>
    <script src="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="<?= BASE_PATH ?>assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="<?= BASE_PATH ?>assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_PATH ?>assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="<?php echo SYSTEM_NAME ?>" />
    <link rel="manifest" href="<?= BASE_PATH ?>assets/favicon/site.webmanifest" />
    <!-- Metas -->
    <meta name="theme-color" content="<?php echo SYSTEM_COLOR ?>" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="https://<?php echo SYSTEM_URL . BASE_PATH ?>/dashboard.php" />
    <meta property="og:title" content="<?php echo SYSTEM_NAME ?> - Intranet <?php echo SERVER_CITY ?>" />
    <meta property="og:image" content="<?php echo META_IMAGE_URL ?>" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />

</head>

<body data-bs-theme="dark" data-page="mitarbeiter">
    <?php include "../../assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <h1>Antrag sichten und bearbeiten</h1>
                    <hr class="text-light my-3">
                    <form action="" id="cirs-form" method="post">
                        <input type="hidden" name="new" value="1" />
                        <input type="hidden" name="case_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="cirs_manger" value="<?= $_SESSION['cirs_user'] ?? "Fehler Fehler" ?>">
                        <div class="intra__tile py-2 px-3">
                            <div class="row">
                                <div class="col mb-3">
                                    <label for="name_dn" class="form-label fw-bold">Name und Dienstnummer <span class="text-main-color">*</span></label>
                                    <input type="text" class="form-control" id="name_dn" name="name_dn" placeholder="" value="<?= $row['name_dn'] ?>" required readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col mb-3">
                                    <label for="dienstgrad" class="form-label fw-bold">Aktueller Dienstgrad <span class="text-main-color">*</span></label>
                                    <input type="text" class="form-control" id="dienstgrad" name="dienstgrad" placeholder="" value="<?= $row['dienstgrad'] ?>" required readonly>
                                </div>
                            </div>
                        </div>
                        <hr class="text-light my-3">
                        <div class="intra__tile py-2 px-3">
                            <h5>Schriftlicher Antrag</h5>
                            <div class="mb-3">
                                <textarea class="form-control" id="freitext" name="freitext" rows="5" readonly><?= $row['freitext'] ?></textarea>
                            </div>
                        </div>
                        <hr class="text-light my-3">
                        <div class="intra__tile py-2 px-3">
                            <div class="mb-3">
                                <?php if ($row['cirs_manager'] != NULL) { ?>
                                    <h5>Antrag bearbeitet von: <?= $row['cirs_manager'] ?></h5>
                                <?php } else { ?>
                                    <h5>Noch kein Bearbeiter festgelegt.</h5>
                                <?php } ?>
                            </div>
                            <hr class="text-light my-3">
                            <hr class="text-light my-3">
                            <h5>Bemerkung durch Bearbeiter</h5>
                            <div class="mb-3">
                                <textarea class="form-control" id="cirs_text" name="cirs_text" rows="5"><?= $row['cirs_text'] ?></textarea>
                            </div>
                            <h5>Status setzen</h5>
                            <div class="mb-3">
                                <select class="form-select" id="cirs_status" name="cirs_status" autocomplete="off">
                                    <option value="0" <?php if ($row['cirs_status'] == "0") echo 'selected'; ?>>in Bearbeitung</option>
                                    <option value="1" <?php if ($row['cirs_status'] == "1") echo 'selected'; ?>>Abgelehnt</option>
                                    <option value="2" <?php if ($row['cirs_status'] == "2") echo 'selected'; ?>>Aufgeschoben</option>
                                    <option value="3" <?php if ($row['cirs_status'] == "3") echo 'selected'; ?>>Angenommen</option>
                                </select>
                            </div>
                        </div>
                        <p><input class="mt-4 btn btn-main-color" name="submit" type="submit" value="Änderungen speichern" /></p>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <?php include __DIR__ . "/../../assets/components/footer.php"; ?>
</body>

</html>