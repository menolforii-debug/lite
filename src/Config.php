<?php

declare(strict_types=1);

namespace LiteCMS;

final class Config
{
    public const BASE_URL = '';
    public const DB_PATH = __DIR__ . '/../storage/database.sqlite';
    public const SITE_ID = 1;

    public static function init(): void
    {
        if (self::BASE_URL === '') {
            $base = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $_SERVER['BASE_URL'] = $base === '/' ? '' : $base;
            return;
        }
        $_SERVER['BASE_URL'] = self::BASE_URL;
    }

    public static function baseUrl(string $path = ''): string
    {
        $base = $_SERVER['BASE_URL'] ?? '';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}
