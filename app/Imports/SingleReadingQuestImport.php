<?php

namespace App\Imports;

use App\Models\Reading;
use App\Models\ReadingQuestion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SingleReadingQuestImport implements ToCollection, WithHeadingRow
{
    protected $imageFileId;

    public function __construct($imageFileId)
    {
        $this->imageFileId = $imageFileId;
    }

    public function collection(Collection $collection)
    {
        $fileReading = Reading::findOrFail($this->imageFileId);

        foreach ($collection as $row) {
            $question = ReadingQuestion::create([
                'question' => $row['question'],
                'option_1' => $row['option_1'],
                'option_2' => $row['option_2'],
                'option_3' => $row['option_3'],
                'option_4' => $row['option_4'],
                'ans_correct' => $row['ans_correct'],
            ]);

            $fileReading->questions()->attach($question->id);
        }
    }
}
