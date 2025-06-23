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

if (!Permissions::check(['admin', 'edivi.view'])) {
    Flash::set('error', 'no-permissions');
    header("Location: " . BASE_PATH . "admin/index.php");
}

$stmt = $pdo->prepare("SELECT * FROM intra_edivi WHERE id = :id");
$stmt->bindParam(':id', $_GET['id']);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (count($row) == 0) {
    Flash::set('edivi', 'not-found');
    header("Location: " . BASE_PATH . "admin/enotf/list.php");
}

$ist_freigegeben = ($row['freigegeben'] == 1);

$row['last_edit'] = (!empty($row['last_edit']))
    ? (new DateTime($row['last_edit']))->format('d.m.Y H:i')
    : "Noch nicht bearbeitet";

$old_status = $row['protokoll_status'];

if (isset($_POST['new']) && $_POST['new'] == 1) {
    $bearbeiter = $_POST['bearbeiter'];
    $protokoll_status = $_POST['protokoll_status'];
    $qmkommentar = $_POST['qmkommentar'];

    switch ($protokoll_status) {
        case 0:
            $status_klar = "Ungesehen";
            $statusstring = '<span class="badge" style="line-height: var(--bs-body-line-height); border-radius: 0;">Ungesehen</span>';
            break;
        case 1:
            $status_klar = "in Prüfung";
            $statusstring = '<span class="badge text-bg-warning" style="line-height: var(--bs-body-line-height); border-radius: 0;">in Prüfung</span>';
            break;
        case 2:
            $status_klar = "Freigegeben";
            $statusstring = '<span class="badge text-bg-success" style="line-height: var(--bs-body-line-height); border-radius: 0;">Freigegeben</span>';
            break;
        case 3:
            $status_klar = "Ungenügend";
            $statusstring = '<span class="badge text-bg-danger" style="line-height: var(--bs-body-line-height); border-radius: 0;">Ungenügend</span>';
            break;
    }

    if ($protokoll_status != $old_status) {
        $logEntries[] = ['id' => $_GET['id'], 'kommentar' => $statusstring, 'bearbeiter' => $bearbeiter, 'log_aktion' => 1];
    }

    if (!empty($qmkommentar)) {
        $logEntries[] = ['id' => $_GET['id'], 'kommentar' => $qmkommentar, 'bearbeiter' => $bearbeiter, 'log_aktion' => 0];
    }

    if (!empty($logEntries)) {
        $stmt = $pdo->prepare("INSERT INTO intra_edivi_qmlog (protokoll_id, kommentar, bearbeiter, log_aktion) VALUES (:id, :kommentar, :bearbeiter, :log_aktion)");

        foreach ($logEntries as $entry) {
            $stmt->execute([
                'id' => $_GET['id'],
                'kommentar' => $entry['kommentar'],
                'bearbeiter' => $entry['bearbeiter'],
                'log_aktion' => $entry['log_aktion']
            ]);
        }
    }

    $auditLogger = new AuditLogger($pdo);
    $auditLogger->log($_SESSION['userid'], 'Protokoll aktualisiert [ID: ' . $_GET['id'] . ']', NULL, 'eNOTF', 1);

    $stmt = $pdo->prepare("UPDATE intra_edivi SET bearbeiter = :bearbeiter, protokoll_status = :status WHERE id = :id");
    $stmt->execute([
        'bearbeiter' => $bearbeiter,
        'status' => $protokoll_status,
        'id' => $_GET['id']
    ]);

    echo "<script>window.onload = function() { window.close(); }</script>";
}

$prot_url = "https://" . SYSTEM_URL . "/admin/enotf/view.php?id=" . $row['id'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>[#<?= $row['enr'] . "] " . $row['patname'] ?> &rsaquo; QM-AKTIONEN &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/divi.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/admin.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <script src="<?= BASE_PATH ?>vendor/components/jquery/jquery.min.js"></script>
    <script src="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- html2canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="<?= BASE_PATH ?>assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="<?= BASE_PATH ?>assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_PATH ?>assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="<?php echo SYSTEM_NAME ?>" />
    <link rel="manifest" href="<?= BASE_PATH ?>assets/favicon/site.webmanifest" />
    <!-- Metas -->
    <meta name="theme-color" content="#ffaf2f" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="<?= $prot_url ?>" />
    <meta property="og:title" content="[#<?= $row['enr'] . "] " . $row['patname'] ?> &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />

</head>

<body data-bs-theme="dark">
    <form name="form" method="post" action="">
        <input type="hidden" name="new" value="1" />
        <div class="container-fluid" id="edivi__container">
            <div class="row h-100">
                <div class="col">
                    <div class="row edivi__box">
                        <div class="col">
                            <h5 class="mb-3">QM-Funktionen [#<?= $row['enr'] ?>]</h5>
                            <div class="row mt-2 mb-1">
                                <div class="col-3 fw-bold">Gesichtet von</div>
                                <div class="col"><input style="border-radius: 0 !important" type="text" name="bearbeiter" id="bearbeiter" class="w-100 form-control edivi__admin" value="<?= $_SESSION['cirs_user'] ?>" readonly></div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-3 fw-bold">Status</div>
                                <div class="col">
                                    <select name="protokoll_status" id="protokoll_status" class="form-select w-100 edivi__admin" style="border-radius: 0 !important">
                                        <option value="0" <?php echo ($row['protokoll_status'] == 0 ? 'selected' : '') ?>>Ungesehen</option>
                                        <option value="1" <?php echo ($row['protokoll_status'] == 1 ? 'selected' : '') ?>>in Prüfung</option>
                                        <option value="2" <?php echo ($row['protokoll_status'] == 2 ? 'selected' : '') ?>>Freigegeben</option>
                                        <option value="3" <?php echo ($row['protokoll_status'] == 3 ? 'selected' : '') ?>>Ungenügend</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-3 fw-bold">Bemerkung</div>
                                <div class="col">
                                    <textarea name="qmkommentar" id="qmkommentar" rows="8" class="w-100 form-control edivi__admin" style="resize: none"></textarea>
                                </div>
                            </div>
                            <div class=" row mt-5 mb-4">
                                <div class="col text-center">
                                    <input class="btn btn-success" name="submit" type="submit" value="Speichern" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php if (!Permissions::check(['admin', 'edivi.edit'])) : ?>
        <script>
            window.close();
        </script>
    <?php endif; ?>
    <script>
        document.getElementById('delete-button').addEventListener('click', function() {
            window.close();
        });
    </script>
    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
</body>

</html>