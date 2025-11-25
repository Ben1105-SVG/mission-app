<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use OpenAI;

class GroqClient
{
    public static function client()
    {
        return OpenAI::factory()
            ->withApiKey(Config::get('groq.api_key'))
            ->withBaseUri('https://api.groq.com/openai/v1')
            ->make();
    }
}
