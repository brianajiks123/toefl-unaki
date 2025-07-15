<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    use HasFactory;

    // Define fillable attributes
    protected $fillable = ['name', 'image_path', 'size', 'part'];

    // Define many-to-many relationship with Batch model
    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'batch_category_readings', 'reading_id', 'batch_id')->withPivot('category_id')->withTimestamps();
    }

    // Define many-to-many relationship with Category model
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'batch_category_readings', 'reading_id', 'category_id')->withPivot('batch_id')->withTimestamps();
    }

    // Define many-to-many relationship with ReadingQuestion model
    public function questions()
    {
        return $this->belongsToMany(ReadingQuestion::class, 'readings_questions', 'reading_id', 'reading_question_id');
    }
}
