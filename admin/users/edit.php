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
use App\Utils\AuditLogger;

if (!Permissions::check(['admin', 'users.edit'])) {
    Flash::set('error', 'no-permissions');
    header("Location: " . BASE_PATH . "admin/users/list.php?message=error-2");
}

require __DIR__ . '/../../assets/config/database.php';

$userid = $_SESSION['userid'];

$stmt = $pdo->prepare("SELECT * FROM intra_users WHERE id = :id");
$stmt->bindParam(':id', $_GET['id']);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$roleID = $row['role'];

$stmt2 = $pdo->prepare("SELECT* FROM intra_users_roles WHERE id = :roleID");
$stmt2->execute(['roleID' => $row['role']]);
$rowrole = $stmt2->fetch(PDO::FETCH_ASSOC);

if ($row['id'] == $userid) {
    Flash::set('user', 'edit-self');
    header("Location: " . BASE_PATH . "admin/users/list.php");
}

if ($rowrole['priority'] <= $_SESSION['role_priority']) {
    Flash::set('user', 'low_permissions');
    header("Location: " . BASE_PATH . "admin/users/list.php");
}

if (isset($_POST['new']) && $_POST['new'] == 1) {
    $id = $_REQUEST['id'];
    $username = $_REQUEST['username'];
    $fullname = $_REQUEST['fullname'];
    $aktenid = trim($_POST['aktenid']) !== '' ? $_POST['aktenid'] : null;
    $role = $_REQUEST['role'];

    $sql = "UPDATE intra_users 
        SET fullname = :fullname, 
            aktenid = :aktenid, 
            role = :role 
        WHERE id = :id";

    $stmti = $pdo->prepare($sql);
    $stmti->execute([
        'fullname' => $fullname,
        'aktenid' => $aktenid,
        'role' => $role,
        'id' => $id
    ]);

    $auditLogger = new AuditLogger($pdo);
    $auditLogger->log($userid, 'Benutzer aktualisiert [ID: ' . $id . ']', NULL, 'Benutzer', 1);

    header("Refresh: 0");
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $row['username'] ?> &rsaquo; Administration &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/style.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/admin.min.css" />
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
    <meta name="theme-color" content="<?php echo SYSTEM_COLOR ?>" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="https://<?php echo SYSTEM_URL . BASE_PATH ?>/dashboard.php" />
    <meta property="og:title" content="<?php echo SYSTEM_NAME ?> - Intranet <?php echo SERVER_CITY ?>" />
    <meta property="og:image" content="<?php echo META_IMAGE_URL ?>" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />

</head>

<body data-bs-theme="dark" data-page="benutzer">
    <?php include "../../assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <h1 class="mb-3">Benutzer bearbeiten <span class="mx-3"></span> <?php if (Permissions::check(['admin', 'users.delete'])) : ?><button class="btn btn-main-color btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="las la-trash"></i> Benutzer löschen</button><?php endif; ?></h1>

                    <form name="form" method="post" action="">
                        <input type="hidden" name="new" value="1" />
                        <input name="id" type="hidden" value="<?= $row['id'] ?>" />
                        <div class="row">
                            <div class="col me-2">
                                <div class="intra__tile py-2 px-3">
                                    <div class="row">
                                        <div class="col mb-3">
                                            <label for="username" class="form-label fw-bold">Benutzername <span class="text-main-color">*</span></label>
                                            <input type="text" class="form-control" id="username" name="username" placeholder="" value="<?= $row['username'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col mb-3">
                                            <label for="fullname" class="form-label fw-bold">Vor- und Zuname <span class="text-main-color">*</span></label>
                                            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="" value="<?= $row['fullname'] ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="intra__tile py-2 px-3">
                                    <div class="row">
                                        <div class="col mb-3">
                                            <label for="aktenid" class="form-label fw-bold">Mitarbeiterakten-ID</label>
                                            <input type="number" class="form-control" id="aktenid" name="aktenid" placeholder="" value="<?= $row['aktenid'] ?? NULL ?>">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col mb-3">
                                            <label for="role" class="form-label fw-bold">Rolle/Gruppe <span class="text-main-color">*</span></label>
                                            <select name="role" id="role" class="form-select" required>
                                                <?php
                                                require __DIR__ . '/../../assets/config/database.php';
                                                $stmt = $pdo->prepare("SELECT * FROM intra_users_roles WHERE priority > :own_prio");
                                                $stmt->execute(['own_prio' => $_SESSION['role_priority']]);
                                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($result as $rr) {
                                                    if ($rr['id'] == $roleID) {
                                                        echo "<option value ='{$rr['id']}' selected='selected'>{$rr['name']}</option>";
                                                    } else {
                                                        echo "<option value ='{$rr['id']}'>{$rr['name']}</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3 mx-auto">
                                <input class="mt-4 btn btn-success btn-sm" name="submit" type="submit" value="Änderungen speichern" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php if (Permissions::check(['admin', 'audit.view'])) : ?>
                <h1 class="mb-3">Benutzer-Log</h1>
                <div class="row">
                    <div class="col">
                        <div class="intra__tile py-2 px-3">
                            <table class="table table-striped" id="table-audit">
                                <thead>
                                    <tr>
                                        <th scope="col">Zeitstempel</th>
                                        <th scope="col">Modul</th>
                                        <th scope="col">Aktion</th>
                                        <th scope="col">Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    require __DIR__ . '/../../assets/config/database.php';
                                    $stmt = $pdo->prepare("SELECT * FROM intra_audit_log WHERE user = :userid");
                                    $stmt->execute(
                                        ['userid' => $row['id']]
                                    );
                                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($result as $rr) {


                                        $datetime = new DateTime($rr['timestamp']);
                                        $date = $datetime->format('d.m.Y  H:i:s');

                                        echo "<tr>";
                                        echo "<td>" . $date . "</td>";
                                        echo "<td class='fw-bold'>" . $rr['module'] . "</td>";
                                        echo "<td>" . $rr['action'] . "</td>";
                                        echo "<td>" . $rr['details'] . "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL BEGIN -->
    <?php if (Permissions::check(['admin', 'users.delete'])) : ?>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Bestätigung erforderlich</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Willst du wirklich den Benutzer <span class="fw-bold"><?= $row['username'] ?></span> löschen?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-color" data-bs-dismiss="modal">Ne, Upsi</button>
                        <button type="button" class="btn btn-main-color" onclick="window.location.href='delete.php?id=<?= $row['id'] ?>';">Benutzer endgültig löschen</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- MODAL END -->
    <?php include __DIR__ . "/../../assets/components/footer.php"; ?>

    <script src="/vendor/datatables.net/datatables.net/js/dataTables.min.js"></script>
    <script src="/vendor/datatables.net/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#table-audit').DataTable({
                stateSave: true,
                paging: true,
                lengthMenu: [10, 20, 40],
                pageLength: 20,
                order: [
                    [0, 'desc']
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
                    "infoFiltered": "| Gefiltert von _MAX_ Einträgen",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "_MENU_ Einträge pro Seite anzeigen",
                    "loadingRecords": "Lade...",
                    "processing": "Verarbeite...",
                    "search": "Eintrag suchen:",
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