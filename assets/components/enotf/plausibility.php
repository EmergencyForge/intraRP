<?php
if ($daten['patsex'] === NULL) {
    echo '[1] Stammdaten: Patienten-Geschlecht ist nicht gesetzt.<br>';
}

if (empty($daten['edatum'])) {
    echo '[1] Stammdaten: Einsatzdatum ist nicht gesetzt.<br>';
}

if (empty($daten['ezeit'])) {
    echo '[1] Stammdaten: Einsatzzeit ist nicht gesetzt.<br>';
}

if (empty($daten['eort'])) {
    echo '[1] Stammdaten: Einsatzort ist nicht gesetzt.<br>';
}

if (empty($daten['awfrei_1']) && empty($daten['awfrei_2']) && empty($daten['awfrei_3'])) {
    echo '[3] Atemwege: Atemwege ist nicht gesetzt.<br>';
}

if (empty($daten['zyanose_1']) && empty($daten['zyanose_2'])) {
    echo '[3] Atemwege: Zyanose ist nicht gesetzt.<br>';
}

if ($daten['awsicherung_neu'] === NULL) {
    echo '[3] Atemwege: Atemwegssicherung ist nicht gesetzt.<br>';
}

if ($daten['b_symptome'] === NULL) {
    echo '[4] Atmung: Symptomauswahl ist nicht gesetzt.<br>';
}

if ($daten['b_auskult'] === NULL) {
    echo '[4] Atmung: Auskultation ist nicht gesetzt.<br>';
}

if ($daten['b_beatmung'] === NULL) {
    echo '[4] Atmung: Beatmung ist nicht gesetzt.<br>';
}

if ($daten['c_kreislauf'] === NULL) {
    echo '[5] Kreislauf: Patientenzustand ist nicht gesetzt.<br>';
}

if ($daten['c_ekg'] === NULL) {
    echo '[5] Kreislauf: EKG ist nicht gesetzt.<br>';
}

if ($daten['d_bewusstsein'] === NULL) {
    echo '[6] Neurologie: Bewusstseinslage ist nicht gesetzt.<br>';
}

if ($daten['d_ex_1'] === NULL) {
    echo '[6] Neurologie: Extremitätenbewegung ist nicht gesetzt.<br>';
}

if ($daten['d_pupillenw_1'] === NULL || $daten['d_lichtreakt_1'] === NULL) {
    echo '[6] Neurologie: Pupillen links sind nicht gesetzt.<br>';
}

if ($daten['d_pupillenw_2'] === NULL || $daten['d_lichtreakt_2'] === NULL) {
    echo '[6] Neurologie: Pupillen rechts sind nicht gesetzt.<br>';
}

if ($daten['d_gcs_1'] === NULL || $daten['d_gcs_2'] === NULL || $daten['d_gcs_3'] === NULL) {
    echo '[6] Neurologie: GCS ist nicht gesetzt.<br>';
}

if ($daten['v_muster_k'] === NULL || $daten['v_muster_t'] === NULL || $daten['v_muster_a'] === NULL || $daten['v_muster_al'] === NULL || $daten['v_muster_bl'] === NULL || $daten['v_muster_w'] === NULL) {
    echo '[7] Erweitern: Verletzungen sind nicht gesetzt.<br>';
}

if ($daten['transportziel'] === NULL) {
    echo 'Abschluss: Transportziel ist nicht gesetzt.<br>';
}

if (empty($daten['pfname'])) {
    echo 'Abschluss: Kein Protokollant gesetzt.<br>';
}

if ($daten['prot_by'] === NULL) {
    echo 'Abschluss: Keine Protokollart gesetzt.<br>';
}
