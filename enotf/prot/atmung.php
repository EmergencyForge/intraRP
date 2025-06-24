<?php
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_samesite', 'None');
    ini_set('session.cookie_secure', '1');
}

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

<body data-page="atmung">
    <?php
    include __DIR__ . '/../../assets/components/enotf/topbar.php';
    ?>
    <form name="form" method="post" action="">
        <input type="hidden" name="new" value="1" />
        <div class="container-fluid" id="edivi__container">
            <div class="row h-100">
                <?php include __DIR__ . '/../../assets/components/enotf/nav.php'; ?>
                <div class="col" id="edivi__content">
                    <div class=" row">
                        <div class="col">
                            <div class="row edivi__box">
                                <h5 class="text-light px-2 py-1">Diagnostik Atmung</h5>
                                <div class="col">
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="b_symptome" class="edivi__description">Atmung</label>
                                            <?php
                                            if ($daten['b_symptome'] === NULL) {
                                            ?>
                                                <select name="b_symptome" id="b_symptome" class="w-100 form-select edivi__input-check" required>
                                                    <option disabled hidden selected>Symptomauswahl</option>
                                                    <option value="0">unauffällig</option>
                                                    <option value="1">Dyspnoe</option>
                                                    <option value="2">Apnoe</option>
                                                    <option value="3">Schnappatmung</option>
                                                    <option value="4">Andere pathol.</option>
                                                    <option value="99">nicht untersucht</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="b_symptome" id="b_symptome" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>Symptomauswahl</option>
                                                    <option value="0" <?php echo ($daten['b_symptome'] == 0 ? 'selected' : '') ?>>unauffällig</option>
                                                    <option value="1" <?php echo ($daten['b_symptome'] == 1 ? 'selected' : '') ?>>Dyspnoe</option>
                                                    <option value="2" <?php echo ($daten['b_symptome'] == 2 ? 'selected' : '') ?>>Apnoe</option>
                                                    <option value="3" <?php echo ($daten['b_symptome'] == 3 ? 'selected' : '') ?>>Schnappatmung</option>
                                                    <option value="4" <?php echo ($daten['b_symptome'] == 4 ? 'selected' : '') ?>>Andere pathol.</option>
                                                    <option value="99" <?php echo ($daten['b_symptome'] == 99 ? 'selected' : '') ?>>nicht untersucht</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col">
                                            <label for="b_auskult" class="edivi__description">Auskultation</label>
                                            <?php
                                            if ($daten['b_auskult'] === NULL) {
                                            ?>
                                                <select name="b_auskult" id="b_auskult" class="w-100 form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">unauffällig</option>
                                                    <option value="1">Spastik</option>
                                                    <option value="2">Stridor</option>
                                                    <option value="3">Rasselgeräusche</option>
                                                    <option value="4">Andere pathol.</option>
                                                    <option value="99">nicht untersucht</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="b_auskult" id="b_auskult" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['b_auskult'] == 0 ? 'selected' : '') ?>>unauffällig</option>
                                                    <option value="1" <?php echo ($daten['b_auskult'] == 1 ? 'selected' : '') ?>>Spastik</option>
                                                    <option value="2" <?php echo ($daten['b_auskult'] == 2 ? 'selected' : '') ?>>Stridor</option>
                                                    <option value="3" <?php echo ($daten['b_auskult'] == 3 ? 'selected' : '') ?>>Rasselgeräusche</option>
                                                    <option value="4" <?php echo ($daten['b_auskult'] == 4 ? 'selected' : '') ?>>Andere pathol.</option>
                                                    <option value="99" <?php echo ($daten['b_auskult'] == 99 ? 'selected' : '') ?>>nicht untersucht</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="spo2" class="edivi__description">SpO<sub>2</sub></label>
                                            <div class="row">
                                                <div class="col"><input class="w-100 vitalparam form-control" type="text" placeholder="0" name="spo2" id="spo2" value="<?= $daten['spo2'] ?>" style="display:inline"> <small>%</small></div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <label for="atemfreq" class="edivi__description">Atemfrequenz</label>
                                            <div class="row">
                                                <div class="col"><input class="w-100 vitalparam form-control" type="text" name="atemfreq" id="atemfreq" placeholder="0" value="<?= $daten['atemfreq'] ?>" style="display:inline"> <small>/min</small></div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <label for="etco2" class="edivi__description">etCO<sub>2</sub></label>
                                            <div class="row">
                                                <div class="col"><input class="w-100 vitalparam form-control" type="text" name="etco2" id="etco2" placeholder="0" value="<?= $daten['etco2'] ?>" style="display:inline"> <small>mmHg</small></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row edivi__box">
                                <h5 class="text-light px-2 py-1">Maßnahmen Atmung</h5>
                                <div class="col">
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="b_beatmung" class="edivi__description">Beatmung</label>
                                            <?php
                                            if ($daten['b_beatmung'] === NULL) {
                                            ?>
                                                <select name="b_beatmung" id="b_beatmung" class="w-100 form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="4">keine</option>
                                                    <option value="0">Spontanatmung</option>
                                                    <option value="1">Assistierte Beatmung</option>
                                                    <option value="2">Kontrollierte Beatmung</option>
                                                    <option value="3">Maschinelle Beatmung</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="b_beatmung" id="b_beatmung" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="4" <?php echo ($daten['b_beatmung'] == 4 ? 'selected' : '') ?>>keine</option>
                                                    <option value="0" <?php echo ($daten['b_beatmung'] == 0 ? 'selected' : '') ?>>Spontanatmung</option>
                                                    <option value="1" <?php echo ($daten['b_beatmung'] == 1 ? 'selected' : '') ?>>Assistierte Beatmung</option>
                                                    <option value="2" <?php echo ($daten['b_beatmung'] == 2 ? 'selected' : '') ?>>Kontrollierte Beatmung</option>
                                                    <option value="3" <?php echo ($daten['b_beatmung'] == 3 ? 'selected' : '') ?>>Maschinelle Beatmung</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
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
</body>

</html>