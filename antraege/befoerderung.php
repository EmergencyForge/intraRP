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

if (!isset($_SESSION['cirs_user']) || empty($_SESSION['cirs_user'])) {
    header("Location: " . BASE_PATH . "admin/users/editprofile.php");
}

if (isset($_POST['new']) && $_POST['new'] == 1) {
    $name_dn = $_REQUEST['name_dn'];
    $dienstgrad = $_REQUEST['dienstgrad'];
    $freitext = $_REQUEST['freitext'];

    do {
        $random_number = mt_rand(100000, 999999);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM intra_antrag_bef WHERE uniqueid = ?");
        $stmt->execute([$random_number]);
        $count = $stmt->fetchColumn();
    } while ($count > 0);

    $stmt = $pdo->prepare("INSERT INTO intra_antrag_bef (`name_dn`, `dienstgrad`, `freitext`, `uniqueid`, `discordid`) VALUES (:name_dn, :dienstgrad, :freitext, :uniqueid, :discordtag)");

    $stmt->execute([
        ':name_dn' => $name_dn,
        ':dienstgrad' => $dienstgrad,
        ':freitext' => $freitext,
        ':uniqueid' => $random_number,
        ':discordtag' => $_SESSION['discordtag']
    ]);

    header('Location: view.php?antrag=' . $random_number . '');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Anträge &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/style.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/cirs.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
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

<body id="antrag">
    <!-- NAVIGATION -->
    <nav class="navbar bg-main-color" id="cirs-nav">
        <div class="container-fluid">
            <div class="container">
                <div class="row w-100">
                    <div class="col d-flex align-items-center justify-content-start">
                        <a id="sb-logo" href="#">
                            <img src="<?= BASE_PATH ?>assets/img/schriftzug_stadt_weiss.png" alt="Stadt <?php echo SERVER_CITY ?>" width="auto" height="64px">
                        </a>
                    </div>
                    <div class="col d-flex align-items-center justify-content-end text-light" id="pageTitle">
                        Antragsmanagement
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- ------------ -->
    <!-- PAGE CONTENT -->
    <!-- ------------ -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-2 border-2 border-top border-semigray bg-gray-color" id="cirs-links">
                <hr class="text-gray-color my-3">
                <?php include '../assets/components/navbar_antraege.php' ?>
            </div>
            <div class="col"></div>
            <div class="col-6 my-5">
                <hr class="text-light my-3">
                <h1>Beförderungsantrag stellen</h1>
                <hr class="text-light my-3">
                <form action="" id="cirs-form" method="post">
                    <input type="hidden" name="new" value="1" />
                    <div class="row">
                        <div class="col mb-3">
                            <label for="name_dn" class="form-label fw-bold">Name und Dienstnummer <span class="text-main-color">*</span></label>
                            <input type="text" class="form-control" id="name_dn" name="name_dn" placeholder="" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="dienstgrad" class="form-label fw-bold">Aktueller Dienstgrad <span class="text-main-color">*</span></label>
                            <input type="text" class="form-control" id="dienstgrad" name="dienstgrad" placeholder="" required>
                        </div>
                    </div>
                    <hr class="text-light my-3">
                    <h5>Schriftlicher Antrag</h5>
                    <div class="mb-3">
                        <textarea class="form-control" id="freitext" name="freitext" rows="5"></textarea>
                    </div>
                    <p><input class="mt-4 btn btn-main-color" name="submit" type="submit" value="Absenden" /></p>
                </form>
            </div>
            <div class="col"></div>
        </div>
    </div>

    <?php include __DIR__ . "/../assets/components/footer.php"; ?>
</body>

</html>