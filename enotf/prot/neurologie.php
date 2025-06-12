<?php
session_start();
require_once __DIR__ . '/../../assets/config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../assets/config/database.php';

use App\Auth\Permissions;

$daten = array();

if (isset($_GET['enr'])) {
    $queryget = "SELECT * FROM intra_edivi WHERE enr = :enr";
    $stmt = $pdo->prepare($queryget);
    $stmt->execute(['enr' => $_GET['enr']]);

    $daten = $stmt->fetch(PDO::FETCH_ASSOC);

    if (count($daten) == 0) {
        header("Location: /enotf/");
        exit();
    }
} else {
    header("Location: /enotf/");
    exit();
}

if ($daten['freigegeben'] == 1) {
    $ist_freigegeben = true;
} else {
    $ist_freigegeben = false;
}

$daten['last_edit'] = !empty($daten['last_edit']) ? (new DateTime($daten['last_edit']))->format('d.m.Y H:i') : NULL;

$enr = $daten['enr'];

$prot_url = "https://" . SYSTEM_URL . "/enotf/prot/index.php?enr=" . $enr;

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

<body data-page="neurologie">
    <div class="container-fluid" id="edivi__topbar">
        <div class="row">
            <div class="col"><a title="Zurück zum Start" href="/enotf/index.php" id="home"><i class="las la-home"></i></a>
                <?php if (Permissions::check(['admin', 'edivi.edit'])) : ?>
                    <a title="QM-Aktionen öffnen" href="/admin/enotf/qm-actions.php?id=<?= $daten['id'] ?>" id="qma" target="_blank"><i class="las la-exclamation"></i></a> <a title="QM-Log öffnen" href="/admin/enotf/qm-log.php?id=<?= $daten['id'] ?>" id="qml" target="_blank"><i class="las la-paperclip"></i></a>
                <?php endif; ?>
            </div>
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
                <?php include __DIR__ . '/../../assets/components/enotf/nav.php'; ?>
                <div class="col" id="edivi__content">
                    <div class=" row">
                        <div class="col">
                            <div class="row edivi__box">
                                <h5 class="text-light px-2 py-1 edivi__group-check">Diagnostik Neurologie</h5>
                                <div class="col">
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="d_bewusstsein" class="edivi__description">Bewusstseinslage</label>
                                            <?php
                                            if ($daten['d_bewusstsein'] === NULL) {
                                            ?>
                                                <select name="d_bewusstsein" id="d_bewusstsein" class="w-100 form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">wach</option>
                                                    <option value="1">somnolent</option>
                                                    <option value="2">sopor</option>
                                                    <option value="3">komatös</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="d_bewusstsein" id="d_bewusstsein" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['d_bewusstsein'] == 0 ? 'selected' : '') ?>>wach</option>
                                                    <option value="1" <?php echo ($daten['d_bewusstsein'] == 1 ? 'selected' : '') ?>>somnolent</option>
                                                    <option value="2" <?php echo ($daten['d_bewusstsein'] == 2 ? 'selected' : '') ?>>sopor</option>
                                                    <option value="3" <?php echo ($daten['d_bewusstsein'] == 3 ? 'selected' : '') ?>>komatös</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col">
                                            <label for="d_ex_1" class="edivi__description">Extremitätenbewegung</label>
                                            <?php
                                            if ($daten['d_ex_1'] === NULL) {
                                            ?>
                                                <select name="d_ex_1" id="d_ex_1" class="w-100 form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">stark eingeschränkt</option>
                                                    <option value="2">leicht eingeschränkt</option>
                                                    <option value="1">uneingeschränkt</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="d_ex_1" id="d_ex_1" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['d_ex_1'] == 0 ? 'selected' : '') ?>>stark eingeschränkt</option>
                                                    <option value="2" <?php echo ($daten['d_ex_1'] == 2 ? 'selected' : '') ?>>leicht eingeschränkt</option>
                                                    <option value="1" <?php echo ($daten['d_ex_1'] == 1 ? 'selected' : '') ?>>uneingeschränkt</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="d_pupillenw_1" class="edivi__description">Pupillenweite li</label>
                                            <?Php if ($daten['d_pupillenw_1'] === NULL) {
                                            ?>
                                                <select name="d_pupillenw_1" id="d_pupillenw_1" class="form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">entrundet</option>
                                                    <option value="1">weit</option>
                                                    <option value="2">mittel</option>
                                                    <option value="3">eng</option>
                                                    <option value="99">n. unters.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="d_pupillenw_1" id="d_pupillenw_1" class="form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['d_pupillenw_1'] == 0 ? 'selected' : '') ?>>entrundet</option>
                                                    <option value="1" <?php echo ($daten['d_pupillenw_1'] == 1 ? 'selected' : '') ?>>weit</option>
                                                    <option value="2" <?php echo ($daten['d_pupillenw_1'] == 2 ? 'selected' : '') ?>>mittel</option>
                                                    <option value="3" <?php echo ($daten['d_pupillenw_1'] == 3 ? 'selected' : '') ?>>eng</option>
                                                    <option value="99" <?php echo ($daten['d_pupillenw_1'] == 99 ? 'selected' : '') ?>>n. unters.</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col">
                                            <label for="d_pupillenw_2" class="edivi__description">Pupillenweite re</label>
                                            <?Php if ($daten['d_pupillenw_2'] === NULL) {
                                            ?>
                                                <select name="d_pupillenw_2" id="d_pupillenw_2" class="form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">entrundet</option>
                                                    <option value="1">weit</option>
                                                    <option value="2">mittel</option>
                                                    <option value="3">eng</option>
                                                    <option value="99">n. unters.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="d_pupillenw_2" id="d_pupillenw_2" class="form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['d_pupillenw_2'] == 0 ? 'selected' : '') ?>>entrundet</option>
                                                    <option value="1" <?php echo ($daten['d_pupillenw_2'] == 1 ? 'selected' : '') ?>>weit</option>
                                                    <option value="2" <?php echo ($daten['d_pupillenw_2'] == 2 ? 'selected' : '') ?>>mittel</option>
                                                    <option value="3" <?php echo ($daten['d_pupillenw_2'] == 3 ? 'selected' : '') ?>>eng</option>
                                                    <option value="99" <?php echo ($daten['d_pupillenw_2'] == 99 ? 'selected' : '') ?>>n. unters.</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="d_lichtreakt_1" class="edivi__description">Lichtreaktion li</label>
                                            <?php
                                            if ($daten['d_lichtreakt_1'] === NULL) {
                                            ?>
                                                <select name="d_lichtreakt_1" id="d_lichtreakt_1" class="form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">prompt</option>
                                                    <option value="1">träge</option>
                                                    <option value="2">keine</option>
                                                    <option value="99">n. unters.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="d_lichtreakt_1" id="d_lichtreakt_1" class="form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['d_lichtreakt_1'] == 0 ? 'selected' : '') ?>>prompt</option>
                                                    <option value="1" <?php echo ($daten['d_lichtreakt_1'] == 1 ? 'selected' : '') ?>>träge</option>
                                                    <option value="2" <?php echo ($daten['d_lichtreakt_1'] == 2 ? 'selected' : '') ?>>keine</option>
                                                    <option value="99" <?php echo ($daten['d_lichtreakt_1'] == 99 ? 'selected' : '') ?>>n. unters.</option>
                                                </select>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="col">
                                            <label for="d_lichtreakt_2" class="edivi__description">Lichtreaktion re</label>
                                            <?php
                                            if ($daten['d_lichtreakt_2'] === NULL) {
                                            ?>
                                                <select name="d_lichtreakt_2" id="d_lichtreakt_2" class="form-select edivi__input-check" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">prompt</option>
                                                    <option value="1">träge</option>
                                                    <option value="2">keine</option>
                                                    <option value="99">n. unters.</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select name="d_lichtreakt_2" id="d_lichtreakt_2" class="form-select edivi__input-check" required autocomplete="off">
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0" <?php echo ($daten['d_lichtreakt_2'] == 0 ? 'selected' : '') ?>>prompt</option>
                                                    <option value="1" <?php echo ($daten['d_lichtreakt_2'] == 1 ? 'selected' : '') ?>>träge</option>
                                                    <option value="2" <?php echo ($daten['d_lichtreakt_2'] == 2 ? 'selected' : '') ?>>keine</option>
                                                    <option value="99" <?php echo ($daten['d_lichtreakt_2'] == 99 ? 'selected' : '') ?>>n. unters.</option>
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
                                <h5 class="text-light px-2 py-1 edivi__group-check">Glasgow-Coma-Scale (GCS)</h5>
                                <div class="col">
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="d_gcs_1" class="edivi__description">Augen öffnen</label>
                                            <div class="row mb-1">
                                                <div class="col">
                                                    <?php
                                                    if ($daten['d_gcs_1'] === NULL) {
                                                    ?>
                                                        <select class="w-100 form-select gcs-select edivi__input-check" name="d_gcs_1" id="d_gcs_1" required data-mapping="4,3,2,1">
                                                            <option disabled hidden selected>---</option>
                                                            <option value="0">spontan (4)</option>
                                                            <option value="1">auf Aufforderung (3)</option>
                                                            <option value="2">auf Schmerzreiz (2)</option>
                                                            <option value="3">kein Öffnen (1)</option>
                                                        </select>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <select class="w-100 form-select gcs-select edivi__input-check" name="d_gcs_1" id="d_gcs_1" required data-mapping="4,3,2,1" autocomplete="off">
                                                            <option disabled hidden selected>---</option>
                                                            <option value="0" <?php echo ($daten['d_gcs_1'] == 0 ? 'selected' : '') ?>>spontan (4)</option>
                                                            <option value="1" <?php echo ($daten['d_gcs_1'] == 1 ? 'selected' : '') ?>>auf Aufforderung (3)</option>
                                                            <option value="2" <?php echo ($daten['d_gcs_1'] == 2 ? 'selected' : '') ?>>auf Schmerzreiz (2)</option>
                                                            <option value="3" <?php echo ($daten['d_gcs_1'] == 3 ? 'selected' : '') ?>>kein Öffnen (1)</option>
                                                        </select>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <div class="row mb-1">
                                                <div class="col">
                                                    <label for="d_gcs_2" class="edivi__description">Beste verbale Reaktion</label>
                                                    <?php
                                                    if ($daten['d_gcs_2'] === NULL) {
                                                    ?>
                                                        <select class="w-100 form-select gcs-select edivi__input-check" name="d_gcs_2" id="d_gcs_2" required data-mapping="5,4,3,2,1">>
                                                            <option disabled hidden selected>---</option>
                                                            <option value="0">orientiert (5)</option>
                                                            <option value="1">desorientiert (4)</option>
                                                            <option value="2">inadäquate Äußerungen (3)</option>
                                                            <option value="3">unverständliche Laute (2)</option>
                                                            <option value="4">keine Reaktion (1)</option>
                                                        </select>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <select class="w-100 form-select gcs-select edivi__input-check" name="d_gcs_2" id="d_gcs_2" required data-mapping="5,4,3,2,1"> autocomplete="off">
                                                            <option disabled hidden selected>---</option>
                                                            <option value="0" <?php echo ($daten['d_gcs_2'] == 0 ? 'selected' : '') ?>>orientiert (5)</option>
                                                            <option value="1" <?php echo ($daten['d_gcs_2'] == 1 ? 'selected' : '') ?>>desorientiert (4)</option>
                                                            <option value="2" <?php echo ($daten['d_gcs_2'] == 2 ? 'selected' : '') ?>>inadäquate Äußerungen (3)</option>
                                                            <option value="3" <?php echo ($daten['d_gcs_2'] == 3 ? 'selected' : '') ?>>unverständliche Laute (2)</option>
                                                            <option value="4" <?php echo ($daten['d_gcs_2'] == 4 ? 'selected' : '') ?>>keine Reaktion (1)</option>
                                                        </select>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col">
                                            <div class="row mb-1">
                                                <div class="col">
                                                    <label for="d_gcs_3" class="edivi__description">Beste motorische Reaktion</label>
                                                    <?php
                                                    if ($daten['d_gcs_3'] === NULL) {
                                                    ?>
                                                        <select class="w-100 form-select gcs-select edivi__input-check" name="d_gcs_3" id="d_gcs_3" required data-mapping="6,5,4,3,2,1">
                                                            <option disabled hidden selected>---</option>
                                                            <option value="0">folgt Aufforderung (6)</option>
                                                            <option value="1">gezielte Abwehrbewegungen (5)</option>
                                                            <option value="2">ungezielte Abwehrbewegungen (4)</option>
                                                            <option value="3">Beugesynergismen (3)</option>
                                                            <option value="4">Strecksynergismen (2)</option>
                                                            <option value="5">keine Reaktion (1)</option>
                                                        </select>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <select class="w-100 form-select gcs-select edivi__input-check" name="d_gcs_3" id="d_gcs_3" required data-mapping="6,5,4,3,2,1" autocomplete="off">
                                                            <option disabled hidden selected>---</option>
                                                            <option value="0" <?php echo ($daten['d_gcs_3'] == 0 ? 'selected' : '') ?>>folgt Aufforderung (6)</option>
                                                            <option value="1" <?php echo ($daten['d_gcs_3'] == 1 ? 'selected' : '') ?>>gezielte Abwehrbewegungen (5)</option>
                                                            <option value="2" <?php echo ($daten['d_gcs_3'] == 2 ? 'selected' : '') ?>>ungezielte Abwehrbewegungen (4)</option>
                                                            <option value="3" <?php echo ($daten['d_gcs_3'] == 3 ? 'selected' : '') ?>>Beugesynergismen (3)</option>
                                                            <option value="4" <?php echo ($daten['d_gcs_3'] == 4 ? 'selected' : '') ?>>Strecksynergismen (2)</option>
                                                            <option value="5" <?php echo ($daten['d_gcs_3'] == 5 ? 'selected' : '') ?>>keine Reaktion (1)</option>
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
                                    <div class="row my-2">
                                        <div class="col">
                                            <label for="_GCS_" class="edivi__description">GCS</label>
                                            <input type="text" class="form-control" id="_GCS_" name="_GCS_" placeholder="3" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </form>
    <?php
    include __DIR__ . '/../../assets/functions/enotf/notify.php';
    include __DIR__ . '/../../assets/functions/enotf/field_checks.php';
    include __DIR__ . '/../../assets/functions/enotf/clock.php';
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selects = document.querySelectorAll('.gcs-select');
            const gcsInput = document.getElementById('_GCS_');

            function updateGCS() {
                let total = 0;
                selects.forEach(sel => {
                    const mapping = sel.dataset.mapping.split(',').map(Number);
                    const selectedIndex = parseInt(sel.value);
                    if (!isNaN(selectedIndex) && selectedIndex < mapping.length) {
                        total += mapping[selectedIndex];
                    }
                });
                gcsInput.value = total;
            }

            selects.forEach(sel => {
                sel.addEventListener('change', updateGCS);
            });


            updateGCS();
        });
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
</body>

</html>