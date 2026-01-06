<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap.php';

use LiteCMS\Http\Input;
use LiteCMS\Http\Response;
use LiteCMS\Models\Infoblock;
use LiteCMS\Models\Section;
use LiteCMS\Services\SlugService;
use LiteCMS\Services\UrlService;
use LiteCMS\Security\Auth;

if (!Auth::check()) {
    Response::jsonError('Unauthorized', 401);
}

$input = Input::json();
$slug = (string) ($input['slug'] ?? '');
$entity = (string) ($input['entity'] ?? '');
$slugService = new SlugService();

if ($slug === '') {
    Response::jsonSuccess(['preview' => '']);
}

if (!$slugService->isValid($slug)) {
    Response::jsonError('Недопустимое ключевое слово');
}

if ($entity === 'infoblocks') {
    $sectionId = (int) ($input['section_id'] ?? 0);
    $existing = (new Infoblock())->findBySectionSlug($sectionId, $slug);
    if ($existing !== null && (int) $existing['id'] !== (int) ($input['id'] ?? 0)) {
        Response::jsonError('Ключевое слово уже занято');
    }
}

if ($entity === 'sections') {
    $sections = (new Section())->listBySite(1);
    foreach ($sections as $section) {
        $matchParent = $section['parent_id'] == ($input['parent_id'] ?? null);
        if ($matchParent && $section['slug'] === $slug && (int) $section['id'] !== (int) ($input['id'] ?? 0)) {
            Response::jsonError('Ключевое слово уже занято');
        }
    }
}

$preview = buildPreview($entity, $slug, $input);
Response::jsonSuccess(['preview' => $preview]);

function buildPreview(string $entity, string $slug, array $input): string
{
    $sections = (new Section())->listBySite(1);
    $urlService = new UrlService();
    if ($entity === 'sections') {
        $chain = buildChain((int) ($input['id'] ?? 0), $slug, $sections, $input['parent_id'] ?? null);
        return $urlService->sectionUrl($chain);
    }
    if ($entity === 'infoblocks') {
        $sectionId = (int) ($input['section_id'] ?? 0);
        $chain = buildChain($sectionId, null, $sections, null);
        return $urlService->itemUrl($chain, $slug, null, 0);
    }
    if ($entity === 'items') {
        $infoblockSlug = (string) ($input['infoblock_slug'] ?? '');
        $sectionId = (int) ($input['section_id'] ?? 0);
        $chain = buildChain($sectionId, null, $sections, null);
        return $urlService->itemUrl($chain, $infoblockSlug, $slug, 0);
    }
    return '';
}

function buildChain(int $sectionId, ?string $currentSlug, array $sections, $parentOverride): array
{
    $chain = [];
    $current = null;
    foreach ($sections as $section) {
        if ($section['id'] === $sectionId) {
            $current = $section;
            break;
        }
    }
    if ($current === null && $currentSlug !== null) {
        $current = ['id' => 0, 'slug' => $currentSlug, 'parent_id' => $parentOverride];
    }
    if ($current !== null && $currentSlug !== null) {
        $current['slug'] = $currentSlug;
        if ($parentOverride !== null) {
            $current['parent_id'] = $parentOverride;
        }
    }
    while ($current) {
        $chain[] = $current;
        $parentId = $current['parent_id'];
        $current = null;
        foreach ($sections as $section) {
            if ($section['id'] === $parentId) {
                $current = $section;
                break;
            }
        }
    }
    return array_reverse($chain);
}
