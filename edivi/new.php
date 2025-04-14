<?php

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

if (isset($_POST['new']) && $_POST['new'] == 1) {
    $enr = $_POST['enr'];
    // check if there already is a protocol with this enr in the database
    $check = $pdo->prepare("SELECT * FROM intra_edivi WHERE enr = :enr");
    $check->execute(array('enr' => $enr));
    $check = $check->fetch();
    if ($check) {
        // if there is, do nothing
    } else {
        // if there is not, create a new protocol
        $statement = $pdo->prepare("INSERT INTO intra_edivi (enr) VALUES (:enr)");
        $result = $statement->execute(array('enr' => $enr));
    }
    header('Location: /edivi/protokoll.php?id=' . $enr);
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>eDIVI &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/style.min.css" />
    <link rel="stylesheet" href="/assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="/assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
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

<body id="dashboard" class="d-flex justify-content-center align-items-center">
    <div class="row">
        <div class="col">
            <div class="card px-4 py-3">
                <h1 id="loginHeader">intra<span class="text-main-color">SB</span></h1>
                <p class="subtext">Das Intranet der Stadt!</p>
                <h4 class="text-center my-2">Protokoll öffnen/anlegen</h4>
                <form name="form" method="post" action="">
                    <input type="hidden" name="new" value="1" />
                    <strong>Einsatznummer:</strong><br>
                    <input class="form-control" type="text" size="40" maxlength="250" name="enr"><br><br>

                    <input class="btn btn-primary w-100" type="submit" value="Suchen">
                </form>
            </div>
        </div>
    </div>
    <footer>
        <div class="footerCopyright">
            <a href="https://hypax.wtf" target="_blank"><i class="las la-code"></i> hypax</a>
            <span>© 2023-<?php echo date("Y"); ?> intraRP | Version <?php echo SYSTEM_VERSION ?></span>
        </div>
    </footer>
</body>

</html>