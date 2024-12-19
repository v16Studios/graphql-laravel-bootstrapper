<?php

namespace GraphQL\Bootstrapper\Interfaces;

interface GraphQlMutation
{
    /**
     * Get the schema name.
     */
    public static function getSchemaName(): string;
}
