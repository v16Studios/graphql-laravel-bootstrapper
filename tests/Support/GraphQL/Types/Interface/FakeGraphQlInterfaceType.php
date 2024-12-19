<?php

namespace GraphQL\Bootstrapper\Tests\Support\GraphQL\Types\Interface;

use GraphQL\Bootstrapper\Interfaces\GraphQlInterface;
use Rebing\GraphQL\Support\InterfaceType;

class FakeGraphQlInterfaceType extends InterfaceType implements GraphQlInterface
{
    protected $attributes = [
        'name' => 'FakeGraphQlInterfaceType',
        'description' => 'A fake graphql interface type for testing.',
    ];
}
