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
        header("Location: " . BASE_PATH . "enotf/");
        exit();
    }
} else {
    header("Location: " . BASE_PATH . "enotf/");
    exit();
}

if ($daten['freigegeben'] == 1) {
    $ist_freigegeben = true;
} else {
    $ist_freigegeben = false;
}

if ($ist_freigegeben) {
    header("Location: " . BASE_PATH . "enotf/prot/index.php?enr=" . $daten['enr']);
    exit();
}

$daten['last_edit'] = !empty($daten['last_edit']) ? (new DateTime($daten['last_edit']))->format('d.m.Y H:i') : NULL;

$enr = $daten['enr'];

$prot_url = "https://" . SYSTEM_URL . "/enotf/prot/index.php?enr=" . $enr;

date_default_timezone_set('Europe/Berlin');
$currentTime = date('H:i');
$currentDate = date('d.m.Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new']) && $_POST['new'] == "1") {
    $requiredFields = [
        'priority',
        'arrival_date',
        'arrival_time',
        'fahrzeug',
        'diagnose',
        'kreislauf',
        'intubiert',
        'ziel'
    ];

    $allFilled = true;
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '' || $_POST[$field] === 'NULL') {
            $allFilled = false;
            break;
        }
    }

    if ($allFilled) {
        $arrivalDateTime = $_POST['arrival_date'] . ' ' . $_POST['arrival_time'] . ':00';
        $stmt = $pdo->prepare("
            INSERT INTO intra_edivi_prereg (
                priority,
                arrival,
                fahrzeug,
                diagnose,
                geschlecht,
                `alter`,
                text,
                kreislauf,
                gcs,
                intubiert,
                ziel
            ) VALUES (
                :priority,
                :arrival,
                :fahrzeug,
                :diagnose,
                :geschlecht,
                :alter,
                :text,
                :kreislauf,
                :gcs,
                :intubiert,
                :ziel
            )
        ");

        $stmt->execute([
            'priority' => $_POST['priority'],
            'arrival' => $arrivalDateTime,
            'fahrzeug' => $_POST['fahrzeug'],
            'diagnose' => $_POST['diagnose'],
            'geschlecht' => $daten['patsex'] ?? NULL,
            'alter' => $_POST['_AGE_'] ?? NULL,
            'text' => $_POST['text'] ?? NULL,
            'kreislauf' => $_POST['kreislauf'],
            'gcs' => $_POST['_GCS_'] ?? NULL,
            'intubiert' => $_POST['intubiert'],
            'ziel' => $_POST['ziel']
        ]);

        header("Location: " . BASE_PATH . "enotf/prot/index.php?enr=" . $daten['enr']);
        exit();
    } else {
        $formError = "Bitte füllen Sie alle Pflichtfelder korrekt aus.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>[#<?= $daten['enr'] ?>] &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
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
    <meta property="og:title" content="[#<?= $daten['enr'] ?>] &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
</head>

<body style="overflow-x:hidden">
    <form name="form" method="post" action="">
        <input type="hidden" name="new" value="1" />
        <div class="container-fluid" id="edivi__container">
            <div class="row h-100">
                <div class="col" id="edivi__content">
                    <h2 class="text-center my-3">Klinik-Voranmeldung</h2>
                    <div class="row">
                        <div class="col">
                            <div class="row edivi__box p-2">
                                <div class="col">
                                    <div class="row">
                                        <div class="col">
                                            <label for="ziel" class="edivi__description">Zielklinik</label>
                                            <select name="ziel" id="ziel" class="w-100 form-select" required>
                                                <option disabled hidden selected value="NULL">---</option>
                                                <?php
                                                require __DIR__ . '/../../assets/config/database.php';

                                                $stmt = $pdo->prepare("SELECT * FROM intra_edivi_ziele WHERE transport = 1 ORDER BY priority ASC");
                                                $stmt->execute();
                                                $ziele = $stmt->fetchAll();
                                                foreach ($ziele as $row) {
                                                    echo '<option value="' . $row['identifier'] . '">' . $row['name'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col">
                                            <div class="col">
                                                <label for="fahrzeug" class="edivi__description">Fahrzeug Transport</label>
                                                <?php
                                                require __DIR__ . '/../../assets/config/database.php';

                                                $selectedFzg = $daten['fzg_transp'] ?? $daten['fzg_na'] ?? 'NULL';

                                                $stmt = $pdo->prepare("SELECT * FROM intra_edivi_fahrzeuge ORDER BY priority ASC");
                                                $stmt->execute();
                                                $fahrzeuge = $stmt->fetchAll();
                                                ?>

                                                <select name="fahrzeug" id="fahrzeug" class="w-100 form-select" required>
                                                    <option value="NULL" <?= $selectedFzg === 'NULL' ? 'selected' : '' ?>>Fzg. Transp.</option>
                                                    <?php foreach ($fahrzeuge as $row): ?>
                                                        <?php
                                                        $isSelected = $row['identifier'] === $selectedFzg;
                                                        $isActive = $row['active'] == 1;
                                                        ?>
                                                        <option value="<?= htmlspecialchars($row['name']) ?>"
                                                            <?= $isSelected ? 'selected' : '' ?>
                                                            <?= !$isActive ? 'disabled' : '' ?>>
                                                            <?= htmlspecialchars($row['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row edivi__box p-2 mt-2">
                                <div class="col">
                                    <div class="row">
                                        <div class="col">
                                            <label for="diagnose" class="edivi__description">Anmeldediagnose</label>
                                            <input type="text" name="diagnose" id="diagnose" class="w-100 form-control" maxlength="255" placeholder="..." value="<?= $daten['diagnose'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col">
                                            <label for="text" class="edivi__description">Anmeldetext</label>
                                            <textarea name="text" id="text" rows="3" class="w-100 form-control" style="resize: none" placeholder="..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row edivi__box p-2">
                                <div class="col">
                                    <div class="row">
                                        <div class="col">
                                            <label for="geschlecht" class="edivi__description">Geschlecht</label>
                                            <select name="geschlecht" id="geschlecht" class="w-100 form-select" readonly autocomplete="off">
                                                <option disabled hidden selected>---</option>
                                                <option value="0" <?php echo ($daten['patsex'] == 0 ? 'selected' : '') ?>>männlich</option>
                                                <option value="1" <?php echo ($daten['patsex'] == 1 ? 'selected' : '') ?>>weiblich</option>
                                                <option value="2" <?php echo ($daten['patsex'] == 2 ? 'selected' : '') ?>>divers</option>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label for="_AGE_" class="edivi__description">Alter</label>
                                            <input type="text" name="_AGE_" id="_AGE_" class="w-100 form-control" value="0" readonly>
                                            <input type="hidden" name="patgebdat" id="patgebdat" value="<?= $daten['patgebdat'] ?>">
                                        </div>
                                        <div class="col">
                                            <label for="_GCS_" class="edivi__description">GCS</label>
                                            <input type="text" class="form-control" id="_GCS_" name="_GCS_" placeholder="3" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col">
                                            <label for="kreislauf" class="edivi__description">Kreislauf</label>
                                            <select name="kreislauf" id="kreislauf" class="w-100 form-select" required autocomplete="off">
                                                <option disabled hidden selected>---</option>
                                                <option value="1">stabil</option>
                                                <option value="0">instabil</option>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label for="intubiert" class="edivi__description">Intubiert</label>
                                            <select name="intubiert" id="intubiert" class="w-100 form-select" required autocomplete="off">
                                                <option disabled hidden selected>---</option>
                                                <option value="0">nein</option>
                                                <option value="1">ja</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row edivi__box p-2">
                                <div class="col">
                                    <div class="row">
                                        <div class="col">
                                            <?php
                                            $currentDate = date('Y-m-d');
                                            ?>
                                            <label for="arrival_date" class="edivi__description">Ankunftsdatum</label>
                                            <div class="input-group mb-2">
                                                <input type="date" name="arrival_date" id="arrival_date" class="form-control" value="<?= $currentDate ?>" required>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <label for="arrival_time" class="edivi__description">Ankunftszeit</label>
                                            <div class="input-group">
                                                <input type="time" name="arrival_time" id="arrival_time" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col">
                                            <label for="priority" class="edivi__description">Priorität</label>
                                            <select name="priority" id="priority" class="w-100 form-select" required autocomplete="off">
                                                <option disabled hidden selected>---</option>
                                                <option value="0">Nicht dringlich</option>
                                                <option value="1">Dringlich</option>
                                                <option value="2">Sofort</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="edivi__freigabe-buttons">
                        <div class="row">
                            <div class="col">
                                <a href="<?= BASE_PATH ?>enotf/prot/index.php?enr=<?= $daten['enr'] ?>">zurück</a>
                            </div>
                            <div class="col">
                                <button type="submit">versenden</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="display:none">
                <select class="w-100 form-select gcs-select edivi__input-check" name="d_gcs_1" id="d_gcs_1" data-mapping="4,3,2,1" autocomplete="off">
                    <option disabled hidden selected>---</option>
                    <option value="0" <?php echo ($daten['d_gcs_1'] == 0 ? 'selected' : '') ?>>spontan (4)</option>
                    <option value="1" <?php echo ($daten['d_gcs_1'] == 1 ? 'selected' : '') ?>>auf Aufforderung (3)</option>
                    <option value="2" <?php echo ($daten['d_gcs_1'] == 2 ? 'selected' : '') ?>>auf Schmerzreiz (2)</option>
                    <option value="3" <?php echo ($daten['d_gcs_1'] == 3 ? 'selected' : '') ?>>kein Öffnen (1)</option>
                </select>
                <select class="w-100 form-select gcs-select edivi__input-check" name="d_gcs_2" id="d_gcs_2" data-mapping="5,4,3,2,1" autocomplete="off">
                    <option disabled hidden selected>---</option>
                    <option value="0" <?php echo ($daten['d_gcs_2'] == 0 ? 'selected' : '') ?>>orientiert (5)</option>
                    <option value="1" <?php echo ($daten['d_gcs_2'] == 1 ? 'selected' : '') ?>>desorientiert (4)</option>
                    <option value="2" <?php echo ($daten['d_gcs_2'] == 2 ? 'selected' : '') ?>>inadäquate Äußerungen (3)</option>
                    <option value="3" <?php echo ($daten['d_gcs_2'] == 3 ? 'selected' : '') ?>>unverständliche Laute (2)</option>
                    <option value="4" <?php echo ($daten['d_gcs_2'] == 4 ? 'selected' : '') ?>>keine Reaktion (1)</option>
                </select>
                <select class="w-100 form-select gcs-select edivi__input-check" name="d_gcs_3" id="d_gcs_3" data-mapping="6,5,4,3,2,1" autocomplete="off">
                    <option disabled hidden selected>---</option>
                    <option value="0" <?php echo ($daten['d_gcs_3'] == 0 ? 'selected' : '') ?>>folgt Aufforderung (6)</option>
                    <option value="1" <?php echo ($daten['d_gcs_3'] == 1 ? 'selected' : '') ?>>gezielte Abwehrbewegungen (5)</option>
                    <option value="2" <?php echo ($daten['d_gcs_3'] == 2 ? 'selected' : '') ?>>ungezielte Abwehrbewegungen (4)</option>
                    <option value="3" <?php echo ($daten['d_gcs_3'] == 3 ? 'selected' : '') ?>>Beugesynergismen (3)</option>
                    <option value="4" <?php echo ($daten['d_gcs_3'] == 4 ? 'selected' : '') ?>>Strecksynergismen (2)</option>
                    <option value="5" <?php echo ($daten['d_gcs_3'] == 5 ? 'selected' : '') ?>>keine Reaktion (1)</option>
                </select>
            </div>
    </form>
    <script>
        function calculateAge(birthDateString) {
            const birthDate = new Date(birthDateString);
            const today = new Date();

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
</body>

</html>