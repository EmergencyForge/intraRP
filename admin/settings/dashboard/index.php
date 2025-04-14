<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    header("Location: /admin/login.php");
    exit();
}

use App\Auth\Permissions;
use App\Helpers\Flash;
use App\Localization\Lang;

Lang::setLanguage(LANG ?? 'de');

if (!Permissions::check(['admin', 'dashboard.manage'])) {
    Flash::set('error', 'no-permissions');
    header("Location: /admin/index.php");
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= __('title', [SYSTEM_NAME]) ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/style.min.css" />
    <link rel="stylesheet" href="/assets/css/admin.min.css" />
    <link rel="stylesheet" href="/assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="/assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <script src="/vendor/components/jquery/jquery.min.js"></script>
    <script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/vendor/datatables.net/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="<?php echo SYSTEM_NAME ?>" />
    <link rel="manifest" href="/assets/favicon/site.webmanifest" />
    <!-- Metas -->
    <meta name="theme-color" content="<?php echo SYSTEM_COLOR ?>" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="https://<?php echo SYSTEM_URL ?>/dashboard.php" />
    <meta property="og:title" content="<?= __('metas.title', [SYSTEM_NAME, SERVER_CITY]) ?>" />
    <meta property="og:image" content="<?php echo META_IMAGE_URL ?>" />
    <meta property="og:description" content="<?= __('metas.description', [RP_ORGTYPE, SERVER_CITY]) ?>" />

</head>

<body data-bs-theme="dark">
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <hr class="text-light my-3">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <h1 class="mb-0"><?= __('settings.dashboard.title') ?></h1>
                        <div class="btn-group">
                            <a href="/dashboard.php" class="btn btn-outline-light me-2" target="_blank"><i class="las la-external-link-alt"></i> <?= __('settings.dashboard.view_dashboard') ?></a>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                                <i class="las la-plus"></i> <?= __('settings.dashboard.create_category') ?>
                            </button>
                        </div>
                    </div>
                    <?php
                    Flash::render();
                    ?>
                    <div class="intra__tile py-2 px-3">
                        <?php
                        require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/database.php';
                        $stmt = $pdo->prepare("SELECT * FROM intra_dashboard_categories ORDER BY priority ASC");
                        $stmt->execute();
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($result as $row) {
                            $stmt2 = $pdo->prepare("SELECT * FROM intra_dashboard_tiles WHERE category = :category_id ORDER BY priority ASC");
                            $stmt2->bindParam(':category_id', $row['id']);
                            $stmt2->execute();
                            $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                            <div class="mb-5">
                                <div class="row">
                                    <div class="col d-flex justify-content-between align-items-center mb-3">
                                        <h2><?= $row['title'] ?></h2>
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                class="btn btn-sm btn-primary me-2 edit-category-btn"
                                                data-id="<?= $row['id'] ?>"
                                                data-title="<?= htmlspecialchars($row['title']) ?>"
                                                data-priority="<?= $row['priority'] ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editCategoryModal">
                                                <i class="las la-pen"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-success create-tile-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#createTileModal"
                                                data-category="<?= $row['id'] ?>">
                                                <i class="las la-plus"></i> <?= __('settings.dashboard.new_link') ?>
                                            </button>

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <ol>
                                            <?php
                                            foreach ($result2 as $tile) {
                                                if ($tile['category'] == $row['id']) {
                                            ?>
                                                    <li class="d-flex justify-content-between align-items-center mb-4">
                                                        <h4><i class="<?= $tile['icon'] ?>"></i> <?= $tile['title'] ?></h4>
                                                        <button type="button"
                                                            class="btn btn-sm btn-primary edit-tile-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editTileModal"
                                                            data-id="<?= $tile['id'] ?>"
                                                            data-category="<?= $tile['category'] ?>"
                                                            data-title="<?= htmlspecialchars($tile['title']) ?>"
                                                            data-url="<?= htmlspecialchars($tile['url']) ?>"
                                                            data-icon="<?= htmlspecialchars($tile['icon']) ?>"
                                                            data-priority="<?= $tile['priority'] ?>">
                                                            <i class="las la-pen"></i> <?= __('settings.dashboard.edit_link') ?>
                                                        </button>

                                                    </li>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        <?php }
                        if ($stmt->rowCount() == 0) {
                            echo '<div class="alert alert-warning" role="alert">' . __('settings.dashboard.no_dashboard') . '</div>';
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- EDIT TILE MODAL -->
    <div class="modal fade" id="editTileModal" tabindex="-1" aria-labelledby="editTileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/admin/settings/dashboard/tiles/update.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTileModalLabel"><?= __('settings.dashboard.modals.edit_link.title') ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="id" id="tile-id">
                        <input type="hidden" name="category" id="tile-category">

                        <div class="mb-3">
                            <label for="tile-title" class="form-label"><?= __('settings.dashboard.modals.edit_link.link_title') ?></label>
                            <input type="text" class="form-control" name="title" id="tile-title" required>
                        </div>

                        <div class="mb-3">
                            <label for="tile-url" class="form-label"><?= __('settings.dashboard.modals.edit_link.url') ?></label>
                            <input type="text" class="form-control" name="url" id="tile-url" required>
                        </div>

                        <div class="mb-3">
                            <label for="tile-icon" class="form-label"><?= __('settings.dashboard.modals.edit_link.icon') ?></label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="icon" id="tile-icon" placeholder="las la-home, las la-info, las la-cog">
                                <span class="input-group-text"><i id="tile-icon-preview" class="las la-external-link-alt"></i></span>
                            </div>
                            <small class="form-text text-muted">
                                <a href="https://icons8.com/line-awesome" target="_blank"><?= __('settings.dashboard.modals.edit_link.view_icons') ?></a>
                            </small>
                            <div id="icon-suggestions" class="border mt-2 p-2 rounded" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                        </div>


                        <div class="mb-3">
                            <label for="tile-priority" class="form-label"><?= __('settings.dashboard.modals.edit_link.priority') ?></label>
                            <input type="number" class="form-control" name="priority" id="tile-priority" required>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-danger" id="delete-tile-btn"><?= __('settings.dashboard.modals.edit_link.delete') ?></button>

                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('settings.dashboard.modals.edit_link.close') ?></button>
                            <button type="submit" class="btn btn-primary"><?= __('settings.dashboard.modals.edit_link.save') ?></button>
                        </div>
                    </div>
                </form>

                <form id="delete-tile-form" action="/admin/settings/dashboard/tiles/delete.php" method="POST" style="display: none;">
                    <input type="hidden" name="id" id="delete-tile-id">
                </form>

            </div>
        </div>
    </div>
    <!-- END EDIT TILE MODAL -->
    <!-- CREATE TILE MODAL -->
    <div class="modal fade" id="createTileModal" tabindex="-1" aria-labelledby="createTileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/admin/settings/dashboard/tiles/create.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createTileModalLabel"><?= __('settings.dashboard.modals.create_link.title') ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="category" id="new-tile-category">

                        <div class="mb-3">
                            <label for="new-tile-title" class="form-label"><?= __('settings.dashboard.modals.create_link.link_title') ?></label>
                            <input type="text" class="form-control" name="title" id="new-tile-title" required>
                        </div>

                        <div class="mb-3">
                            <label for="new-tile-url" class="form-label"><?= __('settings.dashboard.modals.create_link.url') ?></label>
                            <input type="text" class="form-control" name="url" id="new-tile-url" required>
                        </div>

                        <div class="mb-3">
                            <label for="new-tile-icon" class="form-label"><?= __('settings.dashboard.modals.create_link.icon') ?></label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="icon" id="new-tile-icon" placeholder="las la-home, las la-info, las la-cog">
                                <span class="input-group-text"><i id="new-tile-icon-preview" class="las la-external-link-alt"></i></span>
                            </div>
                            <small class="form-text text-muted">
                                <a href="https://icons8.com/line-awesome" target="_blank"><?= __('settings.dashboard.modals.create_link.view_icons') ?></a>
                            </small>
                            <div id="new-icon-suggestions" class="border mt-2 p-2 rounded shadow-sm" style="max-height: 220px; overflow-y: auto; display: none;"></div>
                        </div>


                        <div class="mb-3">
                            <label for="new-tile-priority" class="form-label"><?= __('settings.dashboard.modals.create_link.priority') ?></label>
                            <input type="number" class="form-control" name="priority" id="new-tile-priority" value="0" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('settings.dashboard.modals.create_link.close') ?></button>
                        <button type="submit" class="btn btn-success"><?= __('settings.dashboard.modals.create_link.save') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END CREATE TILE MODAL -->
    <!-- EDIT CATEGORY MODAL -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/admin/settings/dashboard/categories/update.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCategoryModalLabel"><?= __('settings.dashboard.modals.edit_category.title') ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="id" id="category-id">

                        <div class="mb-3">
                            <label for="category-title" class="form-label"><?= __('settings.dashboard.modals.edit_category.category_title') ?></label>
                            <input type="text" class="form-control" name="title" id="category-title" required>
                        </div>

                        <div class="mb-3">
                            <label for="category-priority" class="form-label"><?= __('settings.dashboard.modals.edit_category.priority') ?></label>
                            <input type="number" class="form-control" name="priority" id="category-priority" required>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-danger" id="delete-category-btn"><?= __('settings.dashboard.modals.edit_category.delete') ?></button>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('settings.dashboard.modals.edit_category.close') ?></button>
                            <button type="submit" class="btn btn-primary"><?= __('settings.dashboard.modals.edit_category.save') ?></button>
                        </div>
                    </div>
                </form>

                <form id="delete-category-form" action="/admin/settings/dashboard/categories/delete.php" method="POST" style="display: none;">
                    <input type="hidden" name="id" id="delete-category-id">
                </form>
            </div>
        </div>
    </div>
    <!-- END EDIT CATEGORY MODAL -->
    <!-- CREATE CATEGORY MODAL -->
    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/admin/settings/dashboard/categories/create.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createCategoryModalLabel"><?= __('settings.dashboard.modals.create_category.title') ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="new-category-title" class="form-label"><?= __('settings.dashboard.modals.create_category.category_title') ?></label>
                            <input type="text" class="form-control" name="title" id="new-category-title" required>
                        </div>

                        <div class="mb-3">
                            <label for="new-category-priority" class="form-label"><?= __('settings.dashboard.modals.create_category.priority') ?></label>
                            <input type="number" class="form-control" name="priority" id="new-category-priority" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('settings.dashboard.modals.create_category.close') ?></button>
                        <button type="submit" class="btn btn-success"><?= __('settings.dashboard.modals.create_category.save') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END CREATE CATEGORY MODAL -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.edit-tile-btn').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('tile-id').value = this.dataset.id;
                    document.getElementById('tile-category').value = this.dataset.category;
                    document.getElementById('tile-title').value = this.dataset.title;
                    document.getElementById('tile-url').value = this.dataset.url;
                    document.getElementById('tile-icon').value = this.dataset.icon;
                    document.getElementById('tile-priority').value = this.dataset.priority;

                    document.getElementById('delete-tile-id').value = this.dataset.id;
                });
            });

            document.querySelectorAll('.create-tile-btn').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('new-tile-category').value = this.dataset.category;
                });
            });

            document.getElementById('delete-tile-btn').addEventListener('click', function() {
                if (confirm('Möchtest du diese Verlinkung wirklich löschen?')) {
                    document.getElementById('delete-tile-form').submit();
                }
            });

            document.querySelectorAll('.edit-category-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const title = this.dataset.title;
                    const priority = this.dataset.priority;

                    document.getElementById('category-id').value = id;
                    document.getElementById('category-title').value = title;
                    document.getElementById('category-priority').value = priority;

                    document.getElementById('delete-category-id').value = id;
                });
            });

            document.getElementById('delete-category-btn').addEventListener('click', function() {
                if (confirm('Möchtest du diese Kategorie wirklich löschen?')) {
                    document.getElementById('delete-category-form').submit();
                }
            });

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const iconInputs = [{
                    inputId: 'tile-icon',
                    previewId: 'tile-icon-preview',
                    suggestionsId: 'icon-suggestions'
                },
                {
                    inputId: 'new-tile-icon',
                    previewId: 'new-tile-icon-preview',
                    suggestionsId: 'new-icon-suggestions'
                }
            ];

            let allIcons = [];

            fetch('/assets/json/la-full.json')
                .then(res => res.json())
                .then(data => allIcons = data);

            iconInputs.forEach(({
                inputId,
                previewId,
                suggestionsId
            }) => {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(previewId);
                const suggestions = document.getElementById(suggestionsId);

                if (!input || !preview || !suggestions) return;

                input.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    suggestions.innerHTML = '';

                    if (query.length < 1 || allIcons.length === 0) {
                        suggestions.style.display = 'none';
                        return;
                    }

                    const matches = allIcons.filter(icon => icon.toLowerCase().includes(query)).slice(0, 50);

                    if (matches.length === 0) {
                        suggestions.style.display = 'none';
                        return;
                    }

                    matches.forEach(icon => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'btn btn-light btn-sm me-2 mb-2';
                        btn.innerHTML = `<i class="${icon} me-2"></i> ${icon}`;
                        btn.onclick = () => {
                            input.value = icon;
                            preview.className = icon;
                            suggestions.style.display = 'none';
                        };
                        suggestions.appendChild(btn);
                    });

                    suggestions.style.display = 'block';
                });

                input.addEventListener('change', function() {
                    preview.className = this.value;
                });
            });

            const syncPreview = () => {
                iconInputs.forEach(({
                    inputId,
                    previewId
                }) => {
                    const input = document.getElementById(inputId);
                    const preview = document.getElementById(previewId);

                    if (input && preview && input.value.trim()) {
                        preview.className = input.value.trim();
                    }
                });
            };

            syncPreview();

            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('shown.bs.modal', syncPreview);
            });
        });
    </script>



    <?php include $_SERVER['DOCUMENT_ROOT'] . "/assets/components/footer.php"; ?>
</body>

</html>