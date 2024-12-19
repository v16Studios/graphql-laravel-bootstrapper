<?php

namespace GraphQL\Bootstrapper;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Package
{
    /**
     * Get the composer autoloader for auto-bootstrapping services.
     */
    public static function getAutoloader()
    {
        return app()->runningUnitTests() ? require app()->basePath('../../../autoload.php') : require app()->basePath('vendor/autoload.php');
    }

    /**
     * Get a list of package and app classes.
     */
    public static function getAppClasses(): Collection
    {
        $appNamespace = trim(app()->getNamespace(), '\\');
        $namespaceFilters = collect(config('graphql-laravel-bootstrapper.namespace_filters'))
            ->reduce(function (string $filterString, string $namespaceFilter) {
                return "{$filterString}{$namespaceFilter}|";
            }, '');

        return collect(self::getAutoloader()->getClassMap())
            ->keys()
            ->filter(function ($className) use ($namespaceFilters, $appNamespace) {
                $namespaceFilter = "/^({$namespaceFilters}{$appNamespace})\\\\/";

                return preg_match($namespaceFilter, $className)
                    && class_exists($className)
                    && ! (new \ReflectionClass($className))->isAbstract();
            });
    }

    public static function getClass(string $className)
    {
        return self::getAppClasses()->first(fn ($class) => Str::afterLast($class, '\\') == $className);
    }

    public static function classImplementsInterface($class, $interface): bool
    {
        return in_array($interface, class_implements($class));
    }

    /**
     * Get a list of classes that implement a specific interface.
     */
    public static function getClassesThatImplementInterface(string $interface): Collection
    {
        return self::getAppClasses()->filter(fn ($className) => self::classImplementsInterface($className, $interface));
    }

    /**
     * Get a list of class names that implement a specific interface.
     */
    public static function getClassNamesThatImplementInterface(string $interface): Collection
    {
        return self::getClassesThatImplementInterface($interface)
            ->transform(fn ($class) => Str::afterLast($class, '\\'))
            ->unique();
    }

    /**
     * Get a list of GraphQL fields that implement a specific interface.
     */
    public static function getGraphQlFieldsThatImplementInterface(string $interface): Collection
    {
        return self::getClassNamesThatImplementInterface($interface)
            ->transform(fn ($class) => Str::replace(['Query', 'Mutation'], '', $class))
            ->unique();
    }
}
