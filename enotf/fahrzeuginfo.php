<?php
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_samesite', 'None');
    ini_set('session.cookie_secure', '1');
}

session_start();
require_once __DIR__ . '/../assets/config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../assets/config/database.php';

$prot_url = "https://" . SYSTEM_URL . "/enotf/index.php";

date_default_timezone_set('Europe/Berlin');
$currentTime = date('H:i');
$currentDate = date('d.m.Y');

if (!isset($_SESSION['fahrername']) || !isset($_SESSION['protfzg'])) {
    header("Location: " . BASE_PATH . "enotf/loggedout.php");
    exit();
}

$currentVehicleId = $_SESSION['protfzg'];

$vehicleStmt = $pdo->prepare("SELECT * FROM intra_fahrzeuge WHERE identifier = ? AND active = 1");
$vehicleStmt->execute([$currentVehicleId]);
$vehicle = $vehicleStmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    $vehicleStmt = $pdo->prepare("SELECT * FROM intra_fahrzeuge WHERE active = 1 ORDER BY priority ASC");
    $vehicleStmt->execute();
    $vehicles = $vehicleStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $categoriesStmt = $pdo->prepare("
        SELECT c.*, 
               COUNT(t.id) as tile_count,
               SUM(t.amount) as total_items
        FROM intra_fahrzeuge_beladung_categories c
        LEFT JOIN intra_fahrzeuge_beladung_tiles t ON c.id = t.category
        WHERE (c.veh_type = ? OR c.veh_type IS NULL OR c.veh_type = '')
        GROUP BY c.id
        ORDER BY c.priority ASC, c.title ASC
    ");
    $categoriesStmt->execute([$vehicle['veh_type']]);
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fahrzeuginfo &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/divi.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/_ext/lineawesome/css/line-awesome.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/fonts/mavenpro/css/all.min.css" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <script src="<?= BASE_PATH ?>vendor/components/jquery/jquery.min.js"></script>
    <script src="<?= BASE_PATH ?>vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="<?= BASE_PATH ?>assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="<?= BASE_PATH ?>assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_PATH ?>assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="<?php echo SYSTEM_NAME ?>" />
    <link rel="manifest" href="<?= BASE_PATH ?>assets/favicon/site.webmanifest" />
    <!-- Metas -->
    <meta name="theme-color" content="#ffaf2f" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="<?= $prot_url ?>" />
    <meta property="og:title" content="Fahrzeuginfo &rsaquo; eNOTF &rsaquo; <?php echo SYSTEM_NAME ?>" />
    <meta property="og:image" content="https://<?php echo SYSTEM_URL ?>/assets/img/aelrd.png" />
    <meta property="og:description" content="Fahrzeuginformationen der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
</head>

<body style="overflow-x:hidden" id="edivi__login">
    <div class="container-fluid" id="edivi__container">
        <div class="row h-100">
            <div class="col" id="edivi__content">
                <div class="row border-bottom edivi__header-overview" style="--bs-border-color: #333333">
                    <div class="col">
                        <a href="index.php" class="text-decoration-none text-light" style="font-size:1.6rem">
                            <i class="las la-arrow-left"></i> Zurück zur Übersicht
                        </a>
                    </div>
                    <div class="col border-start" style="--bs-border-color: #333333">
                        <div class="row border-bottom" style="--bs-border-color: #333333">
                            <div class="col">Angemeldet:</div>
                        </div>
                        <div class="row">
                            <div class="col"><?= $_SESSION['fahrername'] ?? 'Fehler Fehler' ?></div>
                            <div class="col border-start" style="--bs-border-color: #333333"><?= $_SESSION['beifahrername'] ?? 'Fehler Fehler' ?></div>
                        </div>
                    </div>
                    <div class="col-2 border-start" style="padding:0;--bs-border-color: #333333">
                        <a href="loggedout.php" class="edivi__nidabutton-primary w-100 h-100 d-flex justify-content-center align-content-center">abmelden</a>
                    </div>
                </div>

                <div class="hr my-2" style="color:transparent"></div>

                <div class="row">
                    <div class="col-12">
                        <h4 class="fw-bold text-light mb-4">
                            <i class="las la-ambulance"></i> Fahrzeuginformationen
                        </h4>

                        <?php if ($vehicle): ?>
                            <!-- Fahrzeugdaten -->
                            <div class="vehicle-info-card p-4 mb-4">
                                <h5 class="text-light mb-3">
                                    <i class="las la-info-circle"></i> Fahrzeugdaten
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-dark-custom">
                                        <tbody>
                                            <tr>
                                                <th scope="row" style="width: 200px;" class="text-light">Fahrzeugname:</th>
                                                <td><?= htmlspecialchars($vehicle['name']) ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="text-light">Fahrzeugtyp:</th>
                                                <td>
                                                    <?= htmlspecialchars($vehicle['veh_type']) ?>
                                                    <?php
                                                    switch ($vehicle['rd_type']) {
                                                        case 1:
                                                            echo '<span class="badge text-bg-danger ms-2 badge-vehicle-type">Notarztbesetzt</span>';
                                                            break;
                                                        case 2:
                                                            echo '<span class="badge text-bg-warning ms-2 badge-vehicle-type">Transportmittel</span>';
                                                            break;
                                                        default:
                                                            echo '<span class="badge text-bg-primary ms-2 badge-vehicle-type">Standard</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="text-light">Kennzeichen:</th>
                                                <td><?= $vehicle['kennzeichen'] ? htmlspecialchars($vehicle['kennzeichen']) : '—' ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="text-light">Bezeichnung:</th>
                                                <td><?= htmlspecialchars($vehicle['identifier']) ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="text-light">Erstellt am:</th>
                                                <td><?= (new DateTime($vehicle['created_at']))->format('d.m.Y H:i') ?> Uhr</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Beladelisten -->
                            <div class="vehicle-info-card p-4">
                                <h5 class="text-light mb-3">
                                    <i class="las la-truck-loading"></i> Beladeliste
                                </h5>

                                <?php if (count($categories) > 0): ?>
                                    <div class="row">
                                        <?php foreach ($categories as $category): ?>
                                            <?php
                                            // Typ-Styling
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
                                            ?>

                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="category-card p-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="text-light mb-0">
                                                            <span class="badge bg-secondary me-2"><?= $category['priority'] ?></span>
                                                            <?= htmlspecialchars($category['title']) ?>
                                                        </h6>
                                                        <span class="badge bg-<?= $typeClass ?>"><?= $typeText ?></span>
                                                    </div>

                                                    <div class="mb-2">
                                                        <small class="text-light">
                                                            <?= $category['tile_count'] ?> Positionen |
                                                            <?= $category['total_items'] ?: 0 ?> Gegenstände gesamt
                                                        </small>
                                                    </div>

                                                    <?php
                                                    $tilesStmt = $pdo->prepare("SELECT * FROM intra_fahrzeuge_beladung_tiles WHERE category = ? ORDER BY title ASC");
                                                    $tilesStmt->execute([$category['id']]);
                                                    $tiles = $tilesStmt->fetchAll(PDO::FETCH_ASSOC);
                                                    ?>

                                                    <?php if (count($tiles) > 0): ?>
                                                        <div class="tiles-container">
                                                            <?php foreach ($tiles as $tile): ?>
                                                                <div class="tile-item d-flex justify-content-between align-items-center">
                                                                    <span class="text-light">
                                                                        <?= htmlspecialchars($tile['title']) ?>
                                                                    </span>
                                                                    <span class="badge bg-primary">
                                                                        <?= $tile['amount'] ?>x
                                                                    </span>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-muted">
                                                            <em>Keine Gegenstände in dieser Kategorie</em>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted text-center py-4">
                                        <i class="las la-exclamation-triangle" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Keine Beladelisten für diesen Fahrzeugtyp vorhanden.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                        <?php else: ?>
                            <!-- Fallback: Kein Fahrzeug gefunden -->
                            <div class="vehicle-info-card p-4">
                                <div class="text-center py-4">
                                    <i class="las la-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                                    <h5 class="text-light mt-3">Fahrzeug nicht gefunden</h5>
                                    <p class="text-muted">
                                        Das Fahrzeug mit der ID "<?= htmlspecialchars($currentVehicleId) ?>" konnte nicht gefunden werden.
                                    </p>

                                    <?php if (isset($vehicles) && count($vehicles) > 0): ?>
                                        <h6 class="text-light mt-4">Verfügbare Fahrzeuge:</h6>
                                        <div class="table-responsive mt-3">
                                            <table class="table table-dark-custom">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Typ</th>
                                                        <th>Identifier</th>
                                                        <th>Kennzeichen</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($vehicles as $veh): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($veh['name']) ?></td>
                                                            <td><?= htmlspecialchars($veh['veh_type']) ?></td>
                                                            <td><?= htmlspecialchars($veh['identifier']) ?></td>
                                                            <td><?= $veh['kennzeichen'] ? htmlspecialchars($veh['kennzeichen']) : '—' ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>