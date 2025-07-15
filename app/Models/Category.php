<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Define fillable attributes
    protected $fillable = ['name'];

    // Define many-to-many relationship with Batch model
    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'batch_category', 'category_id', 'batch_id')->withTimestamps();
    }

    // Define many-to-many relationship with FileListening model
    public function fileListenings()
    {
        return $this->belongsToMany(FileListening::class, 'batch_category_file_listenings', 'category_id', 'file_listening_id')->withPivot('batch_id')->withTimestamps();
    }

    // Define many-to-many relationship with Reading model
    public function readings()
    {
        return $this->belongsToMany(Reading::class, 'batch_category_readings', 'category_id', 'reading_id')->withPivot('batch_id')->withTimestamps();
    }

    // Define many-to-many relationship with Exam model
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'batch_category_exams', 'category_id', 'exam_id')->withPivot('batch_id')->withTimestamps();
    }
}
