<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/permissions.php';
if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

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
    <link rel="stylesheet" href="/assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="/assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/_ext/jquery/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/assets/_ext/datatables/datatables.min.css">
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
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <h1 class="mb-0">Dienstgrade verwalten</h1>

                        <?php if ($admincheck) : ?>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createDienstgradModal">
                                <i class="las la-plus"></i> Dienstgrad erstellen
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php
                    $alerts = [
                        'success' => [
                            'updated' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Änderung erfolgreich gespeichert.'],
                            'deleted' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Der Dienstgrad wurde erfolgreich gelöscht.'],
                            'created' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Der Dienstgrad wurde erfolgreich erstellt.'],
                        ],
                        'error' => [
                            'exception' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Beim Speichern ist ein Fehler aufgetreten.'],
                            'invalid' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Ungültige Eingabe.'],
                            'not-allowed' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Keine Berechtigung.'],
                            'not-found' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Der Dienstgrad wurde nicht gefunden.'],
                            'invalid-id' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Ungültige Dienstgrad-ID.'],
                            'invalid-input' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Bitte fülle alle Pflichtfelder aus, um einen Dienstgrad anzulegen.'],
                        ]
                    ];

                    foreach (['success', 'error'] as $type) {
                        if (isset($_GET[$type]) && isset($alerts[$type][$_GET[$type]])) {
                            $alert = $alerts[$type][$_GET[$type]];
                    ?>
                            <div class="alert alert-<?= htmlspecialchars($alert['type']) ?> alert-dismissible fade show" role="alert">
                                <strong><?= htmlspecialchars($alert['title']) ?></strong> <?= htmlspecialchars($alert['text']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
                            </div>
                    <?php
                            break;
                        }
                    }
                    ?>
                    <div class="intra__tile py-2 px-3">
                        <table class="table table-striped" id="table-dienstgrade">
                            <thead>
                                <tr>
                                    <th scope="col">Priorität</th>
                                    <th scope="col">Badge</th>
                                    <th scope="col">Bezeichnung <i class="las la-venus-mars"></i></th>
                                    <th scope="col">Bezeichnung <i class="las la-mars"></i></th>
                                    <th scope="col">Bezeichnung <i class="las la-venus"></i></th>
                                    <th scope="col">Archiv?</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';
                                $stmt = $pdo->prepare("SELECT * FROM intra_mitarbeiter_dienstgrade");
                                $stmt->execute();
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($result as $row) {
                                    $dimmed = '';

                                    switch ($row['archive']) {
                                        case 0:
                                            $dgActive = "<span class='badge text-bg-success'>Nein</span>";
                                            break;
                                        default:
                                            $dgActive = "<span class='badge text-bg-danger'>Ja</span>";
                                            $dimmed = "style='color:var(--tag-color)'";
                                            break;
                                    }

                                    if ($row['badge'] === NULL) {
                                        $badge = "";
                                    } else {
                                        $badge = "<img src='" . $row['badge'] . "' height='16px' width='auto' alt='Dienstgrad'>";
                                    }

                                    $actions = ($admincheck)
                                        ? "<a title='Fahrzeug bearbeiten' href='#' class='btn btn-sm btn-primary edit-btn' data-bs-toggle='modal' data-bs-target='#editDienstgradModal' data-id='{$row['id']}' data-name='{$row['name']}' data-name_m='{$row['name_m']}' data-name_w='{$row['name_w']}' data-badge='{$row['badge']}' data-priority='{$row['priority']}' data-archive='{$row['archive']}'><i class='las la-pen'></i></a>"
                                        : "";

                                    echo "<tr>";
                                    echo "<td " . $dimmed . ">" . $row['priority'] . "</td>";
                                    echo "<td>" . $badge . "</td>";
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
    <?php if ($admincheck) : ?>
        <div class="modal fade" id="editDienstgradModal" tabindex="-1" aria-labelledby="editDienstgradModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="/admin/personal/management/dienstgrade/update.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editDienstgradModalLabel">Dienstgrad bearbeiten</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="dienstgrad-id">

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
                                <label for="dienstgrad-badge" class="form-label">Badge <small style="opacity:.5">(Pfad oder URL, optional)</small></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="badge" id="dienstgrad-badge">
                                    <span class="input-group-text p-1" id="badge-preview-container">
                                        <img id="badge-preview" src="" alt="Preview" style="height:30px; display: none;">
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="dienstgrad-priority" class="form-label">Priorität <small style="opacity:.5">(Je niedriger die Zahl, desto höher sortiert)</small></label>
                                <input type="number" class="form-control" name="priority" id="dienstgrad-priority" required>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="archive" id="dienstgrad-archive">
                                <label class="form-check-label" for="dienstgrad-archive">Archiv?</label>
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

                    <form id="delete-dienstgrad-form" action="/admin/personal/management/dienstgrade/delete.php" method="POST" style="display:none;">
                        <input type="hidden" name="id" id="dienstgrad-delete-id">
                    </form>

                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- MODAL END -->
    <!-- MODAL 2 BEGIN -->
    <?php if ($admincheck) : ?>
        <div class="modal fade" id="createDienstgradModal" tabindex="-1" aria-labelledby="createDienstgradModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="/admin/personal/management/dienstgrade/create.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createDienstgradModalLabel">Neuen Dienstgrad anlegen</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                        </div>
                        <div class="modal-body">

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
                                <label for="new-dienstgrad-badge" class="form-label">Badge <small style="opacity:.5">(Pfad oder URL, optional)</small></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="badge" id="new-dienstgrad-badge">
                                    <span class="input-group-text p-1" id="new-badge-preview-container">
                                        <img id="new-badge-preview" src="" alt="Preview" style="height:30px; display: none;">
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="new-dienstgrad-priority" class="form-label">Priorität <small style="opacity:.5">(je niedriger, desto höher)</small></label>
                                <input type="number" class="form-control" name="priority" id="new-dienstgrad-priority" value="0" required>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="archive" id="new-dienstgrad-archive">
                                <label class="form-check-label" for="new-dienstgrad-archive">Archiv?</label>
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

    <script src="/assets/_ext/jquery/jquery.dataTables.min.js"></script>
    <script src="/assets/_ext/datatables/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#table-dienstgrade').DataTable({
                stateSave: true,
                paging: true,
                lengthMenu: [10, 20, 50],
                pageLength: 20,
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
                    "infoFiltered": "| Gefiltert von _MAX_ Dienstgraden",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "_MENU_ Dienstgrade pro Seite anzeigen",
                    "loadingRecords": "Lade...",
                    "processing": "Verarbeite...",
                    "search": "Dienstgrad suchen:",
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

            const badgeInput = document.getElementById('dienstgrad-badge');
            const badgePreview = document.getElementById('badge-preview');

            function updateBadgePreview() {
                const value = badgeInput.value.trim();
                if (value) {
                    badgePreview.src = value;
                    badgePreview.style.display = 'block';
                } else {
                    badgePreview.style.display = 'none';
                }
            }

            badgeInput.addEventListener('blur', updateBadgePreview);

            const newBadgeInput = document.getElementById('new-dienstgrad-badge');
            const newBadgePreview = document.getElementById('new-badge-preview');

            function updateNewBadgePreview() {
                const value = newBadgeInput.value.trim();
                if (value) {
                    newBadgePreview.src = value;
                    newBadgePreview.style.display = 'block';
                } else {
                    newBadgePreview.style.display = 'none';
                }
            }

            newBadgeInput.addEventListener('blur', updateNewBadgePreview);

            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    document.getElementById('dienstgrad-id').value = id;
                    document.getElementById('dienstgrad-name').value = this.dataset.name;
                    document.getElementById('dienstgrad-name_m').value = this.dataset.name_m;
                    document.getElementById('dienstgrad-name_w').value = this.dataset.name_w;
                    document.getElementById('dienstgrad-priority').value = this.dataset.priority;
                    document.getElementById('dienstgrad-badge').value = this.dataset.badge;
                    document.getElementById('dienstgrad-archive').checked = this.dataset.archive == 1;

                    document.getElementById('dienstgrad-delete-id').value = id;

                    updateBadgePreview();
                });
            });

            document.getElementById('delete-dienstgrad-btn').addEventListener('click', function() {
                if (confirm('Möchtest du diesen Dienstgrad wirklich löschen?')) {
                    document.getElementById('delete-dienstgrad-form').submit();
                }
            });
        });
    </script>
</body>

</html>