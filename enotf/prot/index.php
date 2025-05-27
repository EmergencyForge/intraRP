<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

$daten = array();

if (isset($_GET['enr'])) {
    $queryget = "SELECT * FROM intra_edivi WHERE enr = :enr";
    $stmt = $pdo->prepare($queryget);
    $stmt->execute(['enr' => $_GET['enr']]);

    $daten = $stmt->fetch(PDO::FETCH_ASSOC);

    if (count($daten) == 0) {
        header("Location: /enotf/bridge/");
        exit();
    }
} else {
    header("Location: /enotf/bridge/");
    exit();
}

if ($daten['freigegeben'] == 1) {
    $ist_freigegeben = true;
} else {
    $ist_freigegeben = false;
}

$daten['last_edit'] = !empty($daten['last_edit']) ? (new DateTime($daten['last_edit']))->format('d.m.Y H:i') : NULL;

$enr = $daten['enr'];

$prot_url = "https://" . SYSTEM_URL . "/enotf/" . $enr;

date_default_timezone_set('Europe/Berlin');
$currentTime = date('H:i');
$currentDate = date('d.m.Y');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>[#<?= $daten['enr'] ?>] &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/divi.min.css" />
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
    <meta name="theme-color" content="#ffaf2f" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="<?= $prot_url ?>" />
    <meta property="og:title" content="[#<?= $daten['enr'] ?>] &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
</head>

<body>
    <div class="container-fluid" id="edivi__topbar">
        <div class="row">
            <div class="col"><a href="/enotf/index.php" id="home"><i class="las la-home"></i></a></div>
            <div class="col text-end d-flex justify-content-end align-items-center">
                <div class="d-flex flex-column align-items-end me-3">
                    <span id="current-time"><?= $currentTime ?></span>
                    <span id="current-date"><?= $currentDate ?></span>
                </div>
                <a href="https://github.com/intraRP/intraRP" target="_blank">
                    <img src="https://dev.intrarp.de/assets/img/defaultLogo.webp" alt="intraRP Logo" height="64px" width="auto">
                </a>
            </div>
        </div>
    </div>
    <?php if ($ist_freigegeben) : ?>
        <div class="container-full edivi__notice edivi__notice-freigeber">
            <div class="row">
                <div class="col-1 text-end"><i class="las la-info"></i></div>
                <div class="col">
                    Das Protokoll wurde durch <strong><?= $daten['freigeber_name'] ?></strong> am <strong><?= $daten['last_edit'] ?></strong> Uhr freigegeben. Es kann nicht mehr bearbeitet werden.
                </div>
            </div>
        </div>
    <?php endif; ?>
    <form name="form" method="post" action="">
        <input type="hidden" name="new" value="1" />
        <div class="container-fluid" id="edivi__container">
            <div class="row h-100">
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/assets/components/enotf/nav.php'; ?>
            </div>
        </div>
    </form>
    <script>
        // eNOTF Buttons
        const o2gabe = document.getElementById("o2gabe");

        function checkCheckbox() {
            if (o2gabe.value > 0) {
                o2gabe.checked = true;
            } else {
                o2gabe.checked = false;
            }
        }

        o2gabe.addEventListener("click", checkCheckbox);
    </script>
    <script>
        // eNOTF Verletzungen
        function setSelectElementStyles() {
            const selectElements = document.querySelectorAll(".edivi__verletzungen");

            selectElements.forEach((selectElement) => {
                const parentCol = selectElement.closest(".edivi__verletzungen-col");

                if (selectElement.value === "0") {
                    parentCol.classList.remove("edivi__verletzungen-yellow", "edivi__verletzungen-green");
                    parentCol.classList.add("edivi__verletzungen-red");
                } else if (selectElement.value === "1") {
                    parentCol.classList.remove("edivi__verletzungen-red", "edivi__verletzungen-green");
                    parentCol.classList.add("edivi__verletzungen-yellow");
                } else if (selectElement.value === "2") {
                    parentCol.classList.remove("edivi__verletzungen-red", "edivi__verletzungen-yellow");
                    parentCol.classList.add("edivi__verletzungen-green");
                } else {
                    parentCol.classList.remove("edivi__verletzungen-red", "edivi__verletzungen-yellow", "edivi__verletzungen-green");
                }
            });
        }

        // Call the function when the page loads
        window.addEventListener("load", setSelectElementStyles);

        // Add event listeners for change events (as you already did)
        const selectElements = document.querySelectorAll(".edivi__verletzungen");

        selectElements.forEach((selectElement) => {
            selectElement.addEventListener("change", setSelectElementStyles);
        });
    </script>
    <script>
        // Get all input elements with the class "edivi__input-check"
        const inputElements = document.querySelectorAll('.edivi__input-check');

        // Function to add or remove the class based on input value
        function handleInputChange(event) {
            const inputElement = event.target;

            // Check if the input is a select element
            if (inputElement.tagName === 'SELECT') {
                const selectedOption = inputElement.querySelector('option:checked');
                if (selectedOption && !selectedOption.disabled) {
                    inputElement.classList.add('edivi__input-checked');
                } else {
                    inputElement.classList.remove('edivi__input-checked');
                }
            } else {
                // Check if the input has a value (excluding select elements)
                if (inputElement.value.trim() === '') {
                    inputElement.classList.remove('edivi__input-checked');
                } else {
                    inputElement.classList.add('edivi__input-checked');
                }
            }
        }

        // Check the initial state of the input elements and add "edivi__input-checked" if not empty (excluding select elements with disabled options)
        inputElements.forEach(inputElement => {
            if (inputElement.tagName === 'SELECT') {
                const selectedOption = inputElement.querySelector('option:checked');
                if (selectedOption && !selectedOption.disabled) {
                    inputElement.classList.add('edivi__input-checked');
                }
            } else if (inputElement.value.trim() !== '') {
                inputElement.classList.add('edivi__input-checked');
            }
        });

        // Add an event listener for the "input" event on each input element
        inputElements.forEach(inputElement => {
            inputElement.addEventListener('input', handleInputChange);
        });
    </script>
    <?php if ($ist_freigegeben) : ?>
        <script>
            // Get all form elements
            var formElements = document.querySelectorAll('input, textarea');
            var selectElements2 = document.querySelectorAll('select');
            var inputElements2 = document.querySelectorAll('.btn-check');
            var inputElements3 = document.querySelectorAll('.form-check-input');

            // Set all form elements to readonly
            formElements.forEach(function(element) {
                element.setAttribute('readonly', 'readonly');
            });

            selectElements2.forEach(function(element) {
                element.setAttribute('disabled', 'disabled');
            });

            inputElements2.forEach(function(element) {
                element.setAttribute('disabled', 'disabled');
            });

            inputElements3.forEach(function(element) {
                element.setAttribute('disabled', 'disabled');
            });
        </script>
    <?php endif; ?>
    <script>
        // Add an event listener to the modal close button
        var modalCloseButton = document.querySelector('#myModal4 .btn-close');
        var freigeberInput = document.getElementById('freigeber');

        modalCloseButton.addEventListener('click', function() {
            // Clear the input field when the modal is closed
            freigeberInput.value = '';
        });
    </script>
    <script>
        function updateContainerClass(index) {
            const containers = document.querySelectorAll('.edivi__zugang-container');
            const selects = document.querySelectorAll('.edivi__zugang-list');

            // Remove any existing classes starting with "edivi__zugang-option"
            containers[index].classList.remove(
                ...Array.from(containers[index].classList).filter(className => className.startsWith('edivi__zugang-opt'))
            );

            // Get the selected value
            const selectedValue = selects[index].value;

            // Add the corresponding class to the container
            containers[index].classList.add(`edivi__zugang-opt${selectedValue}`);
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Run the script once on page load
            const selects = document.querySelectorAll('.edivi__zugang-list');

            selects.forEach((select, index) => {
                select.addEventListener('change', () => {
                    // Call the updateContainerClass function on select change
                    updateContainerClass(index);
                });

                // Call the updateContainerClass function on page load
                updateContainerClass(index);
            });
        });
    </script>
</body>

</html>