<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    // Define date attributes
    protected $date = ['exam_date'];

    // Define time attributes
    protected $time = ['exam_time'];

    // Define fillable attributes
    protected $fillable = ['exam_name', 'exam_date', 'exam_time', 'exam_attempt'];

    // Define many-to-many relationship with Batch model
    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'batch_category_exams', 'exam_id', 'batch_id')->withPivot('category_id')->withTimestamps();
    }

    // Define many-to-many relationship with Category model
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'batch_category_exams', 'exam_id', 'category_id')->withPivot('batch_id')->withTimestamps();
    }

    // Define one-to-many relationship with ExamSession model
    public function examSessions()
    {
        return $this->hasMany(ExamSession::class);
    }

    // Retrieve listening questions for a specific batch
    public function listeningQuestions($batchId)
    {
        return ListeningQuestion::whereHas('fileListenings', function ($query) use ($batchId) {
            $query->whereHas('batches', function ($batchQuery) use ($batchId) {
                $batchQuery->where('batch_id', $batchId);
            });
        })->get();
    }

    // Retrieve structure questions for a specific batch
    public function structureQuestions($batchId)
    {
        return SweQuestion::whereHas('batches', function ($query) use ($batchId) {
            $query->where('batch_id', $batchId);
        })->get();
    }

    // Retrieve reading questions for a specific batch
    public function readingQuestions($batchId)
    {
        return ReadingQuestion::whereHas('readings', function ($query) use ($batchId) {
            $query->whereHas('batches', function ($batchQuery) use ($batchId) {
                $batchQuery->where('batch_id', $batchId);
            });
        })->get();
    }

    // Define one-to-many relationship with UserExamLink model
    public function userExamLinks()
    {
        return $this->hasMany(UserExamLink::class);
    }
}
