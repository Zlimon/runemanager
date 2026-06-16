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

    // TempleOSRS — collection log source (SPEC §5.2/§7).
    'templeosrs' => [
        'url' => env('TEMPLEOSRS_API_URL', 'https://templeosrs.com/api'),
    ],

    // Official OSRS Group Ironman group page — used to validate a group name at
    // setup (SPEC §2.2). The page returns 200 for both states; a missing group
    // shows an "Unable to find group with name" notice.
    'osrs_group_ironman' => [
        'url' => env('OSRS_GROUP_IRONMAN_URL', 'https://secure.runescape.com/m=hiscore_oldschool_ironman/group-ironman/view-group'),
    ],

];
