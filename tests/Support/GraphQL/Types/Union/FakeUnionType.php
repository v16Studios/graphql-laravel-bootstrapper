<?php

namespace GraphQL\Bootstrapper\Tests\Support\GraphQL\Types\Union;

use GraphQL\Bootstrapper\Interfaces\GraphQlUnion;
use Rebing\GraphQL\Support\UnionType;

class FakeUnionType extends UnionType implements GraphQlUnion
{
    protected $attributes = [
        'name' => 'FakeUnionType',
        'description' => 'A fake graphql interface type for testing.',
    ];

    public function types(): array
    {
        return [];
    }
}
