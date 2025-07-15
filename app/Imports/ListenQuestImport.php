<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ListenQuestImport implements WithMultipleSheets
{
    public $data = [
        'part_A' => [],
        'part_B' => [],
        'part_C' => [],
    ];

    public function sheets(): array
    {
        return [
            'part_A' => new SheetImport('part_A', $this->data),
            'part_B' => new SheetImport('part_B', $this->data),
            'part_C' => new SheetImport('part_C', $this->data),
        ];
    }
}
