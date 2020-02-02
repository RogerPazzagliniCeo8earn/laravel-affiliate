<?php
return [
    'credentials' => [
        'awin' => [
            'api_token' => env('AWIN_API_TOKEN'),
            'publisher_id' => env('AWIN_PUBLISHER_ID'),
        ],
        'zanox' => [
            'connect_id' => env('ZANOX_CONNECT_ID'),
            'secret_key' => env('ZANOX_SECRET_KEY'),
        ],
    ],
];
