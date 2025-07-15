<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileListening extends Model
{
    use HasFactory;

    // Define fillable attributes
    protected $fillable = ['name', 'audio_path', 'size', 'part'];

    // Define many-to-many relationship with Batch model
    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'batch_category_file_listenings', 'file_listening_id', 'batch_id')->withPivot('category_id')->withTimestamps();
    }

    // Define many-to-many relationship with Category model
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'batch_category_file_listenings', 'file_listening_id', 'category_id')->withPivot('batch_id')->withTimestamps();
    }

    // Define many-to-many relationship with ListeningQuestion model
    public function questions()
    {
        return $this->belongsToMany(ListeningQuestion::class, 'file_listening_questions', 'file_listening_id', 'listening_question_id');
    }
}
