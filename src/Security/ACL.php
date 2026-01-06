<?php

declare(strict_types=1);

namespace LiteCMS\Security;

final class ACL
{
    public static function isAdmin(): bool
    {
        return (self::userRole() === 'admin');
    }

    public static function isEditor(): bool
    {
        return in_array(self::userRole(), ['admin', 'editor'], true);
    }

    public static function can(string $resource): bool
    {
        if (self::isAdmin()) {
            return true;
        }
        if (self::isEditor() && in_array($resource, ['sections', 'infoblocks', 'items'], true)) {
            return true;
        }
        return false;
    }

    public static function require(string $resource): void
    {
        if (!self::can($resource)) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }

    private static function userRole(): string
    {
        return (string) ($_SESSION['user']['role'] ?? 'guest');
    }
}
