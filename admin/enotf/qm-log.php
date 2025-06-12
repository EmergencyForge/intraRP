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


$prot_url = "https://" . SYSTEM_URL . "/admin/enotf/view.php?id=" . $row['id'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>[#<?= $row['enr'] . "] " . $row['patname'] ?> &rsaquo; QM-LOG &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
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
            <h5 class="mb-3 text-center">QM-Log [#<?= $row['enr'] ?>]</h5>
            <div class="row h-100">
                <div class="col">
                    <?php
                    require __DIR__ . '/../../assets/config/database.php';
                    $stmt = $pdo->prepare("SELECT * FROM intra_edivi_qmlog WHERE protokoll_id = :id ORDER BY id ASC");
                    $stmt->bindParam(':id', $_GET['id']);
                    $stmt->execute();
                    $log_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($log_result) == 0) {
                        echo "<div class='row edivi__box'><div class='col'><p class='text-center'>Keine Einträge vorhanden.</p></div></div>";
                    } else {
                        foreach ($log_result as $log_row) {
                            $log_row['timestamp'] = date("d.m.Y H:i", strtotime($log_row['timestamp']));
                            if ($log_row['log_aktion'] == 0) {
                    ?>
                                <div class='row edivi__box edivi__log-comment'>
                                    <div class="col-1 d-flex justify-content-center align-items-center"><i class="las la-info"></i></div>
                                    <div class='col'>
                                        <small style="opacity:.6" class='mb-0'><b><?= $log_row['bearbeiter'] ?></b> | <?= $log_row['timestamp'] ?></small>
                                        <p class='mb-0'><?= $log_row['kommentar'] ?></p>
                                    </div>
                                </div>
                            <?php
                            } else if ($log_row['log_aktion'] == 1) {
                            ?>
                                <div class='row edivi__box edivi__log-comment'>
                                    <div class="col-1 d-flex justify-content-center align-items-center"></div>
                                    <div class='col'>
                                        <small style="opacity:.6" class='mb-0'><b><?= $log_row['bearbeiter'] ?></b> | <?= $log_row['timestamp'] ?></small>
                                        <p class='mb-0'><?= $log_row['kommentar'] ?></p>
                                    </div>
                                </div>
                    <?php
                            }
                        }
                    }
                    ?>
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