<?php
session_start();
require_once __DIR__ . '/../assets/config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../assets/config/database.php';

$prot_url = "https://" . SYSTEM_URL . "/enotf/index.php";

date_default_timezone_set('Europe/Berlin');
$currentTime = date('H:i');
$currentDate = date('d.m.Y');

if (!isset($_SESSION['fahrername']) || !isset($_SESSION['protfzg'])) {
    header("Location: " . BASE_PATH . "enotf/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/divi.min.css" />
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
    <meta name="theme-color" content="#ffaf2f" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="<?= $prot_url ?>" />
    <meta property="og:title" content="eNOTF &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
</head>

<body style="overflow-x:hidden" id="edivi__login">
    <form name="form" method="post" action="<?= BASE_PATH ?>assets/functions/enrbridge.php" id="enrForm">
        <input type="hidden" name="new" value="1" />
        <input type="hidden" name="action" value="openOrCreate" />
        <input type="hidden" name="prot_by" id="prot_by" value="" />
        <div class="container-fluid" id="edivi__container">
            <div class="row h-100">
                <div class="col" id="edivi__content">
                    <div class="hr my-5" style="color:transparent"></div>
                    <div class="row mx-5">
                        <div class="col">
                            <input type="text" class="form-control mb-3" name="enr" id="enr" placeholder="Einsatznummer" required />
                        </div>
                    </div>
                    <div class="row my-5 mx-5">
                        <div class="col">
                            <button class="edivi__nidabutton-primary w-100" id="rdprot" name="rdprot" onclick="setProtBy(0)">Rettungsdienst-Protokoll</button>
                        </div>
                    </div>
                    <div class="row my-5 mx-5">
                        <div class="col">
                            <button class="edivi__nidabutton-primary w-100" id="naprot" name="naprot" onclick="setProtBy(1)">Notarzt-Protokoll</button>
                        </div>
                    </div>
                    <div class="row my-5 mx-5">
                        <div class="col text-center">
                            <a href="overview.php" class="edivi__nidabutton-secondary w-100" style="display:inline-block">zurück</a>
                        </div>
                    </div>
                </div>
            </div>
    </form>
    <?php
    include __DIR__ . '/../assets/functions/enotf/notify.php';
    ?>
    <script>
        function setProtBy(value) {
            document.getElementById('prot_by').value = value;
        }
    </script>
    <script>
        document.getElementById('enr').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9_]/g, '');
        });
    </script>
    <script>
        document.getElementById('enrForm').addEventListener('submit', function(e) {
            const protBy = document.getElementById('prot_by').value;
            if (protBy !== '0' && protBy !== '1') {
                e.preventDefault();
                alert("Bitte wähle ein Protokoll aus (RD oder NA).");
            }
        });
    </script>
    <script>
        var modalCloseButton = document.querySelector('#myModal4 .btn-close');
        var freigeberInput = document.getElementById('freigeber');

        modalCloseButton.addEventListener('click', function() {
            freigeberInput.value = '';
        });
    </script>
</body>

</html>