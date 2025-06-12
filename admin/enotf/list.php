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

if (!Permissions::check(['admin', 'edivi.view'])) {
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
    <title>Administration &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/style.min.css" />
    <link rel="stylesheet" href="/assets/css/admin.min.css" />
    <link rel="stylesheet" href="/assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="/assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <script src="/vendor/components/jquery/jquery.min.js"></script>
    <script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/vendor/datatables.net/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="<?php echo SYSTEM_NAME ?>" />
    <link rel="manifest" href="/assets/favicon/site.webmanifest" />
    <!-- Metas -->
    <meta name="theme-color" content="<?php echo SYSTEM_COLOR ?>" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="https://<?php echo SYSTEM_URL ?>/dashboard.php" />
    <meta property="og:title" content="<?php echo SYSTEM_NAME ?> - Intranet <?php echo SERVER_CITY ?>" />
    <meta property="og:image" content="<?php echo META_IMAGE_URL ?>" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />

</head>

<body data-bs-theme="dark" data-page="edivi">
    <?php include "../../assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <h1 class="mb-5">Protokollübersicht</h1>
                    <div class="my-3">
                        <?php if (!isset($_GET['view']) or $_GET['view'] != 1) { ?>
                            <a href="?view=1" class="btn btn-secondary btn-sm">Bearbeitete ausblenden</a>
                        <?php } else { ?>
                            <a href="?view=0" class="btn btn-primary btn-sm">Bearbeitete einblenden</a>
                        <?php } ?>
                    </div>
                    <?php
                    Flash::render();
                    ?>
                    <div class="intra__tile py-2 px-3">
                        <table class="table table-striped" id="table-protokoll">
                            <thead>
                                <th scope="col">Einsatznummer</th>
                                <th scope="col">Patient</th>
                                <th scope="col">Angelegt am</th>
                                <th scope="col">Protokollant</th>
                                <th scope="col">Status</th>
                                <th scope="col"></th>
                            </thead>
                            <tbody>
                                <?php
                                require __DIR__ . '/../../assets/config/database.php';
                                $stmt = $pdo->prepare("SELECT * FROM intra_edivi WHERE hidden <> 1");
                                $stmt->execute();
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($result as $row) {
                                    $datetime = new DateTime($row['sendezeit']);
                                    $date = $datetime->format('d.m.Y | H:i');
                                    switch ($row['protokoll_status']) {
                                        case 0:
                                            $status = "<span class='badge text-bg-secondary'>Ungesehen</span>";
                                            break;
                                        case 1:
                                            $status = "<span title='Prüfer: " . $row['bearbeiter'] . "' class='badge text-bg-warning'>in Prüfung</span>";
                                            break;
                                        case 2:
                                            $status = "<span title='Prüfer: " . $row['bearbeiter'] . "' class='badge text-bg-success'>Geprüft</span>";
                                            break;
                                        default:
                                            $status = "<span title='Prüfer: " . $row['bearbeiter'] . "' class='badge text-bg-danger'>Ungenügend</span>";
                                            break;
                                    }

                                    switch ($row['freigegeben']) {
                                        default:
                                            $freigabe_status = "";
                                            break;
                                        case 1:
                                            $freigabe_status = "<span title='Freigeber: " . $row['freigeber_name'] . "' class='badge text-bg-success'>F</span>";
                                            break;
                                    }

                                    if (isset($_GET['view']) && $_GET['view'] == 1) {
                                        if ($row['protokoll_status'] != 0 && $row['protokoll_status'] != 1) {
                                            continue;
                                        }
                                    }

                                    $patname = $row['patname'] ?? "Unbekannt";

                                    $actions = (Permissions::check(['admin', 'edivi.edit']))
                                        ? "<a title='QM-Aktionen öffnen' href='/admin/enotf/qm-actions.php?id={$row['id']}' class='btn btn-sm btn-main-color' target='_blank'><i class='las la-exclamation'></i></a> <a title='QM-Log öffnen' href='/admin/enotf/qm-log.php?id={$row['id']}' class='btn btn-sm btn-dark' target='_blank'><i class='las la-paperclip'></i></a> <a title='Protokoll löschen' href='/admin/enotf/delete.php?id={$row['id']}' class='btn btn-sm btn-danger'><i class='las la-trash'></i></a>"
                                        : "";

                                    echo "<tr>";
                                    echo "<td >" . $row['enr'] . "</td>";
                                    echo "<td>" . $patname . "</td>";
                                    echo "<td><span style='display:none'>" . $row['sendezeit'] . "</span>" . $date . "</td>";
                                    echo "<td>" . $row['pfname'] . " " . $freigabe_status . "</td>";
                                    echo "<td>" . $status . "</td>";
                                    echo "<td><a title='Protokoll ansehen' href='/enotf/prot/index.php?enr={$row['enr']}' class='btn btn-sm btn-primary' target='_blank'><i class='las la-eye'></i></a> {$actions}</td>";
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
            var table = $('#table-protokoll').DataTable({
                stateSave: true,
                paging: true,
                lengthMenu: [10, 20, 50, 100],
                pageLength: 20,
                order: [
                    [2, 'desc']
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
                    "infoFiltered": "| Gefiltert von _MAX_ Protokollen",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "_MENU_ Protokolle pro Seite anzeigen",
                    "loadingRecords": "Lade...",
                    "processing": "Verarbeite...",
                    "search": "Protokoll suchen:",
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