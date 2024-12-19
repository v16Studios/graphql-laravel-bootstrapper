<?php

declare(strict_types=1);

namespace GraphQL\Bootstrapper\GraphQL\Types\Pagination;

use Rebing\GraphQL\Support\Facades\GraphQL;

class ConnectionInput
{
    /**
     * The common arguments for pagination.
     */
    public static function args(?array $args = null): array
    {
        return array_merge($args ?? [], [
            'after' => [
                'type' => GraphQL::type('String'),
                'description' => __('graphql-laravel-bootstrapper::connection_input.args.after'),
                'rules' => ['max:255'],
                'defaultValue' => null,
            ],
            'first' => [
                'type' => GraphQL::type('Int'),
                'description' => __('graphql-laravel-bootstrapper::connection_input.args.first'),
                'rules' => ['nullable', 'integer', 'min:1', 'max:500'],
                'defaultValue' => config('graphql-laravel-bootstrapper.pagination.limit'),
            ],
        ]);
    }
}
