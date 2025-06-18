<?php
session_start();
require_once __DIR__ . '/../../assets/config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../assets/config/database.php';

$prot_url = "https://" . SYSTEM_URL . "/enotf/index.php";

date_default_timezone_set('Europe/Berlin');
$currentTime = date('H:i');
$currentDate = date('d.m.Y');

$ziel = $_GET['klinik'] ?? NULL;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Arrivalboard &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
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
    <meta property="og:title" content="Arrivalboard &rsaquo;eNOTF &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
    <meta http-equiv="refresh" content="60">
</head>

<body style="overflow-x:hidden" id="edivi__arrivalboard">
    <div class="container-fluid">
        <div class="row h-100">
            <div class="col" id="edivi__content">
                <table class="w-100">
                    <thead>
                        <tr>
                            <th class="text-center">Ankunft</th>
                            <th colspan="2">Verdachtsdiagnose</th>
                            <th>Anmeldetext</th>
                            <th class="text-center">Kreislauf</th>
                            <th class="text-center">GCS</th>
                            <th class="text-center">Intubiert</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pdo->prepare("UPDATE intra_edivi_prereg SET active = 0 WHERE active = 1 AND arrival IS NOT NULL AND arrival < NOW() - INTERVAL 10 MINUTE")->execute();
                        if ($ziel) {
                            $stmt = $pdo->prepare("SELECT * FROM intra_edivi_prereg WHERE ziel = :ziel AND active = 1 ORDER BY arrival ASC");
                            $stmt->bindParam(':ziel', $ziel);
                        } else {
                            $stmt = $pdo->prepare("SELECT * FROM intra_edivi_prereg WHERE active = 1 ORDER BY arrival ASC");
                        }
                        $stmt->execute();
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($result as $row) {
                            if (!empty($row['arrival'])) {
                                $row['arrival'] = (new DateTime($row['arrival']))->format('H:i');
                            } else {
                                $row['arrival'] = '—';
                            }

                            if ($row['priority'] == 1) {
                                $rowClass = 'edivi__arrivalboard-prio-1';
                            } elseif ($row['priority'] == 2) {
                                $rowClass = 'edivi__arrivalboard-prio-2';
                            } else {
                                $rowClass = 'edivi__arrivalboard-prio-0';
                            }

                            if ($row['geschlecht'] == 1) {
                                $row['geschlecht'] = '<i class="las la-venus"></i>';
                            } elseif ($row['geschlecht'] == 0) {
                                $row['geschlecht'] = '<i class="las la-mars"></i>';
                            } else {
                                $row['geschlecht'] =   '<i class="las la-genderless"></i>';
                            }

                            if (empty($row['alter'])) {
                                $row['alter'] = '—';
                            }

                            if ($row['kreislauf'] == 1) {
                                $row['kreislauf'] = 'stabil';
                            } else {
                                $row['kreislauf'] = '<span style="color:red">instabil</span>';
                            }

                            if ($row['intubiert'] == 0) {
                                $row['intubiert'] = 'nein';
                            } else {
                                $row['intubiert'] = '<span style="color:red">ja</span>';
                            }

                        ?>
                            <tr class="<?= $rowClass ?>">
                                <td class="edivi__arrivalboard-time">
                                    <span><?= $row['arrival'] ?></span><br>
                                    <?= $row['fahrzeug'] ?>
                                </td>
                                <td><?= $row['diagnose'] ?></td>
                                <td class="edivi__arrivalboard-gender"><?= $row['geschlecht'] ?><br>
                                    <?= $row['alter'] ?></td>
                                <td class="edivi__arrivalboard-text"><?= $row['text'] ?></td>
                                <td class="text-center"><?= $row['kreislauf'] ?></td>
                                <td class="text-center"><?= $row['gcs'] ?></td>
                                <td class="text-center"><?= $row['intubiert'] ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
</body>

</html>