<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    header("Location: /admin/login.php");
    exit();
}

use App\Helpers\Flash;
use App\Localization\Lang;

Lang::setLanguage(LANG ?? 'de');

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

<body data-bs-theme="dark" data-page="dashboard">
    <!-- PRELOAD -->

    <?php include $_SERVER['DOCUMENT_ROOT'] . "/assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row" id="startpage">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <h1><?= Lang::get('dashboard.dashboard') ?></h1>
                    <?php
                    Flash::render();
                    ?>
                    <div class="alert alert-primary" role="alert">
                        <h3><?= __('dashboard.welcome', [$_SESSION['cirs_user']]) ?></h3>
                        <span id="quote-of-the-day"></span>
                        <br>
                    </div>
                    <div class="row">
                        <div class="col-6 me-2 intra__tile">
                            <?php require $_SERVER['DOCUMENT_ROOT'] . "/assets/config/database.php";
                            $stmt = $pdo->prepare("SELECT cirs_status, COUNT(*) as count FROM intra_antrag_bef GROUP BY cirs_status");
                            $stmt->execute();
                            $result3 = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            $data3 = [];
                            $labels3 = [];

                            $antragStatus = larray('dashboard.charts.application.status');

                            if (!empty($result3)) {
                                foreach ($result3 as $row) {
                                    $status = $row['cirs_status'];
                                    $rankLabel = isset($antragStatus[$status]) ? $antragStatus[$status] : __('dashboard.charts.unknown');

                                    $data3[] = $row['count'];
                                    $labels3[] = $rankLabel;
                                }
                            }

                            ?>
                            <canvas id="antragChart"></canvas>
                            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                            <script>
                                var ctx = document.getElementById('antragChart').getContext('2d');
                                var chart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: <?php echo json_encode($labels3); ?>,
                                        datasets: [{
                                            label: <?= json_encode(__('dashboard.charts.application.title')) ?>,
                                            data: <?php echo json_encode($data3); ?>,
                                            backgroundColor: 'rgba(110, 168, 254, .7)',
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                precision: 0
                                            }
                                        }
                                    }
                                });
                            </script>
                        </div>
                        <div class="col intra__tile">

                            <?php
                            $statusMapping = larray('dashboard.charts.edivi.status');

                            $stmt = $pdo->prepare("SELECT protokoll_status, COUNT(*) as count FROM intra_edivi GROUP BY protokoll_status");
                            $stmt->execute();
                            $result2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            $data2 = [];
                            $labels2 = [];

                            if (!empty($result2)) {
                                foreach ($result2 as $row) {
                                    $status = $row['protokoll_status'];
                                    $statusLabel = isset($statusMapping[$status]) ? $statusMapping[$status] : 'Unbekannt';

                                    $data2[] = $row['count'];
                                    $labels2[] = $statusLabel;
                                }
                            }
                            ?>
                            <canvas id="statusChart"></canvas>
                            <script>
                                var ctx2 = document.getElementById('statusChart').getContext('2d');
                                var chart2 = new Chart(ctx2, {
                                    type: 'bar',
                                    data: {
                                        labels: <?php echo json_encode($labels2); ?>,
                                        datasets: [{
                                            label: <?= json_encode(__('dashboard.charts.edivi.title')) ?>,
                                            data: <?php echo json_encode($data2); ?>,
                                            backgroundColor: 'rgba(255, 164, 47, .7)',
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                precision: 0
                                            }
                                        }
                                    }
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $quotes = [
        __('dashboard.quotes.quote-1', [RP_ORGTYPE, SERVER_CITY]),
        __('dashboard.quotes.quote-2'),
        __('dashboard.quotes.quote-3', [SERVER_CITY, RP_ORGTYPE, '']),
        __('dashboard.quotes.quote-4', [RP_ORGTYPE, SERVER_CITY]),
        __('dashboard.quotes.quote-5', [SYSTEM_NAME]),
    ];
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const quotes = <?= json_encode($quotes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];

            document.getElementById("quote-of-the-day").textContent = randomQuote;
        });
    </script>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/assets/components/footer.php"; ?>
</body>

</html>