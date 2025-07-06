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
    $queryVitals = "SELECT * FROM intra_edivi_vitalparameter WHERE enr = :enr ORDER BY zeitpunkt DESC";
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>[#<?= $daten['enr'] ?>] Verlaufsliste &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/divi.min.css" />
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
    <meta name="theme-color" content="#ffaf2f" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="<?= $prot_url ?>" />
    <meta property="og:title" content="[#<?= $daten['enr'] ?>] Verlaufsliste &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />

    <style>
        .vital-table {
            font-size: 14px;
        }

        .vital-value {
            font-weight: 600;
        }

        .vital-value.text-success {
            color: #28a745 !important;
        }

        .vital-value.text-warning {
            color: #ffc107 !important;
        }

        .vital-value.text-danger {
            color: #dc3545 !important;
        }

        .delete-btn {
            opacity: 0.7;
            transition: all 0.3s;
        }

        .delete-btn:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .table-actions {
            position: sticky;
            top: 0;
            background: #343a40;
            z-index: 10;
        }

        .stats-row {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            text-align: center;
            color: white;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            color: #bbb;
        }

        .export-btn {
            margin-left: 10px;
        }
    </style>
</head>

<body data-page="verlauf">
    <?php include __DIR__ . '/../../assets/components/enotf/topbar.php'; ?>

    <div class="container-fluid" id="edivi__container">
        <div class="row h-100">
            <?php include __DIR__ . '/../../assets/components/enotf/nav.php'; ?>
            <div class="col" id="edivi__content">
                <div class="my-4"></div>
                <!-- Statistiken -->
                <?php if (!empty($vitals)):
                    $totalEntries = count($vitals);
                    $firstEntry = end($vitals);
                    $lastEntry = reset($vitals);
                    $timeSpan = '';

                    if ($firstEntry && $lastEntry) {
                        $start = new DateTime($firstEntry['zeitpunkt']);
                        $end = new DateTime($lastEntry['zeitpunkt']);
                        $diff = $start->diff($end);

                        if ($diff->d > 0) {
                            $timeSpan = $diff->d . ' Tag(e)';
                        } elseif ($diff->h > 0) {
                            $timeSpan = $diff->h . ' Stunde(n)';
                        } else {
                            $timeSpan = $diff->i . ' Minute(n)';
                        }
                    }
                ?>
                    <div class="stats-row">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-number"><?= $totalEntries ?></div>
                                    <div class="stat-label">Einträge gesamt</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-number"><?= $timeSpan ?: '-' ?></div>
                                    <div class="stat-label">Zeitspanne</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-number"><?= $firstEntry ? (new DateTime($firstEntry['zeitpunkt']))->format('H:i') : '-' ?></div>
                                    <div class="stat-label">Erste Messung</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-number"><?= $lastEntry ? (new DateTime($lastEntry['zeitpunkt']))->format('H:i') : '-' ?></div>
                                    <div class="stat-label">Letzte Messung</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Verlaufstabelle -->
                <div class="row">
                    <div class="col">
                        <div class="row edivi__box">
                            <h5 class="text-light px-2 py-1">Detaillierte Verlaufsliste</h5>
                            <div class="col p-0">
                                <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                                    <table class="table table-dark table-striped table-hover vital-table mb-0">
                                        <thead class="table-actions">
                                            <tr>
                                                <th style="width: 80px;">Zeit</th>
                                                <th style="width: 70px;">SpO₂</th>
                                                <th style="width: 70px;">AF</th>
                                                <th style="width: 80px;">etCO₂</th>
                                                <th style="width: 90px;">RR sys</th>
                                                <th style="width: 90px;">RR dia</th>
                                                <th style="width: 70px;">HF</th>
                                                <th style="width: 70px;">BZ</th>
                                                <th style="width: 70px;">Temp</th>
                                                <th style="width: 200px;">Bemerkung</th>
                                                <th style="width: 100px;">Erstellt von</th>
                                                <?php if (!$ist_freigegeben): ?><th style="width: 60px;">Aktion</th><?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($vitals as $vital):
                                                $zeitpunkt = new DateTime($vital['zeitpunkt']);
                                            ?>
                                                <tr id="vital-row-<?= $vital['id'] ?>">
                                                    <td class="text-info">
                                                        <strong><?= $zeitpunkt->format('H:i') ?></strong><br>
                                                        <small class="text-muted"><?= $zeitpunkt->format('d.m') ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if ($vital['spo2']): ?>
                                                            <span class="vital-value <?= getVitalClass('spo2', $vital['spo2']) ?>">
                                                                <?= $vital['spo2'] ?>%
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($vital['atemfreq']): ?>
                                                            <span class="vital-value <?= getVitalClass('atemfreq', $vital['atemfreq']) ?>">
                                                                <?= $vital['atemfreq'] ?>/min
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($vital['etco2']): ?>
                                                            <span class="vital-value <?= getVitalClass('etco2', $vital['etco2']) ?>">
                                                                <?= $vital['etco2'] ?>mmHg
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($vital['rrsys']): ?>
                                                            <span class="vital-value <?= getVitalClass('rrsys', $vital['rrsys']) ?>">
                                                                <?= $vital['rrsys'] ?>mmHg
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($vital['rrdias']): ?>
                                                            <span class="vital-value <?= getVitalClass('rrdias', $vital['rrdias']) ?>">
                                                                <?= $vital['rrdias'] ?>mmHg
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($vital['herzfreq']): ?>
                                                            <span class="vital-value <?= getVitalClass('herzfreq', $vital['herzfreq']) ?>">
                                                                <?= $vital['herzfreq'] ?>/min
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($vital['bz']): ?>
                                                            <span class="vital-value <?= getVitalClass('bz', $vital['bz']) ?>">
                                                                <?= $vital['bz'] ?>mg/dl
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($vital['temp']): ?>
                                                            <span class="vital-value <?= getVitalClass('temp', $vital['temp']) ?>">
                                                                <?= $vital['temp'] ?>°C
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-break">
                                                        <?= $vital['bemerkung'] ? htmlspecialchars($vital['bemerkung']) : '<span class="text-muted">-</span>' ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?= htmlspecialchars($vital['erstellt_von']) ?></small>
                                                    </td>
                                                    <?php if (!$ist_freigegeben): ?>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-danger delete-btn"
                                                                onclick="deleteVital(<?= $vital['id'] ?>)"
                                                                title="Eintrag löschen">
                                                                <i class="las la-trash"></i>
                                                            </button>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($vitals)): ?>
                                                <tr>
                                                    <td colspan="<?= !$ist_freigegeben ? '12' : '11' ?>" class="text-center text-muted py-4">
                                                        <i class="las la-info-circle la-2x mb-2"></i><br>
                                                        Noch keine Vitalparameter dokumentiert
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include __DIR__ . '/../../assets/functions/enotf/notify.php';
    include __DIR__ . '/../../assets/functions/enotf/field_checks.php';
    include __DIR__ . '/../../assets/functions/enotf/clock.php';
    ?>

    <script>
        // Vital-Wert löschen
        function deleteVital(id) {
            if (confirm('Möchten Sie diesen Eintrag wirklich löschen?')) {
                // Visuelles Feedback
                const row = document.getElementById('vital-row-' + id);
                if (row) {
                    row.style.opacity = '0.5';
                    row.style.pointerEvents = 'none';
                }

                fetch('verlauf_delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + id
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === 'success') {
                            // Zeile mit Animation entfernen
                            if (row) {
                                row.style.transition = 'all 0.5s ease';
                                row.style.transform = 'translateX(-100%)';
                                row.style.opacity = '0';

                                setTimeout(() => {
                                    row.remove();

                                    // Prüfen ob Tabelle leer ist
                                    const tbody = document.querySelector('.vital-table tbody');
                                    if (tbody && tbody.children.length === 0) {
                                        location.reload();
                                    }
                                }, 500);
                            }

                            // Toast-Benachrichtigung
                            showToast('Eintrag erfolgreich gelöscht', 'success');
                        } else {
                            // Fehler - Zeile zurücksetzen
                            if (row) {
                                row.style.opacity = '1';
                                row.style.pointerEvents = 'auto';
                            }
                            showToast('Fehler beim Löschen: ' + data, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Zeile zurücksetzen
                        if (row) {
                            row.style.opacity = '1';
                            row.style.pointerEvents = 'auto';
                        }
                        showToast('Netzwerkfehler beim Löschen', 'error');
                    });
            }
        }

        // Hilfsfunktion: Zahl aus Text extrahieren
        function extractNumber(text) {
            if (text.includes('-') || text.trim() === '') return '';
            const match = text.match(/[\d,\.]+/);
            return match ? match[0].replace(',', '.') : '';
        }

        // Toast-Benachrichtigung
        function showToast(message, type = 'info') {
            // Einfache Toast-Implementation
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '9999';
            toast.style.minWidth = '300px';
            toast.innerHTML = `
                <i class="las la-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
            `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
</body>

</html>

<?php
// Hilfsfunktion für Vital-Klassifizierung
function getVitalClass($type, $value)
{
    $numValue = floatval(str_replace(',', '.', $value));

    switch ($type) {
        case 'spo2':
            if ($numValue < 88 || $numValue > 100) return 'text-danger';
            if ($numValue < 95) return 'text-warning';
            return 'text-success';

        case 'atemfreq':
            if ($numValue < 9 || $numValue > 27) return 'text-danger';
            if ($numValue < 12 || $numValue > 20) return 'text-warning';
            return 'text-success';

        case 'etco2':
            if ($numValue < 25 || $numValue > 50) return 'text-danger';
            if ($numValue < 33 || $numValue > 43) return 'text-warning';
            return 'text-success';

        case 'rrsys':
            if ($numValue < 81 || $numValue > 179) return 'text-danger';
            if ($numValue < 101 || $numValue > 139) return 'text-warning';
            return 'text-success';

        case 'rrdias':
            if ($numValue < 51 || $numValue > 119) return 'text-danger';
            if ($numValue < 61 || $numValue > 99) return 'text-warning';
            return 'text-success';

        case 'herzfreq':
            if ($numValue < 51 || $numValue > 300) return 'text-danger';
            if ($numValue < 61 || $numValue > 99) return 'text-warning';
            return 'text-success';

        case 'bz':
            if ($numValue < 61 || $numValue > 199) return 'text-danger';
            if ($numValue < 71 || $numValue > 139) return 'text-warning';
            return 'text-success';

        case 'temp':
            if ($numValue < 35 || $numValue > 40) return 'text-danger';
            if ($numValue < 36.1 || $numValue > 37.5) return 'text-warning';
            return 'text-success';

        default:
            return '';
    }
}
?>