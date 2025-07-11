<?php
session_start();
require_once __DIR__ . '/../../assets/config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../assets/config/database.php';

if (!isset($_SESSION['userid']) || !isset($_SESSION['permissions'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Nicht angemeldet']);
    exit();
}

use App\Auth\Permissions;
use App\Utils\AuditLogger;

// Admin-Berechtigung prüfen
if (!Permissions::check(['admin', 'personnel.edit'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Keine Berechtigung']);
    exit();
}

header('Content-Type: application/json');

// POST-Daten lesen
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action']) || !isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage']);
    exit();
}

$action = $input['action'];
$bewerbungId = (int)$input['id'];

try {
    switch ($action) {
        case 'change_status':
            if (!isset($input['status'])) {
                throw new Exception('Status nicht angegeben');
            }

            $newStatus = (int)$input['status'];

            // Aktuelle Bewerbung laden
            $stmt = $pdo->prepare("SELECT * FROM intra_bewerbung WHERE id = :id");
            $stmt->execute(['id' => $bewerbungId]);
            $bewerbung = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$bewerbung) {
                throw new Exception('Bewerbung nicht gefunden');
            }

            if ($bewerbung['deleted'] == 1) {
                throw new Exception('Gelöschte Bewerbungen können nicht bearbeitet werden');
            }

            // Status aktualisieren
            $updateStmt = $pdo->prepare("UPDATE intra_bewerbung SET closed = :status WHERE id = :id");
            $updateStmt->execute([
                'status' => $newStatus,
                'id' => $bewerbungId
            ]);

            // Status-Log erstellen
            $logStmt = $pdo->prepare("INSERT INTO intra_bewerbung_statuslog (bewerbungid, status_alt, status_neu, user, discordid) VALUES (:bewerbungid, :status_alt, :status_neu, :user, :discordid)");
            $logStmt->execute([
                'bewerbungid' => $bewerbungId,
                'status_alt' => $bewerbung['closed'],
                'status_neu' => $newStatus,
                'user' => $_SESSION['cirs_user'],
                'discordid' => $_SESSION['discordtag'] ?? 'Admin'
            ]);

            // Systemnachricht erstellen
            $statusText = $newStatus == 1 ? 'bearbeitet' : 'wieder geöffnet';
            $messageStmt = $pdo->prepare("INSERT INTO intra_bewerbung_messages (bewerbungid, text, user, discordid) VALUES (:bewerbungid, :text, :user, :discordid)");
            $messageStmt->execute([
                'bewerbungid' => $bewerbungId,
                'text' => "Bewerbung wurde als $statusText markiert.",
                'user' => 'System',
                'discordid' => 'System'
            ]);

            // Audit-Log
            $auditlogger = new AuditLogger($pdo);
            $auditlogger->log($_SESSION['userid'], 'Bewerbungsstatus geändert', "Bewerbung #$bewerbungId: Status $statusText", 'Bewerbung', $bewerbungId);

            echo json_encode(['success' => true, 'message' => "Status erfolgreich geändert"]);
            break;

        case 'delete':
            // Aktuelle Bewerbung laden
            $stmt = $pdo->prepare("SELECT * FROM intra_bewerbung WHERE id = :id");
            $stmt->execute(['id' => $bewerbungId]);
            $bewerbung = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$bewerbung) {
                throw new Exception('Bewerbung nicht gefunden');
            }

            if ($bewerbung['deleted'] == 1) {
                throw new Exception('Bewerbung ist bereits gelöscht');
            }

            // Bewerbung als gelöscht markieren
            $updateStmt = $pdo->prepare("UPDATE intra_bewerbung SET deleted = 1 WHERE id = :id");
            $updateStmt->execute(['id' => $bewerbungId]);

            // Systemnachricht erstellen
            $messageStmt = $pdo->prepare("INSERT INTO intra_bewerbung_messages (bewerbungid, text, user, discordid) VALUES (:bewerbungid, :text, :user, :discordid)");
            $messageStmt->execute([
                'bewerbungid' => $bewerbungId,
                'text' => 'Bewerbung wurde gelöscht.',
                'user' => 'System',
                'discordid' => 'System'
            ]);

            // Audit-Log
            $auditlogger = new AuditLogger($pdo);
            $auditlogger->log($_SESSION['userid'], 'Bewerbung gelöscht', "Bewerbung #$bewerbungId von " . $bewerbung['fullname'] . " gelöscht", 'Bewerbung', $bewerbungId);

            echo json_encode(['success' => true, 'message' => 'Bewerbung erfolgreich gelöscht']);
            break;

        case 'restore':
            // Aktuelle Bewerbung laden
            $stmt = $pdo->prepare("SELECT * FROM intra_bewerbung WHERE id = :id");
            $stmt->execute(['id' => $bewerbungId]);
            $bewerbung = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$bewerbung) {
                throw new Exception('Bewerbung nicht gefunden');
            }

            if ($bewerbung['deleted'] == 0) {
                throw new Exception('Bewerbung ist nicht gelöscht');
            }

            // Bewerbung wiederherstellen
            $updateStmt = $pdo->prepare("UPDATE intra_bewerbung SET deleted = 0 WHERE id = :id");
            $updateStmt->execute(['id' => $bewerbungId]);

            // Systemnachricht erstellen
            $messageStmt = $pdo->prepare("INSERT INTO intra_bewerbung_messages (bewerbungid, text, user, discordid) VALUES (:bewerbungid, :text, :user, :discordid)");
            $messageStmt->execute([
                'bewerbungid' => $bewerbungId,
                'text' => 'Bewerbung wurde wiederhergestellt.',
                'user' => 'System',
                'discordid' => 'System'
            ]);

            // Audit-Log
            $auditlogger = new AuditLogger($pdo);
            $auditlogger->log($_SESSION['userid'], 'Bewerbung wiederhergestellt', "Bewerbung #$bewerbungId von " . $bewerbung['fullname'] . " wiederhergestellt", 'Bewerbung', $bewerbungId);

            echo json_encode(['success' => true, 'message' => 'Bewerbung erfolgreich wiederhergestellt']);
            break;

        case 'bulk_action':
            if (!isset($input['ids']) || !is_array($input['ids']) || !isset($input['bulk_action'])) {
                throw new Exception('Ungültige Bulk-Aktion Parameter');
            }

            $ids = array_map('intval', $input['ids']);
            $bulkAction = $input['bulk_action'];
            $successCount = 0;

            foreach ($ids as $id) {
                try {
                    switch ($bulkAction) {
                        case 'close':
                            $updateStmt = $pdo->prepare("UPDATE intra_bewerbung SET closed = 1 WHERE id = :id AND deleted = 0");
                            $updateStmt->execute(['id' => $id]);

                            if ($updateStmt->rowCount() > 0) {
                                // Status-Log erstellen
                                $logStmt = $pdo->prepare("INSERT INTO intra_bewerbung_statuslog (bewerbungid, status_alt, status_neu, user, discordid) VALUES (:bewerbungid, 0, 1, :user, :discordid)");
                                $logStmt->execute([
                                    'bewerbungid' => $id,
                                    'user' => $_SESSION['cirs_user'],
                                    'discordid' => $_SESSION['discordtag'] ?? 'Admin'
                                ]);

                                // Systemnachricht
                                $messageStmt = $pdo->prepare("INSERT INTO intra_bewerbung_messages (bewerbungid, text, user, discordid) VALUES (:bewerbungid, :text, :user, :discordid)");
                                $messageStmt->execute([
                                    'bewerbungid' => $id,
                                    'text' => 'Bewerbung wurde als bearbeitet markiert (Bulk-Aktion).',
                                    'user' => 'System',
                                    'discordid' => 'System'
                                ]);

                                $successCount++;
                            }
                            break;

                        case 'open':
                            $updateStmt = $pdo->prepare("UPDATE intra_bewerbung SET closed = 0 WHERE id = :id AND deleted = 0");
                            $updateStmt->execute(['id' => $id]);

                            if ($updateStmt->rowCount() > 0) {
                                // Status-Log erstellen
                                $logStmt = $pdo->prepare("INSERT INTO intra_bewerbung_statuslog (bewerbungid, status_alt, status_neu, user, discordid) VALUES (:bewerbungid, 1, 0, :user, :discordid)");
                                $logStmt->execute([
                                    'bewerbungid' => $id,
                                    'user' => $_SESSION['cirs_user'],
                                    'discordid' => $_SESSION['discordtag'] ?? 'Admin'
                                ]);

                                // Systemnachricht
                                $messageStmt = $pdo->prepare("INSERT INTO intra_bewerbung_messages (bewerbungid, text, user, discordid) VALUES (:bewerbungid, :text, :user, :discordid)");
                                $messageStmt->execute([
                                    'bewerbungid' => $id,
                                    'text' => 'Bewerbung wurde wieder geöffnet (Bulk-Aktion).',
                                    'user' => 'System',
                                    'discordid' => 'System'
                                ]);

                                $successCount++;
                            }
                            break;

                        case 'delete':
                            $updateStmt = $pdo->prepare("UPDATE intra_bewerbung SET deleted = 1 WHERE id = :id AND deleted = 0");
                            $updateStmt->execute(['id' => $id]);

                            if ($updateStmt->rowCount() > 0) {
                                // Systemnachricht
                                $messageStmt = $pdo->prepare("INSERT INTO intra_bewerbung_messages (bewerbungid, text, user, discordid) VALUES (:bewerbungid, :text, :user, :discordid)");
                                $messageStmt->execute([
                                    'bewerbungid' => $id,
                                    'text' => 'Bewerbung wurde gelöscht (Bulk-Aktion).',
                                    'user' => 'System',
                                    'discordid' => 'System'
                                ]);

                                $successCount++;
                            }
                            break;
                    }
                } catch (Exception $e) {
                    // Einzelne Fehler ignorieren und weitermachen
                    error_log("Bulk Action Error for ID $id: " . $e->getMessage());
                }
            }

            // Audit-Log für Bulk-Aktion
            $auditlogger = new AuditLogger($pdo);
            $auditlogger->log($_SESSION['userid'], 'Bulk-Aktion Bewerbungen', "Aktion: $bulkAction, Erfolgreich: $successCount von " . count($ids), 'Bewerbung', 0);

            echo json_encode([
                'success' => true,
                'message' => "$successCount von " . count($ids) . " Bewerbungen erfolgreich bearbeitet"
            ]);
            break;

        default:
            throw new Exception('Unbekannte Aktion: ' . $action);
    }
} catch (Exception $e) {
    error_log("Bewerbung Action Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
