<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';

if (isset($_POST['enr']) && isset($_POST['field']) && isset($_POST['value'])) {
    $enr = $_POST['enr'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    $allowedFields = ['patname', 'patgebdat', 'patsex', 'edatum', 'ezeit', 'eort', 'awfrei_1', 'awfrei_2', 'awfrei_3', 'awsicherung_neu', 'zyanose_1', 'zyanose_2', 'o2gabe', 'b_symptome', 'b_auskult', 'b_beatmung', 'spo2', 'atemfreq', 'etco2', 'c_zugang_gr_1', 'c_zugang_gr_2', 'c_zugang_gr_3', 'c_zugang_art_1', 'c_zugang_art_2', 'c_zugang_art_3', 'c_zugang_ort_1', 'c_zugang_ort_2', 'c_zugang_ort_3', 'c_kreislauf', 'c_ekg', 'rrsys', 'rrdias', 'herzfreq', 'medis', 'd_bewusstsein', 'd_ex_1', 'd_pupillenw_1', 'd_pupillenw_2', 'd_lichtreakt_1', 'd_lichtreakt_2', 'd_gcs_1', 'd_gcs_2', 'd_gcs_3', 'v_muster_k', 'v_muster_k1', 'v_muster_t', 'v_muster_t1', 'v_muster_a', 'v_muster_a1', 'v_muster_al', 'v_muster_al1', 'v_muster_bl', 'v_muster_bl1', 'v_muster_w', 'v_muster_w1', 'sz_nrs', 'sz_toleranz_1', 'sz_toleranz_2', 'bz', 'temp', 'anmerkungen', 'diagnose', 'fzg_transp', 'fzg_transp_perso', 'fzg_transp_perso_2', 'fzg_na', 'fzg_na_perso', 'fzg_na_perso_2', 'fzg_sonst', 'transportziel', 'pfname', 'prot_by'];

    if ($field === 'freigeber') {
        if (empty($value)) {
            http_response_code(400);
            echo "Freigeber darf nicht leer sein.";
            exit();
        }

        $query = "UPDATE intra_edivi SET freigeber_name = :value, freigegeben = 1, last_edit = NOW() WHERE enr = :enr";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['value' => $value, 'enr' => $enr]);

        echo "Freigeber erfolgreich gespeichert und freigegeben.";
        exit();
    }

    if (in_array($field, $allowedFields)) {
        $query = "UPDATE intra_edivi SET $field = :value, last_edit = NOW() WHERE enr = :enr";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['value' => $value, 'enr' => $enr]);

        echo "Field updated";
    } else {
        http_response_code(400);
        echo "Invalid field";
    }
} else {
    http_response_code(400);
    echo "Missing data";
}
