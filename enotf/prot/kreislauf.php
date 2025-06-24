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

<body data-page="kreislauf">
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
                            <div class="row">
                                <div class="col">
                                    <div class="row edivi__box">
                                        <h5 class="text-light px-2 py-1">Diagnostik Kreislauf</h5>
                                        <div class="col">
                                            <div class="row my-2">
                                                <div class="col">
                                                    <label for="c_kreislauf" class="edivi__description">Patientenzustand</label>
                                                    <?php
                                                    if ($daten['c_kreislauf'] === NULL) {
                                                    ?>
                                                        <select name="c_kreislauf" id="c_kreislauf" class="w-100 form-select edivi__input-check" required>
                                                            <option disabled hidden selected>---</option>
                                                            <option value="0">stabil</option>
                                                            <option value="1">instabil</option>
                                                            <option value="2">nicht beurteilbar</option>
                                                        </select>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <select name="c_kreislauf" id="c_kreislauf" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                                            <option disabled hidden selected>---</option>
                                                            <option value="0" <?php echo ($daten['c_kreislauf'] == 0 ? 'selected' : '') ?>>stabil</option>
                                                            <option value="1" <?php echo ($daten['c_kreislauf'] == 1 ? 'selected' : '') ?>>instabil</option>
                                                            <option value="2" <?php echo ($daten['c_kreislauf'] == 2 ? 'selected' : '') ?>>nicht beurteilbar</option>
                                                        </select>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                                <div class="col">
                                                    <div class="col">
                                                        <label for="c_ekg" class="edivi__description">EKG-Befund</label>
                                                        <?php
                                                        if ($daten['c_ekg'] === NULL) {
                                                        ?>
                                                            <select name="c_ekg" id="c_ekg" class="w-100 form-select edivi__input-check" required>
                                                                <option disabled hidden selected>---</option>
                                                                <option value="0">Sinusrhythmus</option>
                                                                <option value="1">STEMI</option>
                                                                <option value="2">Abs. Arrhythmie</option>
                                                                <option value="3">Kammerflimmern</option>
                                                                <option value="4">Tachykardie</option>
                                                                <option value="5">AV-Block II°/III°</option>
                                                                <option value="6">Asystolie</option>
                                                                <option value="7">Vorhofflimmern</option>
                                                                <option value="8">Bradykardie</option>
                                                                <option value="9">nicht beurteilbar</option>
                                                                <option value="99">nicht erhoben</option>
                                                            </select>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <select name="c_ekg" id="c_ekg" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                                                <option disabled hidden selected>---</option>
                                                                <option value="0" <?php echo ($daten['c_ekg'] == 0 ? 'selected' : '') ?>>Sinusrhythmus</option>
                                                                <option value="1" <?php echo ($daten['c_ekg'] == 1 ? 'selected' : '') ?>>STEMI</option>
                                                                <option value="2" <?php echo ($daten['c_ekg'] == 2 ? 'selected' : '') ?>>Abs. Arrhythmie</option>
                                                                <option value="3" <?php echo ($daten['c_ekg'] == 3 ? 'selected' : '') ?>>Kammerflimmern</option>
                                                                <option value="4" <?php echo ($daten['c_ekg'] == 4 ? 'selected' : '') ?>>Tachykardie</option>
                                                                <option value="5" <?php echo ($daten['c_ekg'] == 5 ? 'selected' : '') ?>>AV-Block II°/III°</option>
                                                                <option value="6" <?php echo ($daten['c_ekg'] == 6 ? 'selected' : '') ?>>Asystolie</option>
                                                                <option value="7" <?php echo ($daten['c_ekg'] == 7 ? 'selected' : '') ?>>Vorhofflimmern</option>
                                                                <option value="8" <?php echo ($daten['c_ekg'] == 8 ? 'selected' : '') ?>>Bradykardie</option>
                                                                <option value="9" <?php echo ($daten['c_ekg'] == 9 ? 'selected' : '') ?>>nicht beurteilbar</option>
                                                                <option value="99" <?php echo ($daten['c_ekg'] == 99 ? 'selected' : '') ?>>nicht erhoben</option>
                                                            </select>
                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row my-2">
                                                <div class="col">
                                                    <label for="rrsys" class="edivi__description">RR sys</label>
                                                    <div class="row mb-1">
                                                        <div class="col">
                                                            <input class="w-100 vitalparam form-control" type="text" name="rrsys" id="rrsys" placeholder="120" value="<?= $daten['rrsys'] ?>" style="display:inline"> <small>mmHg</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <label for="rrdias" class="edivi__description">RR dias</label>
                                                    <div class="row mb-1">
                                                        <div class="col">
                                                            <input class="w-100 vitalparam form-control" type="text" name="rrdias" id="rrdias" placeholder="80" value="<?= $daten['rrdias'] ?>" style="display:inline"> <small>mmHg</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <label for="herzfreq" class="edivi__description">Herzfrequenz</label>
                                                    <div class="row mb-1">
                                                        <div class="col"><input class="w-100 vitalparam form-control" type="text" name="herzfreq" id="herzfreq" placeholder="0" value="<?= $daten['herzfreq'] ?>" style="display:inline"> <small>/min</small></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="row edivi__box">
                                        <h5 class="text-light px-2 py-1">Medikamente <small style="font-size:.7em">(Wirkstoff - Dosierung - Dareichungsform)</small></h5>
                                        <div class="col">
                                            <div class="row my-2">
                                                <div class="col">
                                                    <textarea name="medis" id="medis" rows="10" class="w-100 form-control" placeholder="..." style="resize: none"><?= $daten['medis'] ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row edivi__box">
                                <h5 class="text-light px-2 py-1">Zugänge</h5>
                                <div class="col">
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="c_zugang_art_1" class="edivi__description">Zugangsart</label>
                                            <?php
                                            if ($daten['c_zugang_art_1'] === NULL) {
                                            ?>
                                                <select name="c_zugang_art_1" id="c_zugang_art_1" class="w-100 form-select">
                                                    <option value="" selected>Art</option>
                                                    <option value="3">pvk</option>
                                                    <option value="1">zvk</option>
                                                    <option value="2">i.o.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="c_zugang_art_1" id="c_zugang_art_1" class="w-100 form-select" autocomplete="off">
                                                    <option value="" selected>Art</option>
                                                    <option value="3" <?php echo ($daten['c_zugang_art_1'] == 3 ? 'selected' : '') ?>>pvk</option>
                                                    <option value="1" <?php echo ($daten['c_zugang_art_1'] == 1 ? 'selected' : '') ?>>zvk</option>
                                                    <option value="2" <?php echo ($daten['c_zugang_art_1'] == 2 ? 'selected' : '') ?>>i.o.</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col edivi__zugang-container">
                                            <label for="c_zugang_gr_1" class="edivi__description">Zugangsgröße</label>
                                            <?php
                                            if ($daten['c_zugang_gr_1'] === NULL) {
                                            ?>
                                                <select name="c_zugang_gr_1" id="c_zugang_gr_1" class="w-100 form-select edivi__zugang-list">
                                                    <option value="" selected>Gr.</option>
                                                    <option disabled>-- i.v. --</option>
                                                    <option value="10">G24</option>
                                                    <option value="1">G22</option>
                                                    <option value="2">G20</option>
                                                    <option value="3">G18</option>
                                                    <option value="4">G17</option>
                                                    <option value="5">G16</option>
                                                    <option value="6">G14</option>
                                                    <option disabled>-- i.o. --</option>
                                                    <option value="7">15mm</option>
                                                    <option value="8">25mm</option>
                                                    <option value="9">45mm</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="c_zugang_gr_1" id="c_zugang_gr_1" class="w-100 form-select edivi__zugang-list" autocomplete="off">
                                                    <option value="" selected>Gr.</option>
                                                    <option disabled>-- i.v. --</option>
                                                    <option value="10" <?php echo ($daten['c_zugang_gr_1'] == 10 ? 'selected' : '') ?>>G24</option>
                                                    <option value="1" <?php echo ($daten['c_zugang_gr_1'] == 1 ? 'selected' : '') ?>>G22</option>
                                                    <option value="2" <?php echo ($daten['c_zugang_gr_1'] == 2 ? 'selected' : '') ?>>G20</option>
                                                    <option value="3" <?php echo ($daten['c_zugang_gr_1'] == 3 ? 'selected' : '') ?>>G18</option>
                                                    <option value="4" <?php echo ($daten['c_zugang_gr_1'] == 4 ? 'selected' : '') ?>>G17</option>
                                                    <option value="5" <?php echo ($daten['c_zugang_gr_1'] == 5 ? 'selected' : '') ?>>G16</option>
                                                    <option value="6" <?php echo ($daten['c_zugang_gr_1'] == 6 ? 'selected' : '') ?>>G14</option>
                                                    <option disabled>-- i.o. --</option>
                                                    <option value="7" <?php echo ($daten['c_zugang_gr_1'] == 7 ? 'selected' : '') ?>>15mm</option>
                                                    <option value="8" <?php echo ($daten['c_zugang_gr_1'] == 8 ? 'selected' : '') ?>>25mm</option>
                                                    <option value="9" <?php echo ($daten['c_zugang_gr_1'] == 9 ? 'selected' : '') ?>>45mm</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col">
                                            <label for="c_zugang_ort_1" class="edivi__description">Lokalisation</label>
                                            <input type="text" name="c_zugang_ort_1" id="c_zugang_ort_1" class="w-100 form-control" placeholder="Ort" value="<?= $daten['c_zugang_ort_1'] ?>">
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <?php
                                            if ($daten['c_zugang_art_2'] === NULL) {
                                            ?>
                                                <select name="c_zugang_art_2" id="c_zugang_art_2" class="w-100 form-select">
                                                    <option value="" selected>Art</option>
                                                    <option value="3">pvk</option>
                                                    <option value="1">zvk</option>
                                                    <option value="2">i.o.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="c_zugang_art_2" id="c_zugang_art_2" class="w-100 form-select" autocomplete="off">
                                                    <option value="" selected>Art</option>
                                                    <option value="3" <?php echo ($daten['c_zugang_art_2'] == 3 ? 'selected' : '') ?>>pvk</option>
                                                    <option value="1" <?php echo ($daten['c_zugang_art_2'] == 1 ? 'selected' : '') ?>>zvk</option>
                                                    <option value="2" <?php echo ($daten['c_zugang_art_2'] == 2 ? 'selected' : '') ?>>i.o.</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col edivi__zugang-container">
                                            <?php
                                            if ($daten['c_zugang_gr_2'] === NULL) {
                                            ?>
                                                <select name="c_zugang_gr_2" id="c_zugang_gr_2" class="w-100 form-select edivi__zugang-list">
                                                    <option value="" selected>Gr.</option>
                                                    <option disabled>-- i.v. --</option>
                                                    <option value="10">G24</option>
                                                    <option value="1">G22</option>
                                                    <option value="2">G20</option>
                                                    <option value="3">G18</option>
                                                    <option value="4">G17</option>
                                                    <option value="5">G16</option>
                                                    <option value="6">G14</option>
                                                    <option disabled>-- i.o. --</option>
                                                    <option value="7">15mm</option>
                                                    <option value="8">25mm</option>
                                                    <option value="9">45mm</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="c_zugang_gr_2" id="c_zugang_gr_2" class="w-100 form-select edivi__zugang-list" autocomplete="off">
                                                    <option value="" selected>Gr.</option>
                                                    <option disabled>-- i.v. --</option>
                                                    <option value="10" <?php echo ($daten['c_zugang_gr_2'] == 10 ? 'selected' : '') ?>>G24</option>
                                                    <option value="1" <?php echo ($daten['c_zugang_gr_2'] == 1 ? 'selected' : '') ?>>G22</option>
                                                    <option value="2" <?php echo ($daten['c_zugang_gr_2'] == 2 ? 'selected' : '') ?>>G20</option>
                                                    <option value="3" <?php echo ($daten['c_zugang_gr_2'] == 3 ? 'selected' : '') ?>>G18</option>
                                                    <option value="4" <?php echo ($daten['c_zugang_gr_2'] == 4 ? 'selected' : '') ?>>G17</option>
                                                    <option value="5" <?php echo ($daten['c_zugang_gr_2'] == 5 ? 'selected' : '') ?>>G16</option>
                                                    <option value="6" <?php echo ($daten['c_zugang_gr_2'] == 6 ? 'selected' : '') ?>>G14</option>
                                                    <option disabled>-- i.o. --</option>
                                                    <option value="7" <?php echo ($daten['c_zugang_gr_2'] == 7 ? 'selected' : '') ?>>15mm</option>
                                                    <option value="8" <?php echo ($daten['c_zugang_gr_2'] == 8 ? 'selected' : '') ?>>25mm</option>
                                                    <option value="9" <?php echo ($daten['c_zugang_gr_2'] == 9 ? 'selected' : '') ?>>45mm</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col">
                                            <input type="text" name="c_zugang_ort_2" id="c_zugang_ort_2" class="w-100 form-control" placeholder="Ort" value="<?= $daten['c_zugang_ort_2'] ?>">
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <?php
                                            if ($daten['c_zugang_art_3'] === NULL) {
                                            ?>
                                                <select name="c_zugang_art_3" id="c_zugang_art_3" class="w-100 form-select">
                                                    <option value="" selected>Art</option>
                                                    <option value="3">pvk</option>
                                                    <option value="1">zvk</option>
                                                    <option value="2">i.o.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="c_zugang_art_3" id="c_zugang_art_3" class="w-100 form-select" autocomplete="off">
                                                    <option value="" selected>Art</option>
                                                    <option value="3" <?php echo ($daten['c_zugang_art_3'] == 3 ? 'selected' : '') ?>>pvk</option>
                                                    <option value="1" <?php echo ($daten['c_zugang_art_3'] == 1 ? 'selected' : '') ?>>zvk</option>
                                                    <option value="2" <?php echo ($daten['c_zugang_art_3'] == 2 ? 'selected' : '') ?>>i.o.</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col edivi__zugang-container">
                                            <?php
                                            if ($daten['c_zugang_gr_3'] === NULL) {
                                            ?>
                                                <select name="c_zugang_gr_3" id="c_zugang_gr_3" class="w-100 form-select edivi__zugang-list">
                                                    <option value="" selected>Gr.</option>
                                                    <option disabled>-- i.v. --</option>
                                                    <option value="10">G24</option>
                                                    <option value="1">G22</option>
                                                    <option value="2">G20</option>
                                                    <option value="3">G18</option>
                                                    <option value="4">G17</option>
                                                    <option value="5">G16</option>
                                                    <option value="6">G14</option>
                                                    <option disabled>-- i.o. --</option>
                                                    <option value="7">15mm</option>
                                                    <option value="8">25mm</option>
                                                    <option value="9">45mm</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="c_zugang_gr_3" id="c_zugang_gr_3" class="w-100 form-select edivi__zugang-list" autocomplete="off">
                                                    <option value="" selected>Gr.</option>
                                                    <option disabled>-- i.v. --</option>
                                                    <option value="10" <?php echo ($daten['c_zugang_gr_3'] == 10 ? 'selected' : '') ?>>G24</option>
                                                    <option value="1" <?php echo ($daten['c_zugang_gr_3'] == 1 ? 'selected' : '') ?>>G22</option>
                                                    <option value="2" <?php echo ($daten['c_zugang_gr_3'] == 2 ? 'selected' : '') ?>>G20</option>
                                                    <option value="3" <?php echo ($daten['c_zugang_gr_3'] == 3 ? 'selected' : '') ?>>G18</option>
                                                    <option value="4" <?php echo ($daten['c_zugang_gr_3'] == 4 ? 'selected' : '') ?>>G17</option>
                                                    <option value="5" <?php echo ($daten['c_zugang_gr_3'] == 5 ? 'selected' : '') ?>>G16</option>
                                                    <option value="6" <?php echo ($daten['c_zugang_gr_3'] == 6 ? 'selected' : '') ?>>G14</option>
                                                    <option disabled>-- i.o. --</option>
                                                    <option value="7" <?php echo ($daten['c_zugang_gr_3'] == 7 ? 'selected' : '') ?>>15mm</option>
                                                    <option value="8" <?php echo ($daten['c_zugang_gr_3'] == 8 ? 'selected' : '') ?>>25mm</option>
                                                    <option value="9" <?php echo ($daten['c_zugang_gr_3'] == 9 ? 'selected' : '') ?>>45mm</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col">
                                            <input type="text" name="c_zugang_ort_3" id="c_zugang_ort_3" class="w-100 form-control" placeholder="Ort" value="<?= $daten['c_zugang_ort_3'] ?>">
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
        function updateContainerClass(index) {
            const containers = document.querySelectorAll('.edivi__zugang-container');
            const selects = document.querySelectorAll('.edivi__zugang-list');

            containers[index].classList.remove(
                ...Array.from(containers[index].classList).filter(className => className.startsWith('edivi__zugang-opt'))
            );

            const selectedValue = selects[index].value;

            containers[index].classList.add(`edivi__zugang-opt${selectedValue}`);
        }

        document.addEventListener("DOMContentLoaded", function() {
            const selects = document.querySelectorAll('.edivi__zugang-list');

            selects.forEach((select, index) => {
                select.addEventListener('change', () => {
                    updateContainerClass(index);
                });

                updateContainerClass(index);
            });
        });
    </script>
</body>

</html>