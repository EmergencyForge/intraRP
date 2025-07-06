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

    // GEÄNDERT: Einzelwerte aus neuer Tabelle laden (nur aktive)
    $queryVitals = "SELECT * FROM intra_edivi_vitalparameter_einzelwerte 
                    WHERE enr = :enr AND geloescht = 0 
                    ORDER BY zeitpunkt ASC, parameter_name ASC";
    $stmtVitals = $pdo->prepare($queryVitals);
    $stmtVitals->execute(['enr' => $_GET['enr']]);
    $vitalsRaw = $stmtVitals->fetchAll(PDO::FETCH_ASSOC);

    // GEÄNDERT: Daten für Chart umstrukturieren
    $vitals = [];
    $groupedByTime = [];

    // Nach Zeitpunkt gruppieren
    foreach ($vitalsRaw as $vital) {
        $zeitpunkt = $vital['zeitpunkt'];
        if (!isset($groupedByTime[$zeitpunkt])) {
            $groupedByTime[$zeitpunkt] = [
                'zeitpunkt' => $zeitpunkt,
                'spo2' => null,
                'atemfreq' => null,
                'etco2' => null,
                'rrsys' => null,
                'rrdias' => null,
                'herzfreq' => null,
                'bz' => null,
                'temp' => null
            ];
        }

        // Parameter-Namen zu Feld-Namen mapping
        $parameterMapping = [
            'SpO₂' => 'spo2',
            'Atemfrequenz' => 'atemfreq',
            'etCO₂' => 'etco2',
            'RR systolisch' => 'rrsys',
            'RR diastolisch' => 'rrdias',
            'Herzfrequenz' => 'herzfreq',
            'Blutzucker' => 'bz',
            'Temperatur' => 'temp'
        ];

        if (isset($parameterMapping[$vital['parameter_name']])) {
            $fieldName = $parameterMapping[$vital['parameter_name']];
            $groupedByTime[$zeitpunkt][$fieldName] = $vital['parameter_wert'];
        }
    }

    // Sortierte Zeitpunkte für Chart
    ksort($groupedByTime);
    $vitals = array_values($groupedByTime);
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

