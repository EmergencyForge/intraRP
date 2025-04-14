<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    header("Location: /admin/login.php");
    exit();
}

use App\Auth\Permissions;
use App\Helpers\Flash;
use App\Utils\AuditLogger;
use App\Localization\Lang;

Lang::setLanguage(LANG ?? 'de');

if (!Permissions::check(['admin', 'personnel.edit'])) {
    Flash::set('error', 'no-permissions');
    header("Location: /admin/index.php");
}

$stmtr = $pdo->prepare("SELECT * FROM intra_mitarbeiter_rdquali WHERE none = 1 LIMIT 1");
$stmtr->execute();
$resultr = $stmtr->fetch();

$stmtf = $pdo->prepare("SELECT * FROM intra_mitarbeiter_fwquali WHERE none = 1 LIMIT 1");
$stmtf->execute();
$resultf = $stmtf->fetch();

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

        if (CHAR_ID) {
            $charakterid = $_POST['charakterid'] ?? '';
            if (empty($fullname) || empty($gebdatum) || empty($charakterid) || empty($dienstgrad)) {
                $response['message'] = __('personnel.create.missing_fields');
                echo json_encode($response);
                exit;
            }
        } else {
            if (empty($fullname) || empty($gebdatum) || empty($dienstgrad)) {
                $response['message'] = __('personnel.create.missing_fields');
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
        $logContent = __('personnel.create.personnel_created');
        $logStmt = $pdo->prepare("INSERT INTO intra_mitarbeiter_log (profilid, type, content, paneluser) VALUES (:id, '6', :content, :paneluser)");
        $logStmt->execute([
            'id' => $savedId,
            'content' => $logContent,
            'paneluser' => $edituser
        ]);

        $response['success'] = true;
        $response['message'] = "Benutzer erfolgreich erstellt!";
        $response['redirect'] = "/admin/personal/profile.php?id=" . $savedId;
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
    <title><?= __('title', [SYSTEM_NAME]) ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/style.min.css" />
    <link rel="stylesheet" href="/assets/css/admin.min.css" />
    <link rel="stylesheet" href="/assets/css/personal.min.css" />
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
                    <h1 class="mb-3"><?= __('personnel.create.title') ?></h1>
                    <div class="row">
                        <div class="col">
                            <form id="profil" method="post" novalidate>
                                <div class="intra__tile py-2 px-3">
                                    <div class="w-100 text-center">
                                        <i class="las la-user-circle" style="font-size:94px"></i>
                                        <?php
                                        require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';
                                        $stmt = $pdo->prepare("SELECT id,name,priority FROM intra_mitarbeiter_dienstgrade WHERE archive = 0 ORDER BY priority ASC");
                                        $stmt->execute();
                                        $dgsel = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        ?>

                                        <div class="form-floating">
                                            <select class="form-select mt-3" name="dienstgrad" id="dienstgrad">
                                                <option value="" selected hidden><?= __('personnel.create.select_rank') ?></option>
                                                <?php foreach ($dgsel as $data) {
                                                    if ($dg == $data['id']) {
                                                        echo "<option value='{$data['id']}' selected='selected'>{$data['name']}</option>";
                                                    } else {
                                                        echo "<option value='{$data['id']}'>{$data['name']}</option>";
                                                    }
                                                } ?>
                                            </select>
                                            <label for="dienstgrad"><?= __('personnel.create.rank') ?></label>
                                        </div>
                                        <div class="invalid-feedback"><?= __('personnel.create.invalid_rank') ?></div>
                                        <hr class="my-3">
                                        <input type="hidden" name="new" value="1" />
                                        <table class="mx-auto" style="width: 100%;">
                                            <tbody class="text-start">
                                                <tr>
                                                    <td class="fw-bold text-center" style="width:15%"><?= __('personnel.create.form.fullname') ?></td>
                                                    <td style="width:35%">
                                                        <input class="form-control w-100" type="text" name="fullname" id="fullname" value="" required>
                                                        <div class="invalid-feedback"><?= __('personnel.create.form.invalid_fullname') ?></div>
                                                    </td>
                                                    <td class="fw-bold text-center" style="width: 15%;"><?= __('personnel.create.form.birthday') ?></td>
                                                    <td style="width:35%">
                                                        <input class="form-control" type="date" name="gebdatum" id="gebdatum" value="" min="1900-01-01" required>
                                                        <div class="invalid-feedback"><?= __('personnel.create.form.invalid_birthday') ?></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <?php if (CHAR_ID) : ?>
                                                        <td class="fw-bold text-center" style="width: 15%"><?= __('personnel.create.form.characterid') ?></td>
                                                        <td style="width: 35%;">
                                                            <input class="form-control" type="text" name="charakterid" id="charakterid" value="" pattern="[a-zA-Z]{3}[0-9]{5}" required>
                                                            <div class="invalid-feedback"><?= __('personnel.create.form.invalid_characterid') ?></div>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td class="fw-bold text-center" style="width: 15%;"><?= __('personnel.create.form.gender') ?></td>
                                                    <td style="width: 35%;">
                                                        <select name="geschlecht" id="geschlecht" class="form-select" required>
                                                            <option value="" selected hidden><?= __('personnel.create.form.select_gender') ?></option>
                                                            <option value="0"><?= __('personnel.create.form.gender_male') ?></option>
                                                            <option value="1"><?= __('personnel.create.form.gender_female') ?></option>
                                                            <option value="2"><?= __('personnel.create.form.gender_other') ?></option>
                                                        </select>
                                                        <div class="invalid-feedback"><?= __('personnel.create.form.invalid_gender') ?></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold text-center"><?= __('personnel.create.form.discord') ?></td>
                                                    <td><input class="form-control" type="text" name="discordtag" id="discordtag" value=""></td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold text-center"><?= __('personnel.create.form.phone') ?></td>
                                                    <td><input class="form-control" type="text" name="telefonnr" id="telefonnr" value="0176 00 00 00 0"></td>
                                                    <td class="fw-bold text-center"><?= __('personnel.create.form.servicenr') ?></td>
                                                    <td>
                                                        <input class="form-control" type="number" name="dienstnr" id="dienstnr" value="" oninput="checkDienstnrAvailability()" required>
                                                        <div class="invalid-feedback"><?= __('personnel.create.form.invalid_servicenr') ?></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold"><?= __('personnel.create.form.hiring_date') ?></td>
                                                    <td>
                                                        <input class="form-control" type="date" name="einstdatum" id="einstdatum" value="" min="2022-01-01" required>
                                                        <div class="invalid-feedback"><?= __('personnel.create.form.invalid_hiring_date') ?></div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <a href="#" class="mt-4 btn btn-success btn-sm" id="personal-save">
                                    <i class="las la-plus-circle"></i> <?= __('personnel.create.form.create') ?>
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

        function checkDienstnrAvailability() {
            clearTimeout(delayTimer);

            delayTimer = setTimeout(function() {
                var dienstnr = $('#dienstnr').val();

                $.ajax({
                    url: '/assets/functions/checkdnr.php',
                    method: 'POST',
                    data: {
                        dienstnr: dienstnr
                    },
                    success: function(response) {
                        if (response === 'exists') {
                            alert(<?= json_encode(__('personnel.create.servicenr_exists')) ?>);
                            $('#dienstnr').val('');
                        }
                    }
                });
            }, 500);
        }
    </script>
    <script>
        document.getElementById("personal-save").addEventListener("click", function(event) {
            event.preventDefault();

            var form = document.getElementById("profil");
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
                .catch(error => console.error("Error:", error));
        });
    </script>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/assets/components/footer.php"; ?>
</body>

</html>