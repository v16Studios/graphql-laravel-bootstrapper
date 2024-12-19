<?php

namespace GraphQL\Bootstrapper\Tests\Support\GraphQL\Schemas\Primary\Types;

use GraphQL\Bootstrapper\Interfaces\GraphQlType;
use Rebing\GraphQL\Support\Type;

class FakeSchemaType extends Type implements GraphQlType
{
    protected $attributes = [
        'name' => 'FakeSchemaType',
        'description' => 'A fake schema specific type for testing.',
    ];

    public static function getSchemaName(): string
    {
        return 'primary';
    }
}
