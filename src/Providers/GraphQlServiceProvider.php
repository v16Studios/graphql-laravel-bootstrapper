<?php

namespace GraphQL\Bootstrapper\Providers;

use GraphQL\Bootstrapper\Enums\GraphQlDefinition;
use GraphQL\Bootstrapper\GraphQL\Types\Pagination\ConnectionType;
use GraphQL\Bootstrapper\Interfaces\GraphQlExecutionMiddleware;
use GraphQL\Bootstrapper\Interfaces\GraphQlHttpMiddleware;
use GraphQL\Bootstrapper\Interfaces\GraphQlResolverMiddleware;
use GraphQL\Bootstrapper\Package;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\UploadType;

class GraphQlServiceProvider extends ServiceProvider
{
    private Collection $graphqlClasses;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        config(['graphql.pagination_type' => ConnectionType::class]);

        $this->graphqlClasses = Package::getAppClasses();

        $this->graphQlEnums();
        $this->graphQlGlobalTypes();
        $this->graphQlUnions();
        $this->graphQlInterfaces();
        $this->graphQlSchemas();
        $this->registerGraphQlHttpMiddleware();
        $this->registerGraphQlExecutionMiddleware();
        $this->registerExternalResolverMiddleware();
    }

    /**
     * Register graphql union types.
     */
    protected function graphQlUnions(): void
    {
        $this->graphqlClasses
            ->filter(
                fn ($className) => in_array(GraphQlDefinition::UNION->value, class_implements($className))
            )
            ->each(fn ($className) => GraphQL::addType($className));
    }

    /**
     * Register graphql union types.
     */
    protected function graphQlInterfaces(): void
    {
        $this->graphqlClasses
            ->filter(
                fn ($className) => in_array(GraphQlDefinition::INTERFACE->value, class_implements($className))
            )
            ->each(fn ($className) => GraphQL::addType($className));
    }

    /**
     * Register graphql enum types.
     */
    protected function graphQlEnums(): void
    {
        $this->graphqlClasses
            ->filter(
                fn ($className) => in_array(GraphQlDefinition::ENUM->value, class_implements($className))
            )
            ->each(fn ($className) => GraphQL::addType($className));
    }

    /**
     * Register graphql global types.
     */
    protected function graphQlGlobalTypes(): void
    {
        $this->graphqlClasses
            ->filter(
                fn ($className) => in_array(GraphQlDefinition::TYPE->value, class_implements($className))
                && empty($className::getSchemaName())
            )
            ->each(fn ($className) => GraphQL::addType($className));
    }

    /**
     * Register graphql schemas.
     */
    protected function graphQlSchemas(): void
    {
        // Schema Queries and Mutations
        $queries = $this->graphqlClasses->filter(
            fn ($className) => in_array(GraphQlDefinition::QUERY->value, class_implements($className))
        );

        $mutations = $this->graphqlClasses->filter(
            fn ($className) => in_array(GraphQlDefinition::MUTATION->value, class_implements($className))
        );

        $schemas = [];

        $queries->each(function ($query) use (&$schemas): void {
            $schemas[$query::getSchemaName()]['query'][] = $query;
        });

        $mutations->each(function ($mutation) use (&$schemas): void {
            $schemas[$mutation::getSchemaName()]['mutation'][] = $mutation;
        });

        // Schema specific Types
        $types = $this->graphqlClasses->filter(
            fn ($className) => in_array(GraphQlDefinition::TYPE->value, class_implements($className))
            && ! empty($className::getSchemaName())
        );

        $types->each(function ($type) use (&$schemas): void {
            $schemas[$type::getSchemaName()]['types'][] = $type;
        });

        foreach ($schemas as $schemaName => $schema) {
            config(["graphql.schemas.{$schemaName}" => $schema]);
        }

        // Manually add UploadType after schema has been built.
        GraphQL::addType(UploadType::class);
    }

    protected function registerGraphQlHttpMiddleware(): void
    {
        $httpMiddlewares = Package::getClassesThatImplementInterface(GraphQlHttpMiddleware::class);

        [$globalHttpMiddleware, $schemaHttpMiddleware] = $httpMiddlewares->partition(fn ($middleware) => empty($middleware::forSchema()) || $middleware::forSchema() === 'global');

        $globalHttpMiddleware
            ->each(function ($middleware): void {
                $graphQlHttpMiddleware = config('graphql.route.middleware');
                $graphQlHttpMiddleware[] = $middleware;
                config(['graphql.route.middleware' => $graphQlHttpMiddleware]);
            });

        $schemaHttpMiddleware
            ->each(function ($middleware): void {
                $schema = $middleware::forSchema();
                $graphQlHttpMiddleware = config('graphql.route.middleware');

                $graphQlSchemaHttpMiddleware = config("graphql.schemas.{$schema}.middleware") ?? [];
                $graphQlSchemaHttpMiddleware[] = $middleware;

                config(["graphql.schemas.{$schema}.middleware" => array_merge($graphQlHttpMiddleware, $graphQlSchemaHttpMiddleware)]);
            });
    }

    protected function registerGraphQlExecutionMiddleware(): void
    {
        $executionMiddlewares = Package::getClassesThatImplementInterface(GraphQlExecutionMiddleware::class);

        [$globalExecutionMiddleware, $schemaExecutionMiddleware] = $executionMiddlewares->partition(fn ($middleware) => empty($middleware::forSchema()) || $middleware::forSchema() === 'global');

        $globalExecutionMiddleware
            ->each(function ($middleware): void {
                $graphQlExecutionMiddleware = config('graphql.execution_middleware');
                $graphQlExecutionMiddleware[] = $middleware;
                config(['graphql.execution_middleware' => $graphQlExecutionMiddleware]);
            });

        $schemaExecutionMiddleware
            ->each(function ($middleware): void {
                $schema = $middleware::forSchema();
                $graphQlExecutionMiddleware = config('graphql.execution_middleware');

                $graphQlSchemaExecutionMiddleware = config("graphql.schemas.{$schema}.execution_middleware") ?? [];
                $graphQlSchemaExecutionMiddleware[] = $middleware;

                config(["graphql.schemas.{$schema}.execution_middleware" => array_merge($graphQlExecutionMiddleware, $graphQlSchemaExecutionMiddleware)]);
            });
    }

    protected function registerExternalResolverMiddleware(): void
    {
        $resolverMiddlewares = Package::getClassesThatImplementInterface(GraphQlResolverMiddleware::class)
            ->map(function ($resolverMiddleware) {
                $excludeFrom = collect($resolverMiddleware::excludeFrom())->transform(fn ($class) => class_basename($class));

                return collect($resolverMiddleware::registerOn())
                    ->map(fn ($model, $class) => ['operation' => class_basename($class), 'middleware' => $resolverMiddleware])
                    ->filter(fn ($middleware, $operation) => $excludeFrom->doesntContain($middleware['operation']))
                    ->toArray();
            })->pipe(function (Collection $middlewares) {
                return $middlewares->flatten(1)
                    ->mapToGroups(fn ($operation) => [$operation['operation'] => $operation['middleware']])
                    ->toArray();
            });

        $graphQlResolverMiddleware = config('graphql.resolver_middleware') ?? [];

        config(['graphql.resolver_middleware' => array_merge($graphQlResolverMiddleware, $resolverMiddlewares)]);
    }
}
