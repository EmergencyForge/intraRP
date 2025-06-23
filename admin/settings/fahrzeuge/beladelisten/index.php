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

require __DIR__ . '/../../../../assets/config/database.php';

$vehTypesStmt = $pdo->prepare("SELECT DISTINCT veh_type FROM intra_fahrzeuge_beladung_categories WHERE veh_type IS NOT NULL AND veh_type != '' ORDER BY veh_type");
$vehTypesStmt->execute();
$vehTypes = $vehTypesStmt->fetchAll(PDO::FETCH_COLUMN);

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
            border-left: 4px solid var(--main-color);
        }

        .tile-item {
            padding: 12px;
            margin-bottom: 8px;
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
                    <hr class="text-light my-3">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Beladelisten</h2>
                        <div>
                            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="las la-plus"></i> Neue Kategorie
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTileModal">
                                <i class="las la-plus"></i> Neuer Gegenstand
                            </button>
                        </div>
                    </div>

                    <!-- Filter-Bereich -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <label for="fahrzeugtyp-filter" class="form-label mb-2">Fahrzeugtyp filtern:</label>
                                    <select class="form-control" id="fahrzeugtyp-filter">
                                        <option value="">Alle anzeigen</option>
                                        <option value="null">Ohne Fahrzeugtyp</option>
                                        <?php
                                        foreach ($vehTypes as $vehType) {
                                            echo "<option value='" . htmlspecialchars($vehType) . "'>" . htmlspecialchars($vehType) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="kategorie-filter" class="form-label mb-2">Kategorietyp filtern:</label>
                                    <select class="form-control" id="kategorie-filter">
                                        <option value="">Alle Typen</option>
                                        <option value="0">Nur Notfallrucksack</option>
                                        <option value="1">Nur Innenfach</option>
                                        <option value="2">Nur Außenfach</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-2">Aktionen:</label><br>
                                    <button class="btn btn-outline-secondary btn-sm me-2" id="reset-filter">
                                        <i class="las la-undo"></i> Filter zurücksetzen
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" id="toggle-empty" title="Kategorien ohne Gegenstände ein-/ausblenden">
                                        <i class="las la-eye-slash"></i> <span id="toggle-text">Leere ausblenden</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row intra__tile py-2 px-3">
                <div class="col-12">
                    <div id="categories-container">
                        <?php
                        foreach ($categories as $category) {
                            switch ($category['type']) {
                                case 0:
                                    $typeClass = 'primary';
                                    $typeText = 'Notfallrucksack';
                                    break;
                                case 1:
                                    $typeClass = 'danger';
                                    $typeText = 'Innenfach';
                                    break;
                                case 2:
                                    $typeClass = 'warning';
                                    $typeText = 'Außenfach';
                                    break;
                                default:
                                    $typeClass = 'secondary';
                                    $typeText = 'Unbekannt';
                            }

                            $vehTypeBadge = $category['veh_type'] ? "<span class='badge bg-secondary ms-1'>" . htmlspecialchars($category['veh_type']) . "</span>" : '';

                            echo "<div class='col-12 mb-4 category-item' data-veh-type='" . ($category['veh_type'] ?: 'null') . "' data-category-type='{$category['type']}' data-tile-count='{$category['tile_count']}'>";
                            echo "<div class='card category-card'>";
                            echo "<div class='card-header d-flex justify-content-between align-items-center'>";
                            echo "<div>";
                            echo "<h5 class='mb-1'>";
                            echo "<span class='badge bg-main-color priority-badge me-2'>{$category['priority']}</span>";
                            echo htmlspecialchars($category['title']);
                            echo "</h5>";
                            echo "<span class='badge bg-{$typeClass} badge-type'>{$typeText}</span>";
                            echo $vehTypeBadge;
                            echo "</div>";
                            echo "<div>";
                            echo "<span class='badge bg-secondary me-2'>{$category['tile_count']} Positionen</span>";
                            echo "<button class='btn btn-sm btn-primary me-1 edit-category-btn' data-id='{$category['id']}' data-title='" . htmlspecialchars($category['title']) . "' data-type='{$category['type']}' data-priority='{$category['priority']}' data-veh_type='" . htmlspecialchars($category['veh_type'] ?? '') . "'>";
                            echo "<i class='las la-edit'></i>";
                            echo "</button>";
                            echo "<button class='btn btn-sm btn-danger delete-category-btn' data-id='{$category['id']}'>";
                            echo "<i class='las la-trash'></i>";
                            echo "</button>";
                            echo "</div>";
                            echo "</div>";

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
                                    echo "<strong>" . htmlspecialchars($tile['title']) . "</strong>";
                                    echo "</div>";
                                    echo "<div>";
                                    echo "<span class='badge bg-primary me-2'>{$tile['amount']}x</span>";
                                    echo "<button class='btn btn-sm btn-outline-primary me-1 edit-tile-btn' data-id='{$tile['id']}' data-category='{$tile['category']}' data-title='" . htmlspecialchars($tile['title']) . "' data-amount='{$tile['amount']}'>";
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
                                    <option value="0">Notfallrucksack</option>
                                    <option value="1">Innenfach</option>
                                    <option value="2">Außenfach</option>
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
                                    <option value="0">Notfallrucksack</option>
                                    <option value="1">Innenfach</option>
                                    <option value="2">Außenfach</option>
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
                                        switch ($cat['type']) {
                                            case 0:
                                                $catTypeText = 'Notfallrucksack';
                                                break;
                                            case 1:
                                                $catTypeText = 'Innenfach';
                                                break;
                                            case 2:
                                                $catTypeText = 'Außenfach';
                                                break;
                                            default:
                                                $catTypeText = 'Unbekannt';
                                        }
                                        $vehType = $cat['veh_type'] ? " - {$cat['veh_type']}" : '';
                                        echo "<option value='{$cat['id']}'>" . htmlspecialchars($cat['title']) . " ({$catTypeText}){$vehType}</option>";
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
                                        switch ($cat['type']) {
                                            case 0:
                                                $catTypeText = 'Notfallrucksack';
                                                break;
                                            case 1:
                                                $catTypeText = 'Innenfach';
                                                break;
                                            case 2:
                                                $catTypeText = 'Außenfach';
                                                break;
                                            default:
                                                $catTypeText = 'Unbekannt';
                                        }
                                        $vehType = $cat['veh_type'] ? " - {$cat['veh_type']}" : '';
                                        echo "<option value='{$cat['id']}'>" . htmlspecialchars($cat['title']) . " ({$catTypeText}){$vehType}</option>";
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
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fahrzeugtypFilter = document.getElementById('fahrzeugtyp-filter');
            const kategorieFilter = document.getElementById('kategorie-filter');
            const resetFilterBtn = document.getElementById('reset-filter');
            const toggleEmptyBtn = document.getElementById('toggle-empty');
            const toggleText = document.getElementById('toggle-text');
            let hideEmpty = false;

            function applyFilters() {
                const selectedVehType = fahrzeugtypFilter.value;
                const selectedCategoryType = kategorieFilter.value;
                const categoryItems = document.querySelectorAll('.category-item');
                let visibleCount = 0;

                categoryItems.forEach(item => {
                    let showItem = true;

                    if (selectedVehType && selectedVehType !== item.dataset.vehType) {
                        showItem = false;
                    }

                    if (selectedCategoryType && selectedCategoryType !== item.dataset.categoryType) {
                        showItem = false;
                    }

                    if (hideEmpty && parseInt(item.dataset.tileCount) === 0) {
                        showItem = false;
                    }

                    if (showItem) {
                        item.style.display = 'block';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                updateNoResultsMessage(visibleCount);
            }

            function updateNoResultsMessage(visibleCount) {
                let noResultsMsg = document.getElementById('no-results-message');

                if (visibleCount === 0) {
                    if (!noResultsMsg) {
                        noResultsMsg = document.createElement('div');
                        noResultsMsg.id = 'no-results-message';
                        noResultsMsg.className = 'col-12';
                        noResultsMsg.innerHTML = `
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="las la-search text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">Keine Kategorien gefunden</h5>
                                    <p class="text-muted">Passen Sie Ihre Filter an oder erstellen Sie eine neue Kategorie.</p>
                                </div>
                            </div>
                        `;
                        document.getElementById('categories-container').appendChild(noResultsMsg);
                    }
                    noResultsMsg.style.display = 'block';
                } else {
                    if (noResultsMsg) {
                        noResultsMsg.style.display = 'none';
                    }
                }
            }

            fahrzeugtypFilter.addEventListener('change', applyFilters);
            kategorieFilter.addEventListener('change', applyFilters);

            resetFilterBtn.addEventListener('click', function() {
                fahrzeugtypFilter.value = '';
                kategorieFilter.value = '';
                hideEmpty = false;
                toggleText.textContent = 'Leere ausblenden';
                toggleEmptyBtn.querySelector('i').className = 'las la-eye-slash';
                applyFilters();
            });

            toggleEmptyBtn.addEventListener('click', function() {
                hideEmpty = !hideEmpty;
                if (hideEmpty) {
                    toggleText.textContent = 'Leere einblenden';
                    this.querySelector('i').className = 'las la-eye';
                } else {
                    toggleText.textContent = 'Leere ausblenden';
                    this.querySelector('i').className = 'las la-eye-slash';
                }
                applyFilters();
            });

            const urlParams = new URLSearchParams(window.location.search);
            const urlVehType = urlParams.get('veh_type');
            const urlCategoryType = urlParams.get('category_type');

            if (urlVehType) {
                fahrzeugtypFilter.value = urlVehType;
            }
            if (urlCategoryType) {
                kategorieFilter.value = urlCategoryType;
            }

            if (urlVehType || urlCategoryType) {
                applyFilters();
            }

            function updateURL() {
                const params = new URLSearchParams();
                if (fahrzeugtypFilter.value) params.set('veh_type', fahrzeugtypFilter.value);
                if (kategorieFilter.value) params.set('category_type', kategorieFilter.value);

                const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({}, '', newURL);
            }

            fahrzeugtypFilter.addEventListener('change', updateURL);
            kategorieFilter.addEventListener('change', updateURL);

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

            document.querySelectorAll('.delete-category-btn').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Möchten Sie diese Kategorie wirklich löschen? Alle zugehörigen Gegenstände werden ebenfalls gelöscht.')) {
                        deleteCategory(this.dataset.id);
                    }
                });
            });

            document.querySelectorAll('.delete-tile-btn').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Möchten Sie diesen Gegenstand wirklich löschen?')) {
                        deleteTile(this.dataset.id);
                    }
                });
            });

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