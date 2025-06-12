<?php
require_once __DIR__ . '/../../assets/config/config.php';
require __DIR__ . '/../../assets/config/database.php';

$openedID = $_GET['dok'];

$stmt = $pdo->prepare("SELECT * FROM intra_mitarbeiter_dokumente WHERE docid = :docid");
$stmt->execute(['docid' => $openedID]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row['anrede'] == 1) {
    $anrede = "Frau";
    $anredemw = $anrede;
} elseif ($row['anrede'] == 0) {
    $anrede = "Herr";
    $anredemw = $anrede;
} else {
    $anrede = "Divers";
    $anredemw = NULL;
}

if ($row['type'] != 11) {
    header("Location: " . BASE_PATH . "assets/functions/docredir.php?docid=" . $_GET['dok']);
}

$erhalter_gebdat = $row['erhalter_gebdat'];
$date = DateTime::createFromFormat('Y-m-d', $erhalter_gebdat);
$month_number = $date->format('m');
$month_names = array(
    'Januar',
    'Februar',
    'März',
    'April',
    'Mai',
    'Juni',
    'Juli',
    'August',
    'September',
    'Oktober',
    'November',
    'Dezember'
);
$formatted_date = $date->format('d. ') . $month_names[$month_number - 1] . $date->format(' Y');
$ausstelldatum = date("d.m.Y", strtotime($row['ausstellungsdatum']));

$stmt2 = $pdo->prepare("SELECT id, fullname, aktenid FROM intra_users WHERE id = :id");
$stmt2->execute(['id' => $row['ausstellerid']]);
$adata = $stmt2->fetch(PDO::FETCH_ASSOC);

$stmtdg = $pdo->prepare("SELECT * FROM intra_mitarbeiter_dienstgrade");
$stmtdg->execute();
$dginfo = $stmtdg->fetchAll(PDO::FETCH_UNIQUE);

if ($row['suspendtime'] == "0000-00-00" || $row['suspendtime'] == NULL) {
    $suspenstring = "bis auf unbestimmt";
} else {
    $suspendtime = date("d.m.Y", strtotime($row['suspendtime']));
    $suspenstring = "bis zum " . $suspendtime;
}

if ($row['aussteller_name'] != NULL) {
    $fullname = $row['aussteller_name'];
} else {
    $fullname = $adata['fullname'];
}
$splitname = explode(" ", $fullname);
$lastname = end($splitname);

if ($adata['aktenid'] > 0) {
    $stmt3 = $pdo->prepare("SELECT id, fullname, dienstgrad, qualird, geschlecht, zusatz FROM intra_mitarbeiter WHERE id = :id");
    $stmt3->execute(['id' => $adata['aktenid']]);
    $rdata = $stmt3->fetch(PDO::FETCH_ASSOC);
    if ($row['aussteller_rang'] != NULL) {
        $bfrang = $row['aussteller_rang'];
    } else {
        $bfrang = $rdata['dienstgrad'];
    }
    if ($rdata['geschlecht'] == 0) {
        $dienstgrad2 = $dginfo[$bfrang]['name_m'];
    } elseif ($rdata['geschlecht'] == 1) {
        $dienstgrad2 = $dginfo[$bfrang]['name_w'];
    } else {
        $dienstgrad2 = $dginfo[$bfrang]['name'];
    }
}

$typ = $row['type'];
$typen = [
    10 => "Schriftliche Abmahnung",
    11 => "Vorläufige Dienstenthebung",
    12 => "Dienstentfernung",
    13 => "Außerordentliche Kündigung",
];
$typtext = isset($typen[$typ]) ? $typen[$typ] : '';

$own_url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dienstenthebung &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/style.min.css" />
    <link rel="stylesheet" href="/assets/css/dokumente.min.css" />
    <link rel="stylesheet" href="/assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="/assets/fonts/mavenpro/css/all.min.css" />
    <link rel="stylesheet" href="/assets/fonts/freehand/css/all.min.css" />
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
    <meta property="og:site_name" content="<?php echo RP_ORGTYPE . " " . SERVER_CITY ?>" />
    <meta property="og:url" content="<?= $own_url ?>" />
    <?php echo '<meta property="og:title" content="Dienstenthebung > ' . $row['erhalter'] . '" />'; ?>
    <meta property="og:image" content="<?php echo META_IMAGE_URL ?>" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
</head>

<body class="bg-secondary" data-page-amount="1">
    <div class="page-container">
        <div class="page shadow mx-auto mt-2 bg-light" id="page1">
            <div class="col-4 float-end">
                <img src="/assets/img/schrift_fw_schwarz.png" alt="Schriftzug Berufsfeuerwehr <?php echo SERVER_CITY ?>" style="max-width:100%">
                <div class="my-4"></div>
                <p style="font-size:10pt">Datum</p>
                <p style="font-size:12pt;margin-top:-18px"><?= $ausstelldatum ?></p>
            </div>
            <p style="font-size:10pt"><?php echo RP_ORGTYPE . " " . SERVER_CITY . "<br>" . RP_STREET . "<br>" . RP_ZIP . " " . SERVER_CITY ?></p>
            <p><?= $anrede ?><br>
                <?= $row['erhalter'] ?><br>
                <?php echo RP_ZIP . " " . SERVER_CITY ?>
            </p>
            <div class="my-5"></div>
            <p style="font-size:15pt;font-weight:bolder" class="mb-3"><?= $typtext ?></p>
            <div class="letter-content">
                <p>Sehr
                    <?php if ($row['anrede'] == 1) {
                        echo "geehrte";
                    } elseif ($row['anrede'] == 0) {
                        echo "geehrter";
                    } else {
                        echo "geehrte/-r";
                    }
                    ?>
                    <?= $anredemw ?> <?= $row['erhalter'] ?>,
                </p>
                <p>Mit diesem Schreiben informieren wir Sie über Ihre vorläufige Dienstenthebung.</p>
                <p>Ab sofort sind Sie <?= $suspenstring ?> suspendiert. In diesem Zeitraum sind Sie von Ihren dienstlichen Pflichten entbunden.</p>
                <p>Der Grund der Suspendierung lautet:</p>
                <div class="reasoning border border-2 border-dark py-3 px-2">
                    <?= $row['inhalt'] ?>
                </div>
            </div>
            <div class="my-5"></div>
            <div class="row signatures">
                <div class="col">
                    <table>
                        <tbody>
                            <tr class="text-center" style="border-bottom: 2px solid #000">
                                <td class="signature"><?= $lastname ?></td>
                            </tr>
                            <tr>
                                <td><span class="fw-bold"><?= $fullname ?></span><?php if ($dginfo[$bfrang]['badge']) { ?><br><img src="<?= $dginfo[$bfrang]['badge'] ?>" height='12px' width='auto' alt='Dienstgrad' /><?php } ?> <?= $dienstgrad2 ?>
                                    <?= $rdata['zusatz'] !== null ? '<br>' . $rdata['zusatz'] : '' ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col"></div>
            </div>
            <div class="document-styling">
                <div class="strich-1"></div>
                <div class="strich-2"></div>
                <div class="strich-3"></div>
                <!-- <img src="/assets/img/bf_strich.png" alt="BF Strich"> -->
            </div>
        </div>
    </div>
</body>

</html>