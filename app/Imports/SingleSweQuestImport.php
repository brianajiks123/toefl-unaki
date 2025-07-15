<?php

namespace App\Imports;

use App\Models\SweQuestion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SingleSweQuestImport implements ToCollection, WithHeadingRow
{
    protected $batchId;
    protected $categoryId;

    public function __construct($batchId, $categoryId)
    {
        $this->batchId = $batchId;
        $this->categoryId = $categoryId;
    }

    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $question = $row['question'];

            if (preg_match('/_{5,}/', $question)) {
                $question = str_replace('_____', str_repeat('<u>&nbsp;</u>', 5), $question);
            }

            if (preg_match('/_(.*?)_/', $question)) {
                $question = preg_replace('/_(.*?)_/', '<u>$1</u>', $question);
            }

            $swe_question = SweQuestion::create([
                'question' => $question,
                'option_1' => $row['option_1'],
                'option_2' => $row['option_2'],
                'option_3' => $row['option_3'],
                'option_4' => $row['option_4'],
                'ans_correct' => $row['ans_correct'],
            ]);

            $swe_question->batches()->attach($this->batchId, ['category_id' => $this->categoryId]);
        }
    }
}
