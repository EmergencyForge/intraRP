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
    $zum = "zur";
} elseif ($row['anrede'] == 0) {
    $anrede = "Herr";
    $anredemw = $anrede;
    $zum = "zum";
} else {
    $anrede = "Divers";
    $anredemw = NULL;
    $zum = "zum/zur";
}

if ($row['type'] != 1) {
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
$dg = $row['erhalter_rang'];

$stmtdg = $pdo->prepare("SELECT * FROM intra_mitarbeiter_dienstgrade");
$stmtdg->execute();
$dginfo = $stmtdg->fetchAll(PDO::FETCH_UNIQUE);

if ($row['anrede'] == 1) {
    $dienstgrad = $dginfo[$dg]['name_w'];
} elseif ($row['anrede'] == 0) {
    $dienstgrad = $dginfo[$dg]['name_m'];
} else {
    $dienstgrad = $dginfo[$dg]['name'];
}

$ausstelldatum = date("d.m.Y", strtotime($row['ausstellungsdatum']));

$stmt2 = $pdo->prepare("SELECT id, fullname, aktenid FROM intra_users WHERE id = :id");
$stmt2->execute(['id' => $row['ausstellerid']]);
$adata = $stmt2->fetch(PDO::FETCH_ASSOC);

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
    0 => "Ernennungsurkunde",
    1 => "Beförderungsurkunde",
    2 => "Entlassungsurkunde",
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
    <title>Beförderungsurkunde &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/style.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/dokumente.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/fonts/mavenpro/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/fonts/freehand/css/all.min.css" />
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
    <meta property="og:site_name" content="<?php echo RP_ORGTYPE . " " . SERVER_CITY ?>" />
    <meta property="og:url" content="<?= $own_url ?>" />
    <?php echo '<meta property="og:title" content="Beförderungsurkunde > ' . $row['erhalter'] . '" />'; ?>
    <meta property="og:image" content="<?php echo META_IMAGE_URL ?>" />
    <?php echo '<meta property="og:description" content="Beförderung ' . $zum . ' ' . $dienstgrad . ' der ' . RP_ORGTYPE . ' ' . SERVER_CITY . '" />'; ?>
</head>

<body class="bg-secondary" data-page-amount="2">
    <div class="page-container">
        <div class="page shadow mx-auto mt-4 mb-4 bg-light" id="page-urkunde">
            <table class="docheader">
                <tbody>
                    <tr>
                        <td style="width:20%;padding-left:5px;font-size:8pt"><strong>Version</strong><span class="subtext">1.0</span></td>
                        <td class="text-center" rowspan="2" style="font-size:12pt"><strong><?= $typtext ?></strong><br><?php echo RP_ORGTYPE . " " . SERVER_CITY ?></td>
                        <td class="text-center" style="width:20%" rowspan="2"><img src="<?= BASE_PATH ?>assets/img/wappen_small.png" alt="Berufsfeuerwehr <?php echo SERVER_CITY ?>"></td>
                    </tr>
                    <tr>
                        <td style="width:20%;padding-left:5px;font-size:8pt"><strong>Seite</strong><span class="subtext">1 von 1</span></td>
                    </tr>
                </tbody>
            </table>
            <h1 class="text-center">URKUNDE</h1>
            <div class="urkunde-body text-center">
                <p class="my-5">Im Namen der Stadt <?php echo SERVER_CITY ?></p>
                <p>wird <?= $anrede ?></p>
                <p class="urkunde-important"><?= $row['erhalter'] ?></p>
                <p>» geb. am <?= $formatted_date ?> «</p>
                <p class="my-5">mit sofortiger Wirkung befördert und <?= $zum ?></p>
                <p class="urkunde-important"><?= $dienstgrad ?></p>
                <p class="my-5">der <?php echo RP_ORGTYPE . " " . SERVER_CITY ?> ernannt.</p>
            </div>
            <hr class="text-light my-5">
            <p><?php echo SERVER_CITY ?>, den <?= $ausstelldatum ?>,</p>
            <div class="row signatures">
                <div class="col">
                    <table>
                        <tbody>
                            <tr class="text-center" style="border-bottom: 2px solid #000">
                                <td class="signature"><?= $lastname ?></td>
                            </tr>
                            <tr>
                                <td><span class="fw-bold"><?= $fullname ?></span><?php if (isset($dginfo[$bfrang]['badge'])) { ?><br><img src="<?= $dginfo[$bfrang]['badge'] ?>" height='12px' width='auto' alt='Dienstgrad' /><?php } ?> <?= $dienstgrad2 ?>
                                    <?= $rdata['zusatz'] !== null ? '<br>' . $rdata['zusatz'] : '' ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col"></div>
            </div>
            <div class="urkunde-disclaimer text-center mt-4">
                Diese fiktive Urkunde ist lediglich für das GTA Roleplay Projekt „<?php echo SERVER_NAME ?>“ ausgelegt. Der Besitz dieser Urkunde befugt in keinster Weise zum Führen einer echten Qualifikation.
            </div>
            <!-- <div class="document-styling">
                            <img src="/assets/img/bf_strich.png" alt="BF Strich">
                        </div> -->
        </div>
    </div>
</body>

</html>