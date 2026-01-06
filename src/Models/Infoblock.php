<?php

declare(strict_types=1);

namespace LiteCMS\Models;

use LiteCMS\DB;

final class Infoblock
{
    public function listBySection(int $sectionId): array
    {
        $sql = 'SELECT * FROM infoblocks WHERE section_id = :section_id ORDER BY id';
        return DB::run($sql, ['section_id' => $sectionId]);
    }

    public function findById(int $id): ?array
    {
        $rows = DB::run('SELECT * FROM infoblocks WHERE id = :id', ['id' => $id]);
        return $rows[0] ?? null;
    }

    public function findBySectionSlug(int $sectionId, string $slug): ?array
    {
        $sql = 'SELECT * FROM infoblocks WHERE section_id = :section_id AND slug = :slug';
        $rows = DB::run($sql, ['section_id' => $sectionId, 'slug' => $slug]);
        return $rows[0] ?? null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO infoblocks (site_id, section_id, component_id, title, slug, settings_json, is_active, created_at, updated_at)
                VALUES (:site_id, :section_id, :component_id, :title, :slug, :settings_json, :is_active, :created_at, :updated_at)';
        DB::exec($sql, $data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = 'UPDATE infoblocks SET title = :title, slug = :slug, settings_json = :settings_json, is_active = :is_active, updated_at = :updated_at
                WHERE id = :id';
        DB::exec($sql, $data);
    }

    public function delete(int $id): void
    {
        DB::exec('DELETE FROM infoblocks WHERE id = :id', ['id' => $id]);
    }
}
