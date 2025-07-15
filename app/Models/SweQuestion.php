<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SweQuestion extends Model
{
    use HasFactory;

    // Define fillable attributes
    protected $fillable = ['question', 'option_1', 'option_2', 'option_3', 'option_4', 'ans_correct'];

    // Define many-to-many relationship with Batch model
    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'batch_category_swes', 'swe_question_id', 'batch_id')->withPivot('category_id')->withTimestamps();
    }

    // Define many-to-many relationship with Category model
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'batch_category_swes', 'swe_question_id', 'category_id')->withPivot('batch_id')->withTimestamps();
    }
}
