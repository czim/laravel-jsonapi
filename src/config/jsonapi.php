<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Paths of the API
    |--------------------------------------------------------------------------
    |
    | If these are left empty, they default to the root of the current request.
    | If set, the base_path will be added to the base_url or automatically
    | derived request root.
    */

    'base_url' => '',

    'base_path' => 'v1',

    /*
    |--------------------------------------------------------------------------
    | Names and Identifiers
    |--------------------------------------------------------------------------
    |
    | The names or identifiers that may be used to pass data through requests
    | and other 'global state' resolution.
    |
    */

    'identifiers' => [

        // JSON-API set-up in request as query parameters
        'request' => [
            'debug'   => 'debug',       // boolean 1/0, whether to debug (locally)
            'filter'  => 'filter',      // array filter data, associative
            'include' => 'include',     // comma-separated paths
            'sort'    => 'sort',        // comma-separated fields
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Encoding
    |--------------------------------------------------------------------------
    |
    | Settings for encoding JSON API response
    |
    */

    'encoding' => [

        // NeoMerx Encoder default options
        'encoder_options' => JSON_UNESCAPED_SLASHES,

        // FQN of the translatable trait
        // for which to include attributes through the translations relation
        'translatable_trait' => \Dimsav\Translatable\Translatable::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    |
    | Configuration for dealing with relationships while encoding.
    |
    */

    'relations' => [

        // relation names to always hide by default
        'hide_defaults' => [
            'pivot',
            'translations',
        ],

        // per FQN of resource class, the names of relationships that should be hidden
        'hide' => [

            //\App\Models\Example::class => [
            //    'exampleRelation',
            //],

        ],

        // whether to always include data for all to-one type relationships
        'always_show_data_for_single' => true,

        // per FQN of resource class, the names of relationships that encodes
        // should always show full data for (not just type + id)
        'always_show_data' => [

        ],

    ],

];
