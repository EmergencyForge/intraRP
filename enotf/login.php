<?php
session_start();
require_once __DIR__ . '/../assets/config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../assets/config/database.php';

$prot_url = "https://" . SYSTEM_URL . "/enotf/index.php";

date_default_timezone_set('Europe/Berlin');
$currentTime = date('H:i');
$currentDate = date('d.m.Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['fahrername']      = $_POST['fahrername'];
    $_SESSION['fahrerquali']     = $_POST['fahrerquali'];
    $_SESSION['beifahrername']   = $_POST['beifahrername'] ?? null;
    $_SESSION['beifahrerquali']  = $_POST['beifahrerquali'] ?? null;
    $_SESSION['protfzg']         = $_POST['protfzg'];

    header("Location: overview.php");
    exit();
}

$stmtfn = $pdo->query("SELECT fullname FROM intra_mitarbeiter ORDER BY fullname ASC");
$fullnames = $stmtfn->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
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
    <meta property="og:title" content="eNOTF &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
</head>

<body style="overflow-x:hidden" id="edivi__login">
    <form name="form" method="post" action="">
        <datalist id="nameSuggestions">
            <?php foreach ($fullnames as $name): ?>
                <option value="<?= htmlspecialchars($name) ?>"></option>
            <?php endforeach; ?>
        </datalist>
        <input type="hidden" name="new" value="1" />
        <div class="container-fluid" id="edivi__container">
            <div class="row h-100">
                <div class="col" id="edivi__content">
                    <div class="row my-2 border-bottom border-light" id="edivi__login-title">
                        <div class="col">
                            <h5 class="fw-bold">Anmeldung</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="row mb-2">
                                <div class="col">
                                    <input type="text" class="form-control my-2" name="fahrername" id="fahrername" placeholder="" list="nameSuggestions" required />
                                    <label for="fahrername">Fahrer-Name</label>
                                </div>
                                <div class="col-3">
                                    <select class="form-select my-2" name="fahrerquali" id="fahrerquali" required>
                                        <option value="" selected></option>
                                        <option value="RH">RettHelfer</option>
                                        <option value="RS/A">RettSan i.A.</option>
                                        <option value="RS">RettSan</option>
                                        <option value="NFS/A">NotSan i.A.</option>
                                        <option value="NFS">NotSan</option>
                                        <option value="NA">Notarzt</option>
                                    </select>
                                    <label for="fahrerquali">Qualifikation</label>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col">
                                    <input type="text" class="form-control my-2" name="beifahrername" id="beifahrername" placeholder="" list="nameSuggestions" />

                                    <label for="beifahrername">Beifahrer-Name</label>
                                </div>
                                <div class="col-3">
                                    <select class="form-select my-2" name="beifahrerquali" id="beifahrerquali">
                                        <option value="" selected></option>
                                        <option value="RH">RettHelfer</option>
                                        <option value="RS/A">RettSan i.A.</option>
                                        <option value="RS">RettSan</option>
                                        <option value="NFS/A">NotSan i.A.</option>
                                        <option value="NFS">NotSan</option>
                                        <option value="NA">Notarzt</option>
                                    </select>
                                    <label for="beifahrerquali">Qualifikation</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col"><button type="button" class="edivi__nidabutton w-100" id="crew__delete" name="crew__delete">Besatzung löschen</button></div>
                                <div class="col"><button type="button" class="edivi__nidabutton w-100" id="crew__switch" name="crew__switch">Fahrer / Beifahrer tauschen</button></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <select name="protfzg" id="protfzg" class="form-select my-2" required>
                                        <option value="" disabled selected>Fahrzeug wählen</option>
                                        <?php
                                        $stmt = $pdo->prepare("SELECT * FROM intra_fahrzeuge WHERE active = 1 AND rd_type <> 0 ORDER BY priority ASC");
                                        $stmt->execute();
                                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($result as $row) {
                                            $selected = ($row['aktiv'] == 1) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($row['identifier']) . "'>" . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['veh_type']) . ")</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <hr class="my-5" style="color: transparent">
                            <hr class="my-5" style="color: transparent">
                            </hr>
                            <div class="row">
                                <div class="col text-end">
                                    <button type="submit" class="edivi__nidabutton" style="padding: 20px 40px" id="data__set" name="data__set">OK</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </form>
    <script>
        document.getElementById('crew__delete').addEventListener('click', function() {
            document.getElementById('fahrername').value = '';
            document.getElementById('fahrerquali').value = '';
            document.getElementById('beifahrername').value = '';
            document.getElementById('beifahrerquali').value = '';
        });

        document.getElementById('crew__switch').addEventListener('click', function() {
            const fName = document.getElementById('fahrername');
            const fQuali = document.getElementById('fahrerquali');
            const bName = document.getElementById('beifahrername');
            const bQuali = document.getElementById('beifahrerquali');

            [fName.value, bName.value] = [bName.value, fName.value];
            [fQuali.value, bQuali.value] = [bQuali.value, fQuali.value];
        });
    </script>

    <script>
        var modalCloseButton = document.querySelector('#myModal4 .btn-close');
        var freigeberInput = document.getElementById('freigeber');

        modalCloseButton.addEventListener('click', function() {
            freigeberInput.value = '';
        });
    </script>
</body>

</html>