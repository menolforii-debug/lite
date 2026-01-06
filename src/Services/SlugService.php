<?php

declare(strict_types=1);

namespace LiteCMS\Services;

final class SlugService
{
    private const RESERVED = [
        'admin', 'api', 'ajax', 'assets', 'vendor', 'storage', 'uploads',
        'login', 'logout', 'index.php', 'sitemap.xml', 'robots.txt',
    ];

    public function generate(string $title): string
    {
        $slug = $this->transliterate($title);
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\-_]+/u', '-', $slug);
        $slug = trim($slug ?? '', '-');
        return substr($slug, 0, 120);
    }

    public function isValid(string $slug): bool
    {
        if ($slug === '' || strlen($slug) > 120) {
            return false;
        }
        if (!preg_match('/^[a-z0-9\-_]+$/', $slug)) {
            return false;
        }
        if (in_array($slug, self::RESERVED, true)) {
            return false;
        }
        return true;
    }

    private function transliterate(string $text): string
    {
        $map = [
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i',
            'й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t',
            'у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ь'=>'','ы'=>'y','ъ'=>'',
            'э'=>'e','ю'=>'yu','я'=>'ya',' '=>'-','А'=>'a','Б'=>'b','В'=>'v','Г'=>'g','Д'=>'d','Е'=>'e',
            'Ё'=>'e','Ж'=>'zh','З'=>'z','И'=>'i','Й'=>'y','К'=>'k','Л'=>'l','М'=>'m','Н'=>'n','О'=>'o',
            'П'=>'p','Р'=>'r','С'=>'s','Т'=>'t','У'=>'u','Ф'=>'f','Х'=>'h','Ц'=>'ts','Ч'=>'ch','Ш'=>'sh',
            'Щ'=>'sch','Ь'=>'','Ы'=>'y','Ъ'=>'','Э'=>'e','Ю'=>'yu','Я'=>'ya',
        ];
        return strtr($text, $map);
    }
}
