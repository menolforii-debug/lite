<?php

declare(strict_types=1);

namespace LiteCMS\Services;

use LiteCMS\Config;
use LiteCMS\Models\Infoblock;

final class InfoblockService
{
    private Infoblock $infoblocks;
    private SlugService $slugService;

    public function __construct()
    {
        $this->infoblocks = new Infoblock();
        $this->slugService = new SlugService();
    }

    public function listBySection(int $sectionId): array
    {
        return $this->infoblocks->listBySection($sectionId);
    }

    public function create(array $input): int
    {
        $slug = $this->sanitizeSlug($input['slug'] ?? '', $input['title'] ?? '');
        $data = [
            'site_id' => Config::SITE_ID,
            'section_id' => (int) ($input['section_id'] ?? 0),
            'component_id' => (int) ($input['component_id'] ?? 1),
            'title' => $input['title'] ?? '',
            'slug' => $slug,
            'settings_json' => $input['settings_json'] ?? '{}',
            'is_active' => (int) ($input['is_active'] ?? 1),
            'created_at' => date('c'),
            'updated_at' => date('c'),
        ];
        $id = $this->infoblocks->create($data);
        (new \\LiteCMS\\Models\\SectionInfoblock())->create([
            'section_id' => $data['section_id'],
            'infoblock_id' => $id,
            'sort_order' => 0,
            'position' => null,
        ]);
        return $id;
    }

    public function update(int $id, array $input): void
    {
        $slug = $this->sanitizeSlug($input['slug'] ?? '', $input['title'] ?? '');
        $data = [
            'title' => $input['title'] ?? '',
            'slug' => $slug,
            'settings_json' => $input['settings_json'] ?? '{}',
            'is_active' => (int) ($input['is_active'] ?? 1),
            'updated_at' => date('c'),
        ];
        $this->infoblocks->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->infoblocks->delete($id);
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
