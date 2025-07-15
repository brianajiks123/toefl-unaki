<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingQuestion extends Model
{
    use HasFactory;

    // Define fillable attributes
    protected $fillable = ['question', 'option_1', 'option_2', 'option_3', 'option_4', 'ans_correct'];

    // Define many-to-many relationship with Reading model
    public function readings()
    {
        return $this->belongsToMany(Reading::class, 'readings_questions', 'reading_question_id', 'reading_id');
    }
}
