<?php
require __DIR__ . '/../assets/config/config.php';
require __DIR__ . '/../assets/config/database.php';
ini_set('session.gc_maxlifetime', 604800);
ini_set('session.cookie_path', '/');  // Set the cookie path to the root directory
ini_set('session.cookie_domain', SYSTEM_URL);  // Set the cookie domain to your domain
ini_set('session.cookie_lifetime', 604800);  // Set the cookie lifetime (in seconds)
ini_set('session.cookie_secure', true);  // Set to true if using HTTPS, false otherwise

session_start();

if (isset($_SESSION['userid']) && isset($_SESSION['permissions'])) {
    header('Location: ' . BASE_PATH . 'admin/index.php');
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/style.min.css" />
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

<body data-bs-theme="dark" id="dashboard" class="container-full position-relative">
    <div class="container d-flex justify-content-center align-items-center h-100">
        <div class="row">
            <div class="col">
                <div class="card px-4 py-3">
                    <h1 id="loginHeader"><?php echo SYSTEM_NAME ?></h1>
                    <p class="subtext">Das Intranet der Stadt <?php echo SERVER_CITY ?>!</p>

                    <div class="text-center mb-3">
                        <a href="<?= BASE_PATH ?>auth/discord.php" class="btn btn-primary btn-lg w-100"><i class="lab la-discord la-2x mb-2"></i><br>Mit Discord anmelden</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . "/../assets/components/footer.php"; ?>
</body>

</html>