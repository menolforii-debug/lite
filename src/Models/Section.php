<?php

declare(strict_types=1);

namespace LiteCMS\Models;

use LiteCMS\DB;

final class Section
{
    public function listBySite(int $siteId): array
    {
        $sql = 'SELECT * FROM sections WHERE site_id = :site_id ORDER BY sort_order, id';
        return DB::run($sql, ['site_id' => $siteId]);
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT * FROM sections WHERE id = :id';
        $rows = DB::run($sql, ['id' => $id]);
        return $rows[0] ?? null;
    }

    public function findByParentSlug(int $siteId, ?int $parentId, string $slug): ?array
    {
        if ($parentId === null) {
            $sql = 'SELECT * FROM sections WHERE site_id = :site_id AND parent_id IS NULL AND slug = :slug';
            $rows = DB::run($sql, ['site_id' => $siteId, 'slug' => $slug]);
            return $rows[0] ?? null;
        }
        $sql = 'SELECT * FROM sections WHERE site_id = :site_id AND parent_id = :parent_id AND slug = :slug';
        $rows = DB::run($sql, ['site_id' => $siteId, 'parent_id' => $parentId, 'slug' => $slug]);
        return $rows[0] ?? null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO sections (site_id, parent_id, title, slug, description, seo_title, seo_description, is_active, sort_order, created_at, updated_at)
                VALUES (:site_id, :parent_id, :title, :slug, :description, :seo_title, :seo_description, :is_active, :sort_order, :created_at, :updated_at)';
        DB::exec($sql, $data);
        return (int) DB::pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = 'UPDATE sections SET title = :title, slug = :slug, description = :description, seo_title = :seo_title,
                seo_description = :seo_description, is_active = :is_active, sort_order = :sort_order, updated_at = :updated_at
                WHERE id = :id';
        DB::exec($sql, $data);
    }

    public function delete(int $id): void
    {
        $sql = 'DELETE FROM sections WHERE id = :id';
        DB::exec($sql, ['id' => $id]);
    }
}
