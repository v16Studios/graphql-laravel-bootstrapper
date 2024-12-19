<?php

namespace GraphQL\Bootstrapper;

use GraphQL\Bootstrapper\Providers\GraphQlServiceProvider;
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
    }

    public function packageRegistered(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'graphql-laravel-bootstrapper');
    }
}
