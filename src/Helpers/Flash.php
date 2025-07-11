<?php

namespace App\Helpers;

class Flash
{
    private static array $defaultTitles = [
        'success' => 'Erfolg!',
        'error' => 'Fehler!',
        'warning' => 'Achtung!',
        'info' => 'Information',
        'danger' => 'Fehler!',
    ];

    public static function success(string $text, ?string $title = null): void
    {
        self::setFlash('success', $text, $title);
    }

    public static function error(string $text, ?string $title = null): void
    {
        self::setFlash('danger', $text, $title);
    }

    public static function warning(string $text, ?string $title = null): void
    {
        self::setFlash('warning', $text, $title);
    }

    public static function info(string $text, ?string $title = null): void
    {
        self::setFlash('info', $text, $title);
    }

    public static function danger(string $text, ?string $title = null): void
    {
        self::setFlash('danger', $text, $title);
    }

    private static function setFlash(string $type, string $text, ?string $title = null): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'title' => $title ?? self::$defaultTitles[$type] ?? 'Nachricht',
            'text' => $text
        ];
    }

    public static function get(): ?array
    {
        if (!isset($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }

    public static function render(): void
    {
        $alert = self::get();

        if (!$alert) return;

        echo '<div class="alert alert-' . htmlspecialchars($alert['type']) . ' alert-dismissible fade show" role="alert">';
        echo '<h4 class="alert-heading">' . htmlspecialchars($alert['title']) . '</h4>';
        echo htmlspecialchars($alert['text']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>';
        echo '</div>';
    }

    // Für Rückwärtskompatibilität mit dem alten System
    public static function set(string $type, string $key, array $params = []): void
    {
        // Legacy-Unterstützung für das alte Alert-System
        $legacyAlerts = [
            'role' => [
                'deleted' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Die Rolle wurde erfolgreich gelöscht.'],
                'created' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Die Rolle wurde erfolgreich erstellt.'],
                'not-found' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Die Rolle wurde nicht gefunden.'],
                'invalid-id' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Ungültige Rollen-ID.'],
            ],
            'vehicle' => [
                'deleted' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Das Fahrzeug wurde erfolgreich gelöscht.'],
                'created' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Das Fahrzeug wurde erfolgreich erstellt.'],
                'not-found' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Das Fahrzeug wurde nicht gefunden.'],
                'invalid-id' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Ungültige Fahrzeug-ID.'],
            ],
            'target' => [
                'deleted' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Das Ziel wurde erfolgreich gelöscht.'],
                'created' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Das Ziel wurde erfolgreich erstellt.'],
                'not-found' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Das Ziel wurde nicht gefunden.'],
                'invalid-id' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Ungültige Ziel-ID.'],
            ],
            'edivi' => [
                'deleted' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Das Protokoll wurde erfolgreich gelöscht.'],
                'not-found' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Das Protokoll wurde nicht gefunden.'],
            ],
            'rank' => [
                'deleted' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Der Dienstgrad wurde erfolgreich gelöscht.'],
                'created' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Der Dienstgrad wurde erfolgreich erstellt.'],
                'not-found' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Der Dienstgrad wurde nicht gefunden.'],
                'invalid-id' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Ungültige Dienstgrad-ID.'],
            ],
            'qualification' => [
                'deleted' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Die Qualifikation wurde erfolgreich gelöscht.'],
                'created' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Die Qualifikation wurde erfolgreich erstellt.'],
                'not-found' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Die Qualifikation wurde nicht gefunden.'],
                'invalid-id' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Ungültige Qualifikations-ID.'],
            ],
            'personal' => [
                'deleted' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Das Profil wurde erfolgreich gelöscht.'],
            ],
            'user' => [
                'deleted' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Der Benutzer wurde erfolgreich gelöscht.'],
                'edit-self' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Du kannst dich nicht selbst bearbeiten! Nutze dafür <a href="<?= BASE_PATH ?>admin/users/editprofile.php">Profil bearbeiten</a>.'],
                'low-permissions' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Du kannst keine Benutzer mit den selben oder höheren Berechtigungen bearbeiten!'],
                'new-password' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Das Passwort für den Benutzer <strong>:username</strong> wurde erfolgreich bearbeitet.<br>- Neues Passwort: <code>:pass</code>'],
                'member-id-not-found' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Die angegebene Akten-ID wurde nicht gefunden. Bitte überprüfe die ID und versuche es erneut.'],
            ],
            'own' => [
                'pw-changed' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Deine Daten & dein Passwort wurden aktualisiert!'],
                'data-changed' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Deine Daten wurden aktualisiert!'],
            ],
            'success' => [
                'updated' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Änderungen erfolgreich gespeichert.'],
            ],
            'error' => [
                'exception' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Beim Speichern ist ein Fehler aufgetreten.'],
                'invalid' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Ungültige Eingabe.'],
                'not-allowed' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Keine Berechtigung.'],
                'no-permissions' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Dazu hast du nicht die richtigen Berechtigungen!'],
                'missing-fields' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Es wurden nicht alle Pflichtfelder ausgefüllt.'],
                'invalid-id' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Ungültige/Keine ID angegeben.'],
            ],
            'warning' => [
                'no-fullname' => ['type' => 'warning', 'title' => 'Achtung!', 'text' => 'Du hast noch keinen Namen hinterlegt. <u style="font-weight:bold">Bitte hinterlege deinen Namen jetzt!</u><br>Bei fehlendem Namen kann es zu technischen Problemen kommen.'],
            ],
            'dashboard.tile' => [
                'created' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Die Verlinkung wurde erfolgreich erstellt.'],
                'deleted' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Die Verlinkung wurde erfolgreich gelöscht.'],
                'not-found' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Die Verlinkung wurde nicht gefunden.'],
                'invalid-id' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Ungültige Verlinkungs-ID.'],
            ],
            'dashboard.category' => [
                'created' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Die Kategorie wurde erfolgreich erstellt.'],
                'deleted' => ['type' => 'success', 'title' => 'Erfolg!', 'text' => 'Die Kategorie wurde erfolgreich gelöscht.'],
                'not-found' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Die Kategorie wurde nicht gefunden.'],
                'invalid-id' => ['type' => 'danger', 'title' => 'Fehler!', 'text' => 'Ungültige Kategorie-ID.'],
            ]
        ];

        $alert = $legacyAlerts[$type][$key] ?? null;
        if (!$alert) return;

        // Inject parameters
        $text = $alert['text'];
        foreach ($params as $paramKey => $value) {
            $text = str_replace(':' . $paramKey, htmlspecialchars($value), $text);
        }

        $_SESSION['flash'] = [
            'type' => $alert['type'],
            'title' => $alert['title'],
            'text' => $text
        ];
    }
}
