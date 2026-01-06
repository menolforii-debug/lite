<?php

declare(strict_types=1);

namespace LiteCMS\Services;

use LiteCMS\Config;
use LiteCMS\Models\InfoblockItem;

final class ItemService
{
    private InfoblockItem $items;
    private SlugService $slugService;

    public function __construct()
    {
        $this->items = new InfoblockItem();
        $this->slugService = new SlugService();
    }

    public function listByInfoblock(int $infoblockId): array
    {
        return $this->items->listByInfoblock($infoblockId);
    }

    public function create(array $input): int
    {
        $slug = $this->sanitizeSlug($input['slug'] ?? '');
        $data = [
            'site_id' => Config::SITE_ID,
            'infoblock_id' => (int) ($input['infoblock_id'] ?? 0),
            'title' => $input['title'] ?? '',
            'slug' => $slug,
            'content_html' => $input['content_html'] ?? '',
            'data_json' => $input['data_json'] ?? '{}',
            'is_active' => (int) ($input['is_active'] ?? 1),
            'created_at' => date('c'),
            'updated_at' => date('c'),
        ];
        return $this->items->create($data);
    }

    public function update(int $id, array $input): void
    {
        $slug = $this->sanitizeSlug($input['slug'] ?? '');
        $data = [
            'title' => $input['title'] ?? '',
            'slug' => $slug,
            'content_html' => $input['content_html'] ?? '',
            'data_json' => $input['data_json'] ?? '{}',
            'is_active' => (int) ($input['is_active'] ?? 1),
            'updated_at' => date('c'),
        ];
        $this->items->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->items->delete($id);
    }

    private function sanitizeSlug(string $slug): ?string
    {
        $value = trim($slug);
        if ($value === '') {
            return null;
        }
        if (!$this->slugService->isValid($value)) {
            throw new \InvalidArgumentException('Недопустимое ключевое слово');
        }
        return $value;
    }
}
