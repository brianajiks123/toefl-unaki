<?php

namespace App\Imports;

use App\Models\FileListening;
use App\Models\ListeningQuestion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SingleListenQuestImport implements ToCollection, WithHeadingRow
{
    protected $audioFileId;

    public function __construct($audioFileId)
    {
        $this->audioFileId = $audioFileId;
    }

    public function collection(Collection $collection)
    {
        $fileListening = FileListening::findOrFail($this->audioFileId);

        foreach ($collection as $row) {
            $question = ListeningQuestion::create([
                'option_1' => $row['option_1'],
                'option_2' => $row['option_2'],
                'option_3' => $row['option_3'],
                'option_4' => $row['option_4'],
                'ans_correct' => $row['ans_correct'],
            ]);

            $fileListening->questions()->attach($question->id);
        }
    }
}
