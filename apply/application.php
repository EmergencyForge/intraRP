<?php
session_start();
require_once __DIR__ . '/../assets/config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../assets/config/database.php';

if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: " . BASE_PATH . "admin/login.php");
    exit();
}

use App\Helpers\Flash;

if (!isset($_SESSION['cirs_user']) || empty($_SESSION['cirs_user'])) {
    header("Location: " . BASE_PATH . "admin/users/editprofile.php");
    exit();
}

// Bewerbungs-ID aus URL oder Session ermitteln
$bewerbungId = null;
if (isset($_GET['id'])) {
    $bewerbungId = (int)$_GET['id'];
} else {
    // Aktuelle Bewerbung des Benutzers suchen
    $stmt = $pdo->prepare("SELECT id FROM intra_bewerbung WHERE discordid = :discordid AND deleted = 0 ORDER BY timestamp DESC LIMIT 1");
    $stmt->execute(['discordid' => $_SESSION['discordtag']]);
    $result = $stmt->fetch();
    if ($result) {
        $bewerbungId = $result['id'];
    }
}

if (!$bewerbungId) {
    Flash::error("Keine Bewerbung gefunden.");
    header("Location: " . BASE_PATH . "bewerbung.php");
    exit();
}

// Bewerbung laden und Berechtigung prüfen
$stmt = $pdo->prepare("SELECT * FROM intra_bewerbung WHERE id = :id");
$stmt->execute(['id' => $bewerbungId]);
$bewerbung = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bewerbung) {
    Flash::error("Bewerbung nicht gefunden.");
    header("Location: " . BASE_PATH . "bewerbung.php");
    exit();
}

// Berechtigung prüfen (nur eigene Bewerbung oder Admin)
$isAdmin = false; // Hier könntest du Admin-Berechtigung prüfen
if ($bewerbung['discordid'] !== $_SESSION['discordtag'] && !$isAdmin) {
    Flash::error("Keine Berechtigung für diese Bewerbung.");
    header("Location: " . BASE_PATH . "bewerbung.php");
    exit();
}

// POST-Handler für neue Nachrichten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    try {
        $message = trim($_POST['message'] ?? '');

        if (!empty($message)) {
            $insertStmt = $pdo->prepare("INSERT INTO intra_bewerbung_messages (bewerbungid, text, user, discordid) VALUES (:bewerbungid, :text, :user, :discordid)");
            $insertStmt->execute([
                'bewerbungid' => $bewerbungId,
                'text' => $message,
                'user' => $_SESSION['cirs_user'],
                'discordid' => $_SESSION['discordtag']
            ]);

            Flash::success("Nachricht gesendet.");
        } else {
            Flash::error("Bitte gib eine Nachricht ein.");
        }

        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $bewerbungId);
        exit();
    } catch (Exception $e) {
        Flash::error("Fehler beim Senden der Nachricht: " . $e->getMessage());
        error_log("Message Error: " . $e->getMessage());
    }
}

// AJAX-Handler für neue Nachrichten
if (isset($_GET['ajax'])) {
    if ($_GET['ajax'] === 'check') {
        // Anzahl der Nachrichten zurückgeben
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM (
                SELECT id FROM intra_bewerbung_messages WHERE bewerbungid = :bewerbungid1
                UNION ALL
                SELECT id FROM intra_bewerbung_statuslog WHERE bewerbungid = :bewerbungid2
            ) as combined
        ");
        $countStmt->execute([
            'bewerbungid1' => $bewerbungId,
            'bewerbungid2' => $bewerbungId
        ]);
        $count = $countStmt->fetch()['total'];

        header('Content-Type: application/json');
        echo json_encode(['messageCount' => (int)$count]);
        exit();
    }

    if ($_GET['ajax'] === 'messages') {
        // Nur Chat-Inhalt zurückgeben
        if (empty($messages)) {
            echo '<div class="text-center text-muted py-4">
                    <i class="las la-comments" style="font-size: 3em;"></i>
                    <p>Noch keine Nachrichten vorhanden.</p>
                  </div>';
        } else {
            foreach ($messages as $message) {
                if ($message['type'] === 'status') {
                    echo '<div class="message message-status">
                            <div class="message-content">' . htmlspecialchars($message['text']) . '</div>
                            <div class="message-meta">' . date('d.m.Y H:i', strtotime($message['timestamp']));
                    if ($message['user']) echo ' - ' . htmlspecialchars($message['user']);
                    echo '</div></div>';
                } elseif ($message['user'] === 'System') {
                    echo '<div class="message message-system">
                            <div class="message-content">' . nl2br(htmlspecialchars($message['text'])) . '</div>
                            <div class="message-meta">' . date('d.m.Y H:i', strtotime($message['timestamp'])) . '</div>
                          </div>';
                } elseif ($message['user'] === $_SESSION['cirs_user']) {
                    echo '<div class="message message-own">
                            <div class="message-content">' . nl2br(htmlspecialchars($message['text'])) . '</div>
                            <div class="message-meta">' . date('d.m.Y H:i', strtotime($message['timestamp'])) . '</div>
                          </div>';
                } else {
                    echo '<div class="message message-admin">
                            <div class="message-content">' . nl2br(htmlspecialchars($message['text'])) . '</div>
                            <div class="message-meta">' . htmlspecialchars($message['user']) . ' - ' . date('d.m.Y H:i', strtotime($message['timestamp'])) . '</div>
                          </div>';
                }
            }
        }
        exit();
    }
}

// Nachrichten und Status-Logs laden
$messagesStmt = $pdo->prepare("
    SELECT 'message' as type, id, text, user, discordid, timestamp 
    FROM intra_bewerbung_messages 
    WHERE bewerbungid = :bewerbungid1
    UNION ALL
    SELECT 'status' as type, id, 
           CONCAT('Status geändert von ', COALESCE(status_alt, 'Neu'), ' zu ', 
                  CASE status_neu 
                      WHEN 0 THEN 'Offen' 
                      WHEN 1 THEN 'In Bearbeitung' 
                      WHEN 2 THEN 'Genehmigt' 
                      WHEN 3 THEN 'Abgelehnt' 
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
function getStatusText($closed)
{
    if ($closed == 0) {
        return ['text' => 'Offen', 'class' => 'warning'];
    } else {
        return ['text' => 'Bearbeitet', 'class' => 'info'];
    }
}

$status = getStatusText($bewerbung['closed']);

// Prüfen ob bereits eine erste Nachricht (Bewerbungstext) existiert
$hasInitialMessage = false;
foreach ($messages as $msg) {
    if ($msg['type'] === 'message' && $msg['user'] === $_SESSION['cirs_user']) {
        $hasInitialMessage = true;
        break;
    }
}

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bewerbungsdetails &rsaquo; <?php echo SYSTEM_NAME ?></title>
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

<body data-bs-theme="dark" data-page="bewerbung">
    <?php include __DIR__ . "/../assets/components/navbar.php"; ?>

    <div class="container-full position-relative" id="mainpageContainer">
        <div class="container">
            <div class="row">
                <div class="col">
                    <hr class="text-light my-3">
                    <div class="bewerbung-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1>Bewerbungsdetails</h1>
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
                                <a href="<?= BASE_PATH ?>bewerbung.php" class="btn btn-light btn-sm">
                                    <i class="las la-arrow-left"></i> Zurück
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
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td><?= htmlspecialchars($bewerbung['fullname']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Geburtsdatum:</strong></td>
                                            <td><?= date('d.m.Y', strtotime($bewerbung['gebdatum'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Geschlecht:</strong></td>
                                            <td>
                                                <?php
                                                switch ($bewerbung['geschlecht']) {
                                                    case 0:
                                                        echo 'Männlich';
                                                        break;
                                                    case 1:
                                                        echo 'Weiblich';
                                                        break;
                                                    case 2:
                                                        echo 'Divers';
                                                        break;
                                                    default:
                                                        echo 'Nicht angegeben';
                                                        break;
                                                }
                                                ?>
                                            </td>
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
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Discord-ID:</strong></td>
                                            <td><code><?= htmlspecialchars($bewerbung['discordid']) ?></code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Telefonnummer:</strong></td>
                                            <td><?= htmlspecialchars($bewerbung['telefonnr'] ?: 'Nicht angegeben') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Wunsch-Dienstnummer:</strong></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($bewerbung['dienstnr']) ?></span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Eingereicht am:</strong></td>
                                            <td><?= date('d.m.Y H:i', strtotime($bewerbung['timestamp'])) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat/Kommunikation -->
                    <div class="intra__tile">
                        <div class="form-section">
                            <h5>
                                <i class="las la-comments"></i>
                                <?php if (!$hasInitialMessage): ?>
                                    Bewerbungstext einreichen
                                <?php else: ?>
                                    Kommunikation
                                <?php endif; ?>
                            </h5>

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
                                            <!-- Eigene Nachricht -->
                                            <div class="message message-own">
                                                <div class="message-content">
                                                    <?= nl2br(htmlspecialchars($message['text'])) ?>
                                                </div>
                                                <div class="message-meta">
                                                    <?= date('d.m.Y H:i', strtotime($message['timestamp'])) ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Admin/Bearbeiter Nachricht -->
                                            <div class="message message-admin">
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

                            <!-- Eingabebereich -->
                            <?php if ($bewerbung['closed'] == 0): ?>
                                <div class="chat-input">
                                    <?php if (!$hasInitialMessage): ?>
                                        <!-- Fortschrittsbalken für erste Bewerbung -->
                                        <div class="progress mb-3" style="height: 25px;">
                                            <div class="progress-bar progress-bar-striped" id="applicationProgress" role="progressbar"
                                                style="width: 0%; background-color: #28a745;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                <span id="progressText">0 / 50 Wörter für eine gute Bewerbung</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <form method="post" id="messageForm">
                                        <div class="input-group">
                                            <textarea
                                                class="form-control"
                                                name="message"
                                                id="messageInput"
                                                rows="<?php echo !$hasInitialMessage ? '6' : '3'; ?>"
                                                placeholder="<?php if (!$hasInitialMessage): ?>Schreibe hier deinen ausführlichen Bewerbungstext und erkläre, warum du dich bewerben möchtest... (mindestens 50 Wörter)<?php else: ?>Nachricht eingeben...<?php endif; ?>"
                                                required
                                                maxlength="2000"></textarea>
                                            <button type="submit" class="btn btn-main-color" id="submitBtn" <?php if (!$hasInitialMessage): ?>disabled<?php endif; ?>>
                                                <i class="las la-paper-plane"></i>
                                                <?php if (!$hasInitialMessage): ?>Bewerbung einreichen<?php else: ?>Senden<?php endif; ?>
                                            </button>
                                        </div>
                                        <small class="form-text text-muted mt-2">
                                            <?php if (!$hasInitialMessage): ?>
                                                <span id="wordCountInfo">Beschreibe ausführlich deine Motivation und Qualifikationen. <strong>Mindestens 50 Wörter erforderlich.</strong></span>
                                            <?php else: ?>
                                                Maximale Länge: 2000 Zeichen
                                            <?php endif; ?>
                                        </small>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mt-3">
                                    <i class="las la-info-circle"></i>
                                    Diese Bewerbung wurde abgeschlossen. Weitere Nachrichten sind nicht mehr möglich.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../assets/components/footer.php"; ?>

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

        // Nach dem Absenden einer Nachricht scrollen
        document.getElementById('messageForm').addEventListener('submit', function() {
            setTimeout(scrollToBottom, 100);
        });

        // Wörter zählen für erste Bewerbung
        function countWords(text) {
            return text.trim().split(/\s+/).filter(word => word.length > 0).length;
        }

        // Fortschrittsbalken und Wörter-Zähler für erste Bewerbung
        const messageInput = document.getElementById('messageInput');
        const hasInitialMessage = <?php echo $hasInitialMessage ? 'true' : 'false'; ?>;

        if (!hasInitialMessage) {
            const progressBar = document.getElementById('applicationProgress');
            const progressText = document.getElementById('progressText');
            const submitBtn = document.getElementById('submitBtn');
            const wordCountInfo = document.getElementById('wordCountInfo');

            messageInput.addEventListener('input', function() {
                const text = this.value;
                const wordCount = countWords(text);
                const minWords = 50;
                const progress = Math.min((wordCount / minWords) * 100, 100);

                // Fortschrittsbalken aktualisieren
                progressBar.style.width = progress + '%';
                progressBar.setAttribute('aria-valuenow', progress);

                // Farbe des Fortschrittsbalkens ändern
                if (progress >= 100) {
                    progressBar.style.backgroundColor = '#28a745'; // Grün
                    progressBar.classList.remove('progress-bar-striped');
                    submitBtn.disabled = false;
                } else if (progress >= 60) {
                    progressBar.style.backgroundColor = '#ffc107'; // Gelb
                    progressBar.classList.add('progress-bar-striped');
                    submitBtn.disabled = true;
                } else {
                    progressBar.style.backgroundColor = '#dc3545'; // Rot
                    progressBar.classList.add('progress-bar-striped');
                    submitBtn.disabled = true;
                }

                // Text aktualisieren
                progressText.textContent = `${wordCount} / ${minWords} Wörter für eine gute Bewerbung`;

                // Info-Text aktualisieren
                if (wordCount >= minWords) {
                    wordCountInfo.innerHTML = `<span class="text-success"><strong>Perfekt! Deine Bewerbung ist ausführlich genug.</strong></span>`;
                } else {
                    const remaining = minWords - wordCount;
                    wordCountInfo.innerHTML = `Noch <strong>${remaining} Wörter</strong> für eine vollständige Bewerbung erforderlich.`;
                }
            });
        }

        // Zeichen-Zähler für normale Nachrichten
        const maxLength = 2000;

        messageInput.addEventListener('input', function() {
            const remaining = maxLength - this.value.length;
            const existingCounter = document.querySelector('.char-counter');

            if (existingCounter) {
                existingCounter.remove();
            }

            if (remaining < 200 && hasInitialMessage) {
                const counter = document.createElement('small');
                counter.className = 'char-counter text-muted d-block mt-1';
                counter.textContent = `${remaining} Zeichen verbleibend`;
                this.parentNode.appendChild(counter);
            }
        });

        // Enter-Taste für neue Zeile (kein automatisches Absenden)
        messageInput.addEventListener('keydown', function(e) {
            // Enter fügt immer eine neue Zeile ein, kein automatisches Absenden
            if (e.key === 'Enter' && !e.shiftKey) {
                // Standardverhalten beibehalten (neue Zeile)
                return true;
            }
        });

        // AJAX für neue Nachrichten laden (ohne kompletten Reload)
        let lastMessageCount = <?php echo count($messages); ?>;

        function checkForNewMessages() {
            fetch(window.location.href.split('?')[0] + '?id=<?php echo $bewerbungId; ?>&ajax=check', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.messageCount > lastMessageCount) {
                        // Neue Nachrichten laden
                        loadNewMessages();
                        lastMessageCount = data.messageCount;
                    }
                })
                .catch(error => {
                    console.log('Error checking for new messages:', error);
                });
        }

        function loadNewMessages() {
            fetch(window.location.href.split('?')[0] + '?id=<?php echo $bewerbungId; ?>&ajax=messages', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const chatContainer = document.getElementById('chatContainer');
                    chatContainer.innerHTML = html;
                    scrollToBottom();

                    // Kleine Animation für neue Nachricht
                    const messages = chatContainer.querySelectorAll('.message');
                    if (messages.length > 0) {
                        const lastMessage = messages[messages.length - 1];
                        lastMessage.style.opacity = '0';
                        lastMessage.style.transform = 'translateY(20px)';

                        setTimeout(() => {
                            lastMessage.style.transition = 'all 0.3s ease';
                            lastMessage.style.opacity = '1';
                            lastMessage.style.transform = 'translateY(0)';
                        }, 100);
                    }
                })
                .catch(error => {
                    console.log('Error loading new messages:', error);
                });
        }

        // Auto-Check alle 10 Sekunden (nur wenn Seite aktiv)
        let checkInterval;

        function startAutoCheck() {
            checkInterval = setInterval(function() {
                if (!document.hidden) {
                    checkForNewMessages();
                }
            }, 10000); // Alle 10 Sekunden
        }

        function stopAutoCheck() {
            if (checkInterval) {
                clearInterval(checkInterval);
            }
        }

        // Auto-Check starten
        startAutoCheck();

        // Auto-Check pausieren wenn Tab nicht aktiv
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoCheck();
            } else {
                startAutoCheck();
            }
        });
    </script>
</body>

</html>