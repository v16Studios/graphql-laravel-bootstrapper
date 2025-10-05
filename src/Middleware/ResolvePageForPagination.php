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
    public function handle($root, array $args, $context, ResolveInfo $info, Closure $next)
    {
        $this->resolveCurrentPageForPagination($args['after'] ?? null);

        return $next($root, $args, $context, $info);
    }
}