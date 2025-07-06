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

                <!-- Verlaufsliste -->
                <div class="row">
                    <div class="col">
                        <div class="row edivi__box">
                            <h5 class="text-light px-2 py-1">Verlaufsliste</h5>
                            <div class="col p-3">
                                <div class="vital-list-container">
                                    <?php if (!empty($vitals)): ?>
                                        <?php foreach ($vitals as $vital):
                                            $zeitpunkt = new DateTime($vital['zeitpunkt']);
                                            $values = [];

                                            // Werte sammeln
                                            if ($vital['spo2']) {
                                                $values[] = '<span class="vital-value">SpO₂ ' . $vital['spo2'] . '%</span>';
                                            }
                                            if ($vital['atemfreq']) {
                                                $values[] = '<span class="vital-value">AF ' . $vital['atemfreq'] . '/min</span>';
                                            }
                                            if ($vital['etco2']) {
                                                $values[] = '<span class="vital-value">etCO₂ ' . $vital['etco2'] . 'mmHg</span>';
                                            }
                                            if ($vital['rrsys'] && $vital['rrdias']) {
                                                $values[] = '<span class="vital-value">RR ' . $vital['rrsys'] . '/' . $vital['rrdias'] . 'mmHg</span>';
                                            } elseif ($vital['rrsys']) {
                                                $values[] = '<span class="vital-value">RR sys ' . $vital['rrsys'] . 'mmHg</span>';
                                            } elseif ($vital['rrdias']) {
                                                $values[] = '<span class="vital-value">RR dia ' . $vital['rrdias'] . 'mmHg</span>';
                                            }
                                            if ($vital['herzfreq']) {
                                                $values[] = '<span class="vital-value">HF ' . $vital['herzfreq'] . '/min</span>';
                                            }
                                            if ($vital['bz']) {
                                                $values[] = '<span class="vital-value">BZ ' . $vital['bz'] . 'mg/dl</span>';
                                            }
                                            if ($vital['temp']) {
                                                $values[] = '<span class="vital-value">Temp ' . $vital['temp'] . '°C</span>';
                                            }
                                        ?>
                                            <div class="vital-entry" id="vital-entry-<?= $vital['id'] ?>">
                                                <div class="vital-content">
                                                    <div>
                                                        <span class="vital-time"><?= $zeitpunkt->format('H:i') ?></span>
                                                        <?php if (!empty($values)): ?>
                                                            <?= implode('<span class="vital-separator">•</span>', $values) ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">Keine Werte dokumentiert</span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if ($vital['bemerkung']): ?>
                                                        <div class="vital-bemerkung">
                                                            "<?= htmlspecialchars($vital['bemerkung']) ?>"
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="vital-author">
                                                        <?= htmlspecialchars($vital['erstellt_von']) ?> • <?= $zeitpunkt->format('d.m.Y') ?>
                                                    </div>
                                                </div>

                                                <?php if (!$ist_freigegeben): ?>
                                                    <div>
                                                        <button class="btn btn-sm btn-outline-danger delete-btn"
                                                            onclick="deleteVital(<?= $vital['id'] ?>)"
                                                            title="Eintrag löschen">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="no-vitals">
                                            <i class="las la-info-circle la-3x mb-3"></i>
                                            <h5>Noch keine Vitalparameter dokumentiert</h5>
                                            <p class="text-muted">Beginnen Sie mit der Erfassung der ersten Werte.</p>
                                        </div>
                                    <?php endif; ?>
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
                const entry = document.getElementById('vital-entry-' + id);
                if (entry) {
                    entry.style.opacity = '0.5';
                    entry.style.pointerEvents = 'none';
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
                            // Eintrag mit Animation entfernen
                            if (entry) {
                                entry.style.transition = 'all 0.5s ease';
                                entry.style.transform = 'translateX(-100%)';
                                entry.style.opacity = '0';

                                setTimeout(() => {
                                    entry.remove();

                                    // Prüfen ob Liste leer ist
                                    const container = document.querySelector('.vital-list-container');
                                    const entries = container.querySelectorAll('.vital-entry');
                                    if (entries.length === 0) {
                                        location.reload();
                                    }
                                }, 500);
                            }

                            // Toast-Benachrichtigung
                            showToast('Eintrag erfolgreich gelöscht', 'success');
                        } else {
                            // Fehler - Eintrag zurücksetzen
                            if (entry) {
                                entry.style.opacity = '1';
                                entry.style.pointerEvents = 'auto';
                            }
                            showToast('Fehler beim Löschen: ' + data, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Eintrag zurücksetzen
                        if (entry) {
                            entry.style.opacity = '1';
                            entry.style.pointerEvents = 'auto';
                        }
                        showToast('Netzwerkfehler beim Löschen', 'error');
                    });
            }
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
// Hilfsfunktion für Vital-Klassifizierung - entfernt, da Einfärbung nicht mehr benötigt
?>