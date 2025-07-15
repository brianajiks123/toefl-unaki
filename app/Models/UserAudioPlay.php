<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAudioPlay extends Model
{
    use HasFactory;

    // Define fillable attributes for mass assignment
    protected $fillable = ['user_id', 'file_listening_id', 'status_played'];

    // Define relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Define relationship with Exam model
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }
}
