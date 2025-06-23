<?php
session_start();
require_once __DIR__ . '/../../../../assets/config/config.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    header("Location: " . BASE_PATH . "admin/login.php");
    exit();
}

use App\Auth\Permissions;
use App\Helpers\Flash;

if (!Permissions::check(['admin', 'vehicles.view'])) {
    Flash::set('error', 'no-permissions');
    header("Location: " . BASE_PATH . "admin/index.php");
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Administration &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/style.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/admin.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <script src="<?= BASE_PATH ?>vendor/components/jquery/jquery.min.js"></script>
    <script src="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="<?= BASE_PATH ?>vendor/datatables.net/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="<?= BASE_PATH ?>assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="<?= BASE_PATH ?>assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_PATH ?>assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="<?php echo SYSTEM_NAME ?>" />
    <link rel="manifest" href="<?= BASE_PATH ?>assets/favicon/site.webmanifest" />
    <!-- Metas -->
    <meta name="theme-color" content="<?php echo SYSTEM_COLOR ?>" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="https://<?php echo SYSTEM_URL . BASE_PATH ?>/dashboard.php" />
    <meta property="og:title" content="<?php echo SYSTEM_NAME ?> - Intranet <?php echo SERVER_CITY ?>" />
    <meta property="og:image" content="<?php echo META_IMAGE_URL ?>" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
    <style>
        .category-card {
            transition: all 0.3s ease;
            border-left: 4px solid #007bff;
        }

        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .tile-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            border-left: 3px solid #28a745;
        }

        .badge-type {
            font-size: 0.75em;
        }

        .priority-badge {
            min-width: 30px;
            text-align: center;
        }
    </style>
</head>

