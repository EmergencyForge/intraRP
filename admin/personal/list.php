<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';
if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    header("Location: /admin/login.php");
    exit();
}

use App\Auth\Permissions;
use App\Helpers\Flash;
use App\Localization\Lang;

Lang::setLanguage(LANG ?? 'de');

if (!Permissions::check(['admin', 'personnel.view'])) {
    Flash::set('error', 'no-permissions');
    header("Location: /admin/index.php");
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
    <title><?= __('title', [SYSTEM_NAME]) ?></title>
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
    <meta property="og:title" content="<?= __('metas.title', [SYSTEM_NAME, SERVER_CITY]) ?>" />
    <meta property="og:image" content="<?php echo META_IMAGE_URL ?>" />
    <meta property="og:description" content="<?= __('metas.description', [RP_ORGTYPE, SERVER_CITY]) ?>" />

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
                            <h1><?= __('personnel.list.title') ?></h1>
                        </div>
                        <div class="col">
                            <div class="d-flex justify-content-end">
                                <?php if (isset($_GET['archiv'])) { ?>
                                    <a href="/admin/personal/list.php" class="btn btn-success"><?= __('personnel.list.active_profiles') ?></a>
                                <?php } else { ?>
                                    <a href="/admin/personal/list.php?archiv" class="btn btn-danger"><?= __('personnel.list.archived_profiles') ?></a>
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
                                <th scope="col"><?= __('personnel.list.table.servicenr') ?></th>
                                <th scope="col"><?= __('personnel.list.table.name') ?></th>
                                <th scope="col"><?= __('personnel.list.table.rank') ?></th>
                                <th scope="col"><?= __('personnel.list.table.hiring_date') ?></th>
                                <th scope="col"></th>
                            </thead>
                            <tbody>
                                <?php
                                require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

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
                                        echo "<img src='" . $dginfo2['badge'] . "' height='16px' width='auto' style='padding-right:5px' alt='" . __('personnel.list.table.rank') . "' />";
                                    }
                                    echo $dienstgrad;
                                    if (!$rdginfo2['none']) {
                                        echo " <span class='badge text-bg-warning' style='color:var(--black)'>" . $rdqualtext . "</span></td>";
                                    }
                                    echo "<td><span style='display:none'>" . $row['einstdatum'] . "</span>" . $einstellungsdatum . "</td>";
                                    echo "<td><a href='/admin/personal/profile.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary'>" . __('personnel.list.table.view') . "</a></td>";
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
                    "emptyTable": <?= json_encode(__('datatable.emptytable')) ?>,
                    "info": <?= json_encode(__('datatable.info')) ?>,
                    "infoEmpty": <?= json_encode(__('datatable.infoempty')) ?>,
                    "infoFiltered": <?= json_encode(__('personnel.list.datatable.infofiltered')) ?>,
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": <?= json_encode(__('personnel.list.datatable.lengthmenu')) ?>,
                    "loadingRecords": <?= json_encode(__('datatable.loadingrecords')) ?>,
                    "processing": <?= json_encode(__('datatable.processing')) ?>,
                    "search": <?= json_encode(__('personnel.list.datatable.search')) ?>,
                    "zeroRecords": <?= json_encode(__('datatable.zerorecords')) ?>,
                    "paginate": {
                        "first": <?= json_encode(__('datatable.paginate.first')) ?>,
                        "last": <?= json_encode(__('datatable.paginate.last')) ?>,
                        "next": <?= json_encode(__('datatable.paginate.next')) ?>,
                        "previous": <?= json_encode(__('datatable.paginate.previous')) ?>
                    },
                    "aria": {
                        "sortAscending": <?= json_encode(__('datatable.aria.sortascending')) ?>,
                        "sortDescending": <?= json_encode(__('datatable.aria.sortdescending')) ?>
                    }
                }
            });
        });
    </script>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/assets/components/footer.php"; ?>
</body>

</html>