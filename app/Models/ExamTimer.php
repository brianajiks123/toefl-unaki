<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamTimer extends Model
{
    use HasFactory;

    // Define fillable attributes
    protected $fillable = ['user_id', 'exam_id', 'remaining_time'];

    // Define one-to-one or one-to-many relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define one-to-one or one-to-many relationship with Exam model
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
