<?php

namespace GraphQL\Bootstrapper\Interfaces;

interface GraphQlHttpMiddleware
{
    public static function forSchema(): string;
}
