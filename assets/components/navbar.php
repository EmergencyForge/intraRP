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
                <a class="nav-link" href="/admin/index.php" data-page="dashboard"><i class="las la-home" style="margin-right:3px"></i> <?= __('navbar.dashboard') ?></a>
                <?php if (Permissions::check(['admin', 'users.view'])) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-page="benutzer" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-user-secret" style="margin-right:3px"></i> <?= __('navbar.users') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/users/list.php"><?= __('navbar.users_overview') ?></a></li>
                            <?php if (Permissions::check(['admin', 'users.create'])) { ?>
                                <li><a class="dropdown-item" href="/admin/users/create.php"><?= __('navbar.users_create') ?></a></li>
                            <?php } ?>
                            <div class="dropdown-divider"></div>
                            <?php if (Permissions::check(['admin', 'audit.view'])) { ?>
                                <li><a class="dropdown-item" href="/admin/users/auditlog.php"><?= __('navbar.audit_log') ?></a></li>
                            <?php } ?>
                            <li><a class="dropdown-item" href="/admin/users/roles/index.php"><?= __('navbar.rolemanagement') ?></a></li>
                        </ul>
                    </li>
                <?php }
                if (Permissions::check(['admin', 'personnel.view'])) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-page="mitarbeiter" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-suitcase" style="margin-right:3px"></i> <?= __('navbar.personnel') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/personal/list.php"><?= __('navbar.personnel_overview') ?></a></li>
                            <?php if (Permissions::check(['admin', 'personnel.edit'])) { ?>
                                <li><a class="dropdown-item" href="/admin/personal/create.php"><?= __('navbar.personnel_create') ?></a></li>
                            <?php } ?>
                            <div class="dropdown-divider"></div>
                            <li><a class="dropdown-item" href="/admin/personal/management/dienstgrade/index.php"><?= __('navbar.manage_ranks') ?></a></li>
                            <li><a class="dropdown-item" href="/admin/personal/management/qualifw/index.php"><?= __('navbar.manage_fwqualifications') ?></a></li>
                            <li><a class="dropdown-item" href="/admin/personal/management/qualird/index.php"><?= __('navbar.manage_rdqualifications') ?></a></li>
                        </ul>
                    </li>
                <?php } ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-page="edivi" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="las la-newspaper" style="margin-right:3px"></i> <?= __('navbar.edivi') ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/edivi/protokoll.php" target="_blank"><?= __('navbar.new_protocol') ?></a></li>
                        <?php if (Permissions::check(['admin', 'edivi.view'])) { ?>
                            <li><a class="dropdown-item" href="/admin/edivi/list.php"><?= __('navbar.quality_assurance') ?></a></li>
                            <div class="dropdown-divider"></div>
                            <li><a class="dropdown-item" href="/admin/edivi/management/fahrzeuge/index.php"><?= __('navbar.vehicle_management') ?></a></li>
                            <li><a class="dropdown-item" href="/admin/edivi/management/ziele/index.php"><?= __('navbar.target_management') ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php if (Permissions::check(['admin', 'application.view'])) { ?>
                    <li class="nav-item"><a href="/admin/antraege/list.php" class="nav-link" data-page="antrag"><i class="las la-code-branch" style="margin-right:3px"></i> <?= __('navbar.application') ?></a></li>
                <?php }
                if (Permissions::check(['admin', 'files.upload'])) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-page="upload" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="las la-upload" style="margin-right:3px"></i> <?= __('navbar.files') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/upload/index.php"><?= __('navbar.files_upload') ?></a></li>
                            <?php if (Permissions::check(['admin', 'files.log.view'])) { ?>
                                <li><a class="dropdown-item" href="/admin/upload/overview.php"><?= __('navbar.files_log') ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                <li class="nav-item dropdown" id="intra-usermenu">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= $_SESSION['cirs_username'] ?> <span class="badge text-bg-<?= $_SESSION['role_color'] ?>"><?= $_SESSION['role_name'] ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/admin/users/editprofile.php"><?= __('navbar.edit_profile') ?></a></li>
                        <?php if (Permissions::check(['admin', 'dashboard.manage'])) { ?>
                            <li><a class="dropdown-item" href="/admin/settings/dashboard/index.php"><?= __('navbar.manage_dashboard') ?></a></li>
                        <?php } ?>
                        <li><a class="dropdown-item" href="/admin/logout.php"><?= __('navbar.logout') ?></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<script>
    $(document).ready(function() {
        var currentPage = $("body").data("page");
        $(".nav-link").removeClass("active");
        $(".nav-link[data-page='" + currentPage + "']").addClass("active");
    });

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
</script>