<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = ['inquiry_id', 'question_id', 'answer'];

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