<body data-bs-theme="dark" data-page="settings">
    <?php include __DIR__ . "/../../../../assets/components/navbar.php"; ?>
    <div class="container-full position-relative" id="mainpageContainer">
        <!-- ------------ -->
        <!-- PAGE CONTENT -->
        <!-- ------------ -->
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="las la-truck-loading"></i> Beladelisten-Verwaltung</h2>
                        <div>
                            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="las la-plus"></i> Neue Kategorie
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTileModal">
                                <i class="las la-plus"></i> Neuer Gegenstand
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div id="categories-container" class="intra__tile py-2 px-3">
                        <!-- PHP Content wird hier eingefügt -->
                        <?php
                        // Database connection (anpassen an Ihre config)
                        require __DIR__ . '/../../../../assets/config/database.php';

                        // Kategorien laden
                        $stmt = $pdo->prepare("
                        SELECT c.*, 
                               COUNT(t.id) as tile_count,
                               SUM(t.amount) as total_items
                        FROM intra_fahrzeuge_beladung_categories c
                        LEFT JOIN intra_fahrzeuge_beladung_tiles t ON c.id = t.category
                        GROUP BY c.id
                        ORDER BY c.priority ASC, c.title ASC
                    ");
                        $stmt->execute();
                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($categories as $category) {
                            $typeClass = $category['type'] == 1 ? 'danger' : 'primary';
                            $typeText = $category['type'] == 1 ? 'Fahrzeugspezifisch' : 'Allgemein';
                            $vehTypeText = $category['veh_type'] ? "({$category['veh_type']})" : '';

                            echo "<div class='col-12 mb-4'>";
                            echo "<div class='card category-card'>";
                            echo "<div class='card-header d-flex justify-content-between align-items-center'>";
                            echo "<div>";
                            echo "<h5 class='mb-1'>";
                            echo "<span class='badge bg-secondary priority-badge me-2'>{$category['priority']}</span>";
                            echo "{$category['title']} {$vehTypeText}";
                            echo "</h5>";
                            echo "<span class='badge bg-{$typeClass} badge-type'>{$typeText}</span>";
                            echo "</div>";
                            echo "<div>";
                            echo "<span class='badge bg-info me-2'>{$category['tile_count']} Positionen</span>";
                            echo "<span class='badge bg-success me-2'>{$category['total_items']} Gesamt</span>";
                            echo "<button class='btn btn-sm btn-outline-primary me-1 edit-category-btn' data-id='{$category['id']}' data-title='{$category['title']}' data-type='{$category['type']}' data-priority='{$category['priority']}' data-veh_type='{$category['veh_type']}'>";
                            echo "<i class='las la-edit'></i>";
                            echo "</button>";
                            echo "<button class='btn btn-sm btn-outline-danger delete-category-btn' data-id='{$category['id']}'>";
                            echo "<i class='las la-trash'></i>";
                            echo "</button>";
                            echo "</div>";
                            echo "</div>";

                            // Tiles für diese Kategorie laden
                            $tileStmt = $pdo->prepare("SELECT * FROM intra_fahrzeuge_beladung_tiles WHERE category = ? ORDER BY title ASC");
                            $tileStmt->execute([$category['id']]);
                            $tiles = $tileStmt->fetchAll(PDO::FETCH_ASSOC);

                            echo "<div class='card-body'>";
                            if (count($tiles) > 0) {
                                echo "<div class='row'>";
                                foreach ($tiles as $tile) {
                                    echo "<div class='col-md-6 col-lg-4'>";
                                    echo "<div class='tile-item d-flex justify-content-between align-items-center'>";
                                    echo "<div>";
                                    echo "<strong>{$tile['title']}</strong>";
                                    echo "</div>";
                                    echo "<div>";
                                    echo "<span class='badge bg-primary me-2'>{$tile['amount']}x</span>";
                                    echo "<button class='btn btn-sm btn-outline-primary me-1 edit-tile-btn' data-id='{$tile['id']}' data-category='{$tile['category']}' data-title='{$tile['title']}' data-amount='{$tile['amount']}'>";
                                    echo "<i class='las la-edit'></i>";
                                    echo "</button>";
                                    echo "<button class='btn btn-sm btn-outline-danger delete-tile-btn' data-id='{$tile['id']}'>";
                                    echo "<i class='las la-trash'></i>";
                                    echo "</button>";
                                    echo "</div>";
                                    echo "</div>";
                                    echo "</div>";
                                }
                                echo "</div>";
                            } else {
                                echo "<p class='text-muted mb-0'>Keine Gegenstände in dieser Kategorie.</p>";
                            }
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kategorie hinzufügen Modal -->
        <div class="modal fade" id="addCategoryModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="addCategoryForm" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Neue Kategorie</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="category-title" class="form-label">Titel</label>
                                <input type="text" class="form-control" id="category-title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="category-type" class="form-label">Typ</label>
                                <select class="form-control" id="category-type" name="type">
                                    <option value="0">Allgemein</option>
                                    <option value="1">Fahrzeugspezifisch</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="category-veh_type" class="form-label">Fahrzeugtyp (nur bei fahrzeugspezifisch)</label>
                                <input type="text" class="form-control" id="category-veh_type" name="veh_type" placeholder="z.B. RTW, NEF, KTW">
                            </div>
                            <div class="mb-3">
                                <label for="category-priority" class="form-label">Priorität</label>
                                <input type="number" class="form-control" id="category-priority" name="priority" value="0">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                            <button type="submit" class="btn btn-success">Speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kategorie bearbeiten Modal -->
        <div class="modal fade" id="editCategoryModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="editCategoryForm" method="POST">
                        <input type="hidden" id="edit-category-id" name="id">
                        <div class="modal-header">
                            <h5 class="modal-title">Kategorie bearbeiten</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit-category-title" class="form-label">Titel</label>
                                <input type="text" class="form-control" id="edit-category-title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-category-type" class="form-label">Typ</label>
                                <select class="form-control" id="edit-category-type" name="type">
                                    <option value="0">Allgemein</option>
                                    <option value="1">Fahrzeugspezifisch</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit-category-veh_type" class="form-label">Fahrzeugtyp</label>
                                <input type="text" class="form-control" id="edit-category-veh_type" name="veh_type">
                            </div>
                            <div class="mb-3">
                                <label for="edit-category-priority" class="form-label">Priorität</label>
                                <input type="number" class="form-control" id="edit-category-priority" name="priority">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                            <button type="submit" class="btn btn-primary">Speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Gegenstand hinzufügen Modal -->
    <div class="modal fade" id="addTileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addTileForm" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Neuer Gegenstand</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tile-category" class="form-label">Kategorie</label>
                            <select class="form-control" id="tile-category" name="category" required>
                                <?php
                                foreach ($categories as $cat) {
                                    $vehType = $cat['veh_type'] ? " ({$cat['veh_type']})" : '';
                                    echo "<option value='{$cat['id']}'>{$cat['title']}{$vehType}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tile-title" class="form-label">Bezeichnung</label>
                            <input type="text" class="form-control" id="tile-title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="tile-amount" class="form-label">Anzahl</label>
                            <input type="number" class="form-control" id="tile-amount" name="amount" value="1" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-primary">Speichern</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Gegenstand bearbeiten Modal -->
    <div class="modal fade" id="editTileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editTileForm" method="POST">
                    <input type="hidden" id="edit-tile-id" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Gegenstand bearbeiten</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit-tile-category" class="form-label">Kategorie</label>
                            <select class="form-control" id="edit-tile-category" name="category" required>
                                <?php
                                foreach ($categories as $cat) {
                                    $vehType = $cat['veh_type'] ? " ({$cat['veh_type']})" : '';
                                    echo "<option value='{$cat['id']}'>{$cat['title']}{$vehType}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-tile-title" class="form-label">Bezeichnung</label>
                            <input type="text" class="form-control" id="edit-tile-title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-tile-amount" class="form-label">Anzahl</label>
                            <input type="number" class="form-control" id="edit-tile-amount" name="amount" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-primary">Speichern</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kategorie bearbeiten
            document.querySelectorAll('.edit-category-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
                    document.getElementById('edit-category-id').value = this.dataset.id;
                    document.getElementById('edit-category-title').value = this.dataset.title;
                    document.getElementById('edit-category-type').value = this.dataset.type;
                    document.getElementById('edit-category-priority').value = this.dataset.priority;
                    document.getElementById('edit-category-veh_type').value = this.dataset.veh_type || '';
                    modal.show();
                });
            });

            // Gegenstand bearbeiten
            document.querySelectorAll('.edit-tile-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const modal = new bootstrap.Modal(document.getElementById('editTileModal'));
                    document.getElementById('edit-tile-id').value = this.dataset.id;
                    document.getElementById('edit-tile-category').value = this.dataset.category;
                    document.getElementById('edit-tile-title').value = this.dataset.title;
                    document.getElementById('edit-tile-amount').value = this.dataset.amount;
                    modal.show();
                });
            });

            // Löschbestätigungen
            document.querySelectorAll('.delete-category-btn').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Möchten Sie diese Kategorie wirklich löschen? Alle zugehörigen Gegenstände werden ebenfalls gelöscht.')) {
                        // AJAX Delete Request für Kategorie
                        deleteCategory(this.dataset.id);
                    }
                });
            });

            document.querySelectorAll('.delete-tile-btn').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Möchten Sie diesen Gegenstand wirklich löschen?')) {
                        // AJAX Delete Request für Gegenstand
                        deleteTile(this.dataset.id);
                    }
                });
            });

            // Form Submissions
            document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'add_category');

                fetch('beladung_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
                            location.reload(); // Seite neu laden
                        } else {
                            alert('Fehler: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ein Fehler ist aufgetreten');
                    });
            });

            document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'edit_category');

                fetch('beladung_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById('editCategoryModal')).hide();
                            location.reload();
                        } else {
                            alert('Fehler: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ein Fehler ist aufgetreten');
                    });
            });

            document.getElementById('addTileForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'add_tile');

                fetch('beladung_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById('addTileModal')).hide();
                            location.reload();
                        } else {
                            alert('Fehler: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ein Fehler ist aufgetreten');
                    });
            });

            document.getElementById('editTileForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'edit_tile');

                fetch('beladung_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById('editTileModal')).hide();
                            location.reload();
                        } else {
                            alert('Fehler: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ein Fehler ist aufgetreten');
                    });
            });
        });

        function deleteCategory(id) {
            const formData = new FormData();
            formData.append('action', 'delete_category');
            formData.append('id', id);

            fetch('beladung_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Fehler: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ein Fehler ist aufgetreten');
                });
        }

        function deleteTile(id) {
            const formData = new FormData();
            formData.append('action', 'delete_tile');
            formData.append('id', id);

            fetch('beladung_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Fehler: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ein Fehler ist aufgetreten');
                });
        }
    </script>
    <?php include __DIR__ . "/../../../../assets/components/footer.php"; ?>
</body>

</html>