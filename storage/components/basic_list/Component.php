<?php

declare(strict_types=1);

final class BasicListComponent
{
    public function renderList(array $infoblock, array $items): string
    {
        ob_start();
        $title = $infoblock['title'] ?? '';
        $list = $items;
        require __DIR__ . '/list.php';
        return (string) ob_get_clean();
    }

    public function renderDetail(array $infoblock, ?array $item): string
    {
        ob_start();
        $title = $infoblock['title'] ?? '';
        $record = $item;
        require __DIR__ . '/detail.php';
        return (string) ob_get_clean();
    }
}
