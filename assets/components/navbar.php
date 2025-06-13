<?php

use App\Auth\Permissions; ?>
<nav class="navbar navbar-expand-lg" id="intra-nav">
    <div class="container">
        <a class="navbar-brand" href="#"><img src="<?php echo SYSTEM_LOGO ?>" alt="<?php echo SYSTEM_NAME ?>" style="height:36px;width:auto"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <a class="nav-link" href="<?= BASE_PATH ?>admin/index.php" data-page="dashboard"><i class="las la-home" style="margin-right:3px"></i> Dashboard</a>
                <?php if (Permissions::check(['admin', 'users.view'])) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-page="benutzer" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-user-secret" style="margin-right:3px"></i> Benutzer
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/users/list.php">Übersicht</a></li>
                            <div class="dropdown-divider"></div>
                            <?php if (Permissions::check(['admin', 'audit.view'])) { ?>
                                <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/users/auditlog.php">Audit-Log</a></li>
                            <?php } ?>
                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/users/roles/index.php">Rollenverwaltung</a></li>
                        </ul>
                    </li>
                <?php }
                if (Permissions::check(['admin', 'personnel.view'])) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-page="mitarbeiter" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-suitcase" style="margin-right:3px"></i> Personal
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/personal/list.php">Übersicht</a></li>
                            <?php if (Permissions::check(['admin', 'personnel.edit'])) { ?>
                                <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/personal/create.php">Erstellen</a></li>
                            <?php } ?>
                            <div class="dropdown-divider"></div>
                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/personal/management/dienstgrade/index.php">Dienstgrade verwalten</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/personal/management/qualifw/index.php">FW Qualifikationen verwalten</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/personal/management/qualird/index.php">RD Qualifikationen verwalten</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/personal/management/qualifd/index.php">Fachdienste verwalten</a></li>
                        </ul>
                    </li>
                <?php } ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-page="edivi" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="las la-newspaper" style="margin-right:3px"></i> RD Protokolle
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_PATH ?>enotf/" target="_blank">Neues Protokoll</a></li>
                        <?php if (Permissions::check(['admin', 'edivi.view'])) { ?>
                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/enotf/list.php">Qualitätsmanagement</a></li>
                            <div class="dropdown-divider"></div>
                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/enotf/management/fahrzeuge/index.php">Fahrzeugverwaltung</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/enotf/management/ziele/index.php">Zielverwaltung</a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php if (Permissions::check(['admin', 'application.view'])) { ?>
                    <li class="nav-item"><a href="<?= BASE_PATH ?>admin/antraege/list.php" class="nav-link" data-page="antrag"><i class="las la-code-branch" style="margin-right:3px"></i> Anträge</a></li>
                <?php }
                if (Permissions::check(['admin', 'files.upload'])) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-page="upload" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-upload" style="margin-right:3px"></i> Dateien
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/upload/index.php">Datei hochladen</a></li>
                            <?php if (Permissions::check(['admin', 'files.log.view'])) { ?>
                                <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/upload/overview.php">Verlauf</a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                <li class="nav-item dropdown" id="intra-usermenu">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= $_SESSION['cirs_username'] ?> <span class="badge text-bg-<?= $_SESSION['role_color'] ?>"><?= $_SESSION['role_name'] ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/users/editprofile.php">Profil bearbeiten</a></li>
                        <?php if (Permissions::check(['admin', 'dashboard.manage'])) { ?>
                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/settings/dashboard/index.php">Dashboard-Konfiguration</a></li>
                        <?php } ?>
                        <li><a class="dropdown-item" href="<?= BASE_PATH ?>admin/logout.php">Abmelden</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<script>
    $(document).ready(function() {
        var currentPage = $("body").data("page");

        // Remove active class from all nav-links
        $(".nav-link").removeClass("active");

        // Add active class to the appropriate nav-link
        $(".nav-link[data-page='" + currentPage + "']").addClass("active");
    });

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
</script>