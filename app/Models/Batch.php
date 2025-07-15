<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    // Define fillable attributes
    protected $fillable = ['name'];

    // Define many-to-many relationship with User model
    public function users()
    {
        return $this->belongsToMany(User::class, 'batch_users');
    }

    // Define many-to-many relationship with Category model
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'batch_category', 'batch_id', 'category_id')->withTimestamps();
    }

    // Define many-to-many relationship with FileListening model
    public function fileListenings()
    {
        return $this->belongsToMany(FileListening::class, 'batch_category_file_listenings', 'batch_id', 'file_listening_id')->withPivot('category_id')->withTimestamps();
    }

    // Define many-to-many relationship with SweQuestion model
    public function sweQuestions()
    {
        return $this->belongsToMany(SweQuestion::class, 'batch_category_swes', 'batch_id', 'swe_question_id');
    }

    // Define many-to-many relationship with Reading model
    public function readings()
    {
        return $this->belongsToMany(Reading::class, 'batch_category_readings', 'batch_id', 'reading_id')->withPivot('category_id')->withTimestamps();
    }

    // Define many-to-many relationship with Exam model
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'batch_category_exams', 'batch_id', 'exam_id')->withPivot('category_id')->withTimestamps();
    }
}
