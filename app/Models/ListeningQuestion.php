<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListeningQuestion extends Model
{
    use HasFactory;

    // Define fillable attributes
    protected $fillable = ['option_1', 'option_2', 'option_3', 'option_4', 'ans_correct'];

    // Define many-to-many relationship with FileListening model
    public function fileListenings()
    {
        return $this->belongsToMany(FileListening::class, 'file_listening_questions', 'listening_question_id', 'file_listening_id');
    }
}
