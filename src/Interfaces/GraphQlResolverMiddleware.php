<?php

namespace GraphQL\Bootstrapper\Interfaces;

interface GraphQlResolverMiddleware
{
    public static function registerOn(): array;

    public static function excludeFrom(): array;
}
