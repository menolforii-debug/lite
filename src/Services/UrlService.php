<?php

declare(strict_types=1);

namespace LiteCMS\Services;

final class UrlService
{
    public function sectionUrl(array $chain): string
    {
        $path = '/' . implode('/', array_column($chain, 'slug')) . '/';
        return $this->normalize($path);
    }

    public function itemUrl(array $chain, string $infoblockSlug, ?string $itemSlug, int $itemId): string
    {
        $prefix = $infoblockSlug;
        if ($itemSlug !== null && $itemSlug !== '') {
            $prefix .= '-' . $itemSlug;
        }
        $path = '/' . implode('/', array_column($chain, 'slug')) . '/' . $prefix . '_' . $itemId . '/';
        return $this->normalize($path);
    }

    public function normalize(string $path): string
    {
        $path = strtolower($path);
        $path = preg_replace('#/+#', '/', $path ?? '/');
        if (!str_ends_with($path, '/')) {
            $path .= '/';
        }
        return $path;
    }
}
