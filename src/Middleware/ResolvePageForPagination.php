<?php

namespace GraphQL\Bootstrapper\Middleware;

use Closure;
use GraphQL\Bootstrapper\Traits\HasFieldPagination;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\Middleware;

class ResolvePageForPagination extends Middleware
{
    use HasFieldPagination;

    /**
     * Process the middleware.
     */
    #[\Override]
    public function handle(mixed $root, array $args, mixed $context, ResolveInfo $info, Closure $next): mixed
    {
        $this->resolveCurrentPageForPagination($args['after'] ?? null);

        return $next($root, $args, $context, $info);
    }
}
