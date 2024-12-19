<?php

namespace GraphQL\Bootstrapper\Tests\Support\GraphQL\Types\Global;

use GraphQL\Bootstrapper\Interfaces\GraphQlType;
use Rebing\GraphQL\Support\Type;

class FakeGlobalType extends Type implements GraphQlType
{
    protected $attributes = [
        'name' => 'FakeGlobalType',
        'description' => 'A fake global type for testing.',
    ];

    public static function getSchemaName(): string
    {
        return '';
    }
}
