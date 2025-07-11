<?php
session_start();
require_once __DIR__ . '/../../assets/config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../assets/config/database.php';

if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: " . BASE_PATH . "admin/login.php");
    exit();
}

use App\Auth\Permissions;
use App\Helpers\Flash;
use App\Utils\AuditLogger;

if (!Permissions::check(['admin', 'personnel.edit'])) {
    Flash::set('error', 'no-permissions');
    header("Location: " . BASE_PATH . "admin/index.php");
    exit();
}

$bewerbungId = (int)($_GET['id'] ?? 0);

if (!$bewerbungId) {
    Flash::error("Keine Bewerbungs-ID angegeben.");
    header("Location: " . BASE_PATH . "admin/apply/");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM intra_bewerbung WHERE id = :id");
$stmt->execute(['id' => $bewerbungId]);
$bewerbung = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bewerbung) {
    Flash::error("Bewerbung nicht gefunden.");
    header("Location: " . BASE_PATH . "admin/apply/");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $fullname = trim($_POST['fullname'] ?? '');
        $gebdatum = $_POST['gebdatum'] ?? '';
        $geschlecht = $_POST['geschlecht'] ?? '';
        $telefonnr = trim($_POST['telefonnr'] ?? '');
        $dienstnr = trim($_POST['dienstnr'] ?? '');
        $discordid = trim($_POST['discordid'] ?? '');

        if (empty($fullname) || empty($gebdatum) || $geschlecht === '' || empty($dienstnr) || empty($discordid)) {
            Flash::error("Bitte alle erforderlichen Felder ausfüllen.");
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $bewerbungId);
            exit();
        }

        $date = DateTime::createFromFormat('Y-m-d', $gebdatum);
        if (!$date || $date->format('Y-m-d') !== $gebdatum) {
            Flash::error("Ungültiges Geburtsdatum.");
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $bewerbungId);
            exit();
        }

        if (!in_array($geschlecht, ['0', '1', '2'])) {
            Flash::error("Ungültiges Geschlecht.");
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $bewerbungId);
            exit();
        }

        if (CHAR_ID) {
            $charakterid = trim($_POST['charakterid'] ?? '');
            if (empty($charakterid)) {
                Flash::error("Bitte gebe eine Charakter-ID ein.");
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $bewerbungId);
                exit();
            }
            if (!preg_match('/^[a-zA-Z]{3}[0-9]{5}$/', $charakterid)) {
                Flash::error("Ungültiges Charakter-ID Format. Erwartetes Format: ABC12345");
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $bewerbungId);
                exit();
            }
        } else {
            $charakterid = null;
        }

        $changes = [];
        if ($bewerbung['fullname'] !== $fullname) $changes[] = "Name: '{$bewerbung['fullname']}' → '$fullname'";
        if ($bewerbung['gebdatum'] !== $gebdatum) $changes[] = "Geburtsdatum: '{$bewerbung['gebdatum']}' → '$gebdatum'";
        if ($bewerbung['geschlecht'] != $geschlecht) $changes[] = "Geschlecht: '{$bewerbung['geschlecht']}' → '$geschlecht'";
        if ($bewerbung['telefonnr'] !== $telefonnr) $changes[] = "Telefon: '{$bewerbung['telefonnr']}' → '$telefonnr'";
        if ($bewerbung['dienstnr'] !== $dienstnr) $changes[] = "Dienstnummer: '{$bewerbung['dienstnr']}' → '$dienstnr'";
        if ($bewerbung['discordid'] !== $discordid) $changes[] = "Discord-ID: '{$bewerbung['discordid']}' → '$discordid'";
        if (CHAR_ID && $bewerbung['charakterid'] !== $charakterid) $changes[] = "Charakter-ID: '{$bewerbung['charakterid']}' → '$charakterid'";

        if (empty($changes)) {
            Flash::info("Keine Änderungen vorgenommen.");
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $bewerbungId);
            exit();
        }

        if (CHAR_ID) {
            $updateStmt = $pdo->prepare("UPDATE intra_bewerbung SET 
                fullname = :fullname, 
                gebdatum = :gebdatum, 
                charakterid = :charakterid, 
                geschlecht = :geschlecht, 
                telefonnr = :telefonnr, 
                dienstnr = :dienstnr,
                discordid = :discordid
                WHERE id = :id");
            $updateStmt->execute([
                'fullname' => $fullname,
                'gebdatum' => $gebdatum,
                'charakterid' => $charakterid,
                'geschlecht' => $geschlecht,
                'telefonnr' => $telefonnr,
                'dienstnr' => $dienstnr,
                'discordid' => $discordid,
                'id' => $bewerbungId
            ]);
        } else {
            $updateStmt = $pdo->prepare("UPDATE intra_bewerbung SET 
                fullname = :fullname, 
                gebdatum = :gebdatum, 
                geschlecht = :geschlecht, 
                telefonnr = :telefonnr, 
                dienstnr = :dienstnr,
                discordid = :discordid
                WHERE id = :id");
            $updateStmt->execute([
                'fullname' => $fullname,
                'gebdatum' => $gebdatum,
                'geschlecht' => $geschlecht,
                'telefonnr' => $telefonnr,
                'dienstnr' => $dienstnr,
                'discordid' => $discordid,
                'id' => $bewerbungId
            ]);
        }

        $changeText = "Bewerbungsdaten wurden von " . $_SESSION['cirs_user'] . " bearbeitet:\n" . implode("\n", $changes);
        $messageStmt = $pdo->prepare("INSERT INTO intra_bewerbung_messages (bewerbungid, text, user, discordid) VALUES (:bewerbungid, :text, :user, :discordid)");
        $messageStmt->execute([
            'bewerbungid' => $bewerbungId,
            'text' => $changeText,
            'user' => 'System',
            'discordid' => 'System'
        ]);

        $auditlogger = new AuditLogger($pdo);
        $auditlogger->log($_SESSION['userid'], 'Bewerbung bearbeitet', "Bewerbung #$bewerbungId: " . implode('; ', $changes), 'Bewerbung', $bewerbungId);

        Flash::success("Bewerbung erfolgreich aktualisiert. " . count($changes) . " Änderung(en) vorgenommen.");
        header("Location: view.php?id=" . $bewerbungId);
        exit();
    } catch (Exception $e) {
        Flash::error("Fehler beim Speichern: " . $e->getMessage());
        error_log("Edit Bewerbung Error: " . $e->getMessage());
    }
}

