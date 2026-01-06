<?php

declare(strict_types=1);

namespace LiteCMS\Models;

use LiteCMS\DB;

final class User
{
    public function findByLogin(string $login): ?array
    {
        $rows = DB::run('SELECT * FROM users WHERE login = :login', ['login' => $login]);
        return $rows[0] ?? null;
    }
}
