<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
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

if (!Permissions::check(['admin', 'users.create'])) {
    Flash::set('error', 'no-permissions');
    header("Location: /admin/users/list.php");
}

require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

$userid = $_SESSION['userid'];

if (isset($_POST['new']) && $_POST['new'] == 1) {
    $username = $_REQUEST['username'];
    $fullname = $_REQUEST['fullname'] ?? NULL;
    $aktenid = !empty($_REQUEST['aktenid']) ? (int)$_REQUEST['aktenid'] : NULL;
    $password = password_hash($_REQUEST['password'], PASSWORD_DEFAULT);
    $role = $_REQUEST['role'];
    $jetzt = date("Y-m-d H:i:s");

    $sql = "INSERT INTO intra_users (username, fullname, aktenid, passwort, created_at, role) 
        VALUES (:username, :fullname, :aktenid, :passwort, :created_at, :role)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'username' => $username,
        'fullname' => $fullname,
        'aktenid' => $aktenid,
        'passwort' => $password,
        'created_at' => $jetzt,
        'role' => $role
    ]);

    $auditlogger = new AuditLogger($pdo);
    $auditlogger->log($userid, __('auditlog.user_create'), __('auditlog.user_create_details', [$fullname]), __('auditlog.users'), 1);
    header("Location: /admin/users/list.php");
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

<body data-bs-theme="dark" data-page="benutzer">
    <?php include "../../assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <h1><?= __('users.create.title') ?></h1>
                    <form name="form" method="post" action="">
                        <div class="intra__tile py-2 px-3">
                            <input type="hidden" name="new" value="1" />
                            <div class="row">
                                <div class="col mb-3">
                                    <label for="username" class="form-label fw-bold"><?= __('users.create.username') ?> <span class="text-main-color">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="" required>
                                </div>
                                <div class="col mb-3">
                                    <label for="fullname" class="form-label fw-bold"><?= __('users.create.fullname') ?> <span class="text-main-color">*</span></label>
                                    <input type="text" class="form-control" id="fullname" name="fullname" placeholder="" required>
                                </div>
                                <div class="col mb-3">
                                    <label for="password" class="form-label fw-bold"><?= __('users.create.password') ?> <span class="text-main-color">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="" required>
                                        <button title="<?= __('users.create.show_password') ?>" class="btn btn-outline-warning" type="button" id="show-password-btn"><i class="las la-eye"></i></button>
                                        <button title="<?= __('users.create.generate_password') ?>" class="btn btn-outline-primary" type="button" id="generate-password-btn"><i class="las la-random"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4 mb-3">
                                    <label for="aktenid" class="form-label fw-bold"><?= __('users.create.files_id') ?></label>
                                    <input type="text" class="form-control" id="aktenid" name="aktenid" placeholder="">
                                </div>
                                <div class="col mb-3">
                                    <label for="role" class="form-label fw-bold"><?= __('users.create.role') ?> <span class="text-main-color">*</span></label>
                                    <select name="role" id="role" class="form-select" required>
                                        <option selected hidden disabled><?= __('users.create.role_select') ?></option>
                                        <?php
                                        require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';
                                        $stmt = $pdo->prepare("SELECT * FROM intra_users_roles WHERE priority > :own_prio");
                                        $stmt->execute(['own_prio' => $_SESSION['role_priority']]);
                                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($result as $rr) {
                                            echo "<option value ='{$rr['id']}'>{$rr['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3 mx-auto">
                                <input class="mt-4 btn btn-success btn-sm" name="submit" type="submit" value="<?= __('users.create.submit') ?>" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const generatePasswordBtn = document.getElementById('generate-password-btn');
        generatePasswordBtn.addEventListener('click', function() {
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+~`|}{[]\\:;?><,./-=';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += characters.charAt(Math.floor(Math.random() * characters.length));
            }
            const passwordInput = document.getElementById('password');
            passwordInput.value = password;
        });
    </script>
    <script>
        const passwordInput = document.getElementById('password');
        const showPasswordButton = document.getElementById('show-password-btn');

        showPasswordButton.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        });
    </script>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/assets/components/footer.php"; ?>
</body>

</html>