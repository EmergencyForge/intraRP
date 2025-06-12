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
        header("Location: /enotf/");
        exit();
    }
} else {
    header("Location: /enotf/");
    exit();
}

$stmto = $pdo->prepare("SELECT name FROM intra_edivi_ziele WHERE identifier = :ziel");
$stmto->execute(['ziel' => $daten['transportziel']]);
$ziel = $stmto->fetchColumn();

$stmtNa = $pdo->prepare("SELECT name FROM intra_edivi_fahrzeuge WHERE identifier = :fzg_na");
$stmtNa->execute(['fzg_na' => $daten['fzg_na']]);
$fzgNA = $stmtNa->fetchColumn();

$stmtTransp = $pdo->prepare("SELECT name FROM intra_edivi_fahrzeuge WHERE identifier = :fzg_transp");
$stmtTransp->execute(['fzg_transp' => $daten['fzg_transp']]);
$fzgTransp = $stmtTransp->fetchColumn();

if ($daten['freigegeben'] == 1) {
    $ist_freigegeben = true;
    header("Location: /enotf/prot/index.php?enr=" . $daten['enr']);
    exit();
} else {
    $ist_freigegeben = false;
}

$daten['last_edit'] = !empty($daten['last_edit']) ? (new DateTime($daten['last_edit']))->format('d.m.Y H:i') : NULL;
$daten['patgebdat'] = !empty($daten['patgebdat']) ? (new DateTime($daten['patgebdat']))->format('d.m.Y') : NULL;
$daten['edatum'] = !empty($daten['edatum']) ? (new DateTime($daten['edatum']))->format('d.m.Y') : NULL;

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
    <link rel="stylesheet" href="/assets/css/divi.min.css" />
    <link rel="stylesheet" href="/assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="/assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <script src="/vendor/components/jquery/jquery.min.js"></script>
    <script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="<?php echo SYSTEM_NAME ?>" />
    <link rel="manifest" href="/assets/favicon/site.webmanifest" />
    <!-- Metas -->
    <meta name="theme-color" content="#ffaf2f" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="<?= $prot_url ?>" />
    <meta property="og:title" content="[#<?= $daten['enr'] ?>] &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
</head>

<body data-page="freigabe">
    <form name="form" method="post" action="">
        <input type="hidden" name="new" value="1" />
        <div class="container-fluid" id="edivi__container">
            <div class="row h-100">
                <div class="col" id="edivi__content">
                    <div class="row">
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5>Patient</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col edivi__freigabe">
                                    <table class="w-100">
                                        <tbody>
                                            <tr>
                                                <td><?= $daten['patname'] ?? '<span style="color:lightgray">Kein Name hinterlegt</span>' ?> * <?= $daten['patgebdat'] ?? '<span style="color:lightgray">Kein Datum hinterlegt</span>' ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5>Transport</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col edivi__freigabe">
                                    <table class="w-100">
                                        <tbody>
                                            <tr>
                                                <td>von: <?= $daten['eort'] ?? '<span style="color:lightgray">Kein Ort hinterlegt</span>' ?></td>
                                            </tr>
                                            <tr>
                                                <td>nach: <?= !empty($ziel) ? $ziel : '<span style="color:lightgray">Kein Zielort hinterlegt</span>' ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5>Besatzung</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col edivi__freigabe">
                                    <table class="w-100">
                                        <tbody>
                                            <tr>
                                                <td><?= !empty($fzgTransp) ? $fzgTransp : '<span style="color:lightgray">Kein Transportmittel hinterlegt</span>' ?></td>
                                            </tr>
                                            <tr>
                                                <td><?= $daten['fzg_transp_perso'] ?? '<span style="color:lightgray">Kein Transportführer hinterlegt</span>' ?>, <?= $daten['fzg_transp_perso_2'] ?? '<span style="color:lightgray">Kein Fahrzeugführer hinterlegt</span>' ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5 style="visibility:hidden">Besatzung</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col edivi__freigabe">
                                    <table class="w-100">
                                        <tbody>
                                            <tr>
                                                <td><?= !empty($fzgNA) ? $fzgNA : '<span style="color:lightgray">Kein Notarztzubringer hinterlegt</span>' ?></td>
                                            </tr>
                                            <tr>
                                                <td><?= $daten['fzg_na_perso'] ?? '<span style="color:lightgray">Kein Notarzt hinterlegt</span>' ?>, <?= $daten['fzg_na_perso_2'] ?? '<span style="color:lightgray">Kein Fahrzeugführer/HEMS hinterlegt</span>' ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5>Einsatzdaten</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col edivi__freigabe">
                                    <table class="w-100">
                                        <tbody>
                                            <tr>
                                                <td>Einsatz-Nr.: <?= $daten['enr'] ?></td>
                                            </tr>
                                            <tr>
                                                <td>Beginn: <?= $daten['edatum'] ?? '<span style="color:lightgray">Kein Datum hinterlegt</span>' ?>, <?= $daten['ezeit'] ?? '<span style="color:lightgray">keine Zeit hinterlegt</span>' ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5>Protokollant und freigebende Person</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col edivi__freigabe">
                                    <table class="w-100">
                                        <tbody>
                                            <tr>
                                                <td><?= $daten['pfname'] ?? '<span style="color:lightgray">Kein Protokollant hinterlegt</span>' ?></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <?php
                                                    if ($daten['prot_by'] == 0 && $daten['prot_by'] !== NULL) {
                                                        echo !empty($fzgTransp) ? $fzgTransp : '<span style="color:lightgray">Kein Transportmittel hinterlegt</span>';
                                                    } elseif ($daten['prot_by'] == 1) {
                                                        echo !empty($fzgNA) ? $fzgNA : '<span style="color:lightgray">Kein Notarztzubringer hinterlegt</span>';
                                                    } else {
                                                        echo '<span style="color:lightgray">Keine Protokollart festgelegt</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5>Plausibilitätsprüfung</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col edivi__freigabe">
                                    <table class="w-100">
                                        <tbody>
                                            <tr>
                                                <td class="edivi__checks-text" id="plausibility"><?php include __DIR__ . '/../../assets/components/enotf/plausibility.php'; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="edivi__freigabe-buttons">
                        <div class="row">
                            <div class="col">
                                <a href="/enotf/prot/index.php?enr=<?= $daten['enr'] ?>">zurück</a>
                            </div>
                            <div class="col">
                                <a href="#" id="final">Abschließen!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </form>
    <?php
    include __DIR__ . '/../../assets/functions/enotf/notify.php';
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