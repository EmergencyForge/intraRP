<?php
session_start();
require_once __DIR__ . '/../../assets/config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../assets/config/database.php';

if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: " . BASE_PATH . "admin/login.php");
    exit();
}

use App\Auth\Permissions;
use App\Helpers\Flash;

if (!Permissions::check(['admin', 'personnel.edit'])) {
    Flash::set('error', 'no-permissions');
    header("Location: " . BASE_PATH . "admin/index.php");
    exit();
}

$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$whereConditions = ['1=1'];
$params = [];

switch ($filter) {
    case 'open':
        $whereConditions[] = 'closed = 0 AND deleted = 0';
        break;
    case 'closed':
        $whereConditions[] = 'closed = 1 AND deleted = 0';
        break;
    case 'deleted':
        $whereConditions[] = 'deleted = 1';
        break;
    case 'all':
    default:
        $whereConditions[] = 'deleted = 0';
        break;
}

if (!empty($search)) {
    $whereConditions[] = '(fullname LIKE ? OR discordid LIKE ? OR dienstnr LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$whereClause = implode(' AND ', $whereConditions);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM intra_bewerbung WHERE $whereClause");
$countStmt->execute($params);
$totalCount = $countStmt->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

$stmt = $pdo->prepare("
    SELECT 
        b.*,
        (SELECT COUNT(*) FROM intra_bewerbung_messages WHERE bewerbungid = b.id) as message_count
    FROM intra_bewerbung b 
    WHERE $whereClause 
    ORDER BY b.timestamp DESC 
    LIMIT ? OFFSET ?
");

$allParams = array_merge($params, [$perPage, $offset]);
$stmt->execute($allParams);
$bewerbungen = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN closed = 0 AND deleted = 0 THEN 1 ELSE 0 END) as open,
        SUM(CASE WHEN closed = 1 AND deleted = 0 THEN 1 ELSE 0 END) as closed,
        SUM(CASE WHEN deleted = 1 THEN 1 ELSE 0 END) as deleted
    FROM intra_bewerbung
");
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

function getStatusBadge($closed, $deleted)
{
    if ($deleted) return '<span class="badge bg-danger">Gelöscht</span>';
    if ($closed) return '<span class="badge bg-info">Bearbeitet</span>';
    return '<span class="badge bg-warning">Offen</span>';
}

function getGeschlechtText($geschlecht)
{
    switch ($geschlecht) {
        case 0:
            return 'Männlich';
        case 1:
            return 'Weiblich';
        case 2:
            return 'Divers';
        default:
            return 'Nicht angegeben';
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bewerbungsverwaltung &rsaquo; <?php echo SYSTEM_NAME ?></title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/style.min.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>assets/css/admin.min.css" />
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
    <meta name="theme-color" content="<?php echo SYSTEM_COLOR ?>" />
    <meta property="og:site_name" content="<?php echo SERVER_NAME ?>" />
    <meta property="og:url" content="https://<?php echo SYSTEM_URL . BASE_PATH ?>/dashboard.php" />
    <meta property="og:title" content="<?php echo SYSTEM_NAME ?> - Intranet <?php echo SERVER_CITY ?>" />
    <meta property="og:image" content="<?php echo META_IMAGE_URL ?>" />
    <meta property="og:description" content="Verwaltungsportal der <?php echo RP_ORGTYPE . " " .  SERVER_CITY ?>" />
</head>

<body data-bs-theme="dark" data-page="bewerbungen">
    <?php include __DIR__ . "/../../assets/components/navbar.php"; ?>

    <div class="container-full position-relative" id="mainpageContainer">
        <div class="container">
            <div class="row">
                <div class="col">
                    <hr class="text-light my-3">
                    <h1>Bewerbungsmanagement</h1>

                    <?php Flash::render(); ?>

                    <!-- Statistiken -->
                    <div class="bewerbung-header mb-4">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="stats-item">
                                    <span class="stats-number"><?= $stats['total'] ?></span>
                                    <span class="stats-label">Gesamt</span>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="stats-item">
                                    <span class="stats-number"><?= $stats['open'] ?></span>
                                    <span class="stats-label">Offen</span>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="stats-item">
                                    <span class="stats-number"><?= $stats['closed'] ?></span>
                                    <span class="stats-label">Bearbeitet</span>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="stats-item">
                                    <span class="stats-number"><?= $stats['deleted'] ?></span>
                                    <span class="stats-label">Gelöscht</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter und Suche -->
                    <div class="intra__tile mb-4">
                        <!-- Filter Tabs -->
                        <div class="filter-tabs">
                            <a href="?filter=all<?= $search ? '&search=' . urlencode($search) : '' ?>"
                                class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">
                                <i class="las la-list"></i> Alle (<?= $stats['total'] ?>)
                            </a>
                            <a href="?filter=open<?= $search ? '&search=' . urlencode($search) : '' ?>"
                                class="filter-tab <?= $filter === 'open' ? 'active' : '' ?>">
                                <i class="las la-clock"></i> Offen (<?= $stats['open'] ?>)
                            </a>
                            <a href="?filter=closed<?= $search ? '&search=' . urlencode($search) : '' ?>"
                                class="filter-tab <?= $filter === 'closed' ? 'active' : '' ?>">
                                <i class="las la-check"></i> Bearbeitet (<?= $stats['closed'] ?>)
                            </a>
                            <a href="?filter=deleted<?= $search ? '&search=' . urlencode($search) : '' ?>"
                                class="filter-tab <?= $filter === 'deleted' ? 'active' : '' ?>">
                                <i class="las la-trash"></i> Gelöscht (<?= $stats['deleted'] ?>)
                            </a>
                        </div>

                        <!-- Suchformular -->
                        <div class="form-section">
                            <h5><i class="las la-search"></i> Bewerbungen durchsuchen</h5>
                            <form method="get" class="row g-3">
                                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="search"
                                        value="<?= htmlspecialchars($search) ?>"
                                        placeholder="Suche nach Name, Discord-ID oder Dienstnummer...">
                                </div>
                                <div class="col-md-4">
                                    <div class="btn-group w-100">
                                        <button type="submit" class="btn btn-main-color">
                                            <i class="las la-search"></i> Suchen
                                        </button>
                                        <?php if ($search): ?>
                                            <a href="?filter=<?= htmlspecialchars($filter) ?>" class="btn btn-secondary">
                                                <i class="las la-times"></i> Reset
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Bewerbungsliste -->
                    <div class="intra__tile">
                        <?php if (empty($bewerbungen)): ?>
                            <div class="text-center py-5">
                                <i class="las la-inbox" style="font-size: 4rem; color: #6c757d;"></i>
                                <h4 class="text-muted">Keine Bewerbungen gefunden</h4>
                                <p class="text-muted">
                                    <?php if ($search): ?>
                                        Keine Bewerbungen entsprechen den Suchkriterien.
                                    <?php else: ?>
                                        Es sind noch keine Bewerbungen eingegangen.
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th><i class="las la-hashtag"></i> ID</th>
                                            <th><i class="las la-user"></i> Name</th>
                                            <th><i class="lab la-discord"></i> Discord-ID</th>
                                            <th><i class="las la-calendar"></i> Eingereicht</th>
                                            <th><i class="las la-info-circle"></i> Status</th>
                                            <th><i class="las la-comments"></i> Nachrichten</th>
                                            <th><i class="las la-tools"></i> Aktionen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bewerbungen as $bewerbung): ?>
                                            <tr class="bewerbung-row">
                                                <td>
                                                    <strong>#<?= $bewerbung['id'] ?></strong>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?= htmlspecialchars($bewerbung['fullname']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?= getGeschlechtText($bewerbung['geschlecht']) ?>
                                                            <?php if ($bewerbung['charakterid']): ?>
                                                                • <code><?= htmlspecialchars($bewerbung['charakterid']) ?></code>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code><?= htmlspecialchars($bewerbung['discordid']) ?></code>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?= date('d.m.Y', strtotime($bewerbung['timestamp'])) ?><br>
                                                        <span class="text-muted"><?= date('H:i', strtotime($bewerbung['timestamp'])) ?> Uhr</span>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?= getStatusBadge($bewerbung['closed'], $bewerbung['deleted']) ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($bewerbung['message_count'] > 0): ?>
                                                        <span class="message-indicator" title="<?= $bewerbung['message_count'] ?> Nachrichten">
                                                            <?= $bewerbung['message_count'] ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="table-actions">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="view.php?id=<?= $bewerbung['id'] ?>"
                                                            class="btn btn-main-color"
                                                            title="Bewerbung anzeigen">
                                                            <i class="las la-eye"></i>
                                                        </a>
                                                        <?php if (!$bewerbung['deleted']): ?>
                                                            <a href="edit.php?id=<?= $bewerbung['id'] ?>"
                                                                class="btn btn-secondary"
                                                                title="Bearbeiten">
                                                                <i class="las la-edit"></i>
                                                            </a>
                                                            <?php if ($bewerbung['closed'] == 0): ?>
                                                                <button type="button"
                                                                    class="btn btn-success"
                                                                    title="Als bearbeitet markieren"
                                                                    onclick="changeStatus(<?= $bewerbung['id'] ?>, 1)">
                                                                    <i class="las la-check"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="button"
                                                                    class="btn btn-warning"
                                                                    title="Wieder öffnen"
                                                                    onclick="changeStatus(<?= $bewerbung['id'] ?>, 0)">
                                                                    <i class="las la-redo"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            <button type="button"
                                                                class="btn btn-danger"
                                                                title="Löschen"
                                                                onclick="deleteBewerbung(<?= $bewerbung['id'] ?>)">
                                                                <i class="las la-trash"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button"
                                                                class="btn btn-info"
                                                                title="Wiederherstellen"
                                                                onclick="restoreBewerbung(<?= $bewerbung['id'] ?>)">
                                                                <i class="las la-undo"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <!-- Erste Seite -->
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&page=1">
                                                    <i class="las la-angle-double-left"></i>
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">
                                                    <i class="las la-angle-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Seitenzahlen -->
                                        <?php
                                        $start = max(1, $page - 2);
                                        $end = min($totalPages, $page + 2);

                                        for ($i = $start; $i <= $end; $i++):
                                        ?>
                                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Letzte Seite -->
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">
                                                    <i class="las la-angle-right"></i>
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&page=<?= $totalPages ?>">
                                                    <i class="las la-angle-double-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>

                                <div class="text-center text-muted">
                                    Seite <?= $page ?> von <?= $totalPages ?>
                                    (<?= $totalCount ?> Bewerbungen gesamt)
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../../assets/components/footer.php"; ?>

    <script>
        // Status ändern
        function changeStatus(id, status) {
            const statusText = status === 1 ? 'bearbeitet' : 'offen';
            if (confirm(`Bewerbung #${id} als ${statusText} markieren?`)) {
                fetch('actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'change_status',
                            id: id,
                            status: status
                        })
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
                        alert('Ein Fehler ist aufgetreten.');
                    });
            }
        }

        // Bewerbung löschen
        function deleteBewerbung(id) {
            if (confirm(`Bewerbung #${id} wirklich löschen?\n\nDies markiert die Bewerbung als gelöscht, entfernt sie aber nicht permanent.`)) {
                fetch('actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'delete',
                            id: id
                        })
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
                        alert('Ein Fehler ist aufgetreten.');
                    });
            }
        }

        // Bewerbung wiederherstellen
        function restoreBewerbung(id) {
            if (confirm(`Bewerbung #${id} wiederherstellen?`)) {
                fetch('actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'restore',
                            id: id
                        })
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
                        alert('Ein Fehler ist aufgetreten.');
                    });
            }
        }
    </script>
</body>

</html>