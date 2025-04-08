<?php

namespace App\Localization;

class Lang
{
    protected static $phrases = [];
    protected static $language = 'de';

    public static function setLanguage(string $lang)
    {
        self::$language = $lang;
        $file = __DIR__ . "/../../assets/lang/{$lang}.php";
        if (file_exists($file)) {
            self::$phrases = require $file;
        } else {
            throw new \Exception("Language file for '{$lang}' not found.");
        }
    }

    public static function get(string $key, array $values = []): string
    {
        if (!isset(self::$phrases[$key])) {
            return "[unknown:{$key}]";
        }

        return vsprintf(self::$phrases[$key], $values);
    }
}
