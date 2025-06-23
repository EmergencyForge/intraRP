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
    header("Location: " . BASE_PATH . "enotf/loggedout.php");
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
    <form name="form" method="post" action="">
        <input type="hidden" name="new" value="1" />
        <div class="container-fluid" id="edivi__container">
            <div class="row h-100">
                <div class="col" id="edivi__content">
                    <div class="row border-bottom edivi__header-overview" style="--bs-border-color: #333333">
                        <div class="col"></div>
                        <div class="col border-start" style="--bs-border-color: #333333">
                            <div class="row border-bottom" style="--bs-border-color: #333333">
                                <div class="col">Angemeldet:</div>
                            </div>
                            <div class="row">
                                <div class="col"><?= $_SESSION['fahrername'] ?? 'Fehler Fehler' ?></div>
                                <div class="col border-start" style="--bs-border-color: #333333"><?= $_SESSION['beifahrername'] ?? 'Fehler Fehler' ?></div>
                            </div>
                        </div>
                        <div class="col-2 border-start" style="padding:0;--bs-border-color: #333333">
                            <a href="loggedout.php" class="edivi__nidabutton-primary w-100 h-100 d-flex justify-content-center align-content-center">abmelden</a>
                        </div>
                    </div>
                    <div class="hr my-2" style="color:transparent"></div>
                    <div class="row">
                        <div class="col-8">
                            <div class="row">
                                <div class="col d-flex justify-content-start align-items-center">
                                    <h4 class="fw-bold">Einsatzprotokolle</h4>
                                </div>
                                <div class="col d-flex justify-content-end align-items-center">
                                    <a href="create.php" class="edivi__nidabutton" style="display:inline-block"><i class="las la-plus"></i></a>
                                </div>
                            </div>
                            <div class="row ps-3">
                                <div class="col edivi__box p-4" style="overflow-x: hidden; overflow-y:auto; height: 70vh;">
                                    <!-- <div class="edivi__einsatz-container">
                                        <a href="create.php" class="edivi__einsatz-link">
                                            <div class="row edivi__einsatz">
                                                <div class="col-2 edivi__einsatz-type"><span>A</span></div>
                                                <div class="col edivi__einsatz-enr"></div>
                                                <div class="col edivi__einsatz-dates"></div>
                                                <div class="col edivi__einsatz-name"></div>
                                            </div>
                                        </a>
                                    </div> -->
                                    <?php
                                    $stmt = $pdo->prepare("SELECT patname, patgebdat, edatum, ezeit, enr, prot_by, freigegeben, pfname  FROM intra_edivi WHERE fzg_transp = :fzg_transp AND freigegeben = 0 OR fzg_na = :fzg_na AND freigegeben = 0");
                                    $stmt->execute(
                                        [
                                            ':fzg_transp' => $_SESSION['protfzg'],
                                            ':fzg_na' => $_SESSION['protfzg']
                                        ]
                                    );
                                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($result as $row) {
                                        if (!empty($row['edatum'])) {
                                            $row['edatum'] = (new DateTime($row['edatum']))->format('d.m.Y');
                                        } else {
                                            $row['edatum'] = '—';
                                        }

                                        if (!empty($row['ezeit'])) {
                                            $row['ezeit'] = (new DateTime($row['ezeit']))->format('H:i');
                                        } else {
                                            $row['ezeit'] = '—';
                                        }

                                        if (!empty($row['patgebdat'])) {
                                            $row['patgebdat'] = (new DateTime($row['patgebdat']))->format('d.m.Y');
                                        } else {
                                            $row['patgebdat'] = '—';
                                        }

                                        if (empty($row['patname'])) {
                                            $row['patname'] = '—';
                                        }

                                        if (empty($row['pfname'])) {
                                            $row['pfname'] = '—';
                                        }

                                        if ($row['prot_by'] == 1) {
                                    ?>
                                            <div class="edivi__einsatz-container">
                                                <a href="prot/index.php?enr=<?= $row['enr'] ?>" class="edivi__einsatz-link">
                                                    <div class="row edivi__einsatz edivi__einsatz-set">
                                                        <div class="col-2 edivi__einsatz-type"><span>E</span></div>
                                                        <div class="col edivi__einsatz-enr"><span>#<?= $row['enr'] ?> <span class="edivi__einsatz-cat">NA</span></span><br><?= $row['edatum'] ?> <?= $row['ezeit'] ?> Uhr</div>
                                                        <div class="col edivi__einsatz-name"><span>Patient:</span><br><?= $row['patname'] ?> * <?= $row['patgebdat'] ?></div>
                                                        <div class="col edivi__einsatz-freigeber"><span>Protokollant:</span><br><?= $row['pfname'] ?></div>
                                                    </div>
                                                </a>
                                            </div>
                                        <?php
                                        } else {
                                        ?>
                                            <div class="edivi__einsatz-container">
                                                <a href="prot/index.php?enr=<?= $row['enr'] ?>" class="edivi__einsatz-link">
                                                    <div class="row edivi__einsatz edivi__einsatz-set">
                                                        <div class="col-2 edivi__einsatz-type"><span>E</span></div>
                                                        <div class="col edivi__einsatz-enr"><span>#<?= $row['enr'] ?> <span class="edivi__einsatz-cat">RD</span></span><br><?= $row['edatum'] ?> <?= $row['ezeit'] ?> Uhr</div>
                                                        <div class="col edivi__einsatz-name"><span>Patient:</span><br><?= $row['patname'] ?> * <?= $row['patgebdat'] ?></div>
                                                        <div class="col edivi__einsatz-freigeber"><span>Protokollant:</span><br><?= $row['pfname'] ?></div>
                                                    </div>
                                                </a>
                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <div class="accordion" id="schnellzugriffAccordion" data-theme="dark">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="schnellzugriffHeading">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#schnellzugriffCollapse" aria-expanded="true" aria-controls="schnellzugriffCollapse">
                                                    Schnellzugriff
                                                </button>
                                            </h2>
                                            <div id="schnellzugriffCollapse" class="accordion-collapse collapse show" aria-labelledby="schnellzugriffHeading" data-bs-parent="#schnellzugriffAccordion">
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col p-2">
                                                            <a href="https://www.dgg.bam.de/quickinfo/de/" class="w-100 edivi__nidabutton" style="display:inline-block;text-align:center"><i class="las la-radiation"></i> Datenb. Gefahrgut</a>
                                                        </div>
                                                        <div class="col p-2">
                                                            <a href="https://www.openstreetmap.org/" class="w-100 edivi__nidabutton" style="display:inline-block;text-align:center"><i class="las la-map"></i> Openstreetmap</a>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6 p-2">
                                                            <a href="fahrzeuginfo.php" class="w-100 edivi__nidabutton" style="display:inline-block;text-align:center"><i class="las la-ambulance"></i> Fahrzeuginfo</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    <div class="accordion" id="verwaltungAccordion" data-theme="dark">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="verwaltungHeading">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#verwaltungCollapse" aria-expanded="true" aria-controls="verwaltungCollapse">
                                                    Verwaltung
                                                </button>
                                            </h2>
                                            <div id="verwaltungCollapse" class="accordion-collapse collapse show" aria-labelledby="verwaltungHeading" data-bs-parent="#verwaltungAccordion">
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-6 p-2">
                                                            <a href="<?= BASE_PATH ?>admin" class="w-100 edivi__nidabutton" style="display:inline-block;text-align:center"><i class="las la-toolbox"></i> Administration</a>
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
                </div>
            </div>
    </form>
    <script>
        var modalCloseButton = document.querySelector('#myModal4 .btn-close');
        var freigeberInput = document.getElementById('freigeber');

        modalCloseButton.addEventListener('click', function() {
            freigeberInput.value = '';
        });
    </script>
</body>

</html>