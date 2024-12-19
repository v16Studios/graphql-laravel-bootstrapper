<?php

return [
    /*
    |--------------------------------------------------------------------------
    | The namespace filters
    |--------------------------------------------------------------------------
    |
    | Here you may set the namespaces you like to filter the graphql schemas to
    |
    */
    'namespace_filters' => [
        'GraphQL\\\\Bootstrapper',
    ],

    /*
    |--------------------------------------------------------------------------
    | The pagination limit
    |--------------------------------------------------------------------------
    |
    | Here you may set the default pagination limit for the APIs
    |
    */
    'pagination' => [
        'limit' => env('DEFAULT_PAGINATION_LIMIT', 15),
    ],
];
