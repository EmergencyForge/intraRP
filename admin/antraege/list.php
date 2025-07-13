<?php

session_start();
require_once __DIR__ . '/../../assets/config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    header("Location: " . BASE_PATH . "admin/login.php");
    exit();
}

use App\Auth\Permissions;
use App\Helpers\Flash;

if (!Permissions::check(['admin', 'application.edit'])) {
    Flash::set('error', 'no-permissions');
    header("Location: " . BASE_PATH . "admin/index.php");
}

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Anträge &rsaquo; <?php echo SYSTEM_NAME ?></title>
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
                    <h1 class="mb-5">Antragsübersicht</h1>
                    <div class="intra__tile py-2 px-3">
                        <table class="table table-striped" id="table-antrag">
                            <thead>
                                <th scope="col">Nr.</th>
                                <th scope="col">Von</th>
                                <th scope="col">Status</th>
                                <th scope="col">Datum</th>
                                <th scope="col"></th>
                            </thead>
                            <tbody>
                                <?php
                                require __DIR__ . '/../../assets/config/database.php';
                                $stmt = $pdo->prepare("SELECT * FROM intra_antrag_bef");
                                $stmt->execute();
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                if (count($result) > 0) {
                                    foreach ($result as $row) {
                                        $adddat = date("d.m.Y | H:i", strtotime($row['time_added']));
                                        $cirs_state = "Unbekannt";
                                        $bgColor = "";
                                        switch ($row['cirs_status']) {
                                            case 0:
                                                $cirs_state = "In Bearbeitung";
                                                break;
                                            case 1:
                                                $bgColor = "rgba(255,0,0,.05)";
                                                $cirs_state = "Abgelehnt";
                                                break;
                                            case 2:
                                                $cirs_state = "Aufgeschoben";
                                                break;
                                            case 3:
                                                $bgColor = "rgba(0,255,0,.05)";
                                                $cirs_state = "Angenommen";
                                                break;
                                        }

                                        echo "<tr";
                                        if (!empty($bgColor)) {
                                            echo " style='--bs-table-striped-bg: {$bgColor}; --bs-table-bg: {$bgColor};'";
                                        }
                                        echo ">
                                        <td>{$row['uniqueid']}</td>
                                        <td>{$row['name_dn']}</td>
                                        <td>{$cirs_state}</td>
                                        <td><span style='display:none'>{$row['time_added']}</span>{$adddat}</td>
                                        <td><a class='btn btn-main-color btn-sm' href='" . BASE_PATH . "admin/antraege/view.php?antrag={$row['uniqueid']}'>Öffnen</a></td>
                                    </tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_PATH ?>vendor/datatables.net/datatables.net/js/dataTables.min.js"></script>
    <script src="<?= BASE_PATH ?>vendor/datatables.net/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#table-antrag').DataTable({
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
                    "infoFiltered": "| Gefiltert von _MAX_ Anträgen",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "_MENU_ Anträge pro Seite anzeigen",
                    "loadingRecords": "Lade...",
                    "processing": "Verarbeite...",
                    "search": "Anträge suchen:",
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