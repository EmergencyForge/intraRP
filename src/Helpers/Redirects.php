<?php

namespace App\Helpers;

class Redirects
{
    public static function isInternalUrl(string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $parsedUrl = parse_url($url);
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            return true;
        }

        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        $currentScheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

        $refererHost = $parsedUrl['host'];
        $refererScheme = $parsedUrl['scheme'] ?? $currentScheme;

        $refererPort = $parsedUrl['port'] ?? ($refererScheme === 'https' ? 443 : 80);

        $refererHostWithPort = $refererHost;
        if (($refererScheme === 'http' && $refererPort !== 80) ||
            ($refererScheme === 'https' && $refererPort !== 443)
        ) {
            $refererHostWithPort .= ':' . $refererPort;
        }

        return ($refererHostWithPort === $currentHost && $refererScheme === $currentScheme);
    }

    public static function getRedirectUrl(string $defaultUrl, array $params = []): string
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        if (self::isInternalUrl($referer)) {
            return self::addParamsToUrl($referer, $params);
        }

        return self::addParamsToUrl($defaultUrl, $params);
    }

    private static function addParamsToUrl(string $url, array $params): string
    {
        if (empty($params)) {
            return $url;
        }

        $separator = strpos($url, '?') !== false ? '&' : '?';
        $queryString = http_build_query($params);

        return $url . $separator . $queryString;
    }

    public static function redirectWithSuccess(string $defaultUrl, array $params = ['success' => 1]): void
    {
        $redirectUrl = self::getRedirectUrl($defaultUrl, $params);
        header("Location: " . $redirectUrl);
        exit();
    }

    public static function redirect(string $defaultUrl, array $params = []): void
    {
        $redirectUrl = self::getRedirectUrl($defaultUrl, $params);
        header("Location: " . $redirectUrl);
        exit();
    }

    public static function rememberCurrentPage(string $sessionKey = 'redirect_after'): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $currentUrl = self::getCurrentUrl();
        if (self::isInternalUrl($currentUrl)) {
            $_SESSION[$sessionKey] = $currentUrl;
        }
    }

    public static function redirectToRememberedPage(string $defaultUrl, string $sessionKey = 'redirect_after', array $params = ['success' => 1]): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $rememberedUrl = $_SESSION[$sessionKey] ?? '';
        unset($_SESSION[$sessionKey]);

        if (!empty($rememberedUrl) && self::isInternalUrl($rememberedUrl)) {
            $redirectUrl = self::addParamsToUrl($rememberedUrl, $params);
        } else {
            $redirectUrl = self::addParamsToUrl($defaultUrl, $params);
        }

        header("Location: " . $redirectUrl);
        exit();
    }

    private static function getCurrentUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        return $protocol . '://' . $host . $uri;
    }
}
