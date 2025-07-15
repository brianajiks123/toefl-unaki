<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileDetail extends Model
{
    use HasFactory;

    // Define fillable attributes
    protected $fillable = ['user_id', 'address', 'phone'];

    // Define one-to-one or one-to-many relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
