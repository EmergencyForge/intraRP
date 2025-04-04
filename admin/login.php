<?php
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

ini_set('session.gc_maxlifetime', 604800);
ini_set('session.cookie_path', '/');  // Set the cookie path to the root directory
ini_set('session.cookie_domain', SYSTEM_URL);  // Set the cookie domain to your domain
ini_set('session.cookie_lifetime', 604800);  // Set the cookie lifetime (in seconds)
ini_set('session.cookie_secure', true);  // Set to true if using HTTPS, false otherwise

session_start();

if (isset($_SESSION['userid']) && isset($_SESSION['permissions'])) {
    header('Location: /admin/index.php');
}

if (isset($_GET['login'])) {
    $username = $_POST['username'];
    $passwort = $_POST['passwort'];

    $statement = $pdo->prepare("SELECT * FROM intra_users WHERE username = :username");
    $result = $statement->execute(array('username' => $username));
    $user = $statement->fetch();

    if ($user !== false && password_verify($passwort, $user['passwort'])) {
        $_SESSION['userid'] = $user['id'];
        $_SESSION['cirs_user'] = $user['fullname'];
        $_SESSION['cirs_username'] = $user['username'];
        $_SESSION['aktenid'] = $user['aktenid'];
        $permissions = json_decode($user['permissions'], true) ?? [];
        $_SESSION['permissions'] = $permissions;

        if (isset($_SESSION['redirect_url'])) {
            $redirect_url = $_SESSION['redirect_url'];
            unset($_SESSION['redirect_url']); // Remove the stored URL
            header("Location: $redirect_url");
            exit();
        } else {
            header('Location: /admin/index.php');
        }
    } else {
        $errorMessage = "Benutzername oder Passwort ungültig.<br>";
    }
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
    <meta property="og:url" content="https://<?php echo SYSTEM_URL ?>/dash.php" />
    <meta property="og:title" content="<?php echo SYSTEM_NAME ?> - Intranet <?php echo SERVER_CITY ?>" />
    <meta property="og:image" content="<?php echo META_IMAGE_URL ?>" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
</head>

<body data-bs-theme="dark" id="dashboard" class="d-flex justify-content-center align-items-center">
    <div class="row">
        <div class="col">
            <div class="card px-4 py-3">
                <h1 id="loginHeader"><?php echo SYSTEM_NAME ?></h1>
                <p class="subtext">Das Intranet der Stadt <?php echo SERVER_CITY ?>!</p>
                <?php
                if (isset($errorMessage)) {
                    echo '<div class="alert alert-danger mb-5" role="alert">';
                    echo $errorMessage;
                    echo '</div>';
                }
                ?>

                <form action="?login=1" method="post">
                    <strong>Benutzername:</strong><br>
                    <input class="form-control" type="text" size="40" maxlength="250" name="username"><br><br>

                    <strong>Passwort:</strong><br>
                    <input class="form-control" type="password" size="40" maxlength="250" name="passwort"><br>

                    <input class="btn btn-primary w-100" type="submit" value="Anmelden">
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