<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Option Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default option connection that gets used while
    | using this caching library. This connection is used when another is
    | not explicitly specified when executing a given caching function.
    |
    | Supported:  "database", "file"
    |
    */

    'default' => env('OPTION_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Option Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the option "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same option driver to group types of items stored in your options.
    |
    */

    'stores' => [ 
        'database' => [
            'driver'     => 'database',
            'table'      => 'options',
            'connection' => null,
        ],

        'file' => [
            'driver'      => 'file',
            'path'        => storage_path('framework/option'),
            'single_file' => true,
        ], 
    ],  
];
