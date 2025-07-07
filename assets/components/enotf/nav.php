<div class="col-1 d-flex flex-column" id="edivi__nidanav">
    <a href="<?= BASE_PATH ?>enotf/prot/stammdaten.php?enr=<?= $daten['enr'] ?>" data-page="stammdaten"
        data-requires="patsex,eort,ezeit,eort">
        <span>Rett. Daten</span>
    </a>
    <a href="<?= BASE_PATH ?>enotf/prot/anamnese.php?enr=<?= $daten['enr'] ?>" data-page="anamnese" class="edivi__nidanav-nocheck"><span>Anamnese</span></a>
    <a href="<?= BASE_PATH ?>enotf/prot/atemwege.php?enr=<?= $daten['enr'] ?>" data-page="atemwege"
        data-requires="awfrei_1|awfrei_2|awfrei_3,awsicherung_neu,zyanose_1|zyanose_2">
        <span>Atemwege</span>
    </a>
    <a href="<?= BASE_PATH ?>enotf/prot/atmung.php?enr=<?= $daten['enr'] ?>" data-page="atmung"
        data-requires="b_symptome,b_auskult,b_beatmung">
        <span>Atmung</span>
    </a>
    <a href="<?= BASE_PATH ?>enotf/prot/kreislauf.php?enr=<?= $daten['enr'] ?>" data-page="kreislauf"
        data-requires="c_kreislauf,c_ekg">
        <span>Kreislauf</span>
    </a>
    <a href="<?= BASE_PATH ?>enotf/prot/neurologie.php?enr=<?= $daten['enr'] ?>" data-page="neurologie"
        data-requires="d_bewusstsein,d_ex_1,d_pupillenw_1,d_pupillenw_2,d_lichtreakt_1,d_lichtreakt_2,d_gcs_1,d_gcs_2,d_gcs_3">
        <span>Neurologie</span>
    </a>
    <a href="<?= BASE_PATH ?>enotf/prot/erweitern.php?enr=<?= $daten['enr'] ?>" data-page="erweitern"
        data-requires="v_muster_k,v_muster_t,v_muster_a,v_muster_al,v_muster_bl,v_muster_w">
        <span>Erweitern</span>
    </a>
    <a href="<?= BASE_PATH ?>enotf/prot/verlauf.php?enr=<?= $daten['enr'] ?>" data-page="verlauf"
        class="edivi__nidanav-nocheck edivi__nidanav-nonumber">
        <span>Verlauf</span>
    </a>
    <a href="<?= BASE_PATH ?>enotf/prot/abschluss.php?enr=<?= $daten['enr'] ?>" data-page="abschluss"
        data-requires="transportziel,pfname" class="edivi__nidanav-nonumber">
        <span>Abschluss</span>
    </a>
</div>

<script>
    $(document).ready(function() {
        const currentPage = $("body").data("page");
        $("#edivi__nidanav a").removeClass("active");
        $("#edivi__nidanav a[data-page='" + currentPage + "']").addClass("active");
    });
</script>