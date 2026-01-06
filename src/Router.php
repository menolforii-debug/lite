<?php

declare(strict_types=1);

namespace LiteCMS;

use LiteCMS\Models\Infoblock;
use LiteCMS\Models\InfoblockItem;
use LiteCMS\Services\RenderService;
use LiteCMS\Services\SectionService;
use LiteCMS\Services\UrlService;

final class Router
{
    private SectionService $sections;
    private UrlService $urls;

    public function __construct()
    {
        $this->sections = new SectionService();
        $this->urls = new UrlService();
    }

    public function dispatch(): void
    {
        $path = $this->currentPath();
        $normalized = $this->urls->normalize($path);
        if ($normalized !== $path) {
            $this->redirect($normalized);
        }
        $segments = $this->segments($normalized);
        if ($segments === []) {
            $this->handleRoot($normalized);
            return;
        }
        $chain = $this->sections->resolveChain($segments);
        if ($chain !== []) {
            $this->handleListing($normalized, $chain);
            return;
        }
        $detail = $this->resolveItem($segments);
        if ($detail === null) {
            $this->notFound();
        }
        $this->handleDetail($normalized, $detail);
    }

    private function resolveItem(array $segments): ?array
    {
        $segment = array_pop($segments);
        $itemId = $this->parseItemId($segment);
        if ($itemId === null) {
            return null;
        }
        $chain = $this->sections->resolveChain($segments);
        if ($chain === []) {
            return null;
        }
        $section = end($chain);
        $prefix = substr($segment, 0, strrpos($segment, '_' . $itemId));
        $parts = explode('-', $prefix);
        $infoblockSlug = $parts[0];
        $infoblock = (new Infoblock())->findBySectionSlug((int) $section['id'], $infoblockSlug);
        if ($infoblock === null) {
            return null;
        }
        $item = (new InfoblockItem())->findByInfoblockId((int) $infoblock['id'], $itemId);
        if ($item === null) {
            return null;
        }
        return compact('chain', 'section', 'infoblock', 'item');
    }

    private function parseItemId(string $segment): ?int
    {
        if (!preg_match('/_(\d+)$/', $segment, $matches)) {
            return null;
        }
        return (int) $matches[1];
    }

    private function currentPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = str_replace('index.php', '', $path);
        return $path;
    }

    private function segments(string $path): array
    {
        $trimmed = trim($path, '/');
        if ($trimmed === '') {
            return [];
        }
        return explode('/', $trimmed);
    }

    private function defaultSection(): ?array
    {
        $sections = $this->sections->listTree();
        foreach ($sections as $section) {
            if ($section['parent_id'] === null) {
                return $section;
            }
        }
        return $sections[0] ?? null;
    }

    private function chainForSection(array $section): array
    {
        $sections = $this->sections->listTree();
        $chain = [$section];
        $parentId = $section['parent_id'];
        while ($parentId !== null) {
            foreach ($sections as $item) {
                if ($item['id'] === $parentId) {
                    array_unshift($chain, $item);
                    $parentId = $item['parent_id'];
                    continue 2;
                }
            }
            break;
        }
        return $chain;
    }

    private function handleRoot(string $normalized): void
    {
        $section = $this->defaultSection();
        if ($section === null) {
            $this->notFound();
        }
        $chain = $this->chainForSection($section);
        $canonical = $this->urls->sectionUrl($chain);
        if ($normalized !== $canonical) {
            $this->redirect($canonical);
        }
        (new RenderService())->renderSectionListing($section, $chain);
    }

    private function handleListing(string $normalized, array $chain): void
    {
        $canonical = $this->urls->sectionUrl($chain);
        if ($normalized !== $canonical) {
            $this->redirect($canonical);
        }
        (new RenderService())->renderSectionListing(end($chain), $chain);
    }

    private function handleDetail(string $normalized, array $detail): void
    {
        $itemSlug = $detail['item']['slug'] ?: null;
        $canonical = $this->urls->itemUrl($detail['chain'], $detail['infoblock']['slug'], $itemSlug, (int) $detail['item']['id']);
        if ($normalized !== $canonical) {
            $this->redirect($canonical);
        }
        (new RenderService())->renderItemDetail($detail['section'], $detail['chain'], $detail['infoblock'], $detail['item']);
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path, true, 301);
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo 'Not Found';
        exit;
    }
}
