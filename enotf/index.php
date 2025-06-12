<?php
session_start();
require_once __DIR__ . '/../assets/config/config.php';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/style.min.css" />
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
                    <form method="post">
                        <strong>Einsatznummer:</strong><br>
                        <input class="form-control" type="text" size="40" maxlength="250" id="enrInput" oninput="validateInput(this)"><br><br>
                    </form>

                    <button class="btn btn-primary p-3" onclick="openOrCreate()">
                        <i class="las la-eye la-2x mb-2"></i><br> Protokoll öffnen
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . "/../assets/components/footer.php"; ?>
    <script>
        function openOrCreate() {
            const enrInput = document.getElementById("enrInput");
            const inputValue = enrInput.value;

            if (inputValue.trim() === "") {
                alert("Bitte gib eine gültige Einsatznummer an.");
                return;
            }

            $.ajax({
                type: "POST",
                url: "/assets/functions/enrbridge.php",
                data: {
                    action: "openOrCreate",
                    enr: inputValue
                },
                success: function(redirectUrl) {
                    window.location.href = redirectUrl;
                },
            });
        }
    </script>
    <script>
        function isNumber(event) {
            const key = event.key;
            return /^[0-9_]+$/.test(key);
        }

        function validateInput(inputField) {
            inputField.value = inputField.value.replace(/[^0-9_]/g, '');
        }
    </script>

</body>

</html>