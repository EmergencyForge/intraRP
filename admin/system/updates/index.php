<?php
session_start();
// Normale Authentifizierung für Produktion
require_once __DIR__ . '/../../../assets/config/config.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: " . BASE_PATH . "admin/login.php");
    exit();
}

use App\Auth\Permissions;
use App\Helpers\Flash;

if (!Permissions::check(['full_admin'])) {
    Flash::set('error', 'no-permissions');
    header("Location: " . BASE_PATH . "admin/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="de" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System-Updates - Administration</title>
    <script src="<?= BASE_PATH ?>vendor/components/jquery/jquery.min.js"></script>
    <link rel="stylesheet" href="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/_ext/lineawesome/css/line-awesome.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/style.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/admin.min.css">
    <style>
        .update-card {
            border-left: 4px solid var(--bs-primary);
            transition: all 0.3s ease;
        }

        .update-card.has-update {
            border-left-color: var(--bs-success);
            box-shadow: 0 0 15px rgba(25, 135, 84, 0.3);
        }

        .update-card.error {
            border-left-color: var(--bs-danger);
        }

        .requirement-item {
            padding: 8px 12px;
            margin: 4px 0;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .requirement-item.success {
            background-color: rgba(25, 135, 84, 0.1);
            border: 1px solid rgba(25, 135, 84, 0.3);
        }

        .requirement-item.error {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .log-container {
            height: 300px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }

        .version-badge {
            font-size: 1.1em;
            padding: 8px 16px;
        }

        .progress-container {
            display: none;
        }

        .update-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 350px;
        }
    </style>
</head>

<body data-bs-theme="dark" data-page="settings">
    <?php include __DIR__ . "/../../../assets/components/navbar.php"; ?>

    <div class="container-full position-relative" id="mainpageContainer">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <hr class="text-light my-3">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="las la-sync-alt"></i> System-Updates</h2>
                        <button class="btn btn-outline-primary" onclick="checkForUpdates()">
                            <i class="las la-refresh"></i> Nach Updates suchen
                        </button>
                    </div>
                </div>
            </div>

            <!-- Update-Status Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card update-card" id="updateCard">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="las la-info-circle"></i> Update-Status
                            </h5>
                            <div id="updateStatus">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                Prüfe Updates...
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Aktuelle Version:</h6>
                                    <span class="badge bg-secondary version-badge" id="currentVersion">Lädt...</span>
                                </div>
                                <div class="col-md-6" id="latestVersionContainer" style="display: none;">
                                    <h6>Verfügbare Version:</h6>
                                    <span class="badge bg-success version-badge" id="latestVersion"></span>
                                </div>
                            </div>

                            <div id="releaseNotes" class="mt-3" style="display: none;">
                                <h6>Release Notes:</h6>
                                <div class="border rounded p-3">
                                    <div id="releaseNotesContent"></div>
                                </div>
                            </div>

                            <div id="updateActions" class="mt-3" style="display: none;">
                                <button class="btn btn-success me-2" onclick="startUpdate()">
                                    <i class="las la-download"></i> Update installieren
                                </button>
                                <button class="btn btn-outline-secondary" onclick="checkRequirements()">
                                    <i class="las la-check-circle"></i> Voraussetzungen prüfen
                                </button>
                            </div>

                            <!-- Progress Bar -->
                            <div class="progress-container mt-3" id="progressContainer">
                                <h6>Update-Fortschritt:</h6>
                                <div class="progress mb-2">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" style="width: 0%" id="progressBar">
                                        0%
                                    </div>
                                </div>
                                <div id="progressText">Vorbereitung...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Systemvoraussetzungen -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="las la-cogs"></i> Systemvoraussetzungen
                                <button class="btn btn-sm btn-outline-primary float-end" onclick="checkRequirements()">
                                    <i class="las la-refresh"></i> Erneut prüfen
                                </button>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="requirementsContainer">
                                <div class="text-center text-muted">
                                    <i class="las la-sync-alt la-spin"></i> Lade Voraussetzungen...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update-Log -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="las la-file-alt"></i> Update-Log
                                <button class="btn btn-sm btn-outline-secondary float-end" onclick="clearLog()">
                                    <i class="las la-trash"></i> Log leeren
                                </button>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="log-container" id="logContainer">
                                <div class="text-muted">Update-Log wird hier angezeigt...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update-Notification -->
    <div class="update-notification" id="updateNotification" style="display: none;">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="las la-check-circle"></i>
            <strong>Update verfügbar!</strong> Eine neue Version ist verfügbar.
            <button type="button" class="btn-close" onclick="hideNotification()"></button>
        </div>
    </div>

    <!-- Bestätigungsdialog -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update bestätigen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="las la-exclamation-triangle"></i>
                        <strong>Achtung!</strong> Das Update kann einige Minuten dauern.
                        Schließen Sie diese Seite nicht während des Updates.
                    </div>
                    <p>Möchten Sie das Update wirklich installieren?</p>
                    <ul>
                        <li>Ein Backup wird automatisch erstellt</li>
                        <li>Alle Dateien werden aktualisiert</li>
                        <li>Composer update wird ausgeführt</li>
                        <li>Die Datenbank wird aktualisiert</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-success" onclick="confirmUpdate()">
                        <i class="las la-download"></i> Update starten
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let updateInfo = null;
        let updateModal = null;

        document.addEventListener('DOMContentLoaded', function() {
            updateModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            checkForUpdates();
            checkRequirements();

            // Automatische Überprüfung alle 30 Minuten
            setInterval(checkForUpdates, 30 * 60 * 1000);
        });

        async function checkForUpdates() {
            try {
                const response = await fetch('update_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=check_updates'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const text = await response.text();
                console.log('Server response:', text); // Debug

                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('Serverantwort ist kein gültiges JSON: ' + text.substring(0, 200));
                }

                updateInfo = data;
                updateUI(data);
            } catch (error) {
                console.error('Fehler beim Prüfen der Updates:', error);
                showError('Fehler beim Prüfen der Updates: ' + error.message);
            }
        }

        function updateUI(data) {
            const updateCard = document.getElementById('updateCard');
            const updateStatus = document.getElementById('updateStatus');
            const currentVersionEl = document.getElementById('currentVersion');
            const latestVersionContainer = document.getElementById('latestVersionContainer');
            const latestVersionEl = document.getElementById('latestVersion');
            const releaseNotes = document.getElementById('releaseNotes');
            const releaseNotesContent = document.getElementById('releaseNotesContent');
            const updateActions = document.getElementById('updateActions');
            const updateNotification = document.getElementById('updateNotification');

            currentVersionEl.textContent = data.current_version || 'Unbekannt';

            if (data.error) {
                updateCard.className = 'card update-card error';
                updateStatus.innerHTML = '<i class="las la-exclamation-triangle text-danger"></i> Fehler: ' + (data.error_message || 'Unbekannter Fehler');
                return;
            }

            if (data.has_update) {
                updateCard.className = 'card update-card has-update';
                updateStatus.innerHTML = '<i class="las la-check-circle text-success"></i> Update verfügbar!';

                latestVersionEl.textContent = data.latest_version;
                latestVersionContainer.style.display = 'block';

                if (data.release_notes) {
                    releaseNotesContent.innerHTML = data.release_notes.replace(/\n/g, '<br>');
                    releaseNotes.style.display = 'block';
                }

                updateActions.style.display = 'block';
                updateNotification.style.display = 'block';
            } else {
                updateCard.className = 'card update-card';
                updateStatus.innerHTML = '<i class="las la-check-circle text-success"></i> System ist aktuell';
                latestVersionContainer.style.display = 'none';
                releaseNotes.style.display = 'none';
                updateActions.style.display = 'none';
                updateNotification.style.display = 'none';
            }
        }

        async function checkRequirements() {
            try {
                const response = await fetch('update_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=check_requirements'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const text = await response.text();
                let requirements;
                try {
                    requirements = JSON.parse(text);
                } catch (e) {
                    throw new Error('Serverantwort ist kein gültiges JSON');
                }

                displayRequirements(requirements);
            } catch (error) {
                console.error('Fehler beim Prüfen der Voraussetzungen:', error);
                document.getElementById('requirementsContainer').innerHTML =
                    '<div class="alert alert-danger">Fehler beim Laden der Voraussetzungen: ' + error.message + '</div>';
            }
        }

        function displayRequirements(requirements) {
            const container = document.getElementById('requirementsContainer');
            const reqMap = {
                'php_version': 'PHP Version (>= 7.4)',
                'zip_extension': 'ZIP Extension',
                'curl_extension': 'cURL Extension',
                'writable_root': 'Schreibrechte im Root-Verzeichnis',
                'git_available': 'Git verfügbar',
                'composer_available': 'Composer verfügbar'
            };

            let html = '<div class="row">';

            for (const [key, passed] of Object.entries(requirements)) {
                const name = reqMap[key] || key;
                const statusClass = passed ? 'success' : 'error';
                const icon = passed ? 'las la-check-circle text-success' : 'las la-times-circle text-danger';
                const status = passed ? 'Erfüllt' : 'Nicht erfüllt';

                html += `
                    <div class="col-md-6 mb-2">
                        <div class="requirement-item ${statusClass}">
                            <i class="${icon}"></i>
                            <strong>${name}:</strong> ${status}
                        </div>
                    </div>
                `;
            }

            html += '</div>';
            container.innerHTML = html;
        }

        function startUpdate() {
            if (!updateInfo || !updateInfo.has_update) {
                showError('Kein Update verfügbar');
                return;
            }

            updateModal.show();
        }

        async function confirmUpdate() {
            updateModal.hide();

            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const updateActions = document.getElementById('updateActions');

            progressContainer.style.display = 'block';
            updateActions.style.display = 'none';

            const steps = [
                'Backup wird erstellt...',
                'Update wird heruntergeladen...',
                'Dateien werden extrahiert...',
                'Dateien werden kopiert...',
                'Composer Update wird ausgeführt...',
                'Version wird aktualisiert...',
                'Aufräumen...'
            ];

            let currentStep = 0;
            const updateProgress = () => {
                if (currentStep < steps.length) {
                    const percentage = Math.round((currentStep / steps.length) * 100);
                    progressBar.style.width = percentage + '%';
                    progressBar.textContent = percentage + '%';
                    progressText.textContent = steps[currentStep];
                    currentStep++;
                }
            };

            const progressInterval = setInterval(updateProgress, 2000);
            updateProgress();

            try {
                const response = await fetch('update_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=perform_update'
                });

                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    throw new Error('Serverantwort ist kein gültiges JSON');
                }

                clearInterval(progressInterval);

                if (result.success) {
                    progressBar.style.width = '100%';
                    progressBar.textContent = '100%';
                    progressText.innerHTML = '<i class="las la-check-circle text-success"></i> Update erfolgreich abgeschlossen!';

                    logMessage('Update erfolgreich: ' + result.new_version);

                    setTimeout(() => {
                        showSuccess('Update erfolgreich installiert! Die Seite wird neu geladen...');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }, 1000);
                } else {
                    progressBar.className = 'progress-bar bg-danger';
                    progressText.innerHTML = '<i class="las la-exclamation-triangle text-danger"></i> Update fehlgeschlagen: ' + result.message;

                    logMessage('Update fehlgeschlagen: ' + result.message);
                    showError('Update fehlgeschlagen: ' + result.message);

                    updateActions.style.display = 'block';
                }
            } catch (error) {
                clearInterval(progressInterval);
                console.error('Update-Fehler:', error);

                progressBar.className = 'progress-bar bg-danger';
                progressText.innerHTML = '<i class="las la-exclamation-triangle text-danger"></i> Verbindungsfehler';

                logMessage('Update-Fehler: ' + error.message);
                showError('Update-Fehler: ' + error.message);
                updateActions.style.display = 'block';
            }
        }

        function hideNotification() {
            document.getElementById('updateNotification').style.display = 'none';
        }

        function showSuccess(message) {
            showAlert(message, 'success');
        }

        function showError(message) {
            showAlert(message, 'danger');
        }

        function showAlert(message, type) {
            const alertContainer = document.createElement('div');
            alertContainer.className = 'position-fixed top-0 end-0 p-3';
            alertContainer.style.zIndex = '1055';

            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            document.body.appendChild(alertContainer);

            setTimeout(() => {
                alertContainer.remove();
            }, 5000);
        }

        function logMessage(message) {
            const logContainer = document.getElementById('logContainer');
            const timestamp = new Date().toLocaleString();
            const logEntry = `[${timestamp}] ${message}\n`;

            if (logContainer.textContent.includes('Update-Log wird hier angezeigt...')) {
                logContainer.textContent = '';
            }

            logContainer.textContent += logEntry;
            logContainer.scrollTop = logContainer.scrollHeight;
        }

        function clearLog() {
            const logContainer = document.getElementById('logContainer');
            logContainer.innerHTML = '<div class="text-muted">Update-Log wird hier angezeigt...</div>';
        }

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey && e.key === 'r') || e.key === 'F5') {
                e.preventDefault();
                checkForUpdates();
            }
        });

        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                checkForUpdates();
            }
        });
    </script>

    <?php include __DIR__ . "/../../../assets/components/footer.php"; ?>
</body>

</html>