<?php

declare(strict_types=1);

namespace LiteCMS\Services;

use LiteCMS\Config;
use LiteCMS\Models\Component;
use LiteCMS\Models\InfoblockItem;
use LiteCMS\Models\SectionInfoblock;

final class RenderService
{
    public function renderSectionListing(array $section, array $chain): void
    {
        $infoblocks = $this->fetchInfoblocks((int) $section['id']);
        $html = '';
        foreach ($infoblocks as $infoblock) {
            $items = (new InfoblockItem())->listByInfoblock((int) $infoblock['id']);
            $html .= $this->renderComponent($infoblock, $items, false);
        }
        $page = [
            'title' => $section['seo_title'] ?: $section['title'],
            'description' => $section['seo_description'] ?? '',
            'content' => $html,
        ];
        $this->renderLayout($page);
    }

    public function renderItemDetail(array $section, array $chain, array $infoblock, array $item): void
    {
        $html = $this->renderComponent($infoblock, [$item], true);
        $page = [
            'title' => $item['title'],
            'description' => $section['seo_description'] ?? '',
            'content' => $html,
        ];
        $this->renderLayout($page);
    }

    private function renderComponent(array $infoblock, array $items, bool $detail): string
    {
        $component = (new Component())->findById((int) $infoblock['component_id']);
        if ($component === null) {
            return '';
        }
        $path = rtrim($component['storage_path'], '/');
        $classFile = $path . '/Component.php';
        if (file_exists($classFile)) {
            require_once $classFile;
        }
        if (!class_exists($component['class_name'])) {
            return '';
        }
        $instance = new $component['class_name']();
        if ($detail && method_exists($instance, 'renderDetail')) {
            return (string) $instance->renderDetail($infoblock, $items[0] ?? null);
        }
        if (method_exists($instance, 'renderList')) {
            return (string) $instance->renderList($infoblock, $items);
        }
        return '';
    }

    private function renderLayout(array $page): void
    {
        $layoutPath = __DIR__ . '/../../storage/templates/default';
        $title = $page['title'] ?? '';
        $description = $page['description'] ?? '';
        $content = $page['content'] ?? '';
        require $layoutPath . '/header.php';
        require $layoutPath . '/main.php';
        require $layoutPath . '/footer.php';
    }

    private function fetchInfoblocks(int $sectionId): array
    {
        $links = (new SectionInfoblock())->listBySection($sectionId);
        $infoblocks = [];
        foreach ($links as $link) {
            $infoblocks[] = $link + ['_link' => $link];
        }
        if ($infoblocks === []) {
            return (new \LiteCMS\Models\Infoblock())->listBySection($sectionId);
        }
        $result = [];
        foreach ($infoblocks as $link) {
            $block = (new \LiteCMS\Models\Infoblock())->findById((int) $link['infoblock_id']);
            if ($block !== null) {
                $result[] = $block;
            }
        }
        return $result;
    }
}
