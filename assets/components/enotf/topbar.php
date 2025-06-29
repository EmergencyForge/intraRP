<?php

use App\Auth\Permissions;

?>

<div class="container-fluid" id="edivi__topbar">
    <div class="row">
        <div class="col d-flex align-items-center">
            <a href="<?= BASE_PATH ?>enotf/overview.php" id="home" class="edivi__iconlink">
                <i class="las la-home"></i><br>
                <small>Start</small>
            </a>

            <?php
            if ($daten['freigegeben'] != 1) :
                if (ENOTF_PREREG) : ?>
                    <a href="<?= BASE_PATH ?>enotf/schnittstelle/voranmeldung.php?enr=<?= $enr ?>" id="prereg" class="edivi__iconlink">
                        <i class="las la-hospital"></i><br>
                        <small>Voranmeldung</small>
                    </a>
                <?php endif; ?>

                <a href="<?= BASE_PATH ?>enotf/prot/update_type.php?enr=<?= $enr ?>" id="modify" class="edivi__iconlink">
                    <i class="las la-sync"></i><br>
                    <small>Protokollart ändern</small>
                </a>
            <?php endif; ?>

            <?php if (Permissions::check(['admin', 'edivi.edit'])) : ?>
                <a href="<?= BASE_PATH ?>admin/enotf/qm-actions.php?id=<?= $daten['id'] ?>" id="qma" target="_blank" class="edivi__iconlink">
                    <i class="las la-exclamation"></i><br>
                    <small>QM-Aktion</small>
                </a>
                <a href="<?= BASE_PATH ?>admin/enotf/qm-log.php?id=<?= $daten['id'] ?>" id="qml" target="_blank" class="edivi__iconlink">
                    <i class="las la-paperclip"></i><br>
                    <small>QM-Log</small>
                </a>
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