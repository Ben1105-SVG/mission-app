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
        'status',
        'flags',
        'ac_contact_id',
    ];

    protected $casts = [
        'flags' => 'array',
        'role_duration' => 'decimal:2',
    ];


    public function answers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