$formData = [
    'fullname' => $bewerbung['fullname'],
    'gebdatum' => $bewerbung['gebdatum'],
    'charakterid' => $bewerbung['charakterid'] ?? '',
    'geschlecht' => $bewerbung['geschlecht'],
    'telefonnr' => $bewerbung['telefonnr'],
    'dienstnr' => $bewerbung['dienstnr'],
    'discordid' => $bewerbung['discordid']
];

function getStatusInfo($closed, $deleted)
{
    if ($deleted) return ['text' => 'Gelöscht', 'class' => 'danger'];
    if ($closed) return ['text' => 'Bearbeitet', 'class' => 'info'];
    return ['text' => 'Offen', 'class' => 'warning'];
}

$status = getStatusInfo($bewerbung['closed'], $bewerbung['deleted']);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bewerbung #<?= $bewerbung['id'] ?> bearbeiten &rsaquo; <?php echo SYSTEM_NAME ?></title>
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

<body data-bs-theme="dark" data-page="bewerbungen">
    <?php include __DIR__ . "/../../assets/components/navbar.php"; ?>

    <div class="container-full position-relative" id="mainpageContainer">
        <div class="container">
            <div class="row">
                <div class="col-lg mx-auto mb-4">
                    <hr class="text-light my-3">
                    <div class="bewerbung-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h1 class="mb-2">Bewerbung #<?= $bewerbung['id'] ?> bearbeiten</h1>
                                <p class="mb-0">
                                    <i class="las la-clock"></i>
                                    Eingereicht am <?= date('d.m.Y H:i', strtotime($bewerbung['timestamp'])) ?> Uhr
                                </p>
                            </div>
                            <div class="text-end">
                                <a href="view.php?id=<?= $bewerbung['id'] ?>" class="btn btn-light btn-sm me-2">
                                    <i class="las la-eye"></i> Anzeigen
                                </a>
                                <a href="<?= BASE_PATH ?>admin/apply/" class="btn btn-light btn-sm">
                                    <i class="las la-arrow-left"></i> Übersicht
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php Flash::render(); ?>

                    <?php if ($bewerbung['deleted']): ?>
                        <div class="alert alert-danger">
                            <i class="las la-exclamation-triangle"></i>
                            <strong>Achtung:</strong> Diese Bewerbung ist gelöscht. Änderungen sind möglich, aber die Bewerbung bleibt gelöscht.
                        </div>
                    <?php endif; ?>

                    <form method="post" id="editForm" novalidate>
                        <div class="intra__tile mb-4">
                            <div class="form-section">
                                <h5><i class="las la-user"></i> Persönliche Daten</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fullname" class="form-label required-field">Vor- und Zuname</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname"
                                                value="<?= htmlspecialchars($formData['fullname']) ?>" required>
                                            <div class="invalid-feedback">Bitte gebe einen Namen ein.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="gebdatum" class="form-label required-field">Geburtsdatum</label>
                                            <input type="date" class="form-control" id="gebdatum" name="gebdatum"
                                                value="<?= htmlspecialchars($formData['gebdatum']) ?>" min="1900-01-01" required>
                                            <div class="invalid-feedback">Bitte gebe ein gültiges Geburtsdatum ein.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="geschlecht" class="form-label required-field">Geschlecht</label>
                                            <select class="form-select" id="geschlecht" name="geschlecht" required>
                                                <option value="">Bitte wählen</option>
                                                <option value="0" <?= $formData['geschlecht'] == '0' ? 'selected' : '' ?>>Männlich</option>
                                                <option value="1" <?= $formData['geschlecht'] == '1' ? 'selected' : '' ?>>Weiblich</option>
                                                <option value="2" <?= $formData['geschlecht'] == '2' ? 'selected' : '' ?>>Divers</option>
                                            </select>
                                            <div class="invalid-feedback">Bitte wähle ein Geschlecht aus.</div>
                                        </div>
                                    </div>
                                    <?php if (CHAR_ID): ?>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="charakterid" class="form-label required-field">Charakter-ID</label>
                                                <input type="text" class="form-control" id="charakterid" name="charakterid"
                                                    value="<?= htmlspecialchars($formData['charakterid']) ?>"
                                                    placeholder="ABC12345" pattern="[a-zA-Z]{3}[0-9]{5}" required>
                                                <div class="form-text">Format: 3 Buchstaben + 5 Zahlen (z.B. ABC12345)</div>
                                                <div class="invalid-feedback">Bitte gebe eine gültige Charakter-ID ein.</div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="intra__tile mb-4">
                            <div class="form-section">
                                <h5><i class="las la-address-book"></i> Kontaktdaten</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="discordid" class="form-label required-field">Discord-ID</label>
                                            <input type="text" class="form-control" id="discordid" name="discordid"
                                                value="<?= htmlspecialchars($formData['discordid']) ?>" required>
                                            <div class="invalid-feedback">Bitte gebe eine Discord-ID ein.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="telefonnr" class="form-label">Telefonnummer (IC)</label>
                                            <input type="text" class="form-control" id="telefonnr" name="telefonnr"
                                                value="<?= htmlspecialchars($formData['telefonnr']) ?>"
                                                placeholder="0176 00 00 00 0">
                                            <div class="form-text">In-Character Telefonnummer (optional)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="intra__tile mb-4">
                            <div class="form-section">
                                <h5><i class="las la-id-badge"></i> Bewerbungsdaten</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="dienstnr" class="form-label required-field">Wunsch-Dienstnummer</label>
                                            <input type="text" class="form-control" id="dienstnr" name="dienstnr"
                                                value="<?= htmlspecialchars($formData['dienstnr']) ?>" required>
                                            <div class="form-text">Gewünschte Dienstnummer des Bewerbers</div>
                                            <div class="invalid-feedback">Bitte gebe eine Dienstnummer ein.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Bewerbungs-ID</label>
                                            <input type="text" class="form-control" value="#<?= $bewerbung['id'] ?>" readonly>
                                            <div class="form-text">Eindeutige Bewerbungs-ID (nicht änderbar)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <a href="view.php?id=<?= $bewerbung['id'] ?>" class="btn btn-secondary">
                                    <i class="las la-times"></i> Abbrechen
                                </a>
                            </div>
                            <div>
                                <button type="reset" class="btn btn-outline-secondary me-2">
                                    <i class="las la-undo"></i> Zurücksetzen
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="las la-save"></i> Änderungen speichern
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../../assets/components/footer.php"; ?>

    <script>
        // Bootstrap Validation
        (function() {
            'use strict';

            const form = document.getElementById('editForm');

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            }, false);

            // Charakter-ID Format-Validierung
            <?php if (CHAR_ID): ?>
                const charakterIdInput = document.getElementById('charakterid');
                charakterIdInput.addEventListener('input', function() {
                    const value = this.value.toUpperCase();
                    this.value = value;

                    if (value.length > 0 && !/^[A-Z]{0,3}[0-9]{0,5}$/.test(value)) {
                        this.setCustomValidity('Format: 3 Buchstaben + 5 Zahlen (z.B. ABC12345)');
                    } else if (value.length === 8 && !/^[A-Z]{3}[0-9]{5}$/.test(value)) {
                        this.setCustomValidity('Format: 3 Buchstaben + 5 Zahlen (z.B. ABC12345)');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            <?php endif; ?>

            // Bestätigung bei Reset
            document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
                if (!confirm('Alle Änderungen zurücksetzen? Ungespeicherte Daten gehen verloren.')) {
                    e.preventDefault();
                }
            });

            // Warnung bei Verlassen mit ungespeicherten Änderungen
            let formChanged = false;
            const inputs = form.querySelectorAll('input, select, textarea');

            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    formChanged = true;
                });
            });

            window.addEventListener('beforeunload', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = '';
                    return '';
                }
            });

            // Form submitted - keine Warnung mehr
            form.addEventListener('submit', function() {
                formChanged = false;
            });
        })();
    </script>
</body>

</html>