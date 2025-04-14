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

if (!Permissions::check(['admin', 'files.log.view'])) {
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

<body data-bs-theme="dark" data-page="upload">
    <?php include "../../assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <h1><?= __('upload.log.title') ?></h1>
                    <div class="intra__tile py-2 px-3">
                        <table class="table table-striped" id="table-uploads">
                            <thead>
                                <th scope="col"><?= __('upload.log.id') ?></th>
                                <th scope="col"><?= __('upload.log.preview') ?></th>
                                <th scope="col"><?= __('upload.log.uploaded_by_at') ?></th>
                                <th scope="col"><?= __('upload.log.file_name') ?></th>
                                <th scope="col"><?= __('upload.log.file_link') ?></th>
                            </thead>
                            <tbody>
                                <?php
                                require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';
                                $stmt = $pdo->prepare("SELECT * FROM intra_uploads");
                                $stmt->execute();
                                $result = $stmt->fetchAll();
                                foreach ($result as $row) {
                                    $date = (new DateTime($row['upload_time']))->format('d.m.Y H:i');

                                    if (strpos($row['file_type'], 'image') !== false) {
                                        $preview = "<img src='/assets/upload/" . $row['file_name'] . "' alt='" . __('upload.log.image_preview') . "' height='64px' width='64px'>";
                                    } else {
                                        $preview = "<div>" . __('upload.log.no_preview') . "</div>";
                                    }

                                    echo "<tr>";
                                    echo "<td >" . $row['id'] . "</td>";
                                    echo "<td>" . $preview . "</td>";
                                    echo "<td>" . $row['user_name'] . " / " . $date . "</td>";
                                    echo "<td>" . $row['file_name'] . "</td>";
                                    echo "<td><a href='/assets/upload/" . $row['file_name'] . "' class='btn btn-sm btn-primary' target='_blank'><i class='las la-link'></i></a></td>";
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
            var table = $('#table-uploads').DataTable({
                stateSave: true,
                paging: true,
                lengthMenu: [5, 10, 20, 50],
                pageLength: 10,
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }],
                language: {
                    "decimal": "",
                    "emptyTable": <?= json_encode(__('datatable.emptytable')) ?>,
                    "info": <?= json_encode(__('datatable.info')) ?>,
                    "infoEmpty": <?= json_encode(__('datatable.infoempty')) ?>,
                    "infoFiltered": <?= json_encode(__('upload.log.datatable.infofiltered')) ?>,
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": <?= json_encode(__('upload.log.datatable.lengthmenu')) ?>,
                    "loadingRecords": <?= json_encode(__('datatable.loadingrecords')) ?>,
                    "processing": <?= json_encode(__('datatable.processing')) ?>,
                    "search": <?= json_encode(__('upload.log.datatable.search')) ?>,
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