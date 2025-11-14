<?php

namespace App\Services;

use OpenAI;

class GroqClient
{
    public static function client()
    {
        return OpenAI::factory()
            ->withApiKey(env('GROQ_API_KEY'))
            ->withBaseUri('https://api.groq.com/openai/v1')
            ->make();
    }
}
