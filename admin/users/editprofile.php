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

use App\Helpers\Flash;
use App\Utils\AuditLogger;

$userid = $_SESSION['userid'];

$stmt = $pdo->prepare("SELECT * FROM intra_users WHERE id = :id");
$stmt->bindParam(':id', $userid, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['new']) && $_POST['new'] == 1) {
    $id = $_REQUEST['id'];
    $fullname = trim($_POST['fullname']);
    //$aktenid = isset($_POST['aktenid']) && $_POST['aktenid'] !== '' ? (int)$_POST['aktenid'] : null;

    // if ($aktenid !== null) {
    //     $exists = $pdo->query("SELECT 1 FROM intra_mitarbeiter WHERE id = $aktenid")->fetchColumn();
    //     if (!$exists) {
    //         Flash::set('user', 'member-id-not-found');
    //         header("Location: " . BASE_PATH . "admin/users/editprofile.php");
    //         exit();
    //     }
    // }

    try {
        //$stmt = $pdo->prepare("UPDATE intra_users SET fullname = :fullname, aktenid = :aktenid WHERE id = :id");
        $stmt = $pdo->prepare("UPDATE intra_users SET fullname = :fullname WHERE id = :id");
        $stmt->bindValue(':fullname', $fullname, PDO::PARAM_STR);
        //$stmt->bindValue(':aktenid', $aktenid, $aktenid === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['cirs_user'] = $fullname;
        //$_SESSION['aktenid'] = $aktenid;

        Flash::set('own', 'data-changed');
        $auditLogger = new AuditLogger($pdo);
        $auditLogger->log($userid, 'Daten geändert [ID: ' . $id . ']', null, 'Selbst', 0);
        header("Refresh:0");
        exit();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        Flash::set('error', 'exception');
        header("Location: " . BASE_PATH . "admin/users/editprofile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profil bearbeiten &rsaquo; Administration &rsaquo; <?php echo SYSTEM_NAME ?></title>
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

<body data-bs-theme="dark">
    <?php include "../../assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <h1 class="mb-5">Eigene Daten bearbeiten</h1>
                    <?php

                    if (!isset($_SESSION['cirs_user']) || empty($_SESSION['cirs_user'])) {
                        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
                        echo '<h4 class="alert-heading">Achtung!</h4>';
                        echo 'Du hast noch keinen Namen hinterlegt. <u style="font-weight:bold">Bitte hinterlege deinen Namen jetzt!</u><br>Bei fehlendem Namen kann es zu technischen Problemen kommen.';
                        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>';
                        echo '</div>';
                    }

                    Flash::render();
                    ?>
                    <form name="form" method="post" action="">
                        <div class="intra__tile py-2 px-3">
                            <input type="hidden" name="new" value="1" />
                            <input name="id" type="hidden" value="<?= $row['id'] ?>" />
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="fullname" class="form-label fw-bold">Vor- und Zuname</label>
                                    <input type="text" class="form-control" id="fullname" name="fullname" placeholder="" value="<?= $row['fullname'] ?>">
                                </div>
                                <?php if (!empty($_SESSION['permissions'])): ?>
                                    <!-- <div class="col-6 mb-3">
                                        <label for="aktenid" class="form-label fw-bold">Mitarbeiterakten-ID</label>
                                        <input type="number" class="form-control" id="aktenid" name="aktenid" placeholder="" value="<?= htmlspecialchars($row['aktenid']) ?>">
                                    </div> -->
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3 mx-auto">
                                <input class="mt-4 btn btn-success btn-sm" name="submit" type="submit" value="Änderungen speichern" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <?php include __DIR__ . "/../../assets/components/footer.php"; ?>
</body>

</html>