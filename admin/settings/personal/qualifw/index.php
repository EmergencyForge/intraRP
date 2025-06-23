<?php
session_start();
require_once __DIR__ . '/../../../../assets/config/config.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
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

<body data-bs-theme="dark" data-page="settings">
    <?php include __DIR__ . "/../../../../assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <h1 class="mb-0">FW Qualifikationen verwalten</h1>

                        <?php if (Permissions::check('admin')) : ?>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createDienstgradModal">
                                <i class="las la-plus"></i> Qualifikation erstellen
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php
                    Flash::render();
                    ?>
                    <div class="intra__tile py-2 px-3">
                        <table class="table table-striped" id="table-dienstgrade">
                            <thead>
                                <tr>
                                    <th scope="col">Priorität</th>
                                    <th scope="col">Bezeichnung <i class="las la-venus-mars"></i></th>
                                    <th scope="col">Bezeichnung <i class="las la-mars"></i></th>
                                    <th scope="col">Bezeichnung <i class="las la-venus"></i></th>
                                    <th scope="col">Leer?</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                require __DIR__ . '/../../../../assets/config/database.php';
                                $stmt = $pdo->prepare("SELECT * FROM intra_mitarbeiter_fwquali");
                                $stmt->execute();
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($result as $row) {
                                    $dimmed = '';

                                    switch ($row['none']) {
                                        case 0:
                                            $dgActive = "<span class='badge text-bg-success'>Nein</span>";
                                            break;
                                        default:
                                            $dgActive = "<span class='badge text-bg-danger'>Ja</span>";
                                            $dimmed = "style='color:var(--tag-color)'";
                                            break;
                                    }

                                    $actions = (Permissions::check('admin'))
                                        ? "<a title='Qualifikation bearbeiten' href='#' class='btn btn-sm btn-primary edit-btn' data-bs-toggle='modal' data-bs-target='#editDienstgradModal' data-id='{$row['id']}' data-shortname='{$row['shortname']}' data-name='{$row['name']}' data-name_m='{$row['name_m']}' data-name_w='{$row['name_w']}' data-priority='{$row['priority']}' data-none='{$row['none']}'><i class='las la-pen'></i></a>"
                                        : "";

                                    echo "<tr>";
                                    echo "<td " . $dimmed . ">" . $row['priority'] . "</td>";
                                    echo "<td " . $dimmed . ">" . $row['name'] . "</td>";
                                    echo "<td " . $dimmed . ">" . $row['name_m'] . "</td>";
                                    echo "<td " . $dimmed . ">" . $row['name_w'] . "</td>";
                                    echo "<td>" . $dgActive . "</td>";
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
    </div>

    <!-- MODAL BEGIN -->
    <?php if (Permissions::check('admin')) : ?>
        <div class="modal fade" id="editDienstgradModal" tabindex="-1" aria-labelledby="editDienstgradModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="<?= BASE_PATH ?>admin/settings/personal/qualifw/update.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editDienstgradModalLabel">FW Qualifikation bearbeiten</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="dienstgrad-id">

                            <div class="mb-3">
                                <label for="dienstgrad-shortname" class="form-label">Kurzbezeichnung <small style="opacity:.5">(z.B. B1,B2 etc.)</small></label>
                                <input type="text" class="form-control" name="shortname" id="dienstgrad-shortname" required>
                            </div>

                            <div class="mb-3">
                                <label for="dienstgrad-name" class="form-label">Bezeichnung <small style="opacity:.5">(Allgemein)</small></label>
                                <input type="text" class="form-control" name="name" id="dienstgrad-name" required>
                            </div>

                            <div class="mb-3">
                                <label for="dienstgrad-name_m" class="form-label">Bezeichnung <small style="opacity:.5">(Männlich)</small></label>
                                <input type="text" class="form-control" name="name_m" id="dienstgrad-name_m" required>
                            </div>

                            <div class="mb-3">
                                <label for="dienstgrad-name_w" class="form-label">Bezeichnung <small style="opacity:.5">(Weiblich)</small></label>
                                <input type="text" class="form-control" name="name_w" id="dienstgrad-name_w" required>
                            </div>

                            <div class="mb-3">
                                <label for="dienstgrad-priority" class="form-label">Priorität <small style="opacity:.5">(Je niedriger die Zahl, desto höher sortiert)</small></label>
                                <input type="number" class="form-control" name="priority" id="dienstgrad-priority" required>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="none" id="dienstgrad-none">
                                <label class="form-check-label" for="dienstgrad-none">Leer?</label>
                            </div>

                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <button type="button" class="btn btn-danger" id="delete-dienstgrad-btn">Löschen</button>

                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
                                <button type="submit" class="btn btn-primary">Speichern</button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-dienstgrad-form" action="<?= BASE_PATH ?>admin/settings/personal/qualifw/delete.php" method="POST" style="display:none;">
                        <input type="hidden" name="id" id="dienstgrad-delete-id">
                    </form>

                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- MODAL END -->
    <!-- MODAL 2 BEGIN -->
    <?php if (Permissions::check('admin')) : ?>
        <div class="modal fade" id="createDienstgradModal" tabindex="-1" aria-labelledby="createDienstgradModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="<?= BASE_PATH ?>admin/settings/personal/qualifw/create.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createDienstgradModalLabel">FW Qualifikation anlegen</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                        </div>
                        <div class="modal-body">

                            <div class="mb-3">
                                <label for="new-dienstgrad-shortname" class="form-label">Kurzbezeichnung <small style="opacity:.5">(z.B. B1,B2 etc.)</small></label>
                                <input type="text" class="form-control" name="shortname" id="new-dienstgrad-shortname" required>
                            </div>

                            <div class="mb-3">
                                <label for="new-dienstgrad-name" class="form-label">Bezeichnung <small style="opacity:.5">(Allgemein)</small></label>
                                <input type="text" class="form-control" name="name" id="new-dienstgrad-name" required>
                            </div>

                            <div class="mb-3">
                                <label for="new-dienstgrad-name_m" class="form-label">Bezeichnung <small style="opacity:.5">(Männlich)</small></label>
                                <input type="text" class="form-control" name="name_m" id="new-dienstgrad-name_m" required>
                            </div>

                            <div class="mb-3">
                                <label for="new-dienstgrad-name_w" class="form-label">Bezeichnung <small style="opacity:.5">(Weiblich)</small></label>
                                <input type="text" class="form-control" name="name_w" id="new-dienstgrad-name_w" required>
                            </div>

                            <div class="mb-3">
                                <label for="new-dienstgrad-priority" class="form-label">Priorität <small style="opacity:.5">(je niedriger, desto höher)</small></label>
                                <input type="number" class="form-control" name="priority" id="new-dienstgrad-priority" value="0" required>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="none" id="new-dienstgrad-none">
                                <label class="form-check-label" for="new-dienstgrad-none">Leer?</label>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
                            <button type="submit" class="btn btn-success">Erstellen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- MODAL 2 END -->


    <script src="<?= BASE_PATH ?>vendor/datatables.net/datatables.net/js/dataTables.min.js"></script>
    <script src="<?= BASE_PATH ?>vendor/datatables.net/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#table-dienstgrade').DataTable({
                stateSave: true,
                paging: true,
                lengthMenu: [5, 10, 20],
                pageLength: 10,
                order: [
                    [0, 'asc']
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
                    "infoFiltered": "| Gefiltert von _MAX_ Qualifikationen",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "_MENU_ Qualifikationen pro Seite anzeigen",
                    "loadingRecords": "Lade...",
                    "processing": "Verarbeite...",
                    "search": "Qualifikation suchen:",
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    document.getElementById('dienstgrad-id').value = id;
                    document.getElementById('dienstgrad-name').value = this.dataset.name;
                    document.getElementById('dienstgrad-name_m').value = this.dataset.name_m;
                    document.getElementById('dienstgrad-name_w').value = this.dataset.name_w;
                    document.getElementById('dienstgrad-priority').value = this.dataset.priority;
                    document.getElementById('dienstgrad-shortname').value = this.dataset.shortname;
                    document.getElementById('dienstgrad-none').checked = this.dataset.none == 1;

                    document.getElementById('dienstgrad-delete-id').value = id;
                });
            });

            document.getElementById('delete-dienstgrad-btn').addEventListener('click', function() {
                if (confirm('Möchtest du diese Qualifikation wirklich löschen?')) {
                    document.getElementById('delete-dienstgrad-form').submit();
                }
            });
        });
    </script>
</body>

</html>