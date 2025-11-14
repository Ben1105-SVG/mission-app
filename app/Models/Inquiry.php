<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'group_leader_role',
        'role_duration',
        'first_trip',
        'lgbtq',
        'status',
        'flags',
    ];

    protected $casts = [
        'first_trip' => 'boolean',
        'lgbtq' => 'boolean',
        'flags' => 'array',
    ];


    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
