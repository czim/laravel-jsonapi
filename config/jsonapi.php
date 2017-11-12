<?php

use Czim\JsonApi\Contracts\Support\Error\ErrorDataInterface;
use Czim\JsonApi\Encoder\Transformers\ErrorDataTransformer;
use Czim\JsonApi\Encoder\Transformers\ModelRelationshipTransformer;
use Czim\JsonApi\Encoder\Transformers\ValidationExceptionTransformer;
use Czim\JsonApi\Exceptions\JsonApiValidationException;
use Czim\JsonApi\Support\Resource\RelationshipTransformData;

return [

    // The base relative API url to use for JSON-API links.
    'base_url' => 'http://localhost/api',

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    */

    'repository' => [

        'resource' => [

            // Base namespace in which model-namespace-mirrored classes with resource
            // configurations should be placed.
            //
            // A model:
            //      App\Models\Pages\Post
            // should have a resource in:
            //      App\JsonApi\Resources\Pages\Post.php
            //
            'namespace' => 'App\\JsonApi\\Resources\\',

            // Whether namespace-based collection of resources is enabled.
            'collect' => false,

            // Mapping of resources per model.
            // List of key value pairs: model class FQN => resource class FQN
            // These maps will overrule collected resources, if there are conflicts.
            'map' => [
            ],
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Transform responses
    |--------------------------------------------------------------------------
    */

    'transform' => [

        // As per JSON-API spec, this will ignore default includes set for resources when
        // includes are requested by the client.
        'requested-includes-cancel-defaults' => true,

        // If this is enabled, default includes defined in resources will only be applied
        // at the top level. Any nested resources that are included will NOT have their
        // default includes processed unless specifically requested.
        'top-level-default-includes-only' => true,

        // If this is enabled, the encoder will automatically attempt to determine the
        // URL to be used for links relative to the top level resource.
        'auto-determine-top-resource-url' => true,

        // The default datetime format to apply to date attributes.
        'default-datetime-format' => 'c',

        // If this is enabled, multiple errors for a single input key are string-concatenated
        // in a single JSON-API error object. If disabled, each error has its own error object.
        'group-validation-errors-by-key' => false,

        'links' => [

            // Whether the 'relationships' link should be included where possible.
            'relationships' => true,

            // The segment to add for relationship links:
            // as in
            //      <base URL>/<resource>/<id>/<relationships>/<include key>
            //      http://api.somewhere.com/post/1/relationships/comments
            'relationships-segment' => 'relationships',


            // Whether the 'related' link should be included where possible.
            'related' => true,

            // The segment to add for the related links:
            // as in
            //      <base URL>/<resource>/<id>/<related>/<include key>
            //      http://api.somewhere.com/post/1/related/comments
            // May be empty, in which the URL would like this:
            //      http://api.somewhere.com/post/1/comments
            'related-segment' => 'related',
        ],

        // Generating JSON-API type from Eloquent models.
        'type' => [

            // The namespace for records (models) to left-trim for creating type:
            // If 'App\Models', then App\Models\Pages\Page gets dasherized as type: pages--page.
            // If null, only the classname of the model will be used.
            // If empty string (''), the full namespace will be used.
            'trim-namespace' => null,
        ],

        // Fallback mapping transformers to content type.
        // The TransformerFactory will use this to instantiate transformers based on an is_a() match
        // on given content, if no standard match was found.
        'map' => [
            JsonApiValidationException::class => ValidationExceptionTransformer::class,
            ErrorDataInterface::class         => ErrorDataTransformer::class,
            RelationshipTransformData::class  => ModelRelationshipTransformer::class,
            // \Your\ClassHere::class => \Your\Transformer\ClassHere::class
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Type Generation
    |--------------------------------------------------------------------------
    |
    | JSON-API Type must be consistent, but may be singular or plural.
    |
    */

    'type' => [

        'plural' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'pagination' => [

        // Default page size
        'size' => 25,

        // Maximum allowed page size
        'max_size' => 1000,

    ],


    /*
    |--------------------------------------------------------------------------
    | Request
    |--------------------------------------------------------------------------
    |
    | The JSON-API query string request data is parsed using these options.
    |
    */

    'request' => [

        // Request values are read from the query string using the following keys
        'keys' => [
            'filter'  => 'filter',
            'include' => 'include',
            'page'    => 'page',
            'sort'    => 'sort',
        ],

        'include' => [
            // The token by which the include strings are separated, if multiple includes are given.
            'separator' => ',',
        ],

        'sort' => [
            // The token by which the sort strings are separated, if multiple sort attributes are given.
            'separator' => ',',
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */

    'exceptions' => [

        // Mapping for status code to use for specific exception classes
        'status' => [
            \League\OAuth2\Server\Exception\OAuthException::class              => 403,
            \Illuminate\Database\Eloquent\ModelNotFoundException::class        => 404,
            \Czim\Filter\Exceptions\FilterDataValidationFailedException::class => 422,
            \Illuminate\Validation\ValidationException::class                  => 422,
        ],
    ],

];
