<?php
session_start();
require_once __DIR__ . '/../../assets/config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../assets/config/database.php';

use App\Auth\Permissions;

$daten = array();

if (isset($_GET['enr'])) {
    $queryget = "SELECT * FROM intra_edivi WHERE enr = :enr";
    $stmt = $pdo->prepare($queryget);
    $stmt->execute(['enr' => $_GET['enr']]);

    $daten = $stmt->fetch(PDO::FETCH_ASSOC);

    if (count($daten) == 0) {
        header("Location: " . BASE_PATH . "enotf/");
        exit();
    }
} else {
    header("Location: " . BASE_PATH . "enotf/");
    exit();
}

if ($daten['freigegeben'] == 1) {
    $ist_freigegeben = true;
} else {
    $ist_freigegeben = false;
}

$daten['last_edit'] = !empty($daten['last_edit']) ? (new DateTime($daten['last_edit']))->format('d.m.Y H:i') : NULL;

$enr = $daten['enr'];

$prot_url = "https://" . SYSTEM_URL . "/enotf/prot/index.php?enr=" . $enr;

date_default_timezone_set('Europe/Berlin');
$currentTime = date('H:i');
$currentDate = date('d.m.Y');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>[#<?= $daten['enr'] ?>] &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
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
    <meta property="og:title" content="[#<?= $daten['enr'] ?>] &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
</head>

<body data-page="stammdaten">
    <div class="container-fluid" id="edivi__topbar">
        <div class="row">
            <div class="col"><a title="Zurück zum Start" href="<?= BASE_PATH ?>enotf/index.php" id="home"><i class="las la-home"></i></a>
                <?php if (Permissions::check(['admin', 'edivi.edit'])) : ?>
                    <a title="QM-Aktionen öffnen" href="<?= BASE_PATH ?>admin/enotf/qm-actions.php?id=<?= $daten['id'] ?>" id="qma" target="_blank"><i class="las la-exclamation"></i></a> <a title="QM-Log öffnen" href="<?= BASE_PATH ?>admin/enotf/qm-log.php?id=<?= $daten['id'] ?>" id="qml" target="_blank"><i class="las la-paperclip"></i></a>
                <?php endif; ?>
            </div>
            <div class="col text-end d-flex justify-content-end align-items-center">
                <div class="d-flex flex-column align-items-end me-3">
                    <span id="current-time"><?= $currentTime ?></span>
                    <span id="current-date"><?= $currentDate ?></span>
                </div>
                <a href="https://github.com/intraRP/intraRP" target="_blank">
                    <img src="https://dev.intrarp.de/assets/img/defaultLogo.webp" alt="intraRP Logo" height="64px" width="auto">
                </a>
            </div>
        </div>
    </div>
    <?php if ($ist_freigegeben) : ?>
        <div class="container-full edivi__notice edivi__notice-freigeber">
            <div class="row">
                <div class="col-1 text-end"><i class="las la-info"></i></div>
                <div class="col">
                    Das Protokoll wurde durch <strong><?= $daten['freigeber_name'] ?></strong> am <strong><?= $daten['last_edit'] ?></strong> Uhr freigegeben. Es kann nicht mehr bearbeitet werden.
                </div>
            </div>
        </div>
    <?php endif; ?>
    <form name="form" method="post" action="">
        <input type="hidden" name="new" value="1" />
        <div class="container-fluid" id="edivi__container">
            <div class="row h-100">
                <?php include __DIR__ . '/../../assets/components/enotf/nav.php'; ?>
                <div class="col" id="edivi__content">
                    <div class=" row">
                        <div class="col">
                            <div class="row shadow edivi__box">
                                <h5 class="text-light px-2 py-1">Patientendaten</h5>
                                <div class="col">
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="patname" class="edivi__description">Name</label>
                                            <input type="text" name="patname" id="patname" placeholder="Max Mustermann" class="w-100 form-control" value="<?= $daten['patname'] ?>">
                                        </div>
                                        <div class="col">
                                            <label for="patgebdat" class="edivi__description">Geburtsdatum</label>
                                            <input type="date" name="patgebdat" id="patgebdat" class="w-100 form-control" value="<?= $daten['patgebdat'] ?>">
                                        </div>
                                        <div class="col-1">
                                            <label for="_AGE_" class="edivi__description">Alter</label>
                                            <input type="text" name="_AGE_" id="_AGE_" class="w-100 form-control" value="0" readonly>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="patsex" class="edivi__description">Geschlecht</label>
                                            <?php
                                            if ($daten['patsex'] === NULL) {
                                            ?>
                                                <select name="patsex" id="patsex" class="w-100 form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">männlich</option>
                                                    <option value="1">weiblich</option>
                                                    <option value="2">divers</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="patsex" id="patsex" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['patsex'] == 0 ? 'selected' : '') ?>>männlich</option>
                                                    <option value="1" <?php echo ($daten['patsex'] == 1 ? 'selected' : '') ?>>weiblich</option>
                                                    <option value="2" <?php echo ($daten['patsex'] == 2 ? 'selected' : '') ?>>divers</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row shadow edivi__box">
                                <h5 class="text-light px-2 py-1 edivi__group-check">Einsatzdaten</h5>
                                <div class="col">
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="enr" class="edivi__description">Einsatznummer</label>
                                            <input type="text" name="enr" id="enr" class="w-100 form-control" value="<?= $_GET['enr'] ?>" readonly>
                                        </div>
                                        <div class="col">
                                            <label for="edatum" class="edivi__description">Einsatzdatum</label>
                                            <input type="date" name="edatum" id="edatum" class="w-100 form-control edivi__input-check" value="<?= $daten['edatum'] ?>" required>
                                        </div>
                                        <div class="col">
                                            <label for="ezeit" class="edivi__description">Einsatzzeit</label>
                                            <input type="time" name="ezeit" id="ezeit" class="w-100 form-control edivi__input-check" value="<?= $daten['ezeit'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="eort" class="edivi__description">Einsatzort</label>
                                            <input type="text" name="eort" id="eort" class="w-100 form-control edivi__input-check" placeholder="Einsatzort" value="<?= $daten['eort'] ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </form>
    <?php
    include __DIR__ . '/../../assets/functions/enotf/notify.php';
    include __DIR__ . '/../../assets/functions/enotf/field_checks.php';
    include __DIR__ . '/../../assets/functions/enotf/clock.php';
    ?>
    <?php if ($ist_freigegeben) : ?>
        <script>
            var formElements = document.querySelectorAll('input, textarea');
            var selectElements2 = document.querySelectorAll('select');
            var inputElements2 = document.querySelectorAll('.btn-check');
            var inputElements3 = document.querySelectorAll('.form-check-input');

            formElements.forEach(function(element) {
                element.setAttribute('readonly', 'readonly');
            });

            selectElements2.forEach(function(element) {
                element.setAttribute('disabled', 'disabled');
            });

            inputElements2.forEach(function(element) {
                element.setAttribute('disabled', 'disabled');
            });

            inputElements3.forEach(function(element) {
                element.setAttribute('disabled', 'disabled');
            });
        </script>
    <?php endif; ?>
    <script>
        function calculateAge(birthDateString) {
            const birthDate = new Date(birthDateString);
            const today = new Date();

            if (isNaN(birthDate)) return 0;

            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();

            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            return age >= 0 ? age : 0;
        }

        function updateAge() {
            const birthDateValue = document.getElementById('patgebdat').value;
            const age = calculateAge(birthDateValue);
            document.getElementById('_AGE_').value = age;
        }

        document.addEventListener('DOMContentLoaded', updateAge);
        document.getElementById('patgebdat').addEventListener('input', updateAge);
    </script>
</body>

</html>