<?php

namespace GraphQL\Bootstrapper\Middleware;

use Closure;
use GraphQL\Bootstrapper\Interfaces\PublicGraphQlOperation;
use GraphQL\Bootstrapper\Package;
use GraphQL\Language\Parser;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laragraph\Utils\RequestParser;

class Authenticated
{
    /**
     * The names of the schemas that should not be protected.
     */
    protected array $except = [
        '__schema',
    ];

    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected AuthManager $manager,
        protected RequestParser $parser
    ) {
        $this->except = array_merge($this->except, Package::getGraphQlFieldsThatImplementInterface(PublicGraphQlOperation::class)->all());
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): JsonResponse|RedirectResponse|Response
    {
        if ($this->bypass($request) || $this->manager->authenticate($request)) {
            return $next($request);
        }

        return response()->json(['error' => $this->manager->getError()], 401);
    }

    /**
     * Check if we should bypass.
     */
    protected function bypass(Request $request): bool
    {
        if (! $requests = $this->parser->parseRequest($request)) {
            return false;
        }

        $operationName = $requests->operation;

        foreach (Arr::wrap($requests) as $operation) {
            if (! $operation->query) {
                return false;
            }

            if ($documentNode = Parser::parse($operation->query)) {
                return collect($documentNode->definitions)
                    ->pipe(function (Collection $definitions) use ($operationName) {
                        if ($definitions->containsOneItem()) {
                            $definition = $definitions->sole();
                        } else {
                            $definition = $definitions
                                ->filter(fn ($definition) => $operationName == $definition?->name?->value)
                                ->first();
                        }

                        if (! in_array(
                            $definition?->selectionSet?->selections?->offsetGet(0)?->name?->value,
                            $this->except
                        )) {
                            return false;
                        }

                        return true;
                    });
            }
        }

        return true;
    }
}
