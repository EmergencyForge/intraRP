<?php
session_start();
require_once __DIR__ . '/../assets/config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../assets/config/database.php';

if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: " . BASE_PATH . "admin/login.php");
    exit();
}

use App\Helpers\Flash;

if (!isset($_SESSION['cirs_user']) || empty($_SESSION['cirs_user'])) {
    header("Location: " . BASE_PATH . "admin/users/editprofile.php");
    exit();
}

$mitarbeiterStmt = $pdo->prepare("SELECT COUNT(*) FROM intra_mitarbeiter WHERE discordtag = :discordid");
$mitarbeiterStmt->execute(['discordid' => $_SESSION['discordtag']]);
$mitarbeiterExists = $mitarbeiterStmt->fetchColumn() > 0;

if ($mitarbeiterExists) {
    header("Location: " . BASE_PATH . "admin/index.php");
    exit();
}

$checkStmt = $pdo->prepare("SELECT COUNT(*) FROM intra_bewerbung WHERE discordid = :discordid AND deleted = 0 AND closed = 0");
$checkStmt->execute(['discordid' => $_SESSION['discordtag']]);
$activeBewerbung = $checkStmt->fetchColumn() > 0;

$existingBewerbung = null;
if ($activeBewerbung) {
    $stmt = $pdo->prepare("SELECT * FROM intra_bewerbung WHERE discordid = :discordid AND deleted = 0 AND closed = 0 ORDER BY timestamp DESC LIMIT 1");
    $stmt->execute(['discordid' => $_SESSION['discordtag']]);
    $existingBewerbung = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingBewerbung) {
        header("Location: " . BASE_PATH . "apply/application.php?id=" . $existingBewerbung['id']);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $fullname = $_POST['fullname'] ?? '';
        $gebdatum = $_POST['gebdatum'] ?? '';
        $geschlecht = $_POST['geschlecht'] ?? '';
        $telefonnr = $_POST['telefonnr'] ?? '';
        $dienstnr = $_POST['dienstnr'] ?? '';
        $discordid = $_SESSION['discordtag'];

        if (empty($fullname) || empty($gebdatum) || $geschlecht === '' || empty($dienstnr)) {
            Flash::error("Bitte alle erforderlichen Felder ausfüllen.");
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        if (CHAR_ID) {
            $charakterid = $_POST['charakterid'] ?? '';
            if (empty($charakterid)) {
                Flash::error("Bitte gebe eine Charakter-ID ein.");
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }

            if (!preg_match('/^[a-zA-Z]{3}[0-9]{5}$/', $charakterid)) {
                Flash::error("Ungültiges Charakter-ID Format. Erwartetes Format: ABC12345");
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            $charakterid = '';
        }

        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM intra_bewerbung WHERE discordid = :discordid AND deleted = 0 AND closed = 0");
        $checkStmt->execute(['discordid' => $discordid]);
        if ($checkStmt->fetchColumn() > 0) {
            Flash::error("Es existiert bereits eine aktive Bewerbung für deine Discord-ID.");
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        if ($existingBewerbung) {
            if (CHAR_ID) {
                $updateStmt = $pdo->prepare("UPDATE intra_bewerbung SET 
                    fullname = :fullname, 
                    gebdatum = :gebdatum, 
                    charakterid = :charakterid, 
                    geschlecht = :geschlecht, 
                    telefonnr = :telefonnr, 
                    dienstnr = :dienstnr
                    WHERE id = :id AND discordid = :discordid");
                $updateStmt->execute([
                    'fullname' => $fullname,
                    'gebdatum' => $gebdatum,
                    'charakterid' => $charakterid,
                    'geschlecht' => $geschlecht,
                    'telefonnr' => $telefonnr,
                    'dienstnr' => $dienstnr,
                    'id' => $existingBewerbung['id'],
                    'discordid' => $discordid
                ]);
            } else {
                $updateStmt = $pdo->prepare("UPDATE intra_bewerbung SET 
                    fullname = :fullname, 
                    gebdatum = :gebdatum, 
                    geschlecht = :geschlecht, 
                    telefonnr = :telefonnr, 
                    dienstnr = :dienstnr
                    WHERE id = :id AND discordid = :discordid");
                $updateStmt->execute([
                    'fullname' => $fullname,
                    'gebdatum' => $gebdatum,
                    'geschlecht' => $geschlecht,
                    'telefonnr' => $telefonnr,
                    'dienstnr' => $dienstnr,
                    'id' => $existingBewerbung['id'],
                    'discordid' => $discordid
                ]);
            }

            $logStmt = $pdo->prepare("INSERT INTO intra_bewerbung_statuslog (bewerbungid, status_alt, status_neu, user) VALUES (:bewerbungid, 0, 0, :user)");
            $logStmt->execute([
                'bewerbungid' => $existingBewerbung['id'],
                'user' => $_SESSION['cirs_user']
            ]);

            Flash::success("Deine Bewerbung wurde erfolgreich aktualisiert!");
        } else {
            if (CHAR_ID) {
                $insertStmt = $pdo->prepare("INSERT INTO intra_bewerbung 
                    (discordid, fullname, gebdatum, charakterid, geschlecht, telefonnr, dienstnr, closed, deleted) 
                    VALUES (:discordid, :fullname, :gebdatum, :charakterid, :geschlecht, :telefonnr, :dienstnr, 0, 0)");
                $insertStmt->execute([
                    'discordid' => $discordid,
                    'fullname' => $fullname,
                    'gebdatum' => $gebdatum,
                    'charakterid' => $charakterid,
                    'geschlecht' => $geschlecht,
                    'telefonnr' => $telefonnr,
                    'dienstnr' => $dienstnr
                ]);
            } else {
                $insertStmt = $pdo->prepare("INSERT INTO intra_bewerbung 
                    (discordid, fullname, gebdatum, geschlecht, telefonnr, dienstnr, closed, deleted) 
                    VALUES (:discordid, :fullname, :gebdatum, :geschlecht, :telefonnr, :dienstnr, 0, 0)");
                $insertStmt->execute([
                    'discordid' => $discordid,
                    'fullname' => $fullname,
                    'gebdatum' => $gebdatum,
                    'geschlecht' => $geschlecht,
                    'telefonnr' => $telefonnr,
                    'dienstnr' => $dienstnr
                ]);
            }

            $bewerbungId = $pdo->lastInsertId();

            $logStmt = $pdo->prepare("INSERT INTO intra_bewerbung_statuslog (bewerbungid, status_alt, status_neu, user, discordid) VALUES (:bewerbungid, NULL, 0, :user, :discordid)");
            $logStmt->execute([
                'bewerbungid' => $bewerbungId,
                'user' => $_SESSION['cirs_user'],
                'discordid' => $_SESSION['discordtag']
            ]);

            $messageStmt = $pdo->prepare("INSERT INTO intra_bewerbung_messages (bewerbungid, text, user, discordid) VALUES (:bewerbungid, :text, :user, :discordid)");
            $messageStmt->execute([
                'bewerbungid' => $bewerbungId,
                'text' => 'Bewerbung wurde eingereicht. Vielen Dank für dein Interesse!',
                'user' => 'System',
                'discordid' => 'System'
            ]);

            Flash::success("Deine Bewerbung wurde erfolgreich eingereicht! Bewerbungs-ID: " . $bewerbungId);

            header("Location: " . BASE_PATH . "application.php?id=" . $bewerbungId);
            exit();
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        Flash::error("Ein Fehler ist aufgetreten: " . $e->getMessage());
        error_log("Bewerbung Error: " . $e->getMessage());
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

$statusBadge = 'warning';
$statusText = 'Offen';
if ($existingBewerbung) {
    if ($existingBewerbung['deleted'] == 1) {
        $statusBadge = 'danger';
        $statusText = 'Gelöscht';
    } elseif ($existingBewerbung['closed'] == 1) {
        $statusBadge = 'info';
        $statusText = 'Bearbeitet';
    } else {
        $statusBadge = 'warning';
        $statusText = 'Offen';
    }
}

$formData = [
    'fullname' => $_SESSION['cirs_user'],
    'gebdatum' => '',
    'charakterid' => '',
    'geschlecht' => '',
    'telefonnr' => '',
    'dienstnr' => ''
];

if ($existingBewerbung) {
    $formData = [
        'fullname' => $existingBewerbung['fullname'],
        'gebdatum' => $existingBewerbung['gebdatum'],
        'charakterid' => $existingBewerbung['charakterid'] ?? '',
        'geschlecht' => $existingBewerbung['geschlecht'],
        'telefonnr' => $existingBewerbung['telefonnr'],
        'dienstnr' => $existingBewerbung['dienstnr']
    ];
}

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bewerbungsportal &rsaquo; <?php echo SYSTEM_NAME ?></title>
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

<body data-bs-theme="dark" data-page="bewerbung">
    <?php include __DIR__ . "/../assets/components/navbar.php"; ?>

    <div class="container-full position-relative" id="mainpageContainer">
        <div class="container">
            <div class="row" id="startpage">
                <div class="col">
                    <hr class="text-light my-3">
                    <h1>Bewerbungsportal</h1>
                    <?php Flash::render(); ?>

                    <div class="row">
                        <?php if ($existingBewerbung): ?>
                            <div class="col me-2 intra__tile">
                                <div class="bewerbung-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h4>Deine Bewerbung</h4>
                                            <p class="mb-0">
                                                <i class="las la-clock"></i>
                                                Eingereicht am <?= date('d.m.Y H:i', strtotime($existingBewerbung['timestamp'])) ?>
                                            </p>
                                        </div>
                                        <span class="badge bg-<?= $statusBadge ?> fs-6">
                                            <?= $statusText ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="bewerbung-details">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless table-sm">
                                                <tr>
                                                    <td><strong>Bewerbungs-ID:</strong></td>
                                                    <td><code><?= $existingBewerbung['id'] ?></code></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td><span class="badge bg-<?= $statusBadge ?>"><?= $statusText ?></span></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless table-sm">
                                                <tr>
                                                    <td><strong>Name:</strong></td>
                                                    <td><?= htmlspecialchars($existingBewerbung['fullname']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Wunsch-Dienstnummer:</strong></td>
                                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($existingBewerbung['dienstnr']) ?></span></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <a href="<?= BASE_PATH ?>apply/application.php?id=<?= $existingBewerbung['id'] ?>" class="btn btn-main-color">
                                        <i class="las la-eye"></i> Bewerbung anzeigen
                                    </a>
                                    <?php if ($existingBewerbung['closed'] == 0): ?>
                                        <button class="btn btn-secondary ms-2" onclick="toggleEditMode()">
                                            <i class="las la-edit"></i> Bewerbung bearbeiten
                                        </button>
                                    <?php else: ?>
                                        <div class="alert alert-info mt-3">
                                            <i class="las la-info-circle"></i>
                                            Deine Bewerbung wurde bearbeitet und ist abgeschlossen.
                                            Weitere Änderungen sind nicht mehr möglich.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!$existingBewerbung && !isset($_GET['apply'])): ?>
                            <div class="col me-2 intra__tile">
                                <h4>Willkommen im Bewerbungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?></h4>
                                <p>
                                    Für deine Bewerbung benötigen wir einige Angaben zu deinem Charakter. Halte bitte folgende Daten bereit:
                                <ul>
                                    <li>Den Vor- und Zunamen deines Charakters (falls dieser nicht mit dem bereits angegebenen Namen übereinstimmt),</li>
                                    <li>das Geburtsdatum deines Charakters,</li>
                                    <?php if (CHAR_ID) : ?>
                                        <li>die Charakter-ID ("Ausweisnummer") deines Charakters,</li>
                                    <?php endif; ?>
                                    <li>das Geschlecht deines Charakters,</li>
                                    <li>deine Telefonnummer (IC)</li>
                                    <li>und deine gewünschte Dienstnummer.</li>
                                </ul>
                                </p>
                                <p>Bitte beachte, dass die Bearbeitung deiner Bewerbung einige Zeit in Anspruch nehmen kann. Auf den folgenden Seiten wird der Status deiner Bewerbung kontinuierlich durch uns aktualisiert und kann dauerhaft hier eingesehen werden.</p>
                                <a href="?apply" class="btn btn-main-color">Bewerbungsprozess starten</a>
                            </div>
                        <?php endif; ?>

                        <?php if ((!$existingBewerbung && isset($_GET['apply'])) || ($existingBewerbung && $existingBewerbung['closed'] == 0)): ?>
                            <div class="col me-2 intra__tile" id="bewerbungForm" <?= $existingBewerbung ? 'style="display:none;"' : '' ?>>
                                <div class="form-section">
                                    <h5><i class="las la-user"></i> <?= $existingBewerbung ? 'Bewerbung bearbeiten' : 'Angaben zum Charakter' ?></h5>

                                    <form id="bewerbung" method="post" novalidate>
                                        <input type="hidden" name="new" value="1" />

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="fullname" class="form-label required-field">Vor- und Zuname</label>
                                                    <input class="form-control" type="text" name="fullname" id="fullname"
                                                        value="<?= htmlspecialchars($formData['fullname']) ?>" required>
                                                    <div class="invalid-feedback">Bitte gebe einen Namen ein.</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="gebdatum" class="form-label required-field">Geburtsdatum</label>
                                                    <input class="form-control" type="date" name="gebdatum" id="gebdatum"
                                                        value="<?= htmlspecialchars($formData['gebdatum']) ?>" min="1900-01-01" required>
                                                    <div class="invalid-feedback">Bitte gebe ein Geburtsdatum ein.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <?php if (CHAR_ID): ?>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="charakterid" class="form-label required-field">Charakter-ID</label>
                                                        <input class="form-control" type="text" name="charakterid" id="charakterid"
                                                            placeholder="ABC12345" value="<?= htmlspecialchars($formData['charakterid']) ?>"
                                                            pattern="[a-zA-Z]{3}[0-9]{5}" required>
                                                        <div class="form-text">Format: 3 Buchstaben + 5 Zahlen</div>
                                                        <div class="invalid-feedback">Bitte gebe eine Charakter-ID ein.</div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="geschlecht" class="form-label required-field">Geschlecht</label>
                                                    <select name="geschlecht" id="geschlecht" class="form-select" required>
                                                        <option value="" <?= $formData['geschlecht'] === '' ? 'selected' : '' ?>>Bitte wählen</option>
                                                        <option value="0" <?= $formData['geschlecht'] == '0' ? 'selected' : '' ?>>Männlich</option>
                                                        <option value="1" <?= $formData['geschlecht'] == '1' ? 'selected' : '' ?>>Weiblich</option>
                                                        <option value="2" <?= $formData['geschlecht'] == '2' ? 'selected' : '' ?>>Divers</option>
                                                    </select>
                                                    <div class="invalid-feedback">Bitte wähle ein Geschlecht aus.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="discordid" class="form-label">Discord-ID</label>
                                                    <input class="form-control" type="text" value="<?= $_SESSION['discordtag'] ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="telefonnr" class="form-label">Telefonnummer</label>
                                                    <input class="form-control" type="text" name="telefonnr" id="telefonnr"
                                                        value="<?= htmlspecialchars($formData['telefonnr']) ?>" placeholder="0176 00 00 00 0">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="dienstnr" class="form-label required-field">Wunsch-Dienstnummer</label>
                                                    <div class="dienstnr-container">
                                                        <input class="form-control" type="text" name="dienstnr" id="dienstnr"
                                                            value="<?= htmlspecialchars($formData['dienstnr']) ?>" required>
                                                    </div>
                                                    <div class="invalid-feedback">Bitte gebe eine Wunsch-Dienstnummer ein.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-main-color btn-lg">
                                                <i class="las la-paper-plane"></i>
                                                <?= $existingBewerbung ? 'Bewerbung aktualisieren' : 'Bewerbung einreichen' ?>
                                            </button>
                                            <?php if ($existingBewerbung): ?>
                                                <button type="button" class="btn btn-secondary btn-lg ms-2" onclick="toggleEditMode()">
                                                    <i class="las la-times"></i> Abbrechen
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../assets/components/footer.php"; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('bewerbung');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!this.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        this.classList.add('was-validated');
                    }
                });
            }
        });

        function toggleEditMode() {
            const formContainer = document.getElementById('bewerbungForm');
            if (formContainer) {
                const isHidden = formContainer.style.display === 'none';
                formContainer.style.display = isHidden ? 'block' : 'none';

                if (isHidden) {
                    formContainer.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        }
    </script>
</body>

</html>