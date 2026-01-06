<?php

declare(strict_types=1);

namespace LiteCMS\Models;

use LiteCMS\DB;

final class Site
{
    public function findActiveByDomain(string $domain): ?array
    {
        $sql = 'SELECT * FROM sites WHERE domain = :domain AND is_active = 1';
        $rows = DB::run($sql, ['domain' => $domain]);
        return $rows[0] ?? null;
    }

    public function findActiveDefault(): ?array
    {
        $sql = 'SELECT * FROM sites WHERE is_active = 1 ORDER BY id LIMIT 1';
        $rows = DB::run($sql);
        return $rows[0] ?? null;
    }
}
