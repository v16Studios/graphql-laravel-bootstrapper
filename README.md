# GraphQL Laravel Bootstrapper

A package for automatic schema configuration designed to work with rebing/graphql-laravel. This project is tested with BrowserStack.

[![License: GPL 3.0](https://img.shields.io/badge/license-GPL_3.0-purple)](https://opensource.org/license/gpl-3-0/)

## Installation

Run `composer require v16studios/graphql-laravel-bootstrapper` in the terminal.

Publishing the package config file is optional but recommended if you would like to use a different namespace for your types, queries and mutations:

Publish the config file by running `php artisan vendor:publish --tag=bootstrapper-config` in the terminal.

Afterwards you can modify the namespace(s) that the package uses to find your schema by editing the `namespace_filters` key in the `config/graphql-laravel-bootstrapper.php` file.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The LGPL 3.0 License. Please see [License File](LICENSE) for more information.

## Basic Usage

Instead of manually defining your GraphQL schema in the config files of the Rebing GraphQL package, you can use this package to automatically generate your schemas by implementing the various supplied interfaces on your types, queries and mutations.

For example you can define a 'global' type like so:

```php
<?php

namespace App\GraphQL\Types\Global;

use GraphQL\Bootstrapper\Interfaces\GraphQlType;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type;

class UserType extends Type implements GraphQlType
{
    protected $attributes = [
        'name' => 'User',
        'description' => 'A user account.',
    ];

    public function fields() : array
    {
        return [
            'id' => [
                'type' => GraphQL::type('Int'),
                'description' => 'The id of the User.',
            ],
            'uuid' => [
                'type' => GraphQL::type('String'),
                'description' => "The user's UUID.",
            ],
            'email' => [
                'type' => GraphQL::type('String'),
                'description' => "The user's email address.",
            ],
            'isVerified' => [
                'type' => GraphQL::type('Boolean'),
                'description' => 'Check if the user has verified their email address.',
                'resolve' => function ($user) {
                    return isset($user->email_verified_at);
                },
            ],
            'updatedAt' => [
                'type' => GraphQL::type('String'),
                'resolve' => function ($user) {
                    return $user->updated_at->toIso8601String();
                },
            ],
        ];
    }

    public static function getSchemaName(): string
    {
        return '';
    }
}
```
The `getSchemaName` method should return the name of the schema that this type should be added to. If you return an empty string, the type will be added to all schemas making it a 'global' type.

You can then define a query like so:

```php
<?php

namespace App\GraphQL\Schemas\Primary\Queries;

use GraphQL\Bootstrapper\Interfaces\GraphQlQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class GetUserQuery extends Query implements GraphQlQuery
{
    protected $attributes = [
        'name' => 'GetUser',
        'description' => 'Get a user from their API token.',
    ];

    public function type() : Type
    {
        return GraphQL::type('User');
    }

    public function args() : array
    {
        return [];
    }

    public function resolve($root, $args, $authUser, ResolveInfo $resolveInfo)
    {
        return $authUser;
    }
    
    public static function getSchemaName(): string
    {
        return 'primary';
    }
}
```
Just like the type, the `getSchemaName` method defined in the interface should return the name of the schema that this query should be added to. In this case we are adding it to the `primary` schema.  In the Rebing GraphQL configuration file we have defined `primary` as the default schema name like this:
```php
    // The name of the default schema
    // Used when the route group is directly accessed
    'default_schema' => 'primary',
    
    'schemas' => [
        'primary' => [
            'query' => [
                // ExampleQuery::class,
            ],
            'mutation' => [
                // ExampleMutation::class,
            ],
            // The types only available in this schema
            'types' => [
                // ExampleType::class,
            ],

            // Laravel HTTP middleware
            'middleware' => [],

            // Which HTTP methods to support; must be given in UPPERCASE!
            'method' => ['GET', 'POST'],

            // An array of middlewares, overrides the global ones
            'execution_middleware' => null,
        ],
    ],
```
You can see that we haven't needed to define the `User` type or the `GetUser` query in the configuration file, they are automatically added by the bootstrapper.

Interfaces are provided for adding all types, queries and mutations as well as enums, graphql interfaces, unions and middleware (including http, execution and resolver middleware) to your schemas.

_Please note these docs are a work in progress and will be updated in due course._
