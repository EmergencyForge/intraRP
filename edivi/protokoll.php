<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

use App\Localization\Lang;

Lang::setLanguage(LANG ?? 'de');

$daten = array();

if (isset($_GET['enr'])) {
    $queryget = "SELECT * FROM intra_edivi WHERE enr = :enr";
    $stmt = $pdo->prepare($queryget);
    $stmt->execute(['enr' => $_GET['enr']]);

    $daten = $stmt->fetch(PDO::FETCH_ASSOC);

    if (count($daten) == 0) {
        header("Location: /edivi/");
        exit();
    }
} else {
    header("Location: /edivi/");
    exit();
}

if ($daten['freigegeben'] == 1) {
    $ist_freigegeben = true;
} else {
    $ist_freigegeben = false;
}

$daten['last_edit'] = !empty($daten['last_edit']) ? (new DateTime($daten['last_edit']))->format('d.m.Y H:i') : NULL;

$enr = $daten['enr'];

if (isset($_POST['new']) && $_POST['new'] == 1) {
    // STAMMDATEN
    $patname = $_POST['patname'] ?? NULL;
    $patgebdat = !empty($_POST['patgebdat']) ? $_POST['patgebdat'] : NULL;
    $patsex = $_POST['patsex'] ?? NULL;
    $edatum = $_POST['edatum'];
    $ezeit = $_POST['ezeit'];
    $eort = $_POST['eort'];

    // A - ATEMWEGE
    $awfrei_1 = $_POST['awfrei_1'] ?? 0;
    $awfrei_2 = $_POST['awfrei_2'] ?? 0;
    $awfrei_3 = $_POST['awfrei_3'] ?? 0;
    $awsicherung_neu = $_POST['awsicherung_neu'];
    $zyanose_1 = $_POST['zyanose_1'] ?? 0;
    $zyanose_2 = $_POST['zyanose_2'] ?? 0;
    $o2gabe = $_POST['o2gabe'] ?? 0;

    // B - ATMUNG
    $b_symptome = $_POST['b_symptome'];
    $b_auskult = $_POST['b_auskult'];
    $b_beatmung = $_POST['b_beatmung'];
    $spo2 = $_POST['spo2'] ?? NULL;
    $atemfreq = $_POST['atemfreq'] ?? NULL;
    $etco2 = $_POST['etco2'] ?? NULL;

    // C - KREISLAUF
    $c_kreislauf = $_POST['c_kreislauf'];
    $rrsys = $_POST['rrsys'] ?? NULL;
    $rrdias = $_POST['rrdias'] ?? NULL;
    $herzfreq = $_POST['herzfreq'] ?? NULL;
    $c_ekg = $_POST['c_ekg'];
    $c_zugang_art_1 = ($_POST['c_zugang_art_1'] ?? '') !== '' ? (int)$_POST['c_zugang_art_1'] : NULL;
    $c_zugang_art_2 = ($_POST['c_zugang_art_2'] ?? '') !== '' ? (int)$_POST['c_zugang_art_2'] : NULL;
    $c_zugang_art_3 = ($_POST['c_zugang_art_3'] ?? '') !== '' ? (int)$_POST['c_zugang_art_3'] : NULL;
    $c_zugang_gr_1 = ($_POST['c_zugang_gr_1'] ?? '') !== '' ? (int)$_POST['c_zugang_gr_1'] : NULL;
    $c_zugang_gr_2 = ($_POST['c_zugang_gr_2'] ?? '') !== '' ? (int)$_POST['c_zugang_gr_2'] : NULL;
    $c_zugang_gr_3 = ($_POST['c_zugang_gr_3'] ?? '') !== '' ? (int)$_POST['c_zugang_gr_3'] : NULL;
    $c_zugang_ort_1 = $_POST['c_zugang_ort_1'] ?? NULL;
    $c_zugang_ort_2 = $_POST['c_zugang_ort_2'] ?? NULL;
    $c_zugang_ort_3 = $_POST['c_zugang_ort_3'] ?? NULL;

    // D - NEUROLOGIE
    $d_bewusstsein = $_POST['d_bewusstsein'];
    $d_pupillenw_1 = $_POST['d_pupillenw_1'];
    $d_pupillenw_2 = $_POST['d_pupillenw_2'];
    $d_lichtreakt_1 = $_POST['d_lichtreakt_1'];
    $d_lichtreakt_2 = $_POST['d_lichtreakt_2'];
    $d_gcs_1 = $_POST['d_gcs_1'];
    $d_gcs_2 = $_POST['d_gcs_2'];
    $d_gcs_3 = $_POST['d_gcs_3'];
    $d_ex_1 = $_POST['d_ex_1'];

    // BZ + TEMP
    $bz = $_POST['bz'] ?? NULL;
    $temp = $_POST['temp'] ?? NULL;

    // VERLETZUNGEN
    $v_muster_k = $_POST['v_muster_k'];
    $v_muster_k1 = $_POST['v_muster_k1'];
    $v_muster_t = $_POST['v_muster_t'];
    $v_muster_t1 = $_POST['v_muster_t1'];
    $v_muster_al = $_POST['v_muster_al'];
    $v_muster_al1 = $_POST['v_muster_al1'];
    $v_muster_a = $_POST['v_muster_a'];
    $v_muster_a1 = $_POST['v_muster_a1'];
    $v_muster_bl = $_POST['v_muster_bl'];
    $v_muster_bl1 = $_POST['v_muster_bl1'];
    $v_muster_w = $_POST['v_muster_w'];
    $v_muster_w1 = $_POST['v_muster_w1'];
    $sz_nrs = $_POST['sz_nrs'] ?? NULL;
    $sz_toleranz_1 = $_POST['sz_toleranz_1'] ?? 0;
    $sz_toleranz_2 = $_POST['sz_toleranz_2'] ?? 0;

    // MEDIKAMENTE
    $medis = $_POST['medis'] ?? NULL;

    // DIAGNOSE
    $diagnose = $_POST['diagnose'] ?? NULL;

    // ANMERKUNGEN
    $anmerkungen = $_POST['anmerkungen'] ?? NULL;

    // PROTOKOLLDATEN
    $pfname =  $_POST['pfname'];
    $fzg_transp = $_POST['fzg_transp'] ?? NULL;
    $fzg_transp_perso = $_POST['fzg_transp_perso'] ?? NULL;
    $fzg_na = $_POST['fzg_na'] ?? NULL;
    $fzg_na_perso = $_POST['fzg_na_perso'] ?? NULL;
    $fzg_sonst = $_POST['fzg_sonst'] ?? NULL;
    $naname =  $_POST['naname'] ?? NULL;
    $transportziel =  $_POST['transportziel'] ?? NULL;
    $freigeber = $_POST['freigeber'] ?? NULL;
    if ($freigeber != NULL) {
        $freigeber_name = $freigeber;
        $freigeber_status = 1;
        $last_edit = date("Y-m-d H:i:s");
    } else {
        $freigeber_name = NULL;
        $freigeber_status = 0;
        $last_edit = NULL;
    }

    $query = "UPDATE intra_edivi SET patname = :patname, patgebdat = :patgebdat, patsex = :patsex, edatum = :edatum, ezeit = :ezeit, eort = :eort, awfrei_1 = :awfrei_1, awfrei_2 = :awfrei_2, awfrei_3 = :awfrei_3, awsicherung_neu = :awsicherung_neu, zyanose_1 = :zyanose_1, zyanose_2 = :zyanose_2, o2gabe = :o2gabe, b_symptome = :b_symptome, b_auskult = :b_auskult, b_beatmung = :b_beatmung, spo2 = :spo2, atemfreq = :atemfreq, etco2 = :etco2, c_kreislauf = :c_kreislauf, rrsys = :rrsys, rrdias = :rrdias, herzfreq = :herzfreq, c_ekg = :c_ekg, c_zugang_art_1 = :c_zugang_art_1, c_zugang_art_2 = :c_zugang_art_2, c_zugang_art_3 = :c_zugang_art_3, c_zugang_gr_1 = :c_zugang_gr_1, c_zugang_gr_2 = :c_zugang_gr_2, c_zugang_gr_3 = :c_zugang_gr_3, c_zugang_ort_1 = :c_zugang_ort_1, c_zugang_ort_2 = :c_zugang_ort_2, c_zugang_ort_3 = :c_zugang_ort_3, d_bewusstsein = :d_bewusstsein, d_pupillenw_1 = :d_pupillenw_1, d_pupillenw_2 = :d_pupillenw_2, d_lichtreakt_1 = :d_lichtreakt_1, d_lichtreakt_2 = :d_lichtreakt_2, d_gcs_1 = :d_gcs_1, d_gcs_2 = :d_gcs_2, d_gcs_3 = :d_gcs_3, d_ex_1 = :d_ex_1, bz = :bz, temp = :temp, v_muster_k = :v_muster_k, v_muster_k1 = :v_muster_k1, v_muster_t = :v_muster_t, v_muster_t1 = :v_muster_t1, v_muster_al = :v_muster_al, v_muster_al1 = :v_muster_al1, v_muster_a = :v_muster_a, v_muster_a1 = :v_muster_a1, v_muster_bl = :v_muster_bl, v_muster_bl1 = :v_muster_bl1, v_muster_w = :v_muster_w, v_muster_w1 = :v_muster_w1, sz_nrs = :sz_nrs, sz_toleranz_1 = :sz_toleranz_1, sz_toleranz_2 = :sz_toleranz_2, medis = :medis, diagnose = :diagnose, anmerkungen = :anmerkungen, pfname = :pfname, fzg_transp = :fzg_transp, fzg_transp_perso = :fzg_transp_perso, fzg_na = :fzg_na, fzg_na_perso = :fzg_na_perso, fzg_sonst = :fzg_sonst, naname = :naname, transportziel = :transportziel, freigeber_name = :freigeber_name, freigegeben = :freigeber_status, last_edit = :last_edit WHERE enr = :enr";

    $params = ['patname' => $patname, 'patgebdat' => $patgebdat, 'patsex' => $patsex, 'edatum' => $edatum, 'ezeit' => $ezeit, 'eort' => $eort, 'awfrei_1' => $awfrei_1, 'awfrei_2' => $awfrei_2, 'awfrei_3' => $awfrei_3, 'awsicherung_neu' => $awsicherung_neu, 'zyanose_1' => $zyanose_1, 'zyanose_2' => $zyanose_2, 'o2gabe' => $o2gabe, 'b_symptome' => $b_symptome, 'b_auskult' => $b_auskult, 'b_beatmung' => $b_beatmung, 'spo2' => $spo2, 'atemfreq' => $atemfreq, 'etco2' => $etco2, 'c_kreislauf' => $c_kreislauf, 'rrsys' => $rrsys, 'rrdias' => $rrdias, 'herzfreq' => $herzfreq, 'c_ekg' => $c_ekg, 'c_zugang_art_1' => $c_zugang_art_1, 'c_zugang_art_2' => $c_zugang_art_2, 'c_zugang_art_3' => $c_zugang_art_3, 'c_zugang_gr_1' => $c_zugang_gr_1, 'c_zugang_gr_2' => $c_zugang_gr_2, 'c_zugang_gr_3' => $c_zugang_gr_3, 'c_zugang_ort_1' => $c_zugang_ort_1, 'c_zugang_ort_2' => $c_zugang_ort_2, 'c_zugang_ort_3' => $c_zugang_ort_3, 'd_bewusstsein' => $d_bewusstsein, 'd_pupillenw_1' => $d_pupillenw_1, 'd_pupillenw_2' => $d_pupillenw_2, 'd_lichtreakt_1' => $d_lichtreakt_1, 'd_lichtreakt_2' => $d_lichtreakt_2, 'd_gcs_1' => $d_gcs_1, 'd_gcs_2' => $d_gcs_2, 'd_gcs_3' => $d_gcs_3, 'd_ex_1' => $d_ex_1, 'bz' => $bz, 'temp' => $temp, 'v_muster_k' => $v_muster_k, 'v_muster_k1' => $v_muster_k1, 'v_muster_t' => $v_muster_t, 'v_muster_t1' => $v_muster_t1, 'v_muster_al' => $v_muster_al, 'v_muster_al1' => $v_muster_al1, 'v_muster_a' => $v_muster_a, 'v_muster_a1' => $v_muster_a1, 'v_muster_bl' => $v_muster_bl, 'v_muster_bl1' => $v_muster_bl1, 'v_muster_w' => $v_muster_w, 'v_muster_w1' => $v_muster_w1, 'sz_nrs' => $sz_nrs, 'sz_toleranz_1' => $sz_toleranz_1, 'sz_toleranz_2' => $sz_toleranz_2, 'medis' => $medis, 'diagnose' => $diagnose, 'anmerkungen' => $anmerkungen, 'pfname' => $pfname, 'fzg_transp' => $fzg_transp, 'fzg_transp_perso' => $fzg_transp_perso, 'fzg_na' => $fzg_na, 'fzg_na_perso' => $fzg_na_perso, 'fzg_sonst' => $fzg_sonst, 'naname' => $naname, 'transportziel' => $transportziel, 'freigeber_name' => $freigeber_name, 'freigeber_status' => $freigeber_status, 'last_edit' => $last_edit, 'enr' => $enr];

    $stmts = $pdo->prepare($query);
    $stmts->execute($params);

    header("Refresh: 0");
}

