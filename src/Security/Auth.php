<?php

declare(strict_types=1);

namespace LiteCMS\Security;

use LiteCMS\Models\User;

final class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function login(string $login, string $password): bool
    {
        $user = (new User())->findByLogin($login);
        if ($user === null) {
            return false;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }
        if ((int) $user['is_active'] !== 1) {
            return false;
        }
        $_SESSION['user'] = [
            'id' => $user['id'],
            'login' => $user['login'],
            'role' => $user['role'],
        ];
        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
    }
}
