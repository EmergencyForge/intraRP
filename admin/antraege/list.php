<?php

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    header("Location: /admin/login.php");
    exit();
}

use App\Auth\Permissions;
use App\Helpers\Flash;
use App\Localization\Lang;

Lang::setLanguage(LANG ?? 'de');

if (!Permissions::check(['admin', 'application.edit'])) {
    Flash::set('error', 'no-permissions');
    header("Location: /admin/index.php");
}

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

<body data-bs-theme="dark" data-page="antrag">
    <?php include "../../assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <h1 class="mb-5"><?= __('application.list.title') ?></h1>
                    <div class="intra__tile py-2 px-3">
                        <table class="table table-striped" id="table-antrag">
                            <thead>
                                <th scope="col"><?= __('application.list.table.nr') ?></th>
                                <th scope="col"><?= __('application.list.table.from') ?></th>
                                <th scope="col"><?= __('application.list.table.status') ?></th>
                                <th scope="col"><?= __('application.list.table.date') ?></th>
                                <th scope="col"></th>
                            </thead>
                            <tbody>
                                <?php
                                require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';
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
                                                $cirs_state = __('application.status.0');
                                                break;
                                            case 1:
                                                $bgColor = "rgba(255,0,0,.05)";
                                                $cirs_state = __('application.status.1');
                                                break;
                                            case 2:
                                                $cirs_state = __('application.status.2');
                                                break;
                                            case 3:
                                                $bgColor = "rgba(0,255,0,.05)";
                                                $cirs_state = __('application.status.3');
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
                                        <td><a class='btn btn-main-color btn-sm' href='/admin/antraege/view.php?antrag={$row['uniqueid']}'>" . __('application.list.table.open') . "</a></td>
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

    <script src="/vendor/datatables.net/datatables.net/js/dataTables.min.js"></script>
    <script src="/vendor/datatables.net/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
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
                    "emptyTable": <?= json_encode(__('datatable.emptytable')) ?>,
                    "info": <?= json_encode(__('datatable.info')) ?>,
                    "infoEmpty": <?= json_encode(__('datatable.infoempty')) ?>,
                    "infoFiltered": <?= json_encode(__('application.list.datatable.infofiltered')) ?>,
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": <?= json_encode(__('application.list.datatable.lengthmenu')) ?>,
                    "loadingRecords": <?= json_encode(__('datatable.loadingrecords')) ?>,
                    "processing": <?= json_encode(__('datatable.processing')) ?>,
                    "search": <?= json_encode(__('application.list.datatable.search')) ?>,
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