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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'collectionlog' => [
        'url' => env('COLLECTION_LOG_API_URL'),
    ],

    'wise_old_man' => [
        'url' => env('WOM_API_URL', 'https://api.wiseoldman.net/v2'),
        'user_agent' => env('WOM_USER_AGENT', 'RuneManager/1.0'),
    ],

    // TempleOSRS player_info — used to validate that a username is a Group
    // Ironman account (SPEC §5.2, GROUP mode).
    'templeosrs' => [
        'url' => env('TEMPLEOSRS_API_URL', 'https://templeosrs.com/api'),
    ],

];
