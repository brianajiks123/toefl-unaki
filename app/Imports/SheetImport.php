<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class SheetImport implements ToArray
{
    protected $sheetName;
    protected $data;

    public function __construct($sheetName, &$data)
    {
        $this->sheetName = $sheetName;
        $this->data = &$data;
    }

    public function array(array $array)
    {
        // Skip the header row
        $this->data[$this->sheetName] = array_slice($array, 1);
    }
}
