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
            'ad_space_id' => env('ZANOX_AD_SPACE_ID'),
        ],
    ],

    'db' => [
        'connection' => 'mysql',
        'tables' => [
            'feeds' => 'feeds',
            'products' => 'products',
        ],
    ],

    'product_feeds' => [
        /*
         * null|string
         * The path where to store product feed files.
         * If set to null, storage_path('affiliate/feed') will be used.
         */
        'directory_path' => null,

        /*
         * bool
         */
        'only_joined' => true,

        /*
         * null|array
         * Example: ['US', 'GB', 'IT']
         */
        'regions' => null,

        /*
         * null|array
         * Example: ['en', 'it']
         */
        'languages' => null,
    ],

    'networks' => [
        'awin' => [
            /*
             * see https://wiki.awin.com/index.php/Publisher_Click_Ref
             */
            'tracking_code_param' => 'clickRef',
        ],

        'zanox' => [
            //
        ],
    ],
];
