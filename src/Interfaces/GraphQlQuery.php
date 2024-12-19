<?php

namespace GraphQL\Bootstrapper\Interfaces;

interface GraphQlQuery
{
    /**
     * Get the schema name.
     */
    public static function getSchemaName(): string;
}
