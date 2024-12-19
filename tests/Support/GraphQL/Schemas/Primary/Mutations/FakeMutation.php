<?php

namespace GraphQL\Bootstrapper\Tests\Support\GraphQL\Schemas\Primary\Mutations;

use GraphQL\Bootstrapper\Interfaces\GraphQlMutation;
use GraphQL\Bootstrapper\Interfaces\PublicGraphQlOperation;
use GraphQL\Type\Definition\Type as GraphQLType;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class FakeMutation extends Mutation implements GraphQlMutation, PublicGraphQlOperation
{
    protected $attributes = [
        'name' => 'FakeMutation',
        'description' => 'A fake mutation for testing.',
    ];

    public function type(): GraphQLType
    {
        return GraphQL::type('Boolean');
    }

    public static function getSchemaName(): string
    {
        return 'primary';
    }
}
