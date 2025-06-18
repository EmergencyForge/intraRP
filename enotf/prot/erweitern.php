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

<body data-page="erweitern">
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
                                <h5 class="text-light px-2 py-1">Verletzungen</h5>
                                <div class="col">
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="v_muster_k" class="edivi__description">Kopf</label>
                                            <?php
                                            if ($daten['v_muster_k'] === NULL) {
                                            ?>
                                                <select name="v_muster_k" id="v_muster_k" class="w-100 edivi__verletzungen form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">schwer</option>
                                                    <option value="1">mittel</option>
                                                    <option value="2">leicht</option>
                                                    <option value="3">keine</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="v_muster_k" id="v_muster_k" class="w-100 edivi__verletzungen form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['v_muster_k'] == 0 ? 'selected' : '') ?>>schwer</option>
                                                    <option value="1" <?php echo ($daten['v_muster_k'] == 1 ? 'selected' : '') ?>>mittel</option>
                                                    <option value="2" <?php echo ($daten['v_muster_k'] == 2 ? 'selected' : '') ?>>leicht</option>
                                                    <option value="3" <?php echo ($daten['v_muster_k'] == 3 ? 'selected' : '') ?>>keine</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col-3">
                                            <label for="v_muster_k1" class="edivi__description">Offen/Geschl.</label>
                                            <?php if ($daten['v_muster_k1'] === NULL) {
                                            ?>
                                                <select name="v_muster_k1" id="v_muster_k1" class="w-100 form-select">
                                                    <option value="0" selected>---</option>
                                                    <option value="1">offen</option>
                                                    <option value="2">geschl.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="v_muster_k1" id="v_muster_k1" class="w-100 form-select" autocomplete="off">
                                                    <option value="0" <?php echo ($daten['v_muster_k1'] == 0 ? 'selected' : '') ?>>---</option>
                                                    <option value="1" <?php echo ($daten['v_muster_k1'] == 1 ? 'selected' : '') ?>>offen</option>
                                                    <option value="2" <?php echo ($daten['v_muster_k1'] == 2 ? 'selected' : '') ?>>geschl.</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="v_muster_w" class="edivi__description">Wirbelsäule</label>
                                            <?php
                                            if ($daten['v_muster_w'] === NULL) {
                                            ?>
                                                <select name="v_muster_w" id="v_muster_w" class="w-100 edivi__verletzungen form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">schwer</option>
                                                    <option value="1">mittel</option>
                                                    <option value="2">leicht</option>
                                                    <option value="3">keine</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="v_muster_w" id="v_muster_w" class="w-100 edivi__verletzungen form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['v_muster_w'] == 0 ? 'selected' : '') ?>>schwer</option>
                                                    <option value="1" <?php echo ($daten['v_muster_w'] == 1 ? 'selected' : '') ?>>mittel</option>
                                                    <option value="2" <?php echo ($daten['v_muster_w'] == 2 ? 'selected' : '') ?>>leicht</option>
                                                    <option value="3" <?php echo ($daten['v_muster_w'] == 3 ? 'selected' : '') ?>>keine</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col-3">
                                            <label for="v_muster_w1" class="edivi__description"></label>
                                            <?php if ($daten['v_muster_w1'] === NULL) {
                                            ?>
                                                <select name="v_muster_w1" id="v_muster_w1" class="w-100 form-select">
                                                    <option value="0" selected>---</option>
                                                    <option value="1">offen</option>
                                                    <option value="2">geschl.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="v_muster_w1" id="v_muster_w1" class="w-100 form-select" autocomplete="off">
                                                    <option value="0" <?php echo ($daten['v_muster_w1'] == 0 ? 'selected' : '') ?>>---</option>
                                                    <option value="1" <?php echo ($daten['v_muster_w1'] == 1 ? 'selected' : '') ?>>offen</option>
                                                    <option value="2" <?php echo ($daten['v_muster_w1'] == 2 ? 'selected' : '') ?>>geschl.</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="v_muster_t" class="edivi__description">Thorax</label>
                                            <?php
                                            if ($daten['v_muster_t'] === NULL) {
                                            ?>
                                                <select name="v_muster_t" id="v_muster_t" class="w-100 edivi__verletzungen form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">schwer</option>
                                                    <option value="1">mittel</option>
                                                    <option value="2">leicht</option>
                                                    <option value="3">keine</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="v_muster_t" id="v_muster_t" class="w-100 edivi__verletzungen form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['v_muster_t'] == 0 ? 'selected' : '') ?>>schwer</option>
                                                    <option value="1" <?php echo ($daten['v_muster_t'] == 1 ? 'selected' : '') ?>>mittel</option>
                                                    <option value="2" <?php echo ($daten['v_muster_t'] == 2 ? 'selected' : '') ?>>leicht</option>
                                                    <option value="3" <?php echo ($daten['v_muster_t'] == 3 ? 'selected' : '') ?>>keine</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col-3">
                                            <label for="v_muster_t1" class="edivi__description"></label>
                                            <?php if ($daten['v_muster_t1'] === NULL) {
                                            ?>
                                                <select name="v_muster_t1" id="v_muster_t1" class="w-100 form-select">
                                                    <option value="0" selected>---</option>
                                                    <option value="1">offen</option>
                                                    <option value="2">geschl.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="v_muster_t1" id="v_muster_t1" class="w-100 form-select" autocomplete="off">
                                                    <option value="0" <?php echo ($daten['v_muster_t1'] == 0 ? 'selected' : '') ?>>---</option>
                                                    <option value="1" <?php echo ($daten['v_muster_t1'] == 1 ? 'selected' : '') ?>>offen</option>
                                                    <option value="2" <?php echo ($daten['v_muster_t1'] == 2 ? 'selected' : '') ?>>geschl.</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col edivi__verletzungen-col">
                                            <label for="v_muster_a" class="edivi__description">Abdomen</label>
                                            <?php
                                            if ($daten['v_muster_a'] === NULL) {
                                            ?>
                                                <select name="v_muster_a" id="v_muster_a" class="w-100 edivi__verletzungen form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">schwer</option>
                                                    <option value="1">mittel</option>
                                                    <option value="2">leicht</option>
                                                    <option value="3">keine</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="v_muster_a" id="v_muster_a" class="w-100 edivi__verletzungen form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['v_muster_a'] == 0 ? 'selected' : '') ?>>schwer</option>
                                                    <option value="1" <?php echo ($daten['v_muster_a'] == 1 ? 'selected' : '') ?>>mittel</option>
                                                    <option value="2" <?php echo ($daten['v_muster_a'] == 2 ? 'selected' : '') ?>>leicht</option>
                                                    <option value="3" <?php echo ($daten['v_muster_a'] == 3 ? 'selected' : '') ?>>keine</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col-3">
                                            <label for="v_muster_a1" class="edivi__description"></label>
                                            <?php if ($daten['v_muster_a1'] === NULL) {
                                            ?>
                                                <select name="v_muster_a1" id="v_muster_a1" class="w-100 form-select">
                                                    <option value="0" selected>---</option>
                                                    <option value="1">offen</option>
                                                    <option value="2">geschl.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="v_muster_a1" id="v_muster_a1" class="w-100 form-select" autocomplete="off">
                                                    <option value="0" <?php echo ($daten['v_muster_a1'] == 0 ? 'selected' : '') ?>>---</option>
                                                    <option value="1" <?php echo ($daten['v_muster_a1'] == 1 ? 'selected' : '') ?>>offen</option>
                                                    <option value="2" <?php echo ($daten['v_muster_a1'] == 2 ? 'selected' : '') ?>>geschl.</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col edivi__verletzungen-col">
                                            <label for="v_muster_al" class="edivi__description">Obere Extremitäten</label>
                                            <?php
                                            if ($daten['v_muster_al'] === NULL) {
                                            ?>
                                                <select name="v_muster_al" id="v_muster_al" class="w-100 edivi__verletzungen form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">schwer</option>
                                                    <option value="1">mittel</option>
                                                    <option value="2">leicht</option>
                                                    <option value="3">keine</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="v_muster_al" id="v_muster_al" class="w-100 edivi__verletzungen form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['v_muster_al'] == 0 ? 'selected' : '') ?>>schwer</option>
                                                    <option value="1" <?php echo ($daten['v_muster_al'] == 1 ? 'selected' : '') ?>>mittel</option>
                                                    <option value="2" <?php echo ($daten['v_muster_al'] == 2 ? 'selected' : '') ?>>leicht</option>
                                                    <option value="3" <?php echo ($daten['v_muster_al'] == 3 ? 'selected' : '') ?>>keine</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col-3">
                                            <label for="v_muster_al1" class="edivi__description"></label>
                                            <?php if ($daten['v_muster_al1'] === NULL) {
                                            ?>
                                                <select name="v_muster_al1" id="v_muster_al1" class="w-100 form-select">
                                                    <option value="0" selected>---</option>
                                                    <option value="1">offen</option>
                                                    <option value="2">geschl.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="v_muster_al1" id="v_muster_al1" class="w-100 form-select" autocomplete="off">
                                                    <option value="0" <?php echo ($daten['v_muster_al1'] == 0 ? 'selected' : '') ?>>---</option>
                                                    <option value="1" <?php echo ($daten['v_muster_al1'] == 1 ? 'selected' : '') ?>>offen</option>
                                                    <option value="2" <?php echo ($daten['v_muster_al1'] == 2 ? 'selected' : '') ?>>geschl.</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col edivi__verletzungen-col">
                                            <label for="v_muster_bl" class="edivi__description">Untere Extremitäten</label>
                                            <?php
                                            if ($daten['v_muster_bl'] === NULL) {
                                            ?>
                                                <select name="v_muster_bl" id="v_muster_bl" class="w-100 edivi__verletzungen form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">schwer</option>
                                                    <option value="1">mittel</option>
                                                    <option value="2">leicht</option>
                                                    <option value="3">keine</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="v_muster_bl" id="v_muster_bl" class="w-100 edivi__verletzungen form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['v_muster_bl'] == 0 ? 'selected' : '') ?>>schwer</option>
                                                    <option value="1" <?php echo ($daten['v_muster_bl'] == 1 ? 'selected' : '') ?>>mittel</option>
                                                    <option value="2" <?php echo ($daten['v_muster_bl'] == 2 ? 'selected' : '') ?>>leicht</option>
                                                    <option value="3" <?php echo ($daten['v_muster_bl'] == 3 ? 'selected' : '') ?>>keine</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col-3">
                                            <label for="v_muster_bl1" class="edivi__description"></label>
                                            <?php if ($daten['v_muster_bl1'] === NULL) {
                                            ?>
                                                <select name="v_muster_bl1" id="v_muster_bl1" class="w-100 form-select">
                                                    <option value="0" selected>---</option>
                                                    <option value="1">offen</option>
                                                    <option value="2">geschl.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="v_muster_bl1" id="v_muster_bl1" class="w-100 form-select" autocomplete="off">
                                                    <option value="0" <?php echo ($daten['v_muster_bl1'] == 0 ? 'selected' : '') ?>>---</option>
                                                    <option value="1" <?php echo ($daten['v_muster_bl1'] == 1 ? 'selected' : '') ?>>offen</option>
                                                    <option value="2" <?php echo ($daten['v_muster_bl1'] == 2 ? 'selected' : '') ?>>geschl.</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <label for="sz_nrs" class="edivi__description">Schmerzen</label>
                                        <div class="col">
                                            <input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="11" <?php echo ($daten['sz_nrs'] == 11 ? 'checked' : '') ?>> nicht erhoben
                                        </div>
                                        <div class="col">
                                            <input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="13" <?php echo ($daten['sz_nrs'] == 13 ? 'checked' : '') ?>> nicht beurteilbar
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <?php for ($i = 0; $i <= 10; $i++): ?>
                                            <div class="col">
                                                <input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs_<?= $i ?>" value="<?= $i ?>" <?= ($daten['sz_nrs'] !== NULL && $daten['sz_nrs'] === $i ? 'checked' : '') ?>>
                                                <label for="sz_nrs_<?= $i ?>"><?= $i ?></label>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <div class="row">
                                                <div class="col">
                                                    <input type="checkbox" class="btn-check" id="sz_toleranz_1" name="sz_toleranz_1" value="1" <?php echo ($daten['sz_toleranz_1'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                                    <label class="btn btn-sm btn-outline-light w-100" for="sz_toleranz_1">Tolerabel</label>
                                                </div>
                                                <div class="col">
                                                    <input type="checkbox" class="btn-check" id="sz_toleranz_2" name="sz_toleranz_2" value="1" <?php echo ($daten['sz_toleranz_2'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                                    <label class="btn btn-sm btn-outline-light w-100" for="sz_toleranz_2">Nicht tolerabel</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row edivi__box">
                                <h5 class="text-light px-2 py-1">Diagnostik Erweitert</h5>
                                <div class="col">
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="bz" class="edivi__description">Blutzucker</label>
                                            <div class="row">
                                                <div class="col">
                                                    <input class="w-100 vitalparam form-control" type="text" name="bz" id="bz" placeholder="0" value="<?= $daten['bz'] ?>" style="display:inline"> <small>mg/dl</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <label for="temp" class="edivi__description">Temperatur</label>
                                            <div class="row">
                                                <div class="col">
                                                    <input class="w-100 vitalparam form-control" type="text" name="temp" id="temp" placeholder="0" value="<?= $daten['temp'] ?>" style="display:inline"> <small>°C</small>
                                                </div>
                                            </div>
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