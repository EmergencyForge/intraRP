<?php
session_start();
require_once __DIR__ . '/../../../assets/config/config.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    header("Location: /admin/login.php");
    exit();
}

use App\Auth\Permissions;
use App\Helpers\Flash;

if (!Permissions::check(['admin', 'users.view'])) {
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

<body data-bs-theme="dark" data-page="benutzer">
    <?php include __DIR__ . "/../../../assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <h1 class="mb-0">Rollenverwaltung</h1>
                        <?php if (Permissions::check('full_admin')) : ?>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                                <i class="las la-plus"></i> Rolle erstellen
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php
                    Flash::render();
                    ?>
                    <div class="intra__tile py-2 px-3">
                        <table class="table table-striped" id="table-rollen">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Priorität</th>
                                    <th scope="col">Bezeichnung</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                require __DIR__ . '/../../../assets/config/database.php';
                                $stmt = $pdo->prepare("SELECT * FROM intra_users_roles");
                                $stmt->execute();
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($result as $row) {

                                    $actions = (Permissions::check('full_admin'))
                                        ? "<a title='Rolle bearbeiten' href='#' class='btn btn-sm btn-primary edit-btn' data-bs-toggle='modal' data-bs-target='#editRoleModal' data-id='{$row['id']}' data-name='{$row['name']}' data-priority='{$row['priority']}' data-color='{$row['color']}' data-perms='{$row['permissions']}'><i class='las la-pen'></i></a>"
                                        : "";

                                    echo "<tr>";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td>" . $row['priority'] . "</td>";
                                    echo "<td><span class='badge text-bg-" . $row['color'] . "'>" . $row['name'] . "</span></td>";
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
        <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="/admin/users/roles/update.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editRoleModalLabel">Rolle bearbeiten</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="role-id">

                            <div class="mb-3">
                                <label for="role-name" class="form-label">Bezeichnung</label>
                                <input type="text" class="form-control" name="name" id="role-name" required>
                            </div>

                            <div class="mb-3">
                                <label for="role-priority" class="form-label">Priorität <small style="opacity:.5">(Je niedriger die Zahl, desto höher sortiert)</small></label>
                                <input type="number" class="form-control" name="priority" id="role-priority" required>
                            </div>

                            <?php
                            $permission_groups = [
                                'Anträge' => [
                                    'application.view' => 'Anträge ansehen',
                                    'application.edit' => 'Anträge bearbeiten'
                                ],
                                'eNOTF' => [
                                    'edivi.view' => 'eNOTF Protokolle ansehen',
                                    'edivi.edit' => 'eNOTF Protokolle bearbeiten'
                                ],
                                'Benutzer' => [
                                    'users.view' => 'Benutzer ansehen',
                                    'users.edit' => 'Benutzer bearbeiten',
                                    'users.create' => 'Benutzer erstellen',
                                    'users.delete' => 'Benutzer löschen'
                                ],
                                'Personal' => [
                                    'personnel.view' => 'Mitarbeiter ansehen',
                                    'personnel.edit' => 'Mitarbeiter bearbeiten',
                                    'personnel.delete' => 'Mitarbeiter löschen',
                                    'personnel.comment.delete' => 'Mitarbeiter-Kommentare löschen',
                                    'personnel.documents.manage' => 'Mitarbeiter-Dokumente verwalten',
                                    'audit.view' => 'Logs einsehen',
                                ],
                                'Dateien' => [
                                    'files.upload' => 'Dateien hochladen',
                                    'files.log.view' => 'Datei-Uploads einsehen'
                                ],
                                'Sonstiges' => [
                                    'admin' => '<strong> Admin (Alle Rechte)</strong>',
                                    'dashboard.manage' => 'Dashboard verwalten'
                                ]
                            ];
                            ?>
                            <div class="mb-3">
                                <label class="form-label">Berechtigungen</label>

                                <?php foreach ($permission_groups as $groupName => $group): ?>
                                    <div class="mb-3 border-bottom pb-2">
                                        <h6 class="mb-2"><span style="opacity:.5;font-size:.8rem"><?= $groupName ?></span></h6>
                                        <div class="row">
                                            <?php foreach ($group as $perm => $desc): ?>
                                                <div class="col-6 mb-1">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $perm ?>" id="perm-<?= $perm ?>">
                                                        <label class="form-check-label" for="perm-<?= $perm ?>">
                                                            <?= $desc ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Badge</label>
                                <div class="row">
                                    <?php
                                    $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];
                                    foreach ($colors as $color) :
                                    ?>
                                        <div class="col-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="color" id="role-color-<?= $color ?>" value="<?= $color ?>" required>
                                                <label class="form-check-label w-100" for="role-color-<?= $color ?>">
                                                    <span class="badge text-bg-<?= $color ?> w-100 py-2 d-block text-center"><?= ucfirst($color) ?></span>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <button type="button" class="btn btn-danger" id="delete-role-btn">Löschen</button>
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
                                <button type="submit" class="btn btn-primary">Speichern</button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-role-form" action="/admin/users/roles/delete.php" method="POST" style="display:none;">
                        <input type="hidden" name="id" id="role-delete-id">
                    </form>
                </div>
            </div>
        </div>
        </div>
    <?php endif; ?>
    <!-- MODAL END -->
    <!-- MODAL 2 BEGIN -->
    <?php if (Permissions::check('full_admin')) : ?>
        <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="/admin/users/roles/create.php" method="POST">

                        <div class="modal-header">
                            <h5 class="modal-title" id="createRoleModalLabel">Neue Rolle erstellen</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                        </div>

                        <div class="modal-body">

                            <div class="mb-3">
                                <label for="new-role-name" class="form-label">Bezeichnung</label>
                                <input type="text" class="form-control" name="name" id="new-role-name" required>
                            </div>

                            <div class="mb-3">
                                <label for="new-role-priority" class="form-label">Priorität</label>
                                <input type="number" class="form-control" name="priority" id="new-role-priority" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Berechtigungen</label>
                                <div class="row">
                                    <?php foreach ($permission_groups as $groupName => $group): ?>
                                        <div class="mb-2 border-bottom pb-2">
                                            <h6 class="mb-2"><span style="opacity:.5;font-size:.8rem"><?= $groupName ?></span></h6>
                                            <div class="row">
                                                <?php foreach ($group as $perm => $desc): ?>
                                                    <div class="col-6 mb-1">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $perm ?>" id="perm-create-<?= $perm ?>">
                                                            <label class="form-check-label" for="perm-create-<?= $perm ?>"><?= $desc ?></label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Badge</label>
                                <div class="row">
                                    <?php
                                    $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];
                                    foreach ($colors as $color) :
                                    ?>
                                        <div class="col-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="color" id="new-role-color-<?= $color ?>" value="<?= $color ?>" required>
                                                <label class="form-check-label w-100" for="new-role-color-<?= $color ?>">
                                                    <span class="badge text-bg-<?= $color ?> w-100 py-2 d-block text-center"><?= ucfirst($color) ?></span>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
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


    <script src="/vendor/datatables.net/datatables.net/js/dataTables.min.js"></script>
    <script src="/vendor/datatables.net/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#table-rollen').DataTable({
                stateSave: true,
                paging: true,
                lengthMenu: [5, 10, 20],
                pageLength: 10,
                order: [
                    [1, 'asc']
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
                    "infoFiltered": "| Gefiltert von _MAX_ Rollen",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "_MENU_ Rollen pro Seite anzeigen",
                    "loadingRecords": "Lade...",
                    "processing": "Verarbeite...",
                    "search": "Rolle suchen:",
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
                    document.getElementById('role-id').value = id;
                    document.getElementById('role-name').value = this.dataset.name;
                    document.getElementById('role-priority').value = this.dataset.priority;

                    const perms = JSON.parse(this.dataset.perms || '[]');

                    document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
                        checkbox.checked = perms.includes(checkbox.value);
                    });

                    const colorValue = this.dataset.color || '';
                    const radio = document.getElementById('role-color-' + colorValue);
                    if (radio) radio.checked = true;

                    document.getElementById('role-delete-id').value = id;
                });
            });

            document.getElementById('delete-role-btn').addEventListener('click', function() {
                if (confirm('Möchtest du diese Rolle wirklich löschen?')) {
                    document.getElementById('delete-role-form').submit();
                }
            });
        });
    </script>
</body>

</html>