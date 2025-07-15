<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReadingQuestImport implements WithMultipleSheets
{
    public $data = [];
    protected $maxSheet;

    public function __construct($maxSheet = null)
    {
        $this->maxSheet = $maxSheet;
    }

    public function sheets(): array
    {
        $sheets = [];

        if ($this->maxSheet !== null) {
            for ($ms = 1; $ms <= $this->maxSheet; $ms++) {
                $sheetName = 'quest_' . $ms;
                $sheets[$sheetName] = new SheetImport($sheetName, $this->data);
            }
        }
        return $sheets;
    }
}
