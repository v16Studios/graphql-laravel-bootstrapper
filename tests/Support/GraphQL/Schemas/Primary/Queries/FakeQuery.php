<?php

namespace GraphQL\Bootstrapper\Tests\Support\GraphQL\Schemas\Primary\Queries;

use GraphQL\Bootstrapper\Interfaces\GraphQlQuery;
use GraphQL\Bootstrapper\Interfaces\PublicGraphQlOperation;
use GraphQL\Type\Definition\Type as GraphQLType;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class FakeQuery extends Query implements GraphQlQuery, PublicGraphQlOperation
{
    protected $attributes = [
        'name' => 'FakeQuery',
        'description' => 'A fake query for testing.',
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
