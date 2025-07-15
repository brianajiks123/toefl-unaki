<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Define fillable attributes for mass assignment
    protected $fillable = ['name', 'email', 'password', 'remember_token'];

    // Define attributes to be hidden in serialization
    protected $hidden = ['password', 'remember_token'];

    // Define attribute casting
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Define one-to-one relationship with ProfileDetail
    public function profileDetail()
    {
        return $this->hasOne(ProfileDetail::class);
    }

    // Define many-to-many relationship with Batch
    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'batch_users', 'user_id', 'batch_id');
    }

    // Define one-to-many relationship with UserExamLink
    public function examLinks()
    {
        return $this->hasMany(UserExamLink::class);
    }

    // Define one-to-many relationship with ExamSession
    public function examSessions()
    {
        return $this->hasMany(ExamSession::class);
    }
}
