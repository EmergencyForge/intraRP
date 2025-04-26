<?php

namespace App\Helpers;

class Flash
{
    private static array $alerts = [
        'role' => [
            'deleted' => ['type' => 'success'],
            'created' => ['type' => 'success'],
            'not-found' => ['type' => 'danger'],
            'invalid-id' => ['type' => 'danger'],
        ],
        'vehicle' => [
            'deleted' => ['type' => 'success'],
            'created' => ['type' => 'success'],
            'not-found' => ['type' => 'danger'],
            'invalid-id' => ['type' => 'danger'],
        ],
        'target' => [
            'deleted' => ['type' => 'success'],
            'created' => ['type' => 'success'],
            'not-found' => ['type' => 'danger'],
            'invalid-id' => ['type' => 'danger'],
        ],
        'edivi' => [
            'deleted' => ['type' => 'success'],
            'not-found' => ['type' => 'danger'],
        ],
        'rank' => [
            'deleted' => ['type' => 'success'],
            'created' => ['type' => 'success'],
            'not-found' => ['type' => 'danger'],
            'invalid-id' => ['type' => 'danger'],
        ],
        'qualification' => [
            'deleted' => ['type' => 'success'],
            'created' => ['type' => 'success'],
            'not-found' => ['type' => 'danger'],
            'invalid-id' => ['type' => 'danger'],
        ],
        'personal' => [
            'deleted' => ['type' => 'success'],
        ],
        'user' => [
            'deleted' => ['type' => 'success'],
            'edit-self' => ['type' => 'danger'],
            'low-permissions' => ['type' => 'danger'],
            'new-password' => ['type' => 'success'],
        ],
        'own' => [
            'pw-changed' => ['type' => 'success'],
            'data-changed' => ['type' => 'success'],
        ],
        'success' => [
            'updated' => ['type' => 'success'],
        ],
        'error' => [
            'exception' => ['type' => 'danger'],
            'invalid' => ['type' => 'danger'],
            'not-allowed' => ['type' => 'danger'],
            'no-permissions' => ['type' => 'danger'],
            'missing-fields' => ['type' => 'danger'],
        ],
        'dashboard.tile' => [
            'created' => ['type' => 'success'],
            'deleted' => ['type' => 'success'],
            'not-found' => ['type' => 'danger'],
            'invalid-id' => ['type' => 'danger'],
        ],
        'dashboard.category' => [
            'created' => ['type' => 'success'],
            'deleted' => ['type' => 'success'],
            'not-found' => ['type' => 'danger'],
            'invalid-id' => ['type' => 'danger'],
        ]
    ];

    public static function set(string $type, string $key, array $params = []): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'key' => $key,
            'params' => $params
        ];
    }

    public static function get(): ?array
    {
        if (!isset($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        $alert = self::$alerts[$flash['type']][$flash['key']] ?? null;
        if (!$alert) return null;

        $text = __("flash.{$flash['type']}.{$flash['key']}");
        foreach ($flash['params'] ?? [] as $key => $value) {
            $text = str_replace(':' . $key, htmlspecialchars($value), $text);
        }

        return [
            'type' => $alert['type'],
            'title' => __("flash.titles.{$alert['type']}"),
            'text' => $text
        ];
    }


    public static function render(): void
    {
        $alert = self::get();

        if (!$alert) return;

        echo '<div class="alert alert-' . htmlspecialchars($alert['type']) . ' alert-dismissible fade show" role="alert">';
        echo '<h4 class="alert-heading">' . htmlspecialchars($alert['title']) . '</h4>';
        echo $alert['text'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>';
        echo '</div>';
    }
}
