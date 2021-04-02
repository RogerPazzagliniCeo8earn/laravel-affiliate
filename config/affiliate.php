<?php

return [
    'db' => [
        /*
        |--------------------------------------------------------------------------
        | Database Connection Name
        |--------------------------------------------------------------------------
        |
        | Null or database connection name. If null, default connection will be used.
        |
        */
        'connection' => null,

        'tables' => [
            'advertisers' => 'advertisers',
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

        /*
         * int
         */
        'import_chunk_size' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Networks Configuration
    |--------------------------------------------------------------------------
    */
    'networks' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue' => [
        /*
        |--------------------------------------------------------------------------
        | Queue Connection Name
        |--------------------------------------------------------------------------
        |
        | Null or queue connection name.
        |
        */
        'connection' => null,

        /*
        |--------------------------------------------------------------------------
        | Queue Name
        |--------------------------------------------------------------------------
        |
        | Null or queue name.
        |
        */
        'name' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Chunk Size
    |--------------------------------------------------------------------------
    |
    | The default chunk size.
    |
    */
    'chunk_size' => 1000,
];
