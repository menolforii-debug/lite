<?php

declare(strict_types=1);

namespace LiteCMS\Services;

use LiteCMS\Config;
use LiteCMS\Models\Section;

final class SectionService
{
    private Section $sections;
    private SlugService $slugService;

    public function __construct()
    {
        $this->sections = new Section();
        $this->slugService = new SlugService();
    }

    public function listTree(): array
    {
        return $this->sections->listBySite(Config::SITE_ID);
    }

    public function create(array $input): int
    {
        $slug = $this->sanitizeSlug($input['slug'] ?? '', $input['title'] ?? '');
        $data = [
            'site_id' => Config::SITE_ID,
            'parent_id' => $input['parent_id'] ?? null,
            'title' => $input['title'] ?? '',
            'slug' => $slug,
            'description' => $input['description'] ?? '',
            'seo_title' => $input['seo_title'] ?? '',
            'seo_description' => $input['seo_description'] ?? '',
            'is_active' => (int) ($input['is_active'] ?? 1),
            'sort_order' => (int) ($input['sort_order'] ?? 0),
            'created_at' => date('c'),
            'updated_at' => date('c'),
        ];
        return $this->sections->create($data);
    }

    public function update(int $id, array $input): void
    {
        $slug = $this->sanitizeSlug($input['slug'] ?? '', $input['title'] ?? '');
        $data = [
            'title' => $input['title'] ?? '',
            'slug' => $slug,
            'description' => $input['description'] ?? '',
            'seo_title' => $input['seo_title'] ?? '',
            'seo_description' => $input['seo_description'] ?? '',
            'is_active' => (int) ($input['is_active'] ?? 1),
            'sort_order' => (int) ($input['sort_order'] ?? 0),
            'updated_at' => date('c'),
        ];
        $this->sections->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->sections->delete($id);
    }

    public function resolveChain(array $segments): array
    {
        $chain = [];
        $parentId = null;
        foreach ($segments as $slug) {
            $section = $this->sections->findByParentSlug(Config::SITE_ID, $parentId, $slug);
            if ($section === null) {
                return [];
            }
            $chain[] = $section;
            $parentId = $section['id'];
        }
        return $chain;
    }

    private function sanitizeSlug(string $slug, string $title): string
    {
        $value = $slug !== '' ? $slug : $this->slugService->generate($title);
        if (!$this->slugService->isValid($value)) {
            throw new \InvalidArgumentException('Недопустимое ключевое слово');
        }
        return $value;
    }
}
