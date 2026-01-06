<?php

declare(strict_types=1);

namespace LiteCMS\Models;

use LiteCMS\DB;

final class InfoblockItem
{
    public function listByInfoblock(int $infoblockId): array
    {
        $sql = 'SELECT * FROM infoblock_items WHERE infoblock_id = :infoblock_id ORDER BY id';
        return DB::run($sql, ['infoblock_id' => $infoblockId]);
    }

    public function findById(int $id): ?array
    {
        $rows = DB::run('SELECT * FROM infoblock_items WHERE id = :id', ['id' => $id]);
        return $rows[0] ?? null;
    }

    public function findByInfoblockId(int $infoblockId, int $id): ?array
    {
        $sql = 'SELECT * FROM infoblock_items WHERE infoblock_id = :infoblock_id AND id = :id';
        $rows = DB::run($sql, ['infoblock_id' => $infoblockId, 'id' => $id]);
        return $rows[0] ?? null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO infoblock_items (site_id, infoblock_id, title, slug, content_html, data_json, is_active, created_at, updated_at)
                VALUES (:site_id, :infoblock_id, :title, :slug, :content_html, :data_json, :is_active, :created_at, :updated_at)';
        DB::exec($sql, $data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = 'UPDATE infoblock_items SET title = :title, slug = :slug, content_html = :content_html, data_json = :data_json,
                is_active = :is_active, updated_at = :updated_at WHERE id = :id';
        DB::exec($sql, $data);
    }

    public function delete(int $id): void
    {
        DB::exec('DELETE FROM infoblock_items WHERE id = :id', ['id' => $id]);
    }
}
