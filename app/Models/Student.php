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
        'phone',
    ];

    public $timestamps = false;

    /**
     * Relationship: Student belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function invoices()
{
    return $this->hasMany(Invoice::class);
}

public function payments()
{
    return $this->hasMany(Payment::class);
}
}