<?php

declare(strict_types=1);

namespace LiteCMS\Security;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        return (string) $_SESSION['csrf_token'];
    }

    public static function verify(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }
        return hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token);
    }
}