$prot_url = "https://" . SYSTEM_URL . "/edivi/" . $enr;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= __('edivi.protocol.title', [$daten['enr'], SYSTEM_NAME]) ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/divi.min.css" />
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
    <meta name="theme-color" content="#ffaf2f" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="<?= $prot_url ?>" />
    <meta property="og:title" content="[#<?= $daten['enr'] ?>] &rsaquo; eDIVI &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="<?= __('metas.description', [RP_ORGTYPE, SERVER_CITY]) ?>" />
</head>

<body>
    <form name="form" method="post" action="">
        <input type="hidden" name="new" value="1" />
        <div class="container-fluid" id="edivi__container">
            <?php if ($ist_freigegeben) : ?>
                <div class="container-full edivi__notice edivi__notice-freigeber">
                    <div class="row">
                        <div class="col-1 text-end"><i class="las la-info"></i></div>
                        <div class="col">
                            <?= __('edivi.protocol.released_info', [$daten['freigeber_name'], $daten['last_edit']]) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row h-100">
                <div class="col">
                    <div class="row shadow edivi__box">
                        <div class="col">
                            <h5 class="text-light p-1"><?= __('edivi.protocol.basedata.header') ?></h5>
                            <div class="col">
                                <div class="row my-2">
                                    <div class="col-4 edivi__description"><?= __('edivi.protocol.basedata.name') ?></div>
                                    <div class="col"><input type="text" name="patname" id="patname" placeholder="<?= __('edivi.protocol.basedata.name_placeholder') ?>" class="w-100 form-control" value="<?= $daten['patname'] ?>"></div>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.basedata.birthdate') ?></div>
                                <div class="col"><input type="date" name="patgebdat" id="patgebdat" class="w-100 form-control" value="<?= $daten['patgebdat'] ?>"></div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.basedata.gender') ?></div>
                                <div class="col">
                                    <div class="row">
                                        <?php if ($daten['patsex'] === NULL) : ?>
                                            <div class="col"><input class="form-check-input" type="radio" name="patsex" id="patsex" value="0"><?= __('edivi.protocol.basedata.male') ?></div>
                                            <div class="col"><input class="form-check-input" type="radio" name="patsex" id="patsex" value="1"><?= __('edivi.protocol.basedata.female') ?></div>
                                        <?php elseif ($daten['patsex'] == 1) : ?>
                                            <div class="col"><input class="form-check-input" type="radio" name="patsex" id="patsex" value="0"><?= __('edivi.protocol.basedata.male') ?></div>
                                            <div class="col"><input class="form-check-input" type="radio" name="patsex" id="patsex" value="1" checked><?= __('edivi.protocol.basedata.female') ?></div>
                                        <?php elseif ($daten['patsex'] == 0) : ?>
                                            <div class="col"><input class="form-check-input" type="radio" name="patsex" id="patsex" value="0" checked><?= __('edivi.protocol.basedata.male') ?></div>
                                            <div class="col"><input class="form-check-input" type="radio" name="patsex" id="patsex" value="1"><?= __('edivi.protocol.basedata.female') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.basedata.call_datetime') ?></div>
                                <div class="col">
                                    <input type="date" name="edatum" id="edatum" class="w-100 form-control edivi__input-check" value="<?= $daten['edatum'] ?>" required>
                                </div>
                                <div class="col">
                                    <input type="time" name="ezeit" id="ezeit" class="w-100 form-control edivi__input-check" value="<?= $daten['ezeit'] ?>" required>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.basedata.call_nr') ?></div>
                                <div class="col">
                                    <input type="text" name="enr" id="enr" class="w-100 form-control" value="<?= $_GET['enr'] ?>" readonly>
                                </div>
                                <div class="col">
                                    <input type="text" name="eort" id="eort" class="w-100 form-control edivi__input-check" placeholder="<?= __('edivi.protocol.basedata.location') ?>" value="<?= $daten['eort'] ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row edivi__box">
                        <div class="col">
                            <!-- ------------ -->
                            <!-- A - ATEMWEGE -->
                            <!-- ------------ -->
                            <h5 class="text-light p-1"><?= __('edivi.protocol.airway.header') ?></h5>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.airway.airway.label') ?></div>
                                <div class="col">
                                    <div class="row">
                                        <div class="col">
                                            <input type="checkbox" class="btn-check" id="awfrei_1" name="awfrei_1" value="1" <?php echo ($daten['awfrei_1'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                            <label class="btn btn-sm btn-outline-success w-100" for="awfrei_1"><?= __('edivi.protocol.airway.airway.free') ?></label>
                                        </div>
                                        <div class="col">
                                            <input type="checkbox" class="btn-check" id="awfrei_3" name="awfrei_3" value="1" <?php echo ($daten['awfrei_3'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                            <label class="btn btn-sm btn-outline-warning w-100" for="awfrei_3"><?= __('edivi.protocol.airway.airway.endangered') ?></label>
                                        </div>
                                        <div class="col">
                                            <input type="checkbox" class="btn-check" id="awfrei_2" name="awfrei_2" value="1" <?php echo ($daten['awfrei_2'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                            <label class="btn btn-sm btn-outline-danger w-100" for="awfrei_2"><?= __('edivi.protocol.airway.airway.obstructed') ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.airway.airway_management.label') ?></div>
                                <div class="col">
                                    <?php
                                    if ($daten['awsicherung_neu'] === NULL) {
                                    ?>
                                        <select name="awsicherung_neu" id="awsicherung_neu" class="w-100 form-select edivi__input-check" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0"><?= __('edivi.protocol.airway.airway_management.list.0') ?></option>
                                            <option value="1"><?= __('edivi.protocol.airway.airway_management.list.1') ?></option>
                                            <option value="2"><?= __('edivi.protocol.airway.airway_management.list.2') ?></option>
                                            <option value="3"><?= __('edivi.protocol.airway.airway_management.list.3') ?></option>
                                            <option value="99"><?= __('edivi.protocol.airway.airway_management.list.99') ?></option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="awsicherung_neu" id="awsicherung_neu" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected>---</option>
                                            <option value="0" <?php echo ($daten['awsicherung_neu'] == 0 ? 'selected' : '') ?>><?= __('edivi.protocol.airway.airway_management.list.0') ?></option>
                                            <option value="1" <?php echo ($daten['awsicherung_neu'] == 1 ? 'selected' : '') ?>><?= __('edivi.protocol.airway.airway_management.list.1') ?></option>
                                            <option value="2" <?php echo ($daten['awsicherung_neu'] == 2 ? 'selected' : '') ?>><?= __('edivi.protocol.airway.airway_management.list.2') ?></option>
                                            <option value="3" <?php echo ($daten['awsicherung_neu'] == 3 ? 'selected' : '') ?>><?= __('edivi.protocol.airway.airway_management.list.3') ?></option>
                                            <option value="99" <?php echo ($daten['awsicherung_neu'] == 99 ? 'selected' : '') ?>><?= __('edivi.protocol.airway.airway_management.list.99') ?></option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.airway.cyanosis.label') ?></div>
                                <div class="col">
                                    <div class="row">
                                        <div class="col">
                                            <input type="checkbox" class="btn-check" id="zyanose_1" name="zyanose_1" value="1" <?php echo ($daten['zyanose_1'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                            <label class="btn btn-sm btn-outline-light w-100" for="zyanose_1"><?= __('edivi.protocol.airway.cyanosis.no') ?></label>
                                        </div>
                                        <div class="col">
                                            <input type="checkbox" class="btn-check" id="zyanose_2" name="zyanose_2" value="1" <?php echo ($daten['zyanose_2'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                            <label class="btn btn-sm btn-outline-light w-100" for="zyanose_2"><?= __('edivi.protocol.airway.cyanosis.yes') ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.airway.oxygenation.label') ?></div>
                                <div class="col">
                                    <div class="row">
                                        <div class="col"><input class="w-100 vitalparam form-control" type="text" min="0" max="15" placeholder="" name="o2gabe" id="o2gabe" value="<?= $daten['o2gabe'] ?>" style="display:inline"> <small><?= __('edivi.protocol.airway.oxygenation.liters') ?></small></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row edivi__box">
                        <div class="col">
                            <!-- ------------ -->
                            <!-- B - ATMUNG -->
                            <!-- ------------ -->
                            <h5 class="text-light p-1"><?= __('edivi.protocol.breathing.header') ?></h5>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.breathing.breathing.label') ?></div>
                                <div class="col">
                                    <?php
                                    if ($daten['b_symptome'] === NULL) {
                                    ?>
                                        <select name="b_symptome" id="b_symptome" class="w-100 form-select edivi__input-check" required>
                                            <option disabled hidden selected><?= __('edivi.protocol.breathing.breathing.list.unselected') ?></option>
                                            <option value="0"><?= __('edivi.protocol.breathing.breathing.list.0') ?></option>
                                            <option value="1"><?= __('edivi.protocol.breathing.breathing.list.1') ?></option>
                                            <option value="2"><?= __('edivi.protocol.breathing.breathing.list.2') ?></option>
                                            <option value="3"><?= __('edivi.protocol.breathing.breathing.list.3') ?></option>
                                            <option value="4"><?= __('edivi.protocol.breathing.breathing.list.4') ?></option>
                                            <option value="99"><?= __('edivi.protocol.breathing.breathing.list.99') ?></option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="b_symptome" id="b_symptome" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected><?= __('edivi.protocol.breathing.breathing.list.unselected') ?></option>
                                            <option value="0" <?php echo ($daten['b_symptome'] == 0 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.breathing.list.0') ?></option>
                                            <option value="1" <?php echo ($daten['b_symptome'] == 1 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.breathing.list.1') ?></option>
                                            <option value="2" <?php echo ($daten['b_symptome'] == 2 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.breathing.list.2') ?></option>
                                            <option value="3" <?php echo ($daten['b_symptome'] == 3 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.breathing.list.3') ?></option>
                                            <option value="4" <?php echo ($daten['b_symptome'] == 4 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.breathing.list.4') ?></option>
                                            <option value="99" <?php echo ($daten['b_symptome'] == 99 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.breathing.list.99') ?></option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.breathing.auscultation.label') ?></div>
                                <div class="col">
                                    <?php
                                    if ($daten['b_auskult'] === NULL) {
                                    ?>
                                        <select name="b_auskult" id="b_auskult" class="w-100 form-select edivi__input-check" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0"><?= __('edivi.protocol.breathing.auscultation.list.0') ?></option>
                                            <option value="1"><?= __('edivi.protocol.breathing.auscultation.list.1') ?></option>
                                            <option value="2"><?= __('edivi.protocol.breathing.auscultation.list.2') ?></option>
                                            <option value="3"><?= __('edivi.protocol.breathing.auscultation.list.3') ?></option>
                                            <option value="4"><?= __('edivi.protocol.breathing.auscultation.list.4') ?></option>
                                            <option value="99"><?= __('edivi.protocol.breathing.auscultation.list.99') ?></option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="b_auskult" id="b_auskult" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected>---</option>
                                            <option value="0" <?php echo ($daten['b_auskult'] == 0 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.auscultation.list.0') ?></option>
                                            <option value="1" <?php echo ($daten['b_auskult'] == 1 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.auscultation.list.1') ?></option>
                                            <option value="2" <?php echo ($daten['b_auskult'] == 2 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.auscultation.list.2') ?></option>
                                            <option value="3" <?php echo ($daten['b_auskult'] == 3 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.auscultation.list.3') ?></option>
                                            <option value="4" <?php echo ($daten['b_auskult'] == 4 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.auscultation.list.4') ?></option>
                                            <option value="99" <?php echo ($daten['b_auskult'] == 99 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.auscultation.list.99') ?></option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.breathing.ventilation.label') ?></div>
                                <div class="col">
                                    <?php
                                    if ($daten['b_beatmung'] === NULL) {
                                    ?>
                                        <select name="b_beatmung" id="b_beatmung" class="w-100 form-select edivi__input-check" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="4"><?= __('edivi.protocol.breathing.ventilation.list.4') ?></option>
                                            <option value="0"><?= __('edivi.protocol.breathing.ventilation.list.0') ?></option>
                                            <option value="1"><?= __('edivi.protocol.breathing.ventilation.list.1') ?></option>
                                            <option value="2"><?= __('edivi.protocol.breathing.ventilation.list.2') ?></option>
                                            <option value="3"><?= __('edivi.protocol.breathing.ventilation.list.3') ?></option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="b_beatmung" id="b_beatmung" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected>---</option>
                                            <option value="4" <?php echo ($daten['b_beatmung'] == 4 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.ventilation.list.4') ?></option>
                                            <option value="0" <?php echo ($daten['b_beatmung'] == 0 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.ventilation.list.0') ?></option>
                                            <option value="1" <?php echo ($daten['b_beatmung'] == 1 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.ventilation.list.1') ?></option>
                                            <option value="2" <?php echo ($daten['b_beatmung'] == 2 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.ventilation.list.2') ?></option>
                                            <option value="3" <?php echo ($daten['b_beatmung'] == 3 ? 'selected' : '') ?>><?= __('edivi.protocol.breathing.ventilation.list.3') ?></option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.breathing.spo2') ?></div>
                                <div class="col">
                                    <div class="row vital-wrapper">
                                        <div class="col"><input class="w-100 vitalparam form-control" type="text" placeholder="" name="spo2" id="spo2" value="<?= $daten['spo2'] ?>" data-min="94" style="display:inline"> <small>%</small>
                                            <span class="arrow-indicator"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.breathing.respiratory_rate') ?></div>
                                <div class="col">
                                    <div class="row vital-wrapper">
                                        <div class="col"><input class="w-100 vitalparam form-control" type="text" name="atemfreq" id="atemfreq" value="<?= $daten['atemfreq'] ?>" data-min="12" data-max="18" style="display:inline"> <small><?= __('edivi.protocol.per_min') ?></small>
                                            <span class="arrow-indicator"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.breathing.etco2') ?></div>
                                <div class="col">
                                    <div class="row vital-wrapper">
                                        <div class="col"><input class="w-100 vitalparam form-control" type="text" name="etco2" id="etco2" value="<?= $daten['etco2'] ?>" data-min="33" data-max="43" style="display:inline"> <small><?= __('edivi.protocol.mmhg') ?></small>
                                            <span class="arrow-indicator"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row edivi__box">
                        <div class="col">
                            <!-- ------------ -->
                            <!-- C - KREISLAUF -->
                            <!-- ------------ -->
                            <h5 class="text-light p-1"><?= __('edivi.protocol.circulation.header') ?></h5>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.circulation.circulation.label') ?></div>
                                <div class="col">
                                    <?php
                                    if ($daten['c_kreislauf'] === NULL) {
                                    ?>
                                        <select name="c_kreislauf" id="c_kreislauf" class="w-100 form-select edivi__input-check" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0"><?= __('edivi.protocol.circulation.circulation.list.0') ?></option>
                                            <option value="1"><?= __('edivi.protocol.circulation.circulation.list.1') ?></option>
                                            <option value="2"><?= __('edivi.protocol.circulation.circulation.list.2') ?></option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="c_kreislauf" id="c_kreislauf" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected>---</option>
                                            <option value="0" <?php echo ($daten['c_kreislauf'] == 0 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.circulation.list.0') ?></option>
                                            <option value="1" <?php echo ($daten['c_kreislauf'] == 1 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.circulation.list.1') ?></option>
                                            <option value="2" <?php echo ($daten['c_kreislauf'] == 2 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.circulation.list.2') ?></option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.circulation.bloodpressure.label') ?></div>
                                <div class="col">
                                    <div class="row mb-1">
                                        <div class="col"><small class="fw-bold"><?= __('edivi.protocol.circulation.bloodpressure.sys') ?></small> <input class="w-100 vitalparam form-control" type="text" name="rrsys" id="rrsys" value="<?= $daten['rrsys'] ?>" style="display:inline"> <small class="fw-bold"><?= __('edivi.protocol.circulation.bloodpressure.dias') ?></small> <input class="w-100 vitalparam form-control" type="text" name="rrdias" id="rrdias" value="<?= $daten['rrdias'] ?>" style="display:inline"> <small><?= __('edivi.protocol.mmhg') ?></small></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.circulation.frequency') ?></div>
                                <div class="col">
                                    <div class="row vital-wrapper mb-1">
                                        <div class="col"><input class="w-100 vitalparam form-control" type="text" name="herzfreq" id="herzfreq" value="<?= $daten['herzfreq'] ?>" data-min="60" data-max="90" style="display:inline"> <small><?= __('edivi.protocol.per_min') ?></small>
                                            <span class="arrow-indicator"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"><?= __('edivi.protocol.circulation.ecg.label') ?></div>
                                <div class="col">
                                    <?php
                                    if ($daten['c_ekg'] === NULL) {
                                    ?>
                                        <select name="c_ekg" id="c_ekg" class="w-100 form-select edivi__input-check" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0"><?= __('edivi.protocol.circulation.ecg.list.0') ?></option>
                                            <option value="1"><?= __('edivi.protocol.circulation.ecg.list.1') ?></option>
                                            <option value="2"><?= __('edivi.protocol.circulation.ecg.list.2') ?></option>
                                            <option value="3"><?= __('edivi.protocol.circulation.ecg.list.3') ?></option>
                                            <option value="4"><?= __('edivi.protocol.circulation.ecg.list.4') ?></option>
                                            <option value="5"><?= __('edivi.protocol.circulation.ecg.list.5') ?></option>
                                            <option value="6"><?= __('edivi.protocol.circulation.ecg.list.6') ?></option>
                                            <option value="8"><?= __('edivi.protocol.circulation.ecg.list.8') ?></option>
                                            <option value="9"><?= __('edivi.protocol.circulation.ecg.list.9') ?></option>
                                            <option value="99"><?= __('edivi.protocol.circulation.ecg.list.99') ?></option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="c_ekg" id="c_ekg" class="w-100 form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected>---</option>
                                            <option value="0" <?php echo ($daten['c_ekg'] == 0 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.ecg.list.0') ?></option>
                                            <option value="1" <?php echo ($daten['c_ekg'] == 1 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.ecg.list.1') ?></option>
                                            <option value="2" <?php echo ($daten['c_ekg'] == 2 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.ecg.list.2') ?></option>
                                            <option value="3" <?php echo ($daten['c_ekg'] == 3 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.ecg.list.3') ?></option>
                                            <option value="4" <?php echo ($daten['c_ekg'] == 4 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.ecg.list.4') ?></option>
                                            <option value="5" <?php echo ($daten['c_ekg'] == 5 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.ecg.list.5') ?></option>
                                            <option value="6" <?php echo ($daten['c_ekg'] == 6 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.ecg.list.6') ?></option>
                                            <option value="8" <?php echo ($daten['c_ekg'] == 8 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.ecg.list.8') ?></option>
                                            <option value="9" <?php echo ($daten['c_ekg'] == 9 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.ecg.list.9') ?></option>
                                            <option value="99" <?php echo ($daten['c_ekg'] == 99 ? 'selected' : '') ?>><?= __('edivi.protocol.circulation.ecg.list.99') ?></option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description">Zugänge</div>
                                <div class="col-2">
                                    <?php
                                    if ($daten['c_zugang_art_1'] === NULL) {
                                    ?>
                                        <select name="c_zugang_art_1" id="c_zugang_art_1" class="w-100 form-select">
                                            <option value="" selected>Art</option>
                                            <option value="3">pvk</option>
                                            <option value="1">zvk</option>
                                            <option value="2">i.o.</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="c_zugang_art_1" id="c_zugang_art_1" class="w-100 form-select" autocomplete="off">
                                            <option value="" selected>Art</option>
                                            <option value="3" <?php echo ($daten['c_zugang_art_1'] == 3 ? 'selected' : '') ?>>pvk</option>
                                            <option value="1" <?php echo ($daten['c_zugang_art_1'] == 1 ? 'selected' : '') ?>>zvk</option>
                                            <option value="2" <?php echo ($daten['c_zugang_art_1'] == 2 ? 'selected' : '') ?>>i.o.</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-2 edivi__zugang-container">
                                    <?php
                                    if ($daten['c_zugang_gr_1'] === NULL) {
                                    ?>
                                        <select name="c_zugang_gr_1" id="c_zugang_gr_1" class="w-100 form-select edivi__zugang-list">
                                            <option value="" selected>Gr.</option>
                                            <option disabled>-- i.v. --</option>
                                            <option value="10">G24</option>
                                            <option value="1">G22</option>
                                            <option value="2">G20</option>
                                            <option value="3">G18</option>
                                            <option value="4">G17</option>
                                            <option value="5">G16</option>
                                            <option value="6">G14</option>
                                            <option disabled>-- i.o. --</option>
                                            <option value="7">15mm</option>
                                            <option value="8">25mm</option>
                                            <option value="9">45mm</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="c_zugang_gr_1" id="c_zugang_gr_1" class="w-100 form-select edivi__zugang-list" autocomplete="off">
                                            <option value="" selected>Gr.</option>
                                            <option disabled>-- i.v. --</option>
                                            <option value="10" <?php echo ($daten['c_zugang_gr_1'] == 10 ? 'selected' : '') ?>>G24</option>
                                            <option value="1" <?php echo ($daten['c_zugang_gr_1'] == 1 ? 'selected' : '') ?>>G22</option>
                                            <option value="2" <?php echo ($daten['c_zugang_gr_1'] == 2 ? 'selected' : '') ?>>G20</option>
                                            <option value="3" <?php echo ($daten['c_zugang_gr_1'] == 3 ? 'selected' : '') ?>>G18</option>
                                            <option value="4" <?php echo ($daten['c_zugang_gr_1'] == 4 ? 'selected' : '') ?>>G17</option>
                                            <option value="5" <?php echo ($daten['c_zugang_gr_1'] == 5 ? 'selected' : '') ?>>G16</option>
                                            <option value="6" <?php echo ($daten['c_zugang_gr_1'] == 6 ? 'selected' : '') ?>>G14</option>
                                            <option disabled>-- i.o. --</option>
                                            <option value="7" <?php echo ($daten['c_zugang_gr_1'] == 7 ? 'selected' : '') ?>>15mm</option>
                                            <option value="8" <?php echo ($daten['c_zugang_gr_1'] == 8 ? 'selected' : '') ?>>25mm</option>
                                            <option value="9" <?php echo ($daten['c_zugang_gr_1'] == 9 ? 'selected' : '') ?>>45mm</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col">
                                    <input type="text" name="c_zugang_ort_1" id="c_zugang_ort_1" class="w-100 form-control" placeholder="Ort" value="<?= $daten['c_zugang_ort_1'] ?>">
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"></div>
                                <div class="col-2">
                                    <?php
                                    if ($daten['c_zugang_art_2'] === NULL) {
                                    ?>
                                        <select name="c_zugang_art_2" id="c_zugang_art_2" class="w-100 form-select">
                                            <option value="" selected>Art</option>
                                            <option value="3">pvk</option>
                                            <option value="1">zvk</option>
                                            <option value="2">i.o.</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="c_zugang_art_2" id="c_zugang_art_2" class="w-100 form-select" autocomplete="off">
                                            <option value="" selected>Art</option>
                                            <option value="3" <?php echo ($daten['c_zugang_art_2'] == 3 ? 'selected' : '') ?>>pvk</option>
                                            <option value="1" <?php echo ($daten['c_zugang_art_2'] == 1 ? 'selected' : '') ?>>zvk</option>
                                            <option value="2" <?php echo ($daten['c_zugang_art_2'] == 2 ? 'selected' : '') ?>>i.o.</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-2 edivi__zugang-container">
                                    <?php
                                    if ($daten['c_zugang_gr_2'] === NULL) {
                                    ?>
                                        <select name="c_zugang_gr_2" id="c_zugang_gr_2" class="w-100 form-select edivi__zugang-list">
                                            <option value="" selected>Gr.</option>
                                            <option disabled>-- i.v. --</option>
                                            <option value="10">G24</option>
                                            <option value="1">G22</option>
                                            <option value="2">G20</option>
                                            <option value="3">G18</option>
                                            <option value="4">G17</option>
                                            <option value="5">G16</option>
                                            <option value="6">G14</option>
                                            <option disabled>-- i.o. --</option>
                                            <option value="7">15mm</option>
                                            <option value="8">25mm</option>
                                            <option value="9">45mm</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="c_zugang_gr_2" id="c_zugang_gr_2" class="w-100 form-select edivi__zugang-list" autocomplete="off">
                                            <option value="" selected>Gr.</option>
                                            <option disabled>-- i.v. --</option>
                                            <option value="10" <?php echo ($daten['c_zugang_gr_2'] == 10 ? 'selected' : '') ?>>G24</option>
                                            <option value="1" <?php echo ($daten['c_zugang_gr_2'] == 1 ? 'selected' : '') ?>>G22</option>
                                            <option value="2" <?php echo ($daten['c_zugang_gr_2'] == 2 ? 'selected' : '') ?>>G20</option>
                                            <option value="3" <?php echo ($daten['c_zugang_gr_2'] == 3 ? 'selected' : '') ?>>G18</option>
                                            <option value="4" <?php echo ($daten['c_zugang_gr_2'] == 4 ? 'selected' : '') ?>>G17</option>
                                            <option value="5" <?php echo ($daten['c_zugang_gr_2'] == 5 ? 'selected' : '') ?>>G16</option>
                                            <option value="6" <?php echo ($daten['c_zugang_gr_2'] == 6 ? 'selected' : '') ?>>G14</option>
                                            <option disabled>-- i.o. --</option>
                                            <option value="7" <?php echo ($daten['c_zugang_gr_2'] == 7 ? 'selected' : '') ?>>15mm</option>
                                            <option value="8" <?php echo ($daten['c_zugang_gr_2'] == 8 ? 'selected' : '') ?>>25mm</option>
                                            <option value="9" <?php echo ($daten['c_zugang_gr_2'] == 9 ? 'selected' : '') ?>>45mm</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col">
                                    <input type="text" name="c_zugang_ort_2" id="c_zugang_ort_2" class="w-100 form-control" placeholder="Ort" value="<?= $daten['c_zugang_ort_2'] ?>">
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"></div>
                                <div class="col-2">
                                    <?php
                                    if ($daten['c_zugang_art_3'] === NULL) {
                                    ?>
                                        <select name="c_zugang_art_3" id="c_zugang_art_3" class="w-100 form-select">
                                            <option value="" selected>Art</option>
                                            <option value="3">pvk</option>
                                            <option value="1">zvk</option>
                                            <option value="2">i.o.</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="c_zugang_art_3" id="c_zugang_art_3" class="w-100 form-select" autocomplete="off">
                                            <option value="" selected>Art</option>
                                            <option value="3" <?php echo ($daten['c_zugang_art_3'] == 3 ? 'selected' : '') ?>>pvk</option>
                                            <option value="1" <?php echo ($daten['c_zugang_art_3'] == 1 ? 'selected' : '') ?>>zvk</option>
                                            <option value="2" <?php echo ($daten['c_zugang_art_3'] == 2 ? 'selected' : '') ?>>i.o.</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-2 edivi__zugang-container">
                                    <?php
                                    if ($daten['c_zugang_gr_3'] === NULL) {
                                    ?>
                                        <select name="c_zugang_gr_3" id="c_zugang_gr_3" class="w-100 form-select edivi__zugang-list">
                                            <option value="" selected>Gr.</option>
                                            <option disabled>-- i.v. --</option>
                                            <option value="10">G24</option>
                                            <option value="1">G22</option>
                                            <option value="2">G20</option>
                                            <option value="3">G18</option>
                                            <option value="4">G17</option>
                                            <option value="5">G16</option>
                                            <option value="6">G14</option>
                                            <option disabled>-- i.o. --</option>
                                            <option value="7">15mm</option>
                                            <option value="8">25mm</option>
                                            <option value="9">45mm</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="c_zugang_gr_3" id="c_zugang_gr_3" class="w-100 form-select edivi__zugang-list" autocomplete="off">
                                            <option value="" selected>Gr.</option>
                                            <option disabled>-- i.v. --</option>
                                            <option value="10" <?php echo ($daten['c_zugang_gr_3'] == 10 ? 'selected' : '') ?>>G24</option>
                                            <option value="1" <?php echo ($daten['c_zugang_gr_3'] == 1 ? 'selected' : '') ?>>G22</option>
                                            <option value="2" <?php echo ($daten['c_zugang_gr_3'] == 2 ? 'selected' : '') ?>>G20</option>
                                            <option value="3" <?php echo ($daten['c_zugang_gr_3'] == 3 ? 'selected' : '') ?>>G18</option>
                                            <option value="4" <?php echo ($daten['c_zugang_gr_3'] == 4 ? 'selected' : '') ?>>G17</option>
                                            <option value="5" <?php echo ($daten['c_zugang_gr_3'] == 5 ? 'selected' : '') ?>>G16</option>
                                            <option value="6" <?php echo ($daten['c_zugang_gr_3'] == 6 ? 'selected' : '') ?>>G14</option>
                                            <option disabled>-- i.o. --</option>
                                            <option value="7" <?php echo ($daten['c_zugang_gr_3'] == 7 ? 'selected' : '') ?>>15mm</option>
                                            <option value="8" <?php echo ($daten['c_zugang_gr_3'] == 8 ? 'selected' : '') ?>>25mm</option>
                                            <option value="9" <?php echo ($daten['c_zugang_gr_3'] == 9 ? 'selected' : '') ?>>45mm</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col">
                                    <input type="text" name="c_zugang_ort_3" id="c_zugang_ort_3" class="w-100 form-control" placeholder="Ort" value="<?= $daten['c_zugang_ort_3'] ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col mx-2">
                    <div class="row edivi__box">
                        <div class="col">
                            <!-- ------------ -->
                            <!-- D - NEUROLOGIE -->
                            <!-- ------------ -->
                            <h5 class="text-light p-1">D - Neurologie <em>(Disability)</em></h5>
                            <div class="row my-2">
                                <div class="col-4 edivi__description">Bewusstseinslage</div>
                                <div class="col">
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
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description">Pupillenweite</div>
                                <div class="col">
                                    <?Php if ($daten['d_pupillenw_1'] === NULL) {
                                    ?>
                                        <small>li</small> <select name="d_pupillenw_1" id="d_pupillenw_1" class="form-select edivi__input-check" style="display:inline; max-width: 150px" required>
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
                                        <small>li</small> <select name="d_pupillenw_1" id="d_pupillenw_1" class="form-select edivi__input-check" style="display:inline; max-width: 150px" required autocomplete="off">
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
                                    <?Php if ($daten['d_pupillenw_2'] === NULL) {
                                    ?>
                                        <small>re</small> <select name="d_pupillenw_2" id="d_pupillenw_2" class="form-select edivi__input-check" style="display:inline; max-width: 150px" required>
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
                                        <small>re</small> <select name="d_pupillenw_2" id="d_pupillenw_2" class="form-select edivi__input-check" style="display:inline; max-width: 150px" required autocomplete="off">
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
                                <div class="col-4 edivi__description">Lichtreaktion</div>
                                <div class="col">
                                    <?php
                                    if ($daten['d_lichtreakt_1'] === NULL) {
                                    ?>
                                        <small>li</small> <select name="d_lichtreakt_1" id="d_lichtreakt_1" class="form-select edivi__input-check" style="display:inline; max-width: 150px" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0">prompt</option>
                                            <option value="1">träge</option>
                                            <option value="2">keine</option>
                                            <option value="99">n. unters.</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <small>li</small> <select name="d_lichtreakt_1" id="d_lichtreakt_1" class="form-select edivi__input-check" style="display:inline; max-width: 150px" required autocomplete="off">
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
                                    <?php
                                    if ($daten['d_lichtreakt_2'] === NULL) {
                                    ?>
                                        <small>re</small> <select name="d_lichtreakt_2" id="d_lichtreakt_2" class="form-select edivi__input-check" style="display:inline; max-width: 150px" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0">prompt</option>
                                            <option value="1">träge</option>
                                            <option value="2">keine</option>
                                            <option value="99">n. unters.</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <small>re</small> <select name="d_lichtreakt_2" id="d_lichtreakt_2" class="form-select edivi__input-check" style="display:inline; max-width: 150px" required autocomplete="off">
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
                            <div class="row">
                                <div class="col fw-bold">Glasgow Coma Scale</div>
                            </div>
                            <div class="row ms-1">
                                <div class="col-4 edivi__description"><small>Augen öffnen</small></div>
                                <div class="col">
                                    <div class="row mb-1">
                                        <div class="col">
                                            <?php
                                            if ($daten['d_gcs_1'] === NULL) {
                                            ?>
                                                <select class="w-100 form-select edivi__input-check" name="d_gcs_1" id="d_gcs_1" required>
                                                    <option disabled hidden selected>---</option>
                                                    <option value="0">spontan (4)</option>
                                                    <option value="1">auf Aufforderung (3)</option>
                                                    <option value="2">auf Schmerzreiz (2)</option>
                                                    <option value="3">kein Öffnen (1)</option>
                                                </select>
                                            <?php
                                            } else {
                                            ?>
                                                <select class="w-100 form-select edivi__input-check" name="d_gcs_1" id="d_gcs_1" required autocomplete="off">
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
                                        <div class="col-2"><small class="fw-bold">(4)</small></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row ms-1">
                                <div class="col-4 edivi__description"><small>beste verbale Reaktion</small></div>
                                <div class="col">
                                    <div class="row mb-1">
                                        <div class="col">
                                            <?php
                                            if ($daten['d_gcs_2'] === NULL) {
                                            ?>
                                                <select class="w-100 form-select edivi__input-check" name="d_gcs_2" id="d_gcs_2" required>
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
                                                <select class="w-100 form-select edivi__input-check" name="d_gcs_2" id="d_gcs_2" required autocomplete="off">
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
                                        <div class="col-2"><small class="fw-bold">(5)</small></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row ms-1">
                                <div class="col-4 edivi__description"><small>beste motorische Reaktion</small></div>
                                <div class="col">
                                    <div class="row mb-1">
                                        <div class="col">
                                            <?php
                                            if ($daten['d_gcs_3'] === NULL) {
                                            ?>
                                                <select class="w-100 form-select edivi__input-check" name="d_gcs_3" id="d_gcs_3" required>
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
                                                <select class="w-100 form-select edivi__input-check" name="d_gcs_3" id="d_gcs_3" required autocomplete="off">
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
                                        <div class="col-2"><small class="fw-bold">(6)</small></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4 edivi__description">Extremitätenbewegung</div>
                                <div class="col">
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
                            <div class="mb-3"></div>
                        </div>
                    </div>
                    <div class="row edivi__box">
                        <div class="col">
                            <!-- ------------ -->
                            <!-- EXPOSURE -->
                            <!-- ------------ -->
                            <h5 class="text-light p-1">E - Entkleiden/Erweitern <em>(Exposure)</em></h5>
                            <div class="row my-2">
                                <div class="col-4 edivi__description">Blutzucker</div>
                                <div class="col"><input class="w-100 vitalparam form-control" type="text" name="bz" id="bz" value="<?= $daten['bz'] ?>" style="display:inline; max-width: 75px"> <small>mg/dl</small></div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description">Temperatur</div>
                                <div class="col"><input class="w-100 vitalparam form-control" type="text" name="temp" id="temp" value="<?= $daten['temp'] ?>" style="display:inline; max-width: 75px"> <small>°C</small></div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description">Kopf</div>
                                <div class="col edivi__verletzungen-col">
                                    <?php
                                    if ($daten['v_muster_k'] === NULL) {
                                    ?>
                                        <select name="v_muster_k" id="v_muster_k" class="w-100 edivi__verletzungen form-select edivi__input-check" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0">schwer</option>
                                            <option value="1">mittel</option>
                                            <option value="2">leicht</option>
                                            <option value="3">keine</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="v_muster_k" id="v_muster_k" class="w-100 edivi__verletzungen form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected>---</option>
                                            <option value="0" <?php echo ($daten['v_muster_k'] == 0 ? 'selected' : '') ?>>schwer</option>
                                            <option value="1" <?php echo ($daten['v_muster_k'] == 1 ? 'selected' : '') ?>>mittel</option>
                                            <option value="2" <?php echo ($daten['v_muster_k'] == 2 ? 'selected' : '') ?>>leicht</option>
                                            <option value="3" <?php echo ($daten['v_muster_k'] == 3 ? 'selected' : '') ?>>keine</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-3 ms-1">
                                    <?php if ($daten['v_muster_k1'] === NULL) {
                                    ?>
                                        <select name="v_muster_k1" id="v_muster_k1" class="w-100 form-select">
                                            <option value="0" selected>---</option>
                                            <option value="1">offen</option>
                                            <option value="2">geschl.</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="v_muster_k1" id="v_muster_k1" class="w-100 form-select" autocomplete="off">
                                            <option value="0" <?php echo ($daten['v_muster_k1'] == 0 ? 'selected' : '') ?>>---</option>
                                            <option value="1" <?php echo ($daten['v_muster_k1'] == 1 ? 'selected' : '') ?>>offen</option>
                                            <option value="2" <?php echo ($daten['v_muster_k1'] == 2 ? 'selected' : '') ?>>geschl.</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description">Wirbelsäule</div>
                                <div class="col edivi__verletzungen-col">
                                    <?php
                                    if ($daten['v_muster_w'] === NULL) {
                                    ?>
                                        <select name="v_muster_w" id="v_muster_w" class="w-100 edivi__verletzungen form-select edivi__input-check" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0">schwer</option>
                                            <option value="1">mittel</option>
                                            <option value="2">leicht</option>
                                            <option value="3">keine</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="v_muster_w" id="v_muster_w" class="w-100 edivi__verletzungen form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected>---</option>
                                            <option value="0" <?php echo ($daten['v_muster_w'] == 0 ? 'selected' : '') ?>>schwer</option>
                                            <option value="1" <?php echo ($daten['v_muster_w'] == 1 ? 'selected' : '') ?>>mittel</option>
                                            <option value="2" <?php echo ($daten['v_muster_w'] == 2 ? 'selected' : '') ?>>leicht</option>
                                            <option value="3" <?php echo ($daten['v_muster_w'] == 3 ? 'selected' : '') ?>>keine</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-3 ms-1">
                                    <?php if ($daten['v_muster_w1'] === NULL) {
                                    ?>
                                        <select name="v_muster_w1" id="v_muster_w1" class="w-100 form-select">
                                            <option value="0" selected>---</option>
                                            <option value="1">offen</option>
                                            <option value="2">geschl.</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="v_muster_w1" id="v_muster_w1" class="w-100 form-select" autocomplete="off">
                                            <option value="0" <?php echo ($daten['v_muster_w1'] == 0 ? 'selected' : '') ?>>---</option>
                                            <option value="1" <?php echo ($daten['v_muster_w1'] == 1 ? 'selected' : '') ?>>offen</option>
                                            <option value="2" <?php echo ($daten['v_muster_w1'] == 2 ? 'selected' : '') ?>>geschl.</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description">Thorax</div>
                                <div class="col edivi__verletzungen-col">
                                    <?php
                                    if ($daten['v_muster_t'] === NULL) {
                                    ?>
                                        <select name="v_muster_t" id="v_muster_t" class="w-100 edivi__verletzungen form-select edivi__input-check" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0">schwer</option>
                                            <option value="1">mittel</option>
                                            <option value="2">leicht</option>
                                            <option value="3">keine</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="v_muster_t" id="v_muster_t" class="w-100 edivi__verletzungen form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected>---</option>
                                            <option value="0" <?php echo ($daten['v_muster_t'] == 0 ? 'selected' : '') ?>>schwer</option>
                                            <option value="1" <?php echo ($daten['v_muster_t'] == 1 ? 'selected' : '') ?>>mittel</option>
                                            <option value="2" <?php echo ($daten['v_muster_t'] == 2 ? 'selected' : '') ?>>leicht</option>
                                            <option value="3" <?php echo ($daten['v_muster_t'] == 3 ? 'selected' : '') ?>>keine</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-3 ms-1">
                                    <?php if ($daten['v_muster_t1'] === NULL) {
                                    ?>
                                        <select name="v_muster_t1" id="v_muster_t1" class="w-100 form-select">
                                            <option value="0" selected>---</option>
                                            <option value="1">offen</option>
                                            <option value="2">geschl.</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="v_muster_t1" id="v_muster_t1" class="w-100 form-select" autocomplete="off">
                                            <option value="0" <?php echo ($daten['v_muster_t1'] == 0 ? 'selected' : '') ?>>---</option>
                                            <option value="1" <?php echo ($daten['v_muster_t1'] == 1 ? 'selected' : '') ?>>offen</option>
                                            <option value="2" <?php echo ($daten['v_muster_t1'] == 2 ? 'selected' : '') ?>>geschl.</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description">Abdomen</div>
                                <div class="col edivi__verletzungen-col">
                                    <?php
                                    if ($daten['v_muster_a'] === NULL) {
                                    ?>
                                        <select name="v_muster_a" id="v_muster_a" class="w-100 edivi__verletzungen form-select edivi__input-check" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0">schwer</option>
                                            <option value="1">mittel</option>
                                            <option value="2">leicht</option>
                                            <option value="3">keine</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="v_muster_a" id="v_muster_a" class="w-100 edivi__verletzungen form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected>---</option>
                                            <option value="0" <?php echo ($daten['v_muster_a'] == 0 ? 'selected' : '') ?>>schwer</option>
                                            <option value="1" <?php echo ($daten['v_muster_a'] == 1 ? 'selected' : '') ?>>mittel</option>
                                            <option value="2" <?php echo ($daten['v_muster_a'] == 2 ? 'selected' : '') ?>>leicht</option>
                                            <option value="3" <?php echo ($daten['v_muster_a'] == 3 ? 'selected' : '') ?>>keine</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-3 ms-1">
                                    <?php if ($daten['v_muster_a1'] === NULL) {
                                    ?>
                                        <select name="v_muster_a1" id="v_muster_a1" class="w-100 form-select">
                                            <option value="0" selected>---</option>
                                            <option value="1">offen</option>
                                            <option value="2">geschl.</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="v_muster_a1" id="v_muster_a1" class="w-100 form-select" autocomplete="off">
                                            <option value="0" <?php echo ($daten['v_muster_a1'] == 0 ? 'selected' : '') ?>>---</option>
                                            <option value="1" <?php echo ($daten['v_muster_a1'] == 1 ? 'selected' : '') ?>>offen</option>
                                            <option value="2" <?php echo ($daten['v_muster_a1'] == 2 ? 'selected' : '') ?>>geschl.</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description">Obere Extremitäten</div>
                                <div class="col edivi__verletzungen-col">
                                    <?php
                                    if ($daten['v_muster_al'] === NULL) {
                                    ?>
                                        <select name="v_muster_al" id="v_muster_al" class="w-100 edivi__verletzungen form-select edivi__input-check" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0">schwer</option>
                                            <option value="1">mittel</option>
                                            <option value="2">leicht</option>
                                            <option value="3">keine</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="v_muster_al" id="v_muster_al" class="w-100 edivi__verletzungen form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected>---</option>
                                            <option value="0" <?php echo ($daten['v_muster_al'] == 0 ? 'selected' : '') ?>>schwer</option>
                                            <option value="1" <?php echo ($daten['v_muster_al'] == 1 ? 'selected' : '') ?>>mittel</option>
                                            <option value="2" <?php echo ($daten['v_muster_al'] == 2 ? 'selected' : '') ?>>leicht</option>
                                            <option value="3" <?php echo ($daten['v_muster_al'] == 3 ? 'selected' : '') ?>>keine</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-3 ms-1">
                                    <?php if ($daten['v_muster_al1'] === NULL) {
                                    ?>
                                        <select name="v_muster_al1" id="v_muster_al1" class="w-100 form-select">
                                            <option value="0" selected>---</option>
                                            <option value="1">offen</option>
                                            <option value="2">geschl.</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="v_muster_al1" id="v_muster_al1" class="w-100 form-select" autocomplete="off">
                                            <option value="0" <?php echo ($daten['v_muster_al1'] == 0 ? 'selected' : '') ?>>---</option>
                                            <option value="1" <?php echo ($daten['v_muster_al1'] == 1 ? 'selected' : '') ?>>offen</option>
                                            <option value="2" <?php echo ($daten['v_muster_al1'] == 2 ? 'selected' : '') ?>>geschl.</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description">Untere Extremitäten</div>
                                <div class="col edivi__verletzungen-col">
                                    <?php
                                    if ($daten['v_muster_bl'] === NULL) {
                                    ?>
                                        <select name="v_muster_bl" id="v_muster_bl" class="w-100 edivi__verletzungen form-select edivi__input-check" required>
                                            <option disabled hidden selected>---</option>
                                            <option value="0">schwer</option>
                                            <option value="1">mittel</option>
                                            <option value="2">leicht</option>
                                            <option value="3">keine</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="v_muster_bl" id="v_muster_bl" class="w-100 edivi__verletzungen form-select edivi__input-check" required autocomplete="off">
                                            <option disabled hidden selected>---</option>
                                            <option value="0" <?php echo ($daten['v_muster_bl'] == 0 ? 'selected' : '') ?>>schwer</option>
                                            <option value="1" <?php echo ($daten['v_muster_bl'] == 1 ? 'selected' : '') ?>>mittel</option>
                                            <option value="2" <?php echo ($daten['v_muster_bl'] == 2 ? 'selected' : '') ?>>leicht</option>
                                            <option value="3" <?php echo ($daten['v_muster_bl'] == 3 ? 'selected' : '') ?>>keine</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-3 ms-1">
                                    <?php if ($daten['v_muster_bl1'] === NULL) {
                                    ?>
                                        <select name="v_muster_bl1" id="v_muster_bl1" class="w-100 form-select">
                                            <option value="0" selected>---</option>
                                            <option value="1">offen</option>
                                            <option value="2">geschl.</option>
                                        </select>
                                    <?php
                                    } else {
                                    ?>
                                        <select name="v_muster_bl1" id="v_muster_bl1" class="w-100 form-select" autocomplete="off">
                                            <option value="0" <?php echo ($daten['v_muster_bl1'] == 0 ? 'selected' : '') ?>>---</option>
                                            <option value="1" <?php echo ($daten['v_muster_bl1'] == 1 ? 'selected' : '') ?>>offen</option>
                                            <option value="2" <?php echo ($daten['v_muster_bl1'] == 2 ? 'selected' : '') ?>>geschl.</option>
                                        </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description">Schmerzen</div>
                                <div class="col">
                                    <input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="11" <?php echo ($daten['sz_nrs'] == 11 ? 'checked' : '') ?>> nicht erhoben
                                </div>
                                <div class="col">
                                    <input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="13" <?php echo ($daten['sz_nrs'] == 13 ? 'checked' : '') ?>> nicht beurteilbar
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4"></div>
                                <div class="col"><input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="12" <?php echo ($daten['sz_nrs'] == 12 ? 'checked' : '') ?>> 0</div>
                                <div class="col"><input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="1" <?php echo ($daten['sz_nrs'] == 1 ? 'checked' : '') ?>> 1</div>
                                <div class="col"><input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="2" <?php echo ($daten['sz_nrs'] == 2 ? 'checked' : '') ?>> 2</div>
                                <div class="col"><input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="3" <?php echo ($daten['sz_nrs'] == 3 ? 'checked' : '') ?>> 3</div>
                                <div class="col"><input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="4" <?php echo ($daten['sz_nrs'] == 4 ? 'checked' : '') ?>> 4</div>
                                <div class="col"><input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="5" <?php echo ($daten['sz_nrs'] == 5 ? 'checked' : '') ?>> 5</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4"></div>
                                <div class="col"><input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="6" <?php echo ($daten['sz_nrs'] == 6 ? 'checked' : '') ?>> 6</div>
                                <div class="col"><input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="7" <?php echo ($daten['sz_nrs'] == 7 ? 'checked' : '') ?>> 7</div>
                                <div class="col"><input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="8" <?php echo ($daten['sz_nrs'] == 8 ? 'checked' : '') ?>> 8</div>
                                <div class="col"><input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="9" <?php echo ($daten['sz_nrs'] == 9 ? 'checked' : '') ?>> 9</div>
                                <div class="col"><input class="form-check-input" type="radio" name="sz_nrs" id="sz_nrs" value="10" <?php echo ($daten['sz_nrs'] == 10 ? 'checked' : '') ?>> 10</div>
                                <div class="col"></div>
                            </div>
                            <div class="row my-2">
                                <div class="col-4 edivi__description"></div>
                                <div class="col">
                                    <div class="row">
                                        <div class="col">
                                            <input type="checkbox" class="btn-check" id="sz_toleranz_1" name="sz_toleranz_1" value="1" <?php echo ($daten['sz_toleranz_1'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                            <label class="btn btn-sm btn-outline-light w-100" for="sz_toleranz_1">Tolerabel</label>
                                        </div>
                                        <div class="col">
                                            <input type="checkbox" class="btn-check" id="sz_toleranz_2" name="sz_toleranz_2" value="1" <?php echo ($daten['sz_toleranz_2'] == 1 ? 'checked' : '') ?> autocomplete="off">
                                            <label class="btn btn-sm btn-outline-light w-100" for="sz_toleranz_2">Nicht tolerabel</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row edivi__box">
                        <div class="col">
                            <!-- ------------ -->
                            <!-- MEDIKAMENTE -->
                            <!-- ------------ -->
                            <h5 class="text-light p-1">Medikamente <small style="font-size:.7em">(Wirkstoff - Dosierung - Dareichungsform)</small></h5>
                            <div class="row my-2">
                                <div class="col">
                                    <textarea name="medis" id="medis" rows="10" class="w-100 form-control" placeholder="..." style="resize: none"><?= $daten['medis'] ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="row edivi__box">
                        <div class="col">
                            <!-- ------------ -->
                            <!-- DIAGNOSE -->
                            <!-- ------------ -->
                            <h5 class="text-light p-1">Verdachts-/Erstdiagnose</h5>
                            <div class="row my-2">
                                <div class="col">
                                    <textarea name="diagnose" id="diagnose" rows="3" class="w-100 form-control" style="resize: none" placeholder="..."><?= $daten['diagnose'] ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row edivi__box">
                        <div class="col">
                            <!-- ------------ -->
                            <!-- SONSTIGES -->
                            <!-- ------------ -->
                            <h5 class="text-light p-1">Notfallsituation, SAMPLER(+S), Bemerkungen</h5>
                            <div class="row my-2">
                                <div class="col">
                                    <textarea name="anmerkungen" id="anmerkungen" rows="20" class="w-100 form-control" style="resize: none" placeholder="..."><?= $daten['anmerkungen'] ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row edivi__box">
                        <div class="col">
                            <div class="row mt-3">
                                <div class="col-3 fw-bold">Protokollant</div>
                                <div class="col"><input type="text" name="pfname" id="pfname" class="w-100 form-control edivi__input-check" value="<?= $daten['pfname'] ?>" required></div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-3 fw-bold">Rettungsmittel</div>
                                <div class="col-3">
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
                                    <input type="text" name="fzg_transp_perso" id="fzg_transp_perso" class="w-100 form-control" placeholder="Personal" value="<?= $daten['fzg_transp_perso'] ?>">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-3 fw-bold"></div>
                                <div class="col-3">
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
                                    <input type="text" name="fzg_na_perso" id="fzg_na_perso" class="w-100 form-control" placeholder="Personal" value="<?= $daten['fzg_na_perso'] ?>">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-3 fw-bold"></div>
                                <div class="col">
                                    <input type="text" name="fzg_sonst" id="fzg_sonst" class="w-100 form-control" placeholder="Weitere Rettungsmittel" value="<?= $daten['fzg_sonst'] ?>">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-3 fw-bold">Transportart/-ziel</div>
                                <div class="col">
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
                            <?php if (!$ist_freigegeben) : ?>
                                <div class="row mt-3 mb-2">
                                    <div class="col">
                                        <input class="btn btn-success btn-sm w-100" name="submit" type="submit" value="Protokoll zwischenspeichern" />
                                    </div>
                                </div>
                                <div class="row mt-2 mb-2">
                                    <div class="col">
                                        <button class="btn btn-dark btn-sm w-100" type="button" data-bs-toggle="modal" data-bs-target="#myModal4">Protokoll absenden</button>
                                    </div>
                                </div>
                            <?php endif; ?>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // eDIVI Buttons
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
        // eDIVI Verletzungen
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateArrow(input) {
                const value = parseFloat(input.value);
                const minAttr = input.dataset.min;
                const maxAttr = input.dataset.max;
                const min = minAttr !== undefined ? parseFloat(minAttr) : null;
                const max = maxAttr !== undefined ? parseFloat(maxAttr) : null;

                const wrapper = input.closest('.vital-wrapper');
                const arrowSpan = wrapper?.querySelector('.arrow-indicator');

                if (!arrowSpan) return;

                if (!isNaN(value)) {
                    if (min !== null && !isNaN(min) && value < min) {
                        arrowSpan.innerHTML = '<i title="<?php echo __('edivi.protocol.too_low'); ?>" class="las la-caret-down"></i>';
                    } else if (max !== null && !isNaN(max) && value > max) {
                        arrowSpan.innerHTML = '<i title="<?php echo __('edivi.protocol.too_high'); ?>" class="las la-caret-up"></i>';
                    } else {
                        arrowSpan.textContent = '';
                    }
                } else {
                    arrowSpan.textContent = '';
                }
            }


            document.querySelectorAll('.vitalparam').forEach(function(input) {
                updateArrow(input); // On load

                input.addEventListener('input', function() {
                    updateArrow(input); // On input
                });
            });
        });
    </script>
</body>

</html>