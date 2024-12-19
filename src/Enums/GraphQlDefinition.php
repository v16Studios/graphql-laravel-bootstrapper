<?php

namespace GraphQL\Bootstrapper\Enums;

use GraphQL\Bootstrapper\Interfaces\GraphQlEnum;
use GraphQL\Bootstrapper\Interfaces\GraphQlInterface;
use GraphQL\Bootstrapper\Interfaces\GraphQlMutation;
use GraphQL\Bootstrapper\Interfaces\GraphQlQuery;
use GraphQL\Bootstrapper\Interfaces\GraphQlType;
use GraphQL\Bootstrapper\Interfaces\GraphQlUnion;
use GraphQL\Bootstrapper\Traits\EnumExtensions;

enum GraphQlDefinition: string
{
    use EnumExtensions;

    case TYPE = GraphQlType::class;
    case QUERY = GraphQlQuery::class;
    case MUTATION = GraphQlMutation::class;
    case ENUM = GraphQlEnum::class;
    case UNION = GraphQlUnion::class;
    case INTERFACE = GraphQlInterface::class;
}