// GEÄNDERT: Anzahl der verfügbaren Einzelwerte ermitteln
$queryCount = "SELECT COUNT(*) as count FROM intra_edivi_vitalparameter_einzelwerte WHERE enr = :enr AND geloescht = 0";
$stmtCount = $pdo->prepare($queryCount);
$stmtCount->execute(['enr' => $enr]);
$totalVitals = $stmtCount->fetch(PDO::FETCH_ASSOC)['count'];
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

        .vitals-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .vitals-stat {
            display: inline-block;
            background: rgba(0, 123, 255, 0.2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            margin-right: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body data-page="verlauf">
    <?php include __DIR__ . '/../../assets/components/enotf/topbar.php'; ?>

    <div class="container-fluid" id="edivi__container">
        <div class="row h-100">
            <?php include __DIR__ . '/../../assets/components/enotf/nav.php'; ?>
            <div class="col" id="edivi__content">
                <div class="row my-3">
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <a href="verlauf_list.php?enr=<?= $enr ?>&action=manage" class="btn btn-outline-light">
                                    <i class="las la-list"></i> Verlauf bearbeiten
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="vitals-info">
                            <h6 class="text-light mb-2">
                                <i class="las la-chart-line"></i> Vitalparameter-Übersicht
                            </h6>
                            <div>
                                <span class="vitals-stat">
                                    <i class="las la-database"></i> <?= $totalVitals ?> Einzelwerte erfasst
                                </span>
                                <span class="vitals-stat">
                                    <i class="las la-clock"></i> <?= count($vitals) ?> Zeitpunkte dokumentiert
                                </span>
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

        // Debug: Daten ausgeben
        console.log('Chart Labels:', chartLabels);
        console.log('Chart Data:', chartData);
        console.log('Total Data Points:', chartLabels.length);

        // Konvertiere String-Werte zu Zahlen
        function parseValue(value) {
            if (value === null || value === undefined || value === '') return null;
            const parsed = parseFloat(String(value).replace(',', '.'));
            return isNaN(parsed) ? null : parsed;
        }

        // Alle Daten zu Zahlen konvertieren
        const numericData = {
            spo2: chartData.spo2.map(parseValue),
            rrsys: chartData.rrsys.map(parseValue),
            rrdias: chartData.rrdias.map(parseValue),
            herzfreq: chartData.herzfreq.map(parseValue),
            atemfreq: chartData.atemfreq.map(parseValue),
            temp: chartData.temp.map(parseValue),
            etco2: chartData.etco2.map(parseValue),
            bz: chartData.bz.map(parseValue)
        };

        // Parameter-Kategorisierung
        const parameterConfig = {
            // HOHE WERTE (0-300 Achse)
            rrsys: {
                axis: 'y1',
                color: 'rgb(255, 99, 132)',
                label: 'RR systolisch (mmHg)',
                category: 'Hohe Werte'
            },
            rrdias: {
                axis: 'y1',
                color: 'rgb(54, 162, 235)',
                label: 'RR diastolisch (mmHg)',
                category: 'Hohe Werte'
            },
            herzfreq: {
                axis: 'y1',
                color: 'rgb(255, 205, 86)',
                label: 'Herzfrequenz (/min)',
                category: 'Hohe Werte'
            },
            bz: {
                axis: 'y1',
                color: 'rgb(83, 102, 255)',
                label: 'Blutzucker (mg/dl)',
                category: 'Hohe Werte'
            },
            etco2: {
                axis: 'y',
                color: 'rgb(199, 199, 199)',
                label: 'etCO₂ (mmHg)',
                category: 'Niedrige Werte'
            },

            // NIEDRIGE WERTE (0-100 Achse)
            spo2: {
                axis: 'y',
                color: 'rgb(75, 192, 192)',
                label: 'SpO₂ (%)',
                category: 'Niedrige Werte'
            },
            atemfreq: {
                axis: 'y',
                color: 'rgb(153, 102, 255)',
                label: 'Atemfrequenz (/min)',
                category: 'Niedrige Werte'
            },
            temp: {
                axis: 'y',
                color: 'rgb(255, 159, 64)',
                label: 'Temperatur (°C)',
                category: 'Niedrige Werte'
            }
        };

        // Datasets erstellen
        const datasets = [];
        Object.keys(parameterConfig).forEach(paramKey => {
            const config = parameterConfig[paramKey];
            const hasData = numericData[paramKey].some(v => v !== null && v !== undefined);

            if (hasData) {
                datasets.push({
                    label: config.label,
                    data: numericData[paramKey],
                    borderColor: config.color,
                    backgroundColor: config.color.replace('rgb', 'rgba').replace(')', ', 0.1)'),
                    tension: 0.4,
                    yAxisID: config.axis,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    pointBackgroundColor: config.color,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    borderWidth: 3,
                    hidden: false,
                    spanGaps: false,
                    parameterKey: paramKey,
                    category: config.category
                });
            }
        });

        // Dynamische Skalierung für rechte Achse basierend auf Blutzucker
        function calculateRightAxisMax() {
            const bzValues = numericData.bz.filter(v => v !== null && v !== undefined);
            if (bzValues.length === 0) return 300;

            const maxBZ = Math.max(...bzValues);
            if (maxBZ > 300) {
                console.log(`Blutzucker-Maximum: ${maxBZ} mg/dl - Skala wird auf 600 erweitert`);
                return 600;
            }
            return 300;
        }

        const rightAxisMax = calculateRightAxisMax();
        const rightAxisStep = rightAxisMax === 600 ? 60 : 30;

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
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return 'Zeit: ' + context[0].label;
                            },
                            label: function(context) {
                                const value = context.parsed.y;
                                const label = context.dataset.label;

                                if (value !== null && value !== undefined) {
                                    if (label.includes('SpO₂')) {
                                        return `${label}: ${value.toFixed(1)}%`;
                                    } else if (label.includes('mmHg')) {
                                        return `${label}: ${value.toFixed(0)} mmHg`;
                                    } else if (label.includes('/min')) {
                                        return `${label}: ${value.toFixed(0)}/min`;
                                    } else if (label.includes('°C')) {
                                        return `${label}: ${value.toFixed(1)}°C`;
                                    } else if (label.includes('mg/dl')) {
                                        return `${label}: ${value.toFixed(0)} mg/dl`;
                                    } else {
                                        return `${label}: ${value.toFixed(1)}`;
                                    }
                                }
                                return label + ': Kein Wert';
                            },
                            footer: function(tooltipItems) {
                                // Zeige Achsen-Info mit dynamischer Skalierung
                                const item = tooltipItems[0];
                                if (item) {
                                    const dataset = item.dataset;
                                    const rightAxisText = rightAxisMax === 600 ? '0-600 (erweitert für BZ)' : '0-300';
                                    return `Achse: ${dataset.category} (${dataset.yAxisID === 'y' ? '0-100' : rightAxisText})`;
                                }
                                return '';
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        footerColor: 'rgba(255, 255, 255, 0.7)',
                        borderColor: 'rgba(255, 255, 255, 0.3)',
                        borderWidth: 1
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
                    y: { // NIEDRIGE WERTE (0-100)
                        type: 'linear',
                        position: 'left',
                        min: 0,
                        max: 100,
                        ticks: {
                            color: 'rgba(75, 192, 192, 1)',
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            stepSize: 10
                        },
                        grid: {
                            color: 'rgba(75, 192, 192, 0.2)'
                        },
                        title: {
                            display: true,
                            text: 'SpO₂, AF, Temp, etCO₂',
                            color: 'rgba(75, 192, 192, 1)',
                            font: {
                                size: 13,
                                weight: 'bold'
                            }
                        }
                    },
                    y1: { // HOHE WERTE (dynamisch 0-300 oder 0-600)
                        type: 'linear',
                        position: 'right',
                        min: 0,
                        max: rightAxisMax,
                        ticks: {
                            color: 'rgba(255, 99, 132, 1)',
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            stepSize: rightAxisStep
                        },
                        grid: {
                            display: false // Vermeidet doppelte Gitterlinien
                        },
                        title: {
                            display: true,
                            text: rightAxisMax === 600 ?
                                'RR, HF, BZ' : 'RR, HF, BZ',
                            color: 'rgba(255, 99, 132, 1)',
                            font: {
                                size: 13,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });

        // Einfache Legend ohne Kategorien
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

            const legendItems = document.querySelectorAll('.legend-item');
            if (legendItems[index]) {
                legendItems[index].classList.toggle('hidden', dataset.hidden);
            }

            chart.update();
        }

        // Werte hinzufügen
        function addValues() {
            <?php if (!$ist_freigegeben): ?>
                window.location.href = 'verlauf_add.php?enr=<?= $enr ?>';
            <?php else: ?>
                alert('Diese Dokumentation ist bereits freigegeben und kann nicht mehr bearbeitet werden.');
            <?php endif; ?>
        }

        // Chart-Höhe anpassen
        document.getElementById('chartCombined').style.height = '450px';

        // Legend initialisieren
        createLegend();

        // Informationstext mit dynamischer BZ-Info hinzufügen
        const infoText = document.createElement('div');
        const chartContainer = document.querySelector('.chart-container').parentNode;
        chartContainer.insertBefore(infoText, chartContainer.firstChild);
    </script>
</body>

</html>