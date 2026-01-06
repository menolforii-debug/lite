<?php

declare(strict_types=1);

namespace LiteCMS\Models;

use LiteCMS\DB;

final class Component
{
    public function findById(int $id): ?array
    {
        $rows = DB::run('SELECT * FROM components WHERE id = :id', ['id' => $id]);
        return $rows[0] ?? null;
    }
}
