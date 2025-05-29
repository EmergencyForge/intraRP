<div class="col-1 d-flex flex-column" id="edivi__nidanav">
    <?php if ($daten['patsex'] !== NULL && !empty($daten['eort']) && !empty($daten['ezeit']) && !empty($daten['eort'])) : ?>
        <a href="/enotf/prot/stammdaten.php?enr=<?= $daten['enr'] ?>" data-page="stammdaten" class="edivi__nidanav-filled"><span>Stammdaten</span></a>
    <?php else : ?>
        <a href="/enotf/prot/stammdaten.php?enr=<?= $daten['enr'] ?>" data-page="stammdaten"><span>Stammdaten</span></a>
    <?php endif; ?>
    <a href="/enotf/prot/anamnese.php?enr=<?= $daten['enr'] ?>" data-page="anamnese" class="edivi__nidanav-nocheck"><span>Anamnese</span></a>
    <?php if ((!empty($daten['awfrei_1']) || !empty($daten['awfrei_2']) || !empty($daten['awfrei_3'])) && $daten['awsicherung_neu'] !== NULL && (!empty($daten['zyanose_1']) || !empty($daten['zyanose_2']))) : ?>
        <a href="/enotf/prot/atemwege.php?enr=<?= $daten['enr'] ?>" data-page="atemwege" class="edivi__nidanav-filled"><span>Atemwege</span></a>
    <?php else : ?>
        <a href="/enotf/prot/atemwege.php?enr=<?= $daten['enr'] ?>" data-page="atemwege"><span>Atemwege</span></a>
    <?php endif; ?>
    <?php if ($daten['b_symptome'] !== NULL && $daten['b_auskult'] !== NULL && $daten['b_beatmung'] !== NULL) : ?>
        <a href="/enotf/prot/atmung.php?enr=<?= $daten['enr'] ?>" data-page="atmung" class="edivi__nidanav-filled"><span>Atmung</span></a>
    <?php else : ?>
        <a href="/enotf/prot/atmung.php?enr=<?= $daten['enr'] ?>" data-page="atmung"><span>Atmung</span></a>
    <?php endif; ?>
    <?php if ($daten['c_kreislauf'] !== NULL && $daten['c_ekg'] !== NULL) : ?>
        <a href="/enotf/prot/kreislauf.php?enr=<?= $daten['enr'] ?>" data-page="kreislauf" class="edivi__nidanav-filled"><span>Kreislauf</span></a>
    <?php else : ?>
        <a href="/enotf/prot/kreislauf.php?enr=<?= $daten['enr'] ?>" data-page="kreislauf"><span>Kreislauf</span></a>
    <?php endif; ?>
    <?php if ($daten['d_bewusstsein'] !== NULL && $daten['d_ex_1'] !== NULL && $daten['d_pupillenw_1'] !== NULL && $daten['d_pupillenw_2'] !== NULL && $daten['d_lichtreakt_1'] !== NULL && $daten['d_lichtreakt_2'] !== NULL && $daten['d_gcs_1'] !== NULL && $daten['d_gcs_2'] !== NULL && $daten['d_gcs_3'] !== NULL) : ?>
        <a href="/enotf/prot/neurologie.php?enr=<?= $daten['enr'] ?>" data-page="neurologie" class="edivi__nidanav-filled"><span>Neurologie</span></a>
    <?php else : ?>
        <a href="/enotf/prot/neurologie.php?enr=<?= $daten['enr'] ?>" data-page="neurologie"><span>Neurologie</span></a>
    <?php endif; ?>
    <?php if ($daten['v_muster_k'] !== NULL && $daten['v_muster_t'] !== NULL && $daten['v_muster_a'] !== NULL && $daten['v_muster_al'] !== NULL && $daten['v_muster_bl'] !== NULL && $daten['v_muster_w'] !== NULL) : ?>
        <a href="/enotf/prot/erweitern.php?enr=<?= $daten['enr'] ?>" data-page="erweitern" class="edivi__nidanav-filled"><span>Erweitern</span></a>
    <?php else : ?>
        <a href="/enotf/prot/erweitern.php?enr=<?= $daten['enr'] ?>" data-page="erweitern"><span>Erweitern</span></a>
    <?php endif; ?>
    <?php if ($daten['transportziel'] !== NULL && !empty($daten['pfname'])) : ?>
        <a href="/enotf/prot/abschluss.php?enr=<?= $daten['enr'] ?>" data-page="abschluss" class="edivi__nidanav-filled"><span>Abschluss</span></a>
    <?php else : ?>
        <a href="/enotf/prot/abschluss.php?enr=<?= $daten['enr'] ?>" data-page="abschluss"><span>Abschluss</span></a>
    <?php endif; ?>
</div>

<script>
    $(document).ready(function() {
        var currentPage = $("body").data("page");

        $("#edivi__nidanav a").removeClass("active");

        $("#edivi__nidanav a[data-page='" + currentPage + "']").addClass("active");
    });
</script>