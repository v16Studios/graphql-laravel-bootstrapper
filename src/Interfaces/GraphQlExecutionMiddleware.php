<?php

namespace GraphQL\Bootstrapper\Interfaces;

interface GraphQlExecutionMiddleware
{
    public static function forSchema(): string;
}
