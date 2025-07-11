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

// Admin-Berechtigung prüfen
if (!Permissions::check(['admin', 'personnel.edit'])) {
    Flash::set('error', 'no-permissions');
    header("Location: " . BASE_PATH . "admin/index.php");
    exit();
}

// Bewerbungs-ID aus URL
$bewerbungId = (int)($_GET['id'] ?? 0);

if (!$bewerbungId) {
    Flash::error("Keine Bewerbungs-ID angegeben.");
    header("Location: " . BASE_PATH . "admin/apply/");
    exit();
}

// Bewerbung laden
$stmt = $pdo->prepare("SELECT * FROM intra_bewerbung WHERE id = :id");
$stmt->execute(['id' => $bewerbungId]);
$bewerbung = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bewerbung) {
    Flash::error("Bewerbung nicht gefunden.");
    header("Location: " . BASE_PATH . "admin/apply/");
    exit();
}

// POST-Handler für Admin-Nachrichten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_message'])) {
    try {
        $message = trim($_POST['admin_message'] ?? '');

        if (!empty($message)) {
            $insertStmt = $pdo->prepare("INSERT INTO intra_bewerbung_messages (bewerbungid, text, user, discordid) VALUES (:bewerbungid, :text, :user, :discordid)");
            $insertStmt->execute([
                'bewerbungid' => $bewerbungId,
                'text' => $message,
                'user' => $_SESSION['cirs_user'],
                'discordid' => $_SESSION['discordtag'] ?? 'Admin'
            ]);

            Flash::success("Nachricht gesendet.");
        } else {
            Flash::error("Bitte gib eine Nachricht ein.");
        }

        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $bewerbungId);
        exit();
    } catch (Exception $e) {
        Flash::error("Fehler beim Senden der Nachricht: " . $e->getMessage());
        error_log("Admin Message Error: " . $e->getMessage());
    }
}

// Nachrichten und Status-Logs laden
$messagesStmt = $pdo->prepare("
    SELECT 'message' as type, id, text, user, discordid, timestamp 
    FROM intra_bewerbung_messages 
    WHERE bewerbungid = :bewerbungid1
    UNION ALL
    SELECT 'status' as type, id, 
           CONCAT('Status geändert von ', 
                  CASE COALESCE(status_alt, -1) 
                      WHEN -1 THEN 'Neu' 
                      WHEN 0 THEN 'Offen' 
                      WHEN 1 THEN 'Bearbeitet' 
                      ELSE 'Unbekannt' 
                  END, ' zu ', 
                  CASE status_neu 
                      WHEN 0 THEN 'Offen' 
                      WHEN 1 THEN 'Bearbeitet' 
                      ELSE 'Unbekannt' 
                  END) as text,
           user, discordid, timestamp
    FROM intra_bewerbung_statuslog 
    WHERE bewerbungid = :bewerbungid2
    ORDER BY timestamp ASC
");
$messagesStmt->execute([
    'bewerbungid1' => $bewerbungId,
    'bewerbungid2' => $bewerbungId
]);
$messages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);

// Status-Text ermitteln
function getStatusInfo($closed, $deleted)
{
    if ($deleted) return ['text' => 'Gelöscht', 'class' => 'danger'];
    if ($closed) return ['text' => 'Bearbeitet', 'class' => 'info'];
    return ['text' => 'Offen', 'class' => 'warning'];
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

$status = getStatusInfo($bewerbung['closed'], $bewerbung['deleted']);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bewerbung #<?= $bewerbung['id'] ?> &rsaquo; <?php echo SYSTEM_NAME ?></title>
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
                <div class="col-lg-8">
                    <!-- Header -->
                    <div class="bewerbung-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h1 class="mb-2">Bewerbung #<?= $bewerbung['id'] ?></h1>
                                <h4 class="mb-3"><?= htmlspecialchars($bewerbung['fullname']) ?></h4>
                                <p class="mb-0">
                                    <i class="las la-clock"></i>
                                    Eingereicht am <?= date('d.m.Y H:i', strtotime($bewerbung['timestamp'])) ?> Uhr
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-<?= $status['class'] ?> fs-6 mb-2">
                                    <?= $status['text'] ?>
                                </span>
                                <br>
                                <a href="<?= BASE_PATH ?>admin/apply/" class="btn btn-light btn-sm">
                                    <i class="las la-arrow-left"></i> Zurück zur Übersicht
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php Flash::render(); ?>

                    <!-- Bewerbungsdetails -->
                    <div class="intra__tile mb-4">
                        <div class="form-section">
                            <h5><i class="las la-user"></i> Bewerberdaten</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td width="35%"><strong>Name:</strong></td>
                                            <td><?= htmlspecialchars($bewerbung['fullname']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Geburtsdatum:</strong></td>
                                            <td><?= date('d.m.Y', strtotime($bewerbung['gebdatum'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Geschlecht:</strong></td>
                                            <td><?= getGeschlechtText($bewerbung['geschlecht']) ?></td>
                                        </tr>
                                        <?php if ($bewerbung['charakterid']): ?>
                                            <tr>
                                                <td><strong>Charakter-ID:</strong></td>
                                                <td><code><?= htmlspecialchars($bewerbung['charakterid']) ?></code></td>
                                            </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td width="35%"><strong>Discord-ID:</strong></td>
                                            <td><code><?= htmlspecialchars($bewerbung['discordid']) ?></code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Telefonnummer:</strong></td>
                                            <td><?= htmlspecialchars($bewerbung['telefonnr'] ?: 'Nicht angegeben') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Wunsch-Dienstnr.:</strong></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($bewerbung['dienstnr']) ?></span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge bg-<?= $status['class'] ?>">
                                                    <?= $status['text'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat/Kommunikation -->
                    <div class="intra__tile">
                        <div class="form-section">
                            <h5><i class="las la-comments"></i> Kommunikationsverlauf</h5>

                            <!-- Chat-Bereich -->
                            <div class="chat-container" id="chatContainer">
                                <?php if (empty($messages)): ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="las la-comments" style="font-size: 3em;"></i>
                                        <p>Noch keine Nachrichten vorhanden.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($messages as $message): ?>
                                        <?php if ($message['type'] === 'status'): ?>
                                            <!-- Status-Nachricht -->
                                            <div class="message message-status">
                                                <div class="message-content">
                                                    <i class="las la-info-circle"></i>
                                                    <?= htmlspecialchars($message['text']) ?>
                                                </div>
                                                <div class="message-meta">
                                                    <?= date('d.m.Y H:i', strtotime($message['timestamp'])) ?>
                                                    <?php if ($message['user']): ?>
                                                        - <?= htmlspecialchars($message['user']) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php elseif ($message['user'] === 'System'): ?>
                                            <!-- System-Nachricht -->
                                            <div class="message message-system">
                                                <div class="message-content">
                                                    <i class="las la-cog"></i>
                                                    <?= nl2br(htmlspecialchars($message['text'])) ?>
                                                </div>
                                                <div class="message-meta">
                                                    <?= date('d.m.Y H:i', strtotime($message['timestamp'])) ?>
                                                </div>
                                            </div>
                                        <?php elseif ($message['user'] === $_SESSION['cirs_user']): ?>
                                            <!-- Eigene Admin-Nachricht -->
                                            <div class="message message-own">
                                                <div class="message-content">
                                                    <?= nl2br(htmlspecialchars($message['text'])) ?>
                                                </div>
                                                <div class="message-meta">
                                                    Du - <?= date('d.m.Y H:i', strtotime($message['timestamp'])) ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Bewerber oder andere Admin-Nachricht -->
                                            <?php
                                            $isBewerber = $message['discordid'] === $bewerbung['discordid'];
                                            $messageClass = $isBewerber ? 'message-user' : 'message-admin';
                                            ?>
                                            <div class="message <?= $messageClass ?>">
                                                <div class="message-content">
                                                    <?= nl2br(htmlspecialchars($message['text'])) ?>
                                                </div>
                                                <div class="message-meta">
                                                    <?= htmlspecialchars($message['user']) ?> -
                                                    <?= date('d.m.Y H:i', strtotime($message['timestamp'])) ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Admin-Nachricht senden -->
                            <?php if (!$bewerbung['deleted']): ?>
                                <div class="admin-message-form">
                                    <h6><i class="las la-reply"></i> Admin-Nachricht senden</h6>
                                    <form method="post" id="adminMessageForm">
                                        <div class="mb-3">
                                            <textarea
                                                class="form-control"
                                                name="admin_message"
                                                id="adminMessageInput"
                                                rows="4"
                                                placeholder="Nachricht an den Bewerber..."
                                                required
                                                maxlength="2000"></textarea>
                                            <div class="form-text">
                                                Diese Nachricht wird dem Bewerber angezeigt.
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-main-color">
                                            <i class="las la-paper-plane"></i> Nachricht senden
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mt-3">
                                    <i class="las la-exclamation-triangle"></i>
                                    Diese Bewerbung wurde gelöscht. Keine weiteren Nachrichten möglich.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Aktionen Sidebar -->
                <div class="col-lg-4">
                    <div class="intra__tile">
                        <div class="action-buttons">
                            <h5><i class="las la-tools"></i> Aktionen</h5>

                            <?php if (!$bewerbung['deleted']): ?>
                                <!-- Status ändern -->
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="d-grid gap-2">
                                        <?php if ($bewerbung['closed'] == 0): ?>
                                            <button type="button" class="btn btn-success" onclick="changeStatus(1)">
                                                <i class="las la-check"></i> Als bearbeitet markieren
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-warning" onclick="changeStatus(0)">
                                                <i class="las la-redo"></i> Wieder öffnen
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Weitere Aktionen -->
                                <div class="mb-3">
                                    <label class="form-label">Weitere Aktionen</label>
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-danger" onclick="deleteBewerbung()">
                                            <i class="las la-trash"></i> Bewerbung löschen
                                        </button>
                                        <a href="edit.php?id=<?= $bewerbung['id'] ?>" class="btn btn-secondary">
                                            <i class="las la-edit"></i> Daten bearbeiten
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Bewerbung wiederherstellen -->
                                <div class="mb-3">
                                    <button type="button" class="btn btn-info w-100" onclick="restoreBewerbung()">
                                        <i class="las la-undo"></i> Bewerbung wiederherstellen
                                    </button>
                                </div>
                            <?php endif; ?>

                            <!-- Bewerbungsinfo -->
                            <div class="mt-4">
                                <h6>Bewerbungsinfo</h6>
                                <small class="text-muted">
                                    <strong>ID:</strong> #<?= $bewerbung['id'] ?><br>
                                    <strong>Erstellt:</strong> <?= date('d.m.Y H:i', strtotime($bewerbung['timestamp'])) ?><br>
                                    <strong>Nachrichten:</strong> <?= count(array_filter($messages, fn($m) => $m['type'] === 'message')) ?><br>
                                    <strong>Status-Änderungen:</strong> <?= count(array_filter($messages, fn($m) => $m['type'] === 'status')) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../../assets/components/footer.php"; ?>

    <script>
        // Automatisches Scrollen zum Ende des Chats
        function scrollToBottom() {
            const chatContainer = document.getElementById('chatContainer');
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // Beim Laden der Seite nach unten scrollen
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
        });

        // Status ändern
        function changeStatus(status) {
            const statusText = status === 1 ? 'bearbeitet' : 'offen';
            if (confirm(`Bewerbung #<?= $bewerbung['id'] ?> als ${statusText} markieren?`)) {
                fetch('actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'change_status',
                            id: <?= $bewerbung['id'] ?>,
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
        function deleteBewerbung() {
            if (confirm(`Bewerbung #<?= $bewerbung['id'] ?> von <?= htmlspecialchars($bewerbung['fullname']) ?> wirklich löschen?\n\nDies markiert die Bewerbung als gelöscht.`)) {
                fetch('actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'delete',
                            id: <?= $bewerbung['id'] ?>
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
        function restoreBewerbung() {
            if (confirm(`Bewerbung #<?= $bewerbung['id'] ?> wiederherstellen?`)) {
                fetch('actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'restore',
                            id: <?= $bewerbung['id'] ?>
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

        // Auto-Refresh alle 30 Sekunden für neue Nachrichten
        setInterval(function() {
            if (!document.hidden) {
                location.reload();
            }
        }, 30000);
    </script>
</body>

</html>