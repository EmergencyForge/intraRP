<?php
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_samesite', 'None');
    ini_set('session.cookie_secure', '1');
}

session_start();
require_once __DIR__ . '/../../assets/config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../assets/config/database.php';

use App\Auth\Permissions;

$daten = array();
$vitals = array();

if (isset($_GET['enr'])) {
    // Basis-Daten laden
    $queryget = "SELECT * FROM intra_edivi WHERE enr = :enr";
    $stmt = $pdo->prepare($queryget);
    $stmt->execute(['enr' => $_GET['enr']]);
    $daten = $stmt->fetch(PDO::FETCH_ASSOC);

    if (count($daten) == 0) {
        header("Location: " . BASE_PATH . "enotf/");
        exit();
    }

    // Vitalparameter-Verlauf laden
    $queryVitals = "SELECT * FROM intra_edivi_vitalparameter WHERE enr = :enr ORDER BY zeitpunkt ASC";
    $stmtVitals = $pdo->prepare($queryVitals);
    $stmtVitals->execute(['enr' => $_GET['enr']]);
    $vitals = $stmtVitals->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: " . BASE_PATH . "enotf/");
    exit();
}

if ($daten['freigegeben'] == 1) {
    $ist_freigegeben = true;
} else {
    $ist_freigegeben = false;
}

$daten['last_edit'] = !empty($daten['last_edit']) ? (new DateTime($daten['last_edit']))->format('d.m.Y H:i') : NULL;
$enr = $daten['enr'];
$prot_url = "https://" . SYSTEM_URL . "/enotf/prot/index.php?enr=" . $enr;

date_default_timezone_set('Europe/Berlin');
$currentTime = date('H:i');
$currentDate = date('d.m.Y');

// JSON-Daten für Chart.js vorbereiten
$chartLabels = [];
$chartSpo2 = [];
$chartRRSys = [];
$chartRRDias = [];
$chartHerzfreq = [];
$chartAtemfreq = [];
$chartTemp = [];
$chartEtco2 = [];
$chartBz = [];

