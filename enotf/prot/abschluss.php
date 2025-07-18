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

<body data-page="abschluss">
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
                                <h5 class="text-light px-2 py-1">Transportdaten</h5>
                                <div class="col">
                                    <div class="row mt-2">
                                        <div class="col-3">
                                            <label for="fzg_transp" class="edivi__description">Fahrzeug Transport</label>
                                            <?php if ($daten['fzg_transp'] === NULL) : ?>
                                                <select name="fzg_transp" id="fzg_transp" class="w-100 form-select">
                                                    <option selected value="NULL">Fzg. Transp.</option>
                                                    <?php
                                                    require __DIR__ . '/../../assets/config/database.php';

                                                    $stmt = $pdo->prepare("SELECT * FROM intra_fahrzeuge WHERE rd_type = 2 AND active = 1 ORDER BY priority ASC");
                                                    $stmt->execute();
                                                    $fahrzeuge = $stmt->fetchAll();
                                                    foreach ($fahrzeuge as $row) {
                                                        echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            <?php else : ?>
                                                <select name="fzg_transp" id="fzg_transp" class="w-100 form-select">
                                                    <option selected value="NULL">Fzg. Transp.</option>
                                                    <?php
                                                    require __DIR__ . '/../../assets/config/database.php';

                                                    $stmt = $pdo->prepare("SELECT * FROM intra_fahrzeuge WHERE rd_type = 2 ORDER BY priority ASC");
                                                    $stmt->execute();
                                                    $fahrzeuge = $stmt->fetchAll();

                                                    foreach ($fahrzeuge as $row) {
                                                        if ($row['identifier'] == $daten['fzg_transp'] && $row['active'] == 1) {
                                                            echo '<option value="' . $row['identifier'] . '" selected>' . $row['name'] . '</option>';
                                                        } elseif ($row['identifier'] == $daten['fzg_transp'] && $row['active'] == 0) {
                                                            echo '<option value="' . $row['identifier'] . '" selected disabled>' . $row['name'] . '</option>';
                                                        } else {
                                                            echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col">
                                            <label for="fzg_transp_perso" class="edivi__description">Besatzung Transpormittel</label>
                                            <input type="text" name="fzg_transp_perso" id="fzg_transp_perso" class="w-100 form-control" placeholder="Transportführer RTW/KTW" value="<?= $daten['fzg_transp_perso'] ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-3">
                                        </div>
                                        <div class="col">
                                            <input type="text" name="fzg_transp_perso_2" id="fzg_transp_perso_2" class="w-100 form-control" placeholder="Fahrzeugführer RTW/KTW" value="<?= $daten['fzg_transp_perso_2'] ?>">
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-3">
                                            <label for="fzg_na" class="edivi__description">Fahrzeug Notarzt</label>
                                            <?php if ($daten['fzg_na'] === NULL) : ?>
                                                <select name="fzg_na" id="fzg_na" class="w-100 form-select">
                                                    <option selected value="NULL">Fzg. NA</option>
                                                    <?php
                                                    require __DIR__ . '/../../assets/config/database.php';

                                                    $stmt = $pdo->prepare("SELECT * FROM intra_fahrzeuge WHERE rd_type = 1 AND active = 1 ORDER BY priority ASC");
                                                    $stmt->execute();
                                                    $fahrzeuge = $stmt->fetchAll();
                                                    foreach ($fahrzeuge as $row) {
                                                        echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            <?php else : ?>
                                                <select name="fzg_na" id="fzg_na" class="w-100 form-select">
                                                    <option selected value="NULL">Fzg. NA</option>
                                                    <?php
                                                    require __DIR__ . '/../../assets/config/database.php';

                                                    $stmt = $pdo->prepare("SELECT * FROM intra_fahrzeuge WHERE rd_type = 1 ORDER BY priority ASC");
                                                    $stmt->execute();
                                                    $fahrzeuge = $stmt->fetchAll();

                                                    foreach ($fahrzeuge as $row) {
                                                        if ($row['identifier'] == $daten['fzg_na'] && $row['active'] == 1) {
                                                            echo '<option value="' . $row['identifier'] . '" selected>' . $row['name'] . '</option>';
                                                        } elseif ($row['identifier'] == $daten['fzg_na'] && $row['active'] == 0) {
                                                            echo '<option value="' . $row['identifier'] . '" selected disabled>' . $row['name'] . '</option>';
                                                        } else {
                                                            echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col">
                                            <label for="fzg_na_perso" class="edivi__description">Besatzung Notarztzubringer</label>
                                            <input type="text" name="fzg_na_perso" id="fzg_na_perso" class="w-100 form-control" placeholder="Notarzt" value="<?= $daten['fzg_na_perso'] ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-3">
                                        </div>
                                        <div class="col">
                                            <input type="text" name="fzg_na_perso_2" id="fzg_na_perso_2" class="w-100 form-control" placeholder="Fahrzeugführer NEF/HEMS-TC" value="<?= $daten['fzg_na_perso_2'] ?>">
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="fzg_sonst" class="edivi__description">Sonstige Fahrzeuge</label>
                                            <input type="text" name="fzg_sonst" id="fzg_sonst" class="w-100 form-control" placeholder="Weitere Rettungsmittel" value="<?= $daten['fzg_sonst'] ?>">
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="transportziel" class="edivi__description">Transportart oder Transportziel</label>
                                            <?php
                                            if ($daten['transportziel'] === NULL) {
                                            ?>
                                                <select name="transportziel" id="transportziel" class="w-100 form-select edivi__input-check" required>
                                                    <option disabled hidden selected value="NULL">---</option>
                                                    <?php
                                                    require __DIR__ . '/../../assets/config/database.php';

                                                    $stmt = $pdo->prepare("SELECT * FROM intra_edivi_ziele WHERE active = 1 ORDER BY priority ASC");
                                                    $stmt->execute();
                                                    $ziele = $stmt->fetchAll();
                                                    foreach ($ziele as $row) {
                                                        echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="transportziel" id="transportziel" class="w-100 mb-2 form-select edivi__input-check" autocomplete="off">
                                                    <option disabled hidden selected value="NULL">---</option>
                                                    <?php
                                                    require __DIR__ . '/../../assets/config/database.php';

                                                    $stmt = $pdo->prepare("SELECT * FROM intra_edivi_ziele ORDER BY priority ASC");
                                                    $stmt->execute();
                                                    $fahrzeuge = $stmt->fetchAll();

                                                    foreach ($fahrzeuge as $row) {
                                                        if ($row['identifier'] == $daten['transportziel'] && $row['active'] == 1) {
                                                            echo '<option value="' . $row['identifier'] . '" selected>' . $row['name'] . '</option>';
                                                        } elseif ($row['identifier'] == $daten['transportziel'] && $row['active'] == 0) {
                                                            echo '<option value="' . $row['identifier'] . '" selected disabled>' . $row['name'] . '</option>';
                                                        } else {
                                                            echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                        }
                                                    }
                                                    ?>
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
                            <div class="row edivi__box">
                                <h5 class="text-light px-2 py-1 edivi__group-check">Protokolldaten</h5>
                                <div class="col">
                                    <?php
                                    $stmtfn = $pdo->query("SELECT fullname FROM intra_mitarbeiter ORDER BY fullname ASC");
                                    $fullnames = $stmtfn->fetchAll(PDO::FETCH_COLUMN);
                                    ?>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="pfname" class="edivi__description">Protokollant</label>
                                            <input type="text" name="pfname" id="pfname" class="w-100 form-control edivi__input-check" placeholder="Max Mustermann" value="<?= htmlspecialchars($daten['pfname'] ?? '') ?>" list="nameSuggestions" required>
                                            <datalist id="nameSuggestions">
                                                <?php foreach ($fullnames as $name): ?>
                                                    <option value="<?= htmlspecialchars($name) ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="prot_by" class="edivi__description">Protokoll durch</label>
                                            <select name="prot_by" id="prot_by" class="w-100 form-select edivi__input-check" readonly required autocomplete="off">
                                                <option disabled hidden selected>---</option>
                                                <option value="0" <?php echo ($daten['prot_by'] == 0 ? 'selected' : '') ?>>Transportmittel</option>
                                                <option value="1" <?php echo ($daten['prot_by'] == 1 ? 'selected' : '') ?>>Notarzt</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (!$ist_freigegeben) : ?>
                        <a href="<?= BASE_PATH ?>enotf/prot/freigabe.php?enr=<?= $daten['enr'] ?>" id="abschluss__btn">Abschließen</a>
                    <?php endif; ?>
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
        var modalCloseButton = document.querySelector('#myModal4 .btn-close');
        var freigeberInput = document.getElementById('freigeber');

        modalCloseButton.addEventListener('click', function() {
            freigeberInput.value = '';
        });
    </script>
</body>

</html>