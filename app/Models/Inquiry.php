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
        'ac_contact_id',
    ];

    protected $casts = [
        'first_trip' => 'boolean',
        'lgbtq' => 'boolean',
        'flags' => 'array',
        'role_duration' => 'decimal:2',
    ];


    public function answers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
