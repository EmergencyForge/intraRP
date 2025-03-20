<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/permissions.php';
if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    // Store the current page's URL in a session variable
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    // Redirect the user to the login page
    header("Location: /admin/login.php");
    exit();
} else if ($notadmincheck && !$edview) {
    header("Location: /admin/index.php");
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
    <link rel="stylesheet" href="/assets/fonts/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="/assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/jquery/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
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
    <meta property="og:url" content="https://<?php echo SYSTEM_URL ?>/dash.php" />
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
                    <?php if (isset($_GET['message']) && $_GET['message'] === 'error-1') { ?>
                        <div class="alert alert-danger" role="alert">
                            <h5>Fehler!</h5>
                            Du kannst dich nicht selbst bearbeiten!
                        </div>
                    <?php } else if (isset($_GET['message']) && $_GET['message'] === 'error-2') { ?>
                        <div class="alert alert-danger" role="alert">
                            <h5>Fehler!</h5>
                            Dazu hast du nicht die richtigen Berechtigungen!
                        </div>
                    <?php } ?>
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
                            require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';
                            $stmt = $pdo->prepare("SELECT * FROM intra_edivi WHERE hidden <> 1");
                            $stmt->execute();
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                $datetime = new DateTime($row['sendezeit']);
                                $date = $datetime->format('d.m.Y | H:i');
                                switch ($row['protokoll_status']) {
                                    case 0:
                                        $status = "<span class='badge bg-secondary'>Ungesehen</span>";
                                        break;
                                    case 1:
                                        $status = "<span title='Prüfer: " . $row['bearbeiter'] . "' class='badge bg-warning'>in Prüfung</span>";
                                        break;
                                    case 2:
                                        $status = "<span title='Prüfer: " . $row['bearbeiter'] . "' class='badge bg-success'>Geprüft</span>";
                                        break;
                                    default:
                                        $status = "<span title='Prüfer: " . $row['bearbeiter'] . "' class='badge bg-danger'>Ungenügend</span>";
                                        break;
                                }

                                switch ($row['freigegeben']) {
                                    default:
                                        $freigabe_status = "";
                                        break;
                                    case 1:
                                        $freigabe_status = "<span title='Freigeber: " . $row['freigeber_name'] . "' class='badge bg-success'>F</span>";
                                        break;
                                }

                                if (isset($_GET['view']) && $_GET['view'] == 1) {
                                    if ($row['protokoll_status'] != 0 && $row['protokoll_status'] != 1) {
                                        continue;
                                    }
                                }

                                $patname = $row['patname'] ?? "Unbekannt";

                                $actions = ($edview || $admincheck)
                                    ? "<a title='Protokoll ansehen' href='/admin/edivi/view.php?id={$row['id']}' class='btn btn-sm btn-primary'><i class='fa-solid fa-eye'></i></a> 
                                        <a title='Protokoll löschen' href='/admin/edivi/delete.php?id={$row['id']}' class='btn btn-sm btn-danger'><i class='fa-solid fa-trash'></i></a>"
                                    : "";

                                echo "<tr>";
                                echo "<td >" . $row['enr'] . "</td>";
                                echo "<td>" . $patname . "</td>";
                                echo "<td><span style='display:none'>" . $row['sendezeit'] . "</span>" . $date . "</td>";
                                echo "<td>" . $row['pfname'] . " " . $freigabe_status . "</td>";
                                echo "<td>" . $status . "</td>";
                                echo "<td>{$actions}</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
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
</body>

</html>