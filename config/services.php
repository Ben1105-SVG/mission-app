<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'activecampaign' => [
        'url' => env('ACTIVE_CAMPAIGN_URL'),   // e.g. https://youraccount.api-us1.com
        'key' => env('ACTIVE_CAMPAIGN_KEY'),
        // tags: set tag ids created in ActiveCampaign for each status
        'tags' => [
            'green' => env('AC_TAG_GREEN'),   // optional tag id for green
            'yellow' => env('AC_TAG_YELLOW'), // tag id for yellow follow-up
            'red' => env('AC_TAG_RED'),       // tag id for red
            'default' => env('AC_TAG_DEFAULT'), // fallback tag
            // optional automations mapping
            'automations' => [
                'yellow' => env('AC_AUTOMATION_YELLOW'),
                'red' => env('AC_AUTOMATION_RED'),
            ],
        ],
    ],


];
