<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'user_id',
    ];

    public $timestamps = false;

    /**
     * Relationship: Student belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}