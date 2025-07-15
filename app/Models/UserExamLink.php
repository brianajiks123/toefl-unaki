<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExamLink extends Model
{
    use HasFactory;

    // Define fillable attributes for mass assignment
    protected $fillable = ['user_id', 'exam_id', 'link', 'token', 'current_part'];

    // Define relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define relationship with Exam model
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
