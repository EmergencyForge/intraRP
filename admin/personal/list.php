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

if (!Permissions::check(['admin', 'personnel.view'])) {
    Flash::set('error', 'no-permissions');
    header("Location: " . BASE_PATH . "admin/index.php");
}

$stmtg = $pdo->prepare("SELECT * FROM intra_mitarbeiter_dienstgrade");
$stmtg->execute();
$dginfo = $stmtg->fetchAll(PDO::FETCH_UNIQUE);

$stmtr = $pdo->prepare("SELECT * FROM intra_mitarbeiter_rdquali");
$stmtr->execute();
$rdginfo = $stmtr->fetchAll(PDO::FETCH_UNIQUE);

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Administration &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/style.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/admin.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <script src="<?= BASE_PATH ?>vendor/components/jquery/jquery.min.js"></script>
    <script src="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="<?= BASE_PATH ?>vendor/datatables.net/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="<?= BASE_PATH ?>assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="<?= BASE_PATH ?>assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_PATH ?>assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="<?php echo SYSTEM_NAME ?>" />
    <link rel="manifest" href="<?= BASE_PATH ?>assets/favicon/site.webmanifest" />
    <!-- Metas -->
    <meta name="theme-color" content="<?php echo SYSTEM_COLOR ?>" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="https://<?php echo SYSTEM_URL . BASE_PATH ?>/dashboard.php" />
    <meta property="og:title" content="<?php echo SYSTEM_NAME ?> - Intranet <?php echo SERVER_CITY ?>" />
    <meta property="og:image" content="<?php echo META_IMAGE_URL ?>" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />

</head>

<body data-bs-theme="dark" data-page="mitarbeiter">
    <?php include "../../assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <div class="row mb-5">
                        <div class="col">
                            <h1>Mitarbeiterübersicht</h1>
                        </div>
                        <div class="col">
                            <div class="d-flex justify-content-end">
                                <?php if (isset($_GET['archiv'])) { ?>
                                    <a href="<?= BASE_PATH ?>admin/personal/list.php" class="btn btn-success">Aktive Mitarbeiter</a>
                                <?php } else { ?>
                                    <a href="<?= BASE_PATH ?>admin/personal/list.php?archiv" class="btn btn-danger">Archiv</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    Flash::render();
                    ?>
                    <div class="intra__tile py-2 px-3">
                        <table class="table table-striped" id="mitarbeiterTable">
                            <thead>
                                <th scope="col">Dienstnummer</th>
                                <th scope="col">Name</th>
                                <th scope="col">Dienstgrad</th>
                                <th scope="col">Einstellungsdatum</th>
                                <th scope="col"></th>
                            </thead>
                            <tbody>
                                <?php
                                require __DIR__ . '/../../assets/config/database.php';

                                $stmta = $pdo->prepare("SELECT id,archive FROM intra_mitarbeiter_dienstgrade WHERE archive = 1");
                                $stmta->execute();
                                $stdata = $stmta->fetch();

                                if (isset($_GET['archiv'])) {
                                    $listQuery = "SELECT * FROM intra_mitarbeiter WHERE dienstgrad = :dienstgrad ORDER BY einstdatum ASC";
                                    $params = [$stdata['id']];
                                } else {
                                    $listQuery = "SELECT * FROM intra_mitarbeiter WHERE dienstgrad <> :dienstgrad ORDER BY einstdatum ASC";
                                    $params = [$stdata['id']];
                                }
                                $stmt = $pdo->prepare($listQuery);
                                $stmt->execute($params);
                                $result = $stmt->fetchAll();

                                foreach ($result as $row) {
                                    $einstellungsdatum = (new DateTime($row['einstdatum']))->format('d.m.Y');

                                    $dginfo2 = $dginfo[$row['dienstgrad']];
                                    $rdginfo2 = $rdginfo[$row['qualird']];

                                    if ($row['geschlecht'] == 0) {
                                        $dienstgrad = $dginfo2['name_m'];
                                        $rdqualtext = $rdginfo2['name_m'];
                                    } elseif ($row['geschlecht'] == 1) {
                                        $dienstgrad = $dginfo2['name_w'];
                                        $rdqualtext = $rdginfo2['name_w'];
                                    } else {
                                        $dienstgrad = $dginfo2['name'];
                                        $rdqualtext = $rdginfo2['name'];
                                    }

                                    echo "<tr>";
                                    echo "<td >" . $row['dienstnr'] . "</td>";
                                    echo "<td>" . $row['fullname'] .  "</td>";
                                    echo "<td>";
                                    if ($dginfo2['badge']) {
                                        echo "<img src='" . $dginfo2['badge'] . "' height='16px' width='auto' style='padding-right:5px' alt='Dienstgrad' />";
                                    }
                                    echo $dienstgrad;
                                    if (!$rdginfo2['none']) {
                                        echo " <span class='badge text-bg-warning' style='color:var(--black)'>" . $rdqualtext . "</span></td>";
                                    }
                                    echo "<td><span style='display:none'>" . $row['einstdatum'] . "</span>" . $einstellungsdatum . "</td>";
                                    echo "<td><a href='" . BASE_PATH . "admin/personal/profile.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary'>Ansehen</a></td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="/vendor/datatables.net/datatables.net/js/dataTables.min.js"></script>
    <script src="/vendor/datatables.net/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#mitarbeiterTable').DataTable({
                stateSave: true,
                paging: true,
                lengthMenu: [10, 20, 50, 100],
                pageLength: 20,
                order: [
                    [3, 'asc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }],
                language: {
                    "decimal": "",
                    "emptyTable": "Keine Daten vorhanden",
                    "info": "Zeige _START_ bis _END_  | Gesamt: _TOTAL_",
                    "infoEmpty": "Keine Daten verfügbar",
                    "infoFiltered": "| Gefiltert von _MAX_ Mitarbeitern",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "_MENU_ Mitarbeiter pro Seite anzeigen",
                    "loadingRecords": "Lade...",
                    "processing": "Verarbeite...",
                    "search": "Mitarbeiter suchen:",
                    "zeroRecords": "Keine Einträge gefunden",
                    "paginate": {
                        "first": "Erste",
                        "last": "Letzte",
                        "next": "Nächste",
                        "previous": "Vorherige"
                    },
                    "aria": {
                        "sortAscending": ": aktivieren, um Spalte aufsteigend zu sortieren",
                        "sortDescending": ": aktivieren, um Spalte absteigend zu sortieren"
                    }
                }
            });
        });
    </script>
    <?php include __DIR__ . "/../../assets/components/footer.php"; ?>
</body>

</html>