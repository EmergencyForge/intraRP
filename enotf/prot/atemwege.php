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

<body data-page="atemwege">
    <?php
    include __DIR__ . '/../../assets/components/enotf/topbar.php';
    ?>
    <form name="form" method="post" action="">
        <input type="hidden" name="new" value="1" />
        <div class="container-fluid" id="edivi__container">
            <div class="row h-100">
                <?php include __DIR__ . '/../../assets/components/enotf/nav.php'; ?>
                <div class="col" id="edivi__content">
                    <div class="row">
                        <div class="col">
                            <div class="row edivi__box">
                                <h5 class="text-light px-2 py-1">Diagnostik Atemwege</h5>
                                <div class="col">
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="awfrei_1" class="edivi__description">Atemwege</label>
                                            <div class="row">
                                                <div class="col">
                                                    <input type="checkbox" class="btn-check" id="awfrei_1" name="awfrei_1" value="1" <?php echo ($daten['awfrei_1'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                                    <label class="btn btn-sm btn-outline-success w-100" for="awfrei_1">frei</label>
                                                </div>
                                                <div class="col">
                                                    <input type="checkbox" class="btn-check" id="awfrei_3" name="awfrei_3" value="1" <?php echo ($daten['awfrei_3'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                                    <label class="btn btn-sm btn-outline-warning w-100" for="awfrei_3">gefährdet</label>
                                                </div>
                                                <div class="col">
                                                    <input type="checkbox" class="btn-check" id="awfrei_2" name="awfrei_2" value="1" <?php echo ($daten['awfrei_2'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                                    <label class="btn btn-sm btn-outline-danger w-100" for="awfrei_2">verlegt</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="zyanose_1" class="edivi__description">Zyanose</label>
                                            <div class="row">
                                                <div class="col">
                                                    <input type="checkbox" class="btn-check" id="zyanose_1" name="zyanose_1" value="1" <?php echo ($daten['zyanose_1'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                                    <label class="btn btn-sm btn-outline-success w-100" for="zyanose_1">Nein</label>
                                                </div>
                                                <div class="col"></div>
                                                <div class="col">
                                                    <input type="checkbox" class="btn-check" id="zyanose_2" name="zyanose_2" value="1" <?php echo ($daten['zyanose_2'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                                    <label class="btn btn-sm btn-outline-danger w-100" for="zyanose_2">Ja</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="row edivi__box">
                        <h5 class="text-light px-2 py-1">Maßnahmen Atemwege</h5>
                        <div class="col">
                            <div class="row my-2">
                                <div class="col">
                                    <label for="awsicherung_neu" class="edivi__description">Atemwegssicherung</label>
                                    <?php
                                    if ($daten['awsicherung_neu'] === NULL) {
                                    ?>
                                        <select name="awsicherung_neu" id="awsicherung_neu" class="w-100 form-select edivi__input-check" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0">keine</option>
                                            <option value="1">Endotrachealtubus</option>
                                            <option value="2">Larynxtubus</option>
                                            <option value="3">Guedel- / Wendltubus</option>
                                            <option value="99">Sonstige</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="awsicherung_neu" id="awsicherung_neu" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected>---</option>
                                            <option value="0" <?php echo ($daten['awsicherung_neu'] == 0 ? 'selected' : '') ?>>keine</option>
                                            <option value="1" <?php echo ($daten['awsicherung_neu'] == 1 ? 'selected' : '') ?>>Endotrachealtubus</option>
                                            <option value="2" <?php echo ($daten['awsicherung_neu'] == 2 ? 'selected' : '') ?>>Larynxtubus</option>
                                            <option value="3" <?php echo ($daten['awsicherung_neu'] == 3 ? 'selected' : '') ?>>Guedel- / Wendltubus</option>
                                            <option value="99" <?php echo ($daten['awsicherung_neu'] == 99 ? 'selected' : '') ?>>Sonstige</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col">
                                    <label for="o2gabe" class="edivi__description">Sauerstoffgabe</label>
                                    <div class="row">
                                        <div class="col"><input class="w-100 vitalparam form-control" type="text" min="0" max="15" placeholder="0" name="o2gabe" id="o2gabe" value="<?= $daten['o2gabe'] ?>" style="display:inline"> <small>L/min</small></div>
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