<?php

namespace GraphQL\Bootstrapper;

use GraphQL\Bootstrapper\Providers\GraphQlServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelPackageTools\Exceptions\InvalidPackage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GraphQlBootstrapperServiceProvider extends PackageServiceProvider
{
    /**
     * Configure provider.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('graphql-laravel-bootstrapper')
            ->hasConfigFile(['graphql-laravel-bootstrapper'])
            ->hasTranslations();
    }

    /**
     * Register provider.
     *
     *
     * @throws InvalidPackage
     */
    public function register(): void
    {
        parent::register();
    }

    /**
     * Boot provider.
     */
    public function boot(): void
    {
        parent::boot();

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

    public function packageRegistered(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'graphql-laravel-bootstrapper');
    }
}
