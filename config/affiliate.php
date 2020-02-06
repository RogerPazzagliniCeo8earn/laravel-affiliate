<?php
return [
    'credentials' => [
        'awin' => [
            'api_token' => env('AWIN_API_TOKEN'),
            'publisher_id' => env('AWIN_PUBLISHER_ID'),
            'product_feed_api_key' => env('AWIN_PRODUCT_FEED_API_KEY'),
        ],
        'zanox' => [
            'connect_id' => env('ZANOX_CONNECT_ID'),
            'secret_key' => env('ZANOX_SECRET_KEY'),
        ],
    ],
    'db' => [
        'connection' => 'sqlite',
        'tables' => [
            'feeds' => 'feeds',
            'products' => 'products',
        ],
    ],

    /*
     * The path where to store product feed files.
     * If set to null, storage_path('affiliate/product_feed') will be used.
     */
    'product_feed_directory_path' => null,
];
