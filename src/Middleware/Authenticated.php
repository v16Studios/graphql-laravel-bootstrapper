<?php

namespace GraphQL\Bootstrapper\Middleware;

use Closure;
use GraphQL\Bootstrapper\Interfaces\PublicGraphQlOperation;
use GraphQL\Bootstrapper\Package;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\OperationDefinitionNode;
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
        if ($this->bypass($request) || $this->manager->guard()->check()) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthenticated.'], 401);
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

            $documentNode = Parser::parse($operation->query);

            return collect($documentNode->definitions)
                ->filter(fn ($definition) => $definition instanceof OperationDefinitionNode)
                ->pipe(function (Collection $definitions) use ($operationName) {
                    if ($definitions->hasSole()) {
                        $definition = $definitions->sole();
                    } else {
                        $definition = $definitions
                            ->first(fn (OperationDefinitionNode $definition) => $operationName === $definition->name?->value);
                    }

                    $field = $definition?->selectionSet->selections->offsetGet(0);

                    if (! $field instanceof FieldNode || ! in_array($field->name->value, $this->except, true)) {
                        return false;
                    }

                    return true;
                });
        }

        return true;
    }
}
