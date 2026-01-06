<?php

declare(strict_types=1);

namespace LiteCMS\Models;

use LiteCMS\DB;

final class SectionInfoblock
{
    public function listBySection(int $sectionId): array
    {
        $sql = 'SELECT * FROM section_infoblocks WHERE section_id = :section_id ORDER BY sort_order, id';
        return DB::run($sql, ['section_id' => $sectionId]);
    }

    public function create(array $data): void
    {
        $sql = 'INSERT INTO section_infoblocks (section_id, infoblock_id, sort_order, position)\n                VALUES (:section_id, :infoblock_id, :sort_order, :position)';
        DB::exec($sql, $data);
    }
}
