<?php

use App\Auth\Permissions; ?>

<div class="cirs-nav">
    <h6>Anträge</h6>
    <div class="cirs-link">
        <a href="<?= BASE_PATH ?>antraege/befoerderung.php" class="text-decoration-none">Neuen Beförderungsantrag stellen </i></a>
    </div>
    <?php
    if (isset($_SESSION['userid']) && isset($_SESSION['permissions'])) {
        if (Permissions::check(['admin', 'application.view'])) { ?>
            <hr class="my-3">
            <h6>Verwaltung</h6>
            <div class="cirs-link mb-2">
                <a href="<?= BASE_PATH ?>admin/antraege/list.php" class="text-decoration-none">Übersicht</i></a>
            </div>
            <div class="cirs-link mb-2">
                <a href="<?= BASE_PATH ?>admin/index.php" class="text-decoration-none">Zurück zum Dashboard</i></a>
            </div>
            <div class="cirs-link mb-2">
                <a href="<?= BASE_PATH ?>admin/logout.php" class="text-decoration-none">Abmelden</a>
            </div>
        <?php }
    } else { ?>
        <div class="cirs-login">
            <a href="<?= BASE_PATH ?>admin/login.php" class="text-decoration-none"><i class="las la-user"></i> Login</a>
        </div>
    <?php } ?>
</div>