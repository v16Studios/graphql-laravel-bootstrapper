<?php

namespace GraphQL\Bootstrapper\Tests\Unit;

use GraphQL\Bootstrapper\GraphQlBootstrapperServiceProvider;
use GraphQL\Bootstrapper\Tests\Support\GraphQL\Enums\FakeGraphQlEnumType;
use GraphQL\Bootstrapper\Tests\Support\GraphQL\Schemas\Primary\Mutations\FakeMutation;
use GraphQL\Bootstrapper\Tests\Support\GraphQL\Schemas\Primary\Queries\FakeQuery;
use GraphQL\Bootstrapper\Tests\Support\GraphQL\Schemas\Primary\Types\FakeSchemaType;
use GraphQL\Bootstrapper\Tests\Support\GraphQL\Types\Global\FakeGlobalType;
use GraphQL\Bootstrapper\Tests\Support\GraphQL\Types\Interface\FakeGraphQlInterfaceType;
use GraphQL\Bootstrapper\Tests\Support\GraphQL\Types\Union\FakeUnionType;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Rebing\GraphQL\Support\Facades\GraphQL;

#[CoversClass(GraphQlBootstrapperServiceProvider::class)]
class GraphQlBootstrapperServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            GraphQlBootstrapperServiceProvider::class,
        ];
    }

    #[Test]
    public function it_correctly_configures_graphql_schema(): void
    {
        $expectedQuery = (new FakeQuery)->getAttributes()['name'];
        $expectedMutation = (new FakeMutation)->getAttributes()['name'];
        $expectedSchemaType = (new FakeSchemaType)->getAttributes()['name'];
        $expectedGlobalType = (new FakeGlobalType)->getAttributes()['name'];
        $expectedEnumType = (new FakeGraphQlEnumType)->getAttributes()['name'];
        $expectedInterfaceType = (new FakeGraphQlInterfaceType)->getAttributes()['name'];
        $expectedUnionType = (new FakeUnionType)->getAttributes()['name'];

        $schemaQueryFields = GraphQL::schema('primary')->getConfig()->getQuery()->getFields();
        $schemaMutationFields = GraphQL::schema('primary')->getConfig()->getMutation()->getFields();
        $graphQlTypes = GraphQL::getTypes();

        $this->assertArrayHasKey($expectedQuery, $schemaQueryFields);
        $this->assertArrayNotHasKey($expectedMutation, $schemaQueryFields);

        $this->assertArrayHasKey($expectedMutation, $schemaMutationFields);
        $this->assertArrayNotHasKey($expectedQuery, $schemaMutationFields);

        $this->assertArrayHasKey($expectedSchemaType, $graphQlTypes);
        $this->assertArrayHasKey($expectedGlobalType, $graphQlTypes);
        $this->assertArrayHasKey($expectedEnumType, $graphQlTypes);
        $this->assertArrayHasKey($expectedInterfaceType, $graphQlTypes);
        $this->assertArrayHasKey($expectedUnionType, $graphQlTypes);
    }

    #[Test]
    public function it_merges_and_publishes_package_configuration_and_translations(): void
    {
        $this->assertSame(
            ['GraphQL\\\\Bootstrapper'],
            config('graphql-laravel-bootstrapper.namespace_filters')
        );

        $this->assertSame(
            'GraphQL Bootstrapper',
            trans('graphql-laravel-bootstrapper::exception.category')
        );

        $publishGroups = ServiceProvider::publishableGroups();
        $pathsToPublish = ServiceProvider::pathsToPublish(
            GraphQlBootstrapperServiceProvider::class,
            'bootstrapper-config'
        );

        $this->assertContains('bootstrapper-config', $publishGroups);
        $this->assertArrayHasKey(
            realpath(__DIR__ . '/../../config/graphql-laravel-bootstrapper.php'),
            array_combine(
                array_map(static fn (string $path) => realpath($path) ?: $path, array_keys($pathsToPublish)),
                array_values($pathsToPublish)
            )
        );
    }
}