foreach ($vitals as $vital) {
    $zeitpunkt = new DateTime($vital['zeitpunkt']);
    $chartLabels[] = $zeitpunkt->format('H:i');
    $chartSpo2[] = $vital['spo2'] ? floatval(str_replace(',', '.', $vital['spo2'])) : null;
    $chartRRSys[] = $vital['rrsys'] ? floatval(str_replace(',', '.', $vital['rrsys'])) : null;
    $chartRRDias[] = $vital['rrdias'] ? floatval(str_replace(',', '.', $vital['rrdias'])) : null;
    $chartHerzfreq[] = $vital['herzfreq'] ? floatval(str_replace(',', '.', $vital['herzfreq'])) : null;
    $chartAtemfreq[] = $vital['atemfreq'] ? floatval(str_replace(',', '.', $vital['atemfreq'])) : null;
    $chartTemp[] = $vital['temp'] ? floatval(str_replace(',', '.', $vital['temp'])) : null;
    $chartEtco2[] = $vital['etco2'] ? floatval(str_replace(',', '.', $vital['etco2'])) : null;
    $chartBz[] = $vital['bz'] ? floatval(str_replace(',', '.', $vital['bz'])) : null;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>[#<?= $daten['enr'] ?>] &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/divi.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <script src="<?= BASE_PATH ?>vendor/components/jquery/jquery.min.js"></script>
    <script src="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="<?= BASE_PATH ?>assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="<?= BASE_PATH ?>assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_PATH ?>assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="<?php echo SYSTEM_NAME ?>" />
    <link rel="manifest" href="<?= BASE_PATH ?>assets/favicon/site.webmanifest" />
    <!-- Metas -->
    <meta name="theme-color" content="#ffaf2f" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="<?= $prot_url ?>" />
    <meta property="og:title" content="[#<?= $daten['enr'] ?>] Vitalparameter &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />

    <style>
        .chart-container {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chart-container:hover {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
        }

        .chart-click-hint {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .chart-container:hover .chart-click-hint {
            opacity: 1;
        }

        .legend-toggle {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 10px;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 12px;
        }

        .legend-item:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .legend-item.hidden {
            opacity: 0.5;
            text-decoration: line-through;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
    </style>
</head>

<body data-page="verlauf">
    <?php include __DIR__ . '/../../assets/components/enotf/topbar.php'; ?>

    <div class="container-fluid" id="edivi__container">
        <div class="row h-100">
            <?php include __DIR__ . '/../../assets/components/enotf/nav.php'; ?>
            <div class="col" id="edivi__content">

                <!-- Aktionen Header -->
                <div class="row my-3">
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <a href="verlauf_list.php?enr=<?= $enr ?>" class="btn btn-outline-light">
                                    <i class="las la-list"></i> Verlaufsliste
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kombinierter Chart -->
                <div class="row">
                    <div class="col">
                        <div class="row edivi__box">
                            <h5 class="text-light px-2 py-1">Alle Vitalparameter</h5>
                            <div class="col p-3">
                                <div class="legend-toggle" id="legendToggle">
                                    <!-- Wird durch JavaScript gefüllt -->
                                </div>
                                <div class="chart-container position-relative" onclick="addValues()">
                                    <div class="chart-click-hint">
                                        <i class="las la-plus"></i> Klicken zum Hinzufügen
                                    </div>
                                    <canvas id="chartCombined" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (empty($vitals)): ?>
                    <div class="row mt-3">
                        <div class="col text-center">
                            <div class="alert alert-info">
                                <h5><i class="las la-info-circle"></i> Noch keine Vitalparameter dokumentiert</h5>
                                <p>Klicken Sie auf "Werte hinzufügen" oder auf den Chart-Bereich, um die ersten Vitalparameter zu erfassen.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    include __DIR__ . '/../../assets/functions/enotf/notify.php';
    include __DIR__ . '/../../assets/functions/enotf/field_checks.php';
    include __DIR__ . '/../../assets/functions/enotf/clock.php';
    ?>

    <script>
        // Chart-Daten
        const chartLabels = <?= json_encode($chartLabels) ?>;
        const chartData = {
            spo2: <?= json_encode($chartSpo2) ?>,
            rrsys: <?= json_encode($chartRRSys) ?>,
            rrdias: <?= json_encode($chartRRDias) ?>,
            herzfreq: <?= json_encode($chartHerzfreq) ?>,
            atemfreq: <?= json_encode($chartAtemfreq) ?>,
            temp: <?= json_encode($chartTemp) ?>,
            etco2: <?= json_encode($chartEtco2) ?>,
            bz: <?= json_encode($chartBz) ?>
        };

        // Debug: Temperatur-Daten ausgeben
        console.log('Temperatur-Daten (nach Konvertierung):', chartData.temp);
        console.log('Temperatur-Datentypen:', chartData.temp.map(v => typeof v + ': ' + v));

        // Dataset-Konfiguration
        const datasets = [{
                label: 'SpO₂ (%)',
                data: chartData.spo2,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                yAxisID: 'y',
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: 'rgb(75, 192, 192)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                borderWidth: 3,
                hidden: false,
                spanGaps: false
            },
            {
                label: 'RR systolisch (mmHg)',
                data: chartData.rrsys,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.4,
                yAxisID: 'y1',
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: 'rgb(255, 99, 132)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                borderWidth: 3,
                hidden: false,
                spanGaps: false
            },
            {
                label: 'RR diastolisch (mmHg)',
                data: chartData.rrdias,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4,
                yAxisID: 'y1',
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: 'rgb(54, 162, 235)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                borderWidth: 3,
                hidden: false,
                spanGaps: false
            },
            {
                label: 'Herzfrequenz (/min)',
                data: chartData.herzfreq,
                borderColor: 'rgb(255, 205, 86)',
                backgroundColor: 'rgba(255, 205, 86, 0.1)',
                tension: 0.4,
                yAxisID: 'y2',
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: 'rgb(255, 205, 86)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                borderWidth: 3,
                hidden: false,
                spanGaps: false
            },
            {
                label: 'Atemfrequenz (/min)',
                data: chartData.atemfreq,
                borderColor: 'rgb(153, 102, 255)',
                backgroundColor: 'rgba(153, 102, 255, 0.1)',
                tension: 0.4,
                yAxisID: 'y3',
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: 'rgb(153, 102, 255)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                borderWidth: 3,
                hidden: false,
                spanGaps: false
            },
            {
                label: 'Temperatur (°C)',
                data: chartData.temp,
                borderColor: 'rgb(255, 159, 64)',
                backgroundColor: 'rgba(255, 159, 64, 0.1)',
                tension: 0.4,
                yAxisID: 'y4',
                pointRadius: 5, // Zurück auf normal
                pointHoverRadius: 8,
                pointBackgroundColor: 'rgb(255, 159, 64)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                borderWidth: 3, // Zurück auf normal
                hidden: false,
                spanGaps: false
            },
            {
                label: 'etCO₂ (mmHg)',
                data: chartData.etco2,
                borderColor: 'rgb(199, 199, 199)',
                backgroundColor: 'rgba(199, 199, 199, 0.1)',
                tension: 0.4,
                yAxisID: 'y5',
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: 'rgb(199, 199, 199)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                borderWidth: 3,
                hidden: false,
                spanGaps: false
            },
            {
                label: 'Blutzucker (mg/dl)',
                data: chartData.bz,
                borderColor: 'rgb(83, 102, 255)',
                backgroundColor: 'rgba(83, 102, 255, 0.1)',
                tension: 0.4,
                yAxisID: 'y6',
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: 'rgb(83, 102, 255)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                borderWidth: 3,
                hidden: false,
                spanGaps: false
            }
        ];

        // Chart erstellen
        const ctx = document.getElementById('chartCombined').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false // Verwenden custom legend
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return 'Zeit: ' + context[0].label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: 'white'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.1)'
                        }
                    },
                    y: { // SpO₂
                        type: 'linear',
                        position: 'left',
                        min: 85,
                        max: 100,
                        ticks: {
                            color: 'rgba(75, 192, 192, 0.8)'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.1)'
                        },
                        display: true
                    },
                    y1: { // RR
                        type: 'linear',
                        position: 'right',
                        min: 40,
                        max: 200,
                        ticks: {
                            color: 'rgba(255, 99, 132, 0.8)'
                        },
                        grid: {
                            display: false
                        },
                        display: true
                    },
                    y2: { // HF
                        type: 'linear',
                        min: 40,
                        max: 150,
                        ticks: {
                            display: false
                        },
                        grid: {
                            display: false
                        },
                        display: false
                    },
                    y3: { // AF
                        type: 'linear',
                        min: 8,
                        max: 35,
                        ticks: {
                            display: false
                        },
                        grid: {
                            display: false
                        },
                        display: false
                    },
                    y4: { // Temp
                        type: 'linear',
                        min: 35,
                        max: 42,
                        ticks: {
                            display: true,
                            color: 'rgba(255, 159, 64, 0.8)'
                        },
                        grid: {
                            display: false
                        },
                        display: true,
                        position: 'right'
                    },
                    y5: { // etCO₂
                        type: 'linear',
                        min: 20,
                        max: 60,
                        ticks: {
                            display: false
                        },
                        grid: {
                            display: false
                        },
                        display: false
                    },
                    y6: { // BZ
                        type: 'linear',
                        min: 50,
                        max: 250,
                        ticks: {
                            display: false
                        },
                        grid: {
                            display: false
                        },
                        display: false
                    }
                }
            }
        });

        // Custom Legend erstellen
        function createLegend() {
            const legendContainer = document.getElementById('legendToggle');
            legendContainer.innerHTML = '';

            datasets.forEach((dataset, index) => {
                const legendItem = document.createElement('div');
                legendItem.className = 'legend-item';
                legendItem.onclick = () => toggleDataset(index);

                const color = document.createElement('div');
                color.className = 'legend-color';
                color.style.backgroundColor = dataset.borderColor;

                const label = document.createElement('span');
                label.textContent = dataset.label;

                legendItem.appendChild(color);
                legendItem.appendChild(label);
                legendContainer.appendChild(legendItem);
            });
        }

        // Dataset ein-/ausblenden
        function toggleDataset(index) {
            const dataset = chart.data.datasets[index];
            dataset.hidden = !dataset.hidden;

            const legendItem = document.querySelectorAll('.legend-item')[index];
            legendItem.classList.toggle('hidden', dataset.hidden);

            chart.update();
        }

        // Werte hinzufügen
        function addValues() {
            <?php if (!$ist_freigegeben): ?>
                window.location.href = 'verlauf_add.php?enr=<?= $enr ?>';
            <?php endif; ?>
        }

        // Chart-Höhe anpassen
        document.getElementById('chartCombined').style.height = '400px';

        // Legend initialisieren
        createLegend();

        // Lösch-Funktion (falls noch verwendet)
        function deleteVital(id) {
            if (confirm('Möchten Sie diesen Eintrag wirklich löschen?')) {
                fetch('verlauf_delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + id
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'success') {
                            location.reload();
                        } else {
                            alert('Fehler beim Löschen: ' + data);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Fehler beim Löschen');
                    });
            }
        }
    </script>
</body>

</html>