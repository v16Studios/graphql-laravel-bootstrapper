<?php

namespace GraphQL\Bootstrapper\Interfaces;

interface GraphQlType
{
    /**
     * Get the schema name.
     */
    public static function getSchemaName(): string;
}
