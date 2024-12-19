<?php

namespace GraphQL\Bootstrapper\Tests\Support\GraphQL\Enums;

use GraphQL\Bootstrapper\Interfaces\GraphQlEnum;
use Rebing\GraphQL\Support\EnumType;

class FakeGraphQlEnumType extends EnumType implements GraphQlEnum
{
    protected $attributes = [
        'name' => 'FakeGraphQlEnumType',
        'description' => 'A fake graphql enum type for testing.',
    ];
}
