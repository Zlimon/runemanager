<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Data Freshness
    |--------------------------------------------------------------------------
    |
    | Per SPEC §5.3, each data type on a player profile carries a "last updated"
    | timestamp. Once the data is older than the configured threshold (minutes),
    | the UI flags it as stale.
    |
    | Stays a config value for now; will migrate to instance Settings (§12)
    | once the admin backend lands.
    |
    */

    'freshness' => [
        'stale_after_minutes' => (int) env('FRESHNESS_STALE_AFTER_MINUTES', 60),
    ],

];
