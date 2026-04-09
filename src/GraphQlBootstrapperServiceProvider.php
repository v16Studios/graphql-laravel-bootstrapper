<?php

namespace GraphQL\Bootstrapper;

use GraphQL\Bootstrapper\Providers\GraphQlServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class GraphQlBootstrapperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/graphql-laravel-bootstrapper.php', 'graphql-laravel-bootstrapper');
    }

    public function boot(): void
    {
        $this->registerResources();

        $this->app->register(GraphQlServiceProvider::class);

        Builder::macro('lengthAwareCursorPaginate', function ($limit, $order = 'id', $cache = false) {
            if ($cache) {
                $totalCount = (int) Cache::remember(
                    $this->toRawSql(),
                    6,
                    fn () => Cache::lock('graphql-pagination-'. $this->toRawSql())->get(fn () => $this->count())
                );
            }

            return [
                'total' => $totalCount ?? $this->count(),
                'items' => $this->orderBy($order)->cursorPaginate($limit),
            ];
        });

        Builder::macro('lengthAwareCursorPaginateDesc', function ($limit, $order = 'id', $cache = false) {
            if ($cache) {
                $totalCount = (int) Cache::remember(
                    $this->toRawSql(),
                    6,
                    fn () => Cache::lock('graphql-pagination-' . $this->toRawSql())->get(fn () => $this->count())
                );
            }

            return [
                'total' => $totalCount ?? $this->count(),
                'items' => $this->orderByDesc($order)->cursorPaginate($limit),
            ];
        });
    }

    protected function registerResources(): void
    {
        $configPath = __DIR__ . '/../config/graphql-laravel-bootstrapper.php';

        $this->publishes([
            $configPath => Config::get('path.config') . '/graphql-laravel-bootstrapper.php',
        ], 'bootstrapper-config');

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'graphql-laravel-bootstrapper');
    }
}
