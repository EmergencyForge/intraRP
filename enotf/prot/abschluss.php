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

<body data-page="abschluss">
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
                <div class="col" id="edivi__content">
                    <div class=" row">
                        <div class="col">
                            <div class="row edivi__box">
                                <h5 class="text-light px-2 py-1">Transportdaten</h5>
                                <div class="col">
                                    <div class="row my-2">
                                        <div class="col-3">
                                            <label for="fzg_transp" class="edivi__description">Fahrzeug Transport</label>
                                            <?php if ($daten['fzg_transp'] === NULL) : ?>
                                                <select name="fzg_transp" id="fzg_transp" class="w-100 form-select">
                                                    <option selected value="NULL">Fzg. Transp.</option>
                                                    <?php
                                                    require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

                                                    $stmt = $pdo->prepare("SELECT * FROM intra_edivi_fahrzeuge WHERE doctor = 0 AND active = 1 ORDER BY priority ASC");
                                                    $stmt->execute();
                                                    $fahrzeuge = $stmt->fetchAll();
                                                    foreach ($fahrzeuge as $row) {
                                                        echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            <?php else : ?>
                                                <select name="fzg_transp" id="fzg_transp" class="w-100 form-select">
                                                    <option selected value="NULL">Fzg. Transp.</option>
                                                    <?php
                                                    require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

                                                    $stmt = $pdo->prepare("SELECT * FROM intra_edivi_fahrzeuge WHERE doctor = 0 ORDER BY priority ASC");
                                                    $stmt->execute();
                                                    $fahrzeuge = $stmt->fetchAll();

                                                    foreach ($fahrzeuge as $row) {
                                                        if ($row['identifier'] == $daten['fzg_transp'] && $row['active'] == 1) {
                                                            echo '<option value="' . $row['identifier'] . '" selected>' . $row['name'] . '</option>';
                                                        } elseif ($row['identifier'] == $daten['fzg_transp'] && $row['active'] == 0) {
                                                            echo '<option value="' . $row['identifier'] . '" selected disabled>' . $row['name'] . '</option>';
                                                        } else {
                                                            echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col">
                                            <label for="fzg_transp_perso" class="edivi__description">Besatzung</label>
                                            <input type="text" name="fzg_transp_perso" id="fzg_transp_perso" class="w-100 form-control" placeholder="Personal RTW/KTW" value="<?= $daten['fzg_transp_perso'] ?>">
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col-3">
                                            <label for="fzg_na" class="edivi__description">Fahrzeug Notarzt</label>
                                            <?php if ($daten['fzg_na'] === NULL) : ?>
                                                <select name="fzg_na" id="fzg_na" class="w-100 form-select">
                                                    <option selected value="NULL">Fzg. NA</option>
                                                    <?php
                                                    require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

                                                    $stmt = $pdo->prepare("SELECT * FROM intra_edivi_fahrzeuge WHERE doctor = 1 AND active = 1 ORDER BY priority ASC");
                                                    $stmt->execute();
                                                    $fahrzeuge = $stmt->fetchAll();
                                                    foreach ($fahrzeuge as $row) {
                                                        echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            <?php else : ?>
                                                <select name="fzg_na" id="fzg_na" class="w-100 form-select">
                                                    <option selected value="NULL">Fzg. NA</option>
                                                    <?php
                                                    require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

                                                    $stmt = $pdo->prepare("SELECT * FROM intra_edivi_fahrzeuge WHERE doctor = 1 ORDER BY priority ASC");
                                                    $stmt->execute();
                                                    $fahrzeuge = $stmt->fetchAll();

                                                    foreach ($fahrzeuge as $row) {
                                                        if ($row['identifier'] == $daten['fzg_na'] && $row['active'] == 1) {
                                                            echo '<option value="' . $row['identifier'] . '" selected>' . $row['name'] . '</option>';
                                                        } elseif ($row['identifier'] == $daten['fzg_na'] && $row['active'] == 0) {
                                                            echo '<option value="' . $row['identifier'] . '" selected disabled>' . $row['name'] . '</option>';
                                                        } else {
                                                            echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col">
                                            <label for="fzg_na_perso" class="edivi__description"></label>
                                            <input type="text" name="fzg_na_perso" id="fzg_na_perso" class="w-100 form-control" placeholder="Personal NEF" value="<?= $daten['fzg_na_perso'] ?>">
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="fzg_sonst" class="edivi__description">Sonstige Fahrzeuge</label>
                                            <input type="text" name="fzg_sonst" id="fzg_sonst" class="w-100 form-control" placeholder="Weitere Rettungsmittel" value="<?= $daten['fzg_sonst'] ?>">
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="transportziel" class="edivi__description">Transportart oder Transportziel</label>
                                            <?php
                                            if ($daten['transportziel'] === NULL) {
                                            ?>
                                                <select name="transportziel" id="transportziel" class="w-100 form-select edivi__input-check" required>
                                                    <option disabled hidden selected value="NULL">---</option>
                                                    <?php
                                                    require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

                                                    $stmt = $pdo->prepare("SELECT * FROM intra_edivi_ziele ORDER BY priority ASC");
                                                    $stmt->execute();
                                                    $ziele = $stmt->fetchAll();
                                                    foreach ($ziele as $row) {
                                                        echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="transportziel" id="transportziel" class="w-100 mb-2 form-select edivi__input-check" autocomplete="off">
                                                    <option disabled hidden selected value="NULL">---</option>
                                                    <?php
                                                    require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

                                                    $stmt = $pdo->prepare("SELECT * FROM intra_edivi_ziele ORDER BY priority ASC");
                                                    $stmt->execute();
                                                    $fahrzeuge = $stmt->fetchAll();

                                                    foreach ($fahrzeuge as $row) {
                                                        if ($row['identifier'] == $daten['transportziel'] && $row['active'] == 1) {
                                                            echo '<option value="' . $row['identifier'] . '" selected>' . $row['name'] . '</option>';
                                                        } elseif ($row['identifier'] == $daten['transportziel'] && $row['active'] == 0) {
                                                            echo '<option value="' . $row['identifier'] . '" selected disabled>' . $row['name'] . '</option>';
                                                        } else {
                                                            echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row edivi__box">
                                <h5 class="text-light px-2 py-1 edivi__group-check">Protokolldaten</h5>
                                <div class="col">
                                    <?php
                                    $stmtfn = $pdo->query("SELECT fullname FROM intra_mitarbeiter ORDER BY fullname ASC");
                                    $fullnames = $stmtfn->fetchAll(PDO::FETCH_COLUMN);
                                    ?>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="pfname" class="edivi__description">Protokollant</label>
                                            <input type="text" name="pfname" id="pfname" class="w-100 form-control edivi__input-check" placeholder="Max Mustermann" value="" <?= htmlspecialchars($daten['pfname'] ?? '') ?>" list="nameSuggestions" required>
                                            <datalist id="nameSuggestions">
                                                <?php foreach ($fullnames as $name): ?>
                                                    <option value="<?= htmlspecialchars($name) ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>
                                    <?php if (!$ist_freigegeben) : ?>
                                        <div class="row my-2">
                                            <div class="col">
                                                <button class="btn btn-success btn-sm w-100" type="button" data-bs-toggle="modal" data-bs-target="#myModal4">Protokoll freigeben & absenden</button>
                                            </div>
                                        </div>
                                        <!-- MODAL -->
                                        <div class="modal fade" id="myModal4" tabindex="-1" aria-labelledby="myModalLabel4" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel4">Protokoll absenden</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mt-2 mb-1">
                                                            <div class="col">
                                                                <strong style="color:red">Achtung!</strong> Sobald das Protokoll freigegeben wurde kann es nicht mehr bearbeitet werden!
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2 mb-1">
                                                            <div class="col">
                                                                <div class="row my-1">
                                                                    <div class="col-3 fw-bold">Freigegeben durch</div>
                                                                    <div class="col">
                                                                        <input type="text" id="freigeber" name="freigeber" class="form-control w-100" placeholder="Max Mustermann">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input class="btn btn-success" name="submit" type="submit" value="Protokoll absenden" />
                                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Abbrechen</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- MODAL ENDE -->
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </form>
    <script>
        const inputElements = document.querySelectorAll('.edivi__input-check');

        function toggleInputChecked(inputElement) {
            if (inputElement.tagName === 'SELECT') {
                const selectedOption = inputElement.querySelector('option:checked');
                if (selectedOption && !selectedOption.disabled) {
                    inputElement.classList.add('edivi__input-checked');
                } else {
                    inputElement.classList.remove('edivi__input-checked');
                }
            } else {
                if (inputElement.value.trim() === '') {
                    inputElement.classList.remove('edivi__input-checked');
                } else {
                    inputElement.classList.add('edivi__input-checked');
                }
            }

            const groupContainer = inputElement.closest('.edivi__box');
            const groupHeading = groupContainer ? groupContainer.querySelector('h5.edivi__group-check') : null;

            if (groupHeading) {
                inputElement.style.borderLeft = '0';
            } else {
                inputElement.style.borderLeft = '';
            }
        }

        function checkGroupStatus() {
            const groupHeadings = document.querySelectorAll('h5.edivi__group-check');

            groupHeadings.forEach(groupHeading => {
                const groupContainer = groupHeading.closest('.edivi__box');
                if (!groupContainer) return;

                const groupInputs = groupContainer.querySelectorAll('.edivi__input-check');

                let allFilled = true;
                groupInputs.forEach(input => {
                    if (input.tagName === 'SELECT') {
                        const selectedOption = input.querySelector('option:checked');
                        if (!selectedOption || selectedOption.disabled) {
                            allFilled = false;
                        }
                    } else if (input.value.trim() === '') {
                        allFilled = false;
                    }

                    input.style.borderLeft = '0';
                });

                if (allFilled) {
                    groupHeading.classList.add('edivi__group-checked');
                } else {
                    groupHeading.classList.remove('edivi__group-checked');
                }
            });
        }

        inputElements.forEach(inputElement => {
            toggleInputChecked(inputElement);
            inputElement.addEventListener('input', () => {
                toggleInputChecked(inputElement);
                checkGroupStatus();
            });
        });

        document.addEventListener('DOMContentLoaded', checkGroupStatus);
    </script>
    <?php if ($ist_freigegeben) : ?>
        <script>
            var formElements = document.querySelectorAll('input, textarea');
            var selectElements2 = document.querySelectorAll('select');
            var inputElements2 = document.querySelectorAll('.btn-check');
            var inputElements3 = document.querySelectorAll('.form-check-input');

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
        var modalCloseButton = document.querySelector('#myModal4 .btn-close');
        var freigeberInput = document.getElementById('freigeber');

        modalCloseButton.addEventListener('click', function() {
            freigeberInput.value = '';
        });
    </script>
    <script>
        function updateContainerClass(index) {
            const containers = document.querySelectorAll('.edivi__zugang-container');
            const selects = document.querySelectorAll('.edivi__zugang-list');

            containers[index].classList.remove(
                ...Array.from(containers[index].classList).filter(className => className.startsWith('edivi__zugang-opt'))
            );

            const selectedValue = selects[index].value;

            containers[index].classList.add(`edivi__zugang-opt${selectedValue}`);
        }

        document.addEventListener("DOMContentLoaded", function() {
            const selects = document.querySelectorAll('.edivi__zugang-list');

            selects.forEach((select, index) => {
                select.addEventListener('change', () => {
                    updateContainerClass(index);
                });

                updateContainerClass(index);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $("form[name='form'] input:not([readonly]):not([disabled]):not(#freigeber), form[name='form'] select:not([readonly]):not([disabled]), form[name='form'] textarea:not([readonly]):not([disabled]):not(#freigeber)")
                .on('blur change', function() {
                    var fieldName = $(this).attr('name');
                    var enr = <?= json_encode($enr) ?>;
                    var fieldValue;

                    if ($(this).is(':checkbox')) {
                        fieldValue = $(this).is(':checked') ? 1 : 0;
                    } else {
                        fieldValue = $(this).val();
                    }

                    $.ajax({
                        url: '/assets/functions/save_fields.php',
                        type: 'POST',
                        data: {
                            enr: enr,
                            field: fieldName,
                            value: fieldValue
                        },
                        success: function(response) {
                            console.log("Feld bearbeitet: " + fieldName + " zu: " + fieldValue);
                        },
                        error: function() {
                            console.error("!FEHLER! bei Feld: " + fieldName);
                        }
                    });
                });

            $('#myModal4 input[name="submit"]').on('click', function(e) {
                e.preventDefault();
                var freigeberValue = $('#freigeber').val();
                var enr = <?= json_encode($enr) ?>;

                $.ajax({
                    url: '/assets/functions/save_fields.php',
                    type: 'POST',
                    data: {
                        enr: enr,
                        field: 'freigeber',
                        value: freigeberValue
                    },
                    success: function(response) {
                        console.log("Freigeber gespeichert: " + freigeberValue);
                        location.reload();
                    },
                    error: function() {
                        console.error("!FEHLER! beim Speichern des Freigeber-Feldes");
                    }
                });
            });

        });
    </script>
    <script>
        function calculateAge(birthDateString) {
            const birthDate = new Date(birthDateString);
            const today = new Date();

            // Check if the date is valid
            if (isNaN(birthDate)) return 0;

            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();

            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            return age >= 0 ? age : 0;
        }

        function updateAge() {
            const birthDateValue = document.getElementById('patgebdat').value;
            const age = calculateAge(birthDateValue);
            document.getElementById('_AGE_').value = age;
        }

        document.addEventListener('DOMContentLoaded', updateAge);
        document.getElementById('patgebdat').addEventListener('input', updateAge);
    </script>
    <script>
        function updateTimeAndDate() {
            const now = new Date();
            const berlinTime = new Date(now.toLocaleString("en-US", {
                timeZone: "Europe/Berlin"
            }));
            const time = berlinTime.toLocaleTimeString('de-DE', {
                hour: '2-digit',
                minute: '2-digit'
            }); // No seconds
            const date = berlinTime.toLocaleDateString('de-DE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });

            document.getElementById('current-time').textContent = time;
            document.getElementById('current-date').textContent = date;
        }

        // Update every minute
        setInterval(updateTimeAndDate, 60000);
        updateTimeAndDate();
    </script>
</body>

</html>