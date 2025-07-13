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
}

$stmtr = $pdo->prepare("SELECT * FROM intra_mitarbeiter_rdquali WHERE none = 1 LIMIT 1");
$stmtr->execute();
$resultr = $stmtr->fetch();

$stmtf = $pdo->prepare("SELECT * FROM intra_mitarbeiter_fwquali WHERE none = 1 LIMIT 1");
$stmtf->execute();
$resultf = $stmtf->fetch();

$fromBewerbung = false;
$bewerbungData = [];

if (isset($_GET['from_bewerbung'])) {
    $bewerbungId = (int)$_GET['from_bewerbung'];

    if ($bewerbungId > 0) {
        $bewerbungStmt = $pdo->prepare("SELECT * FROM intra_bewerbung WHERE id = :id");
        $bewerbungStmt->execute(['id' => $bewerbungId]);
        $bewerbung = $bewerbungStmt->fetch(PDO::FETCH_ASSOC);

        if ($bewerbung) {
            $fromBewerbung = true;
            $bewerbungData = [
                'id' => $bewerbung['id'],
                'fullname' => $bewerbung['fullname'] ?? '',
                'gebdatum' => $bewerbung['gebdatum'] ?? '',
                'geschlecht' => $bewerbung['geschlecht'] ?? '',
                'discordid' => $bewerbung['discordid'] ?? '',
                'telefonnr' => $bewerbung['telefonnr'] ?? '',
                'dienstnr' => $bewerbung['dienstnr'] ?? '',
                'charakterid' => $bewerbung['charakterid'] ?? ''
            ];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];

    try {
        $fullname = $_POST['fullname'] ?? '';
        $gebdatum = $_POST['gebdatum'] ?? '';
        $dienstgrad = $_POST['dienstgrad'] ?? '';
        $geschlecht = $_POST['geschlecht'] ?? '';
        $discordtag = $_POST['discordtag'] ?? '';
        $telefonnr = $_POST['telefonnr'] ?? '';
        $dienstnr = $_POST['dienstnr'] ?? '';
        $einstdatum = $_POST['einstdatum'] ?? '';
        $qualird = $resultr['id'];
        $qualifw = $resultf['id'];

        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM intra_mitarbeiter WHERE dienstnr = :dienstnr");
        $checkStmt->execute(['dienstnr' => $dienstnr]);
        $dienstnrExists = $checkStmt->fetchColumn() > 0;

        if ($dienstnrExists) {
            $response['message'] = "Diese Dienstnummer ist bereits vergeben. Bitte wählen Sie eine andere.";
            echo json_encode($response);
            exit;
        }

        if (CHAR_ID) {
            $charakterid = $_POST['charakterid'] ?? '';
            if (empty($fullname) || empty($gebdatum) || empty($charakterid) || empty($dienstgrad)) {
                $response['message'] = "Bitte alle erforderlichen Felder ausfüllen.";
                echo json_encode($response);
                exit;
            }
        } else {
            $charakterid = '';
            if (empty($fullname) || empty($gebdatum) || empty($dienstgrad)) {
                $response['message'] = "Bitte alle erforderlichen Felder ausfüllen.";
                echo json_encode($response);
                exit;
            }
        }

        if (CHAR_ID) {
            $stmt = $pdo->prepare("INSERT INTO intra_mitarbeiter 
            (fullname, gebdatum, charakterid, dienstgrad, geschlecht, discordtag, telefonnr, dienstnr, einstdatum, qualifw2, qualird) 
            VALUES (:fullname, :gebdatum, :charakterid, :dienstgrad, :geschlecht, :discordtag, :telefonnr, :dienstnr, :einstdatum, :qualifw, :qualird)");
            $stmt->execute([
                'fullname' => $fullname,
                'gebdatum' => $gebdatum,
                'charakterid' => $charakterid,
                'dienstgrad' => $dienstgrad,
                'geschlecht' => $geschlecht,
                'discordtag' => $discordtag,
                'telefonnr' => $telefonnr,
                'dienstnr' => $dienstnr,
                'einstdatum' => $einstdatum,
                'qualifw' => $qualifw,
                'qualird' => $qualird
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO intra_mitarbeiter 
            (fullname, gebdatum, dienstgrad, geschlecht, discordtag, telefonnr, dienstnr, einstdatum, qualifw2, qualird) 
            VALUES (:fullname, :gebdatum, :dienstgrad, :geschlecht, :discordtag, :telefonnr, :dienstnr, :einstdatum, :qualifw, :qualird)");
            $stmt->execute([
                'fullname' => $fullname,
                'gebdatum' => $gebdatum,
                'dienstgrad' => $dienstgrad,
                'geschlecht' => $geschlecht,
                'discordtag' => $discordtag,
                'telefonnr' => $telefonnr,
                'dienstnr' => $dienstnr,
                'einstdatum' => $einstdatum,
                'qualifw' => $qualifw,
                'qualird' => $qualird
            ]);
        }

        $savedId = $pdo->lastInsertId();

        $edituser = $_SESSION['cirs_user'] ?? 'Unknown';
        $logContent = 'Mitarbeiter wurde angelegt.';
        $logStmt = $pdo->prepare("INSERT INTO intra_mitarbeiter_log (profilid, type, content, paneluser) VALUES (:id, '6', :content, :paneluser)");
        $logStmt->execute([
            'id' => $savedId,
            'content' => $logContent,
            'paneluser' => $edituser
        ]);

        $response['success'] = true;
        $response['message'] = "Benutzer erfolgreich erstellt!";
        $response['redirect'] = BASE_PATH . "admin/personal/profile.php?id=" . $savedId;
    } catch (Exception $e) {
        $response['message'] = "Fehler: " . $e->getMessage();
    }

    $auditlogger = new AuditLogger($pdo);
    $auditlogger->log($_SESSION['userid'], 'Mitarbeiter erstellt', 'Name: ' . $fullname . ', Dienstnummer: ' . $dienstnr, 'Mitarbeiter', 1);

    echo json_encode($response);
    exit;
}
?>


<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Administration &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/style.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/admin.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/personal.min.css" />
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

<body data-bs-theme="dark" data-page="mitarbeiter">
    <?php include "../../assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <h1 class="mb-3">Mitarbeiterprofil</h1>
                    <div class="row">
                        <div class="col">
                            <form id="profil" method="post" novalidate>
                                <div class="intra__tile py-2 px-3">
                                    <div class="w-100 text-center">
                                        <i class="las la-user-circle" style="font-size:94px"></i>
                                        <?php
                                        require __DIR__ . '/../../assets/config/database.php';
                                        $stmt = $pdo->prepare("SELECT id,name,priority FROM intra_mitarbeiter_dienstgrade WHERE archive = 0 ORDER BY priority ASC");
                                        $stmt->execute();
                                        $dgsel = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        ?>

                                        <div class="form-floating">
                                            <select class="form-select mt-3" name="dienstgrad" id="dienstgrad">
                                                <option value="" selected hidden>Dienstgrad wählen</option>
                                                <?php foreach ($dgsel as $data) {
                                                    echo "<option value='{$data['id']}'>{$data['name']}</option>";
                                                } ?>
                                            </select>
                                            <label for="dienstgrad">Dienstgrad</label>
                                        </div>
                                        <div class="invalid-feedback">Bitte wähle einen Dienstgrad aus.</div>
                                        <hr class="my-3">
                                        <input type="hidden" name="new" value="1" />
                                        <table class="mx-auto" style="width: 100%;">
                                            <tbody class="text-start">
                                                <tr>
                                                    <td class="fw-bold text-center" style="width:15%">Vor- und Zuname</td>
                                                    <td style="width:35%">
                                                        <input class="form-control w-100" type="text" name="fullname" id="fullname"
                                                            value="<?= $fromBewerbung ? htmlspecialchars($bewerbungData['fullname']) : '' ?>" required>
                                                        <div class="invalid-feedback">Bitte gebe einen Namen ein.</div>
                                                    </td>
                                                    <td class="fw-bold text-center" style="width: 15%;">Geburtsdatum</td>
                                                    <td style="width:35%">
                                                        <input class="form-control" type="date" name="gebdatum" id="gebdatum"
                                                            value="<?= $fromBewerbung ? $bewerbungData['gebdatum'] : '' ?>" min="1900-01-01" required>
                                                        <div class="invalid-feedback">Bitte gebe ein Geburtsdatum ein.</div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <?php if (CHAR_ID) : ?>
                                                        <td class="fw-bold text-center" style="width: 15%">Charakter-ID</td>
                                                        <td style="width: 35%;">
                                                            <input class="form-control" type="text" name="charakterid" id="charakterid"
                                                                placeholder="ABC12345"
                                                                value="<?= $fromBewerbung ? htmlspecialchars($bewerbungData['charakterid']) : '' ?>"
                                                                pattern="[a-zA-Z]{3}[0-9]{5}" required>
                                                            <div class="invalid-feedback">Bitte gebe eine charakter-ID ein.</div>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td class="fw-bold text-center" style="width: 15%;">Geschlecht</td>
                                                    <td style="width: 35%;">
                                                        <select name="geschlecht" id="geschlecht" class="form-select" required>
                                                            <option value="" <?= !$fromBewerbung ? 'selected' : '' ?> hidden>Bitte wählen</option>
                                                            <option value="0" <?= $fromBewerbung && $bewerbungData['geschlecht'] == '0' ? 'selected' : '' ?>>Männlich</option>
                                                            <option value="1" <?= $fromBewerbung && $bewerbungData['geschlecht'] == '1' ? 'selected' : '' ?>>Weiblich</option>
                                                            <option value="2" <?= $fromBewerbung && $bewerbungData['geschlecht'] == '2' ? 'selected' : '' ?>>Divers</option>
                                                        </select>
                                                        <div class="invalid-feedback">Bitte wähle ein Geschlecht aus.</div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold text-center">Discord-ID</td>
                                                    <td>
                                                        <input class="form-control" type="number" name="discordtag" id="discordtag"
                                                            value="<?= $fromBewerbung ? htmlspecialchars($bewerbungData['discordid']) : '' ?>"
                                                            minlength="17" maxlength="18" required>
                                                        <div class="invalid-feedback">Bitte gib eine Discord-ID an.</div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold text-center">Telefonnummer</td>
                                                    <td><input class="form-control" type="text" name="telefonnr" id="telefonnr"
                                                            value="<?= $fromBewerbung && !empty($bewerbungData['telefonnr']) ? htmlspecialchars($bewerbungData['telefonnr']) : '0176 00 00 00 0' ?>"></td>
                                                    <td class="fw-bold text-center">Dienstnummer</td>
                                                    <td class="dienstnr-container">
                                                        <input class="form-control" type="number" name="dienstnr" id="dienstnr" value="<?= $fromBewerbung ? htmlspecialchars($bewerbungData['dienstnr']) : '' ?>" oninput="checkDienstnrAvailability()" required>
                                                        <div id="dienstnr-status" class="dienstnr-status"></div>
                                                        <div class="invalid-feedback">Bitte gebe eine Dienstnummer ein.</div>
                                                        <div id="dienstnr-feedback" class="text-danger small" style="display: none;"></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Einstellungsdatum</td>
                                                    <td>
                                                        <input class="form-control" type="date" name="einstdatum" id="einstdatum" value="" min="2022-01-01" required>
                                                        <div class="invalid-feedback">Bitte gebe ein Einstellungsdatum ein.</div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <a href="#" class="mt-4 btn btn-success btn-sm" id="personal-save">
                                    <i class="las la-plus-circle"></i> Benutzer erstellen
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var delayTimer;
        var isDienstnrAvailable = false;

        function checkDienstnrAvailability() {
            clearTimeout(delayTimer);

            const dienstnrInput = document.getElementById('dienstnr');
            const statusElement = document.getElementById('dienstnr-status');
            const feedbackElement = document.getElementById('dienstnr-feedback');
            const dienstnr = dienstnrInput.value.trim();

            dienstnrInput.classList.remove('valid', 'invalid');
            feedbackElement.style.display = 'none';
            statusElement.innerHTML = '';
            statusElement.className = 'dienstnr-status';

            if (!dienstnr) {
                isDienstnrAvailable = false;
                return;
            }

            statusElement.innerHTML = '<div class="spinner"></div>';
            statusElement.classList.add('loading');

            delayTimer = setTimeout(function() {
                $.ajax({
                    url: '<?= BASE_PATH ?>assets/functions/checkdnr.php',
                    method: 'POST',
                    data: {
                        dienstnr: dienstnr
                    },
                    dataType: 'text',
                    success: function(response) {
                        statusElement.classList.remove('loading');

                        response = response.trim();

                        if (response === 'exists') {
                            statusElement.innerHTML = '<i class="las la-times"></i>';
                            statusElement.classList.add('unavailable');
                            dienstnrInput.classList.add('invalid');
                            feedbackElement.textContent = 'Diese Dienstnummer ist bereits vergeben.';
                            feedbackElement.style.display = 'block';
                            isDienstnrAvailable = false;
                        } else if (response === 'not_exists') {
                            statusElement.innerHTML = '<i class="las la-check"></i>';
                            statusElement.classList.add('available');
                            dienstnrInput.classList.add('valid');
                            isDienstnrAvailable = true;
                        } else {
                            statusElement.innerHTML = '<i class="las la-exclamation-triangle"></i>';
                            statusElement.classList.add('unavailable');
                            feedbackElement.textContent = 'Unerwartete Antwort vom Server: ' + response;
                            feedbackElement.style.display = 'block';
                            isDienstnrAvailable = false;
                        }
                    },
                    error: function(xhr, status, error) {
                        statusElement.classList.remove('loading');
                        statusElement.innerHTML = '<i class="las la-exclamation-triangle"></i>';
                        statusElement.classList.add('unavailable');
                        feedbackElement.textContent = 'Verbindungsfehler: ' + xhr.status + ' - ' + error;
                        feedbackElement.style.display = 'block';
                        isDienstnrAvailable = false;
                    }
                });
            }, 500);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const dienstnrInput = document.getElementById('dienstnr');

            if (dienstnrInput.value.trim()) {
                checkDienstnrAvailability();
            }
        });

        document.getElementById("personal-save").addEventListener("click", function(event) {
            event.preventDefault();

            var form = document.getElementById("profil");
            var dienstnrInput = document.getElementById('dienstnr');

            if (dienstnrInput.value.trim() && !isDienstnrAvailable) {
                var errorAlert = document.createElement("div");
                errorAlert.className = "alert alert-danger mt-3";
                errorAlert.innerHTML = "Bitte wählen Sie eine verfügbare Dienstnummer.";
                form.prepend(errorAlert);

                setTimeout(() => {
                    errorAlert.remove();
                }, 5000);

                return;
            }

            if (!form.checkValidity()) {
                form.classList.add("was-validated");
                return;
            }

            var formData = new FormData(form);

            fetch("create.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var successAlert = document.createElement("div");
                        successAlert.className = "alert alert-success mt-3";
                        successAlert.innerHTML = data.message;
                        form.prepend(successAlert);

                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    } else {
                        var errorAlert = document.createElement("div");
                        errorAlert.className = "alert alert-danger mt-3";
                        errorAlert.innerHTML = data.message;
                        form.prepend(errorAlert);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    var errorAlert = document.createElement("div");
                    errorAlert.className = "alert alert-danger mt-3";
                    errorAlert.innerHTML = "Ein unerwarteter Fehler ist aufgetreten.";
                    form.prepend(errorAlert);
                });
        });
    </script>
    <?php include __DIR__ . "/../../assets/components/footer.php"; ?>
</body>

</html>