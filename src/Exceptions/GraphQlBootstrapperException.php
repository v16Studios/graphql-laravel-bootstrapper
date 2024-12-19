<?php

namespace GraphQL\Bootstrapper\Exceptions;

use GraphQL\Error\ClientAware;

class GraphQlBootstrapperException extends \Exception implements ClientAware
{
    /**
     * Determine if the exception is safe to be displayed to the user.
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Get exception category.
     */
    public function getCategory(): string
    {
        return __('graphql-laravel-bootstrapper::exception.category');
    }
}
