<?php

namespace GraphQL\Bootstrapper\Tests\Feature\Pagination;

use GraphQL\Bootstrapper\GraphQlBootstrapperServiceProvider;
use GraphQL\Bootstrapper\GraphQL\Types\Pagination\ConnectionType;
use GraphQL\Bootstrapper\Middleware\ResolvePageForPagination;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Type as GraphQLType;

class CursorPaginationTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            GraphQlBootstrapperServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Use in-memory SQLite for simplicity
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Register our temporary GraphQL types and schema (primary)
        $app['config']->set('graphql.schemas', [
            'cursor' => [
                'query' => [
                    'postsConnection' => PostsConnectionQuery::class,
                ],
                'mutation' => [],
                'types' => [
                    'Post' => PostType::class,
                ],
            ],
        ]);

        // Register global types required by the connection
        $app['config']->set('graphql.types', [
            \GraphQL\Bootstrapper\GraphQL\Types\Pagination\PageInfoType::class,
        ]);

        // Ensure our connection type is used
        $app['config']->set('graphql.pagination_type', ConnectionType::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create schema and seed simple data set
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });

        foreach (range(1, 5) as $i) {
            Post::query()->create(['title' => "Post $i"]);
        }
    }

    #[Test]
    public function it_paginates_with_after_cursor_and_updates_pageinfo(): void
    {
        // 1) First page without `after` to get a cursor
        $query = /** @lang GraphQL */ <<<'GQL'
            query FirstPage($first: Int!) {
              postsConnection(first: $first) {
                totalCount
                edges { cursor node { id title } }
                pageInfo { hasNextPage hasPreviousPage startCursor endCursor }
              }
            }
        GQL;

        $firstResult = GraphQL::query($query, ['first' => 2], ['schema' => 'cursor']);
        if (array_key_exists('errors', $firstResult)) {
            $this->fail('GraphQL errors: ' . var_export($firstResult['errors'], true));
        }
        $firstData = $firstResult['data']['postsConnection'];

        $this->assertSame(5, $firstData['totalCount']);
        $this->assertTrue($firstData['pageInfo']['hasNextPage']);
        $this->assertFalse($firstData['pageInfo']['hasPreviousPage']);
        $this->assertCount(2, $firstData['edges']);

        $afterCursor = $firstData['edges'][1]['cursor']; // cursor for the 2nd node
        $this->assertNotSame('', $afterCursor);

        // 2) Second page with `after` to verify ResolvePageForPagination is applied
        $nextQuery = /** @lang GraphQL */ <<<'GQL'
            query NextPage($first: Int!, $after: String) {
              postsConnection(first: $first, after: $after) {
                totalCount
                edges { cursor node { id title } }
                pageInfo { hasNextPage hasPreviousPage startCursor endCursor }
              }
            }
        GQL;

        $secondResult = GraphQL::query($nextQuery, ['first' => 2, 'after' => $afterCursor], ['schema' => 'cursor']);
        $this->assertArrayNotHasKey('errors', $secondResult);
        $secondData = $secondResult['data']['postsConnection'];

        // Expect pageInfo.startCursor to reflect the `after` cursor we sent
        $this->assertSame($afterCursor, $secondData['pageInfo']['startCursor']);
        $this->assertTrue($secondData['pageInfo']['hasPreviousPage']);
        $this->assertCount(2, $secondData['edges']);

        // Validate items are the next 2 posts (ids 3 and 4)
        $this->assertSame('3', $secondData['edges'][0]['node']['id']);
        $this->assertSame('4', $secondData['edges'][1]['node']['id']);
    }
}

// --- Minimal model, type, and query used only by this test ---

class Post extends Model
{
    protected $table = 'posts';
    protected $guarded = [];
    public $timestamps = true;
}

class PostType extends GraphQLType
{
    public function attributes(): array
    {
        return [
            'name' => 'Post',
            'model' => Post::class,
        ];
    }

    public function fields(): array
    {
        return [
            'id' => [ 'type' => GraphQL::type('ID!') ],
            'title' => [ 'type' => GraphQL::type('String!') ],
        ];
    }

    public static function getSchemaName(): string
    {
        return 'cursor';
    }
}

class PostsConnectionQuery extends Query
{
    protected $attributes = [
        'name' => 'postsConnection',
        'description' => 'Posts connection with cursor pagination',
    ];

    /** @var list<class-string> */
    protected $middleware = [
        ResolvePageForPagination::class,
    ];

    public function type(): \GraphQL\Type\Definition\Type
    {
        // Wrap the Post type into a Connection
        return GraphQL::wrapType('Post', 'PostConnection', ConnectionType::class);
    }

    public function args(): array
    {
        return [
            'first' => [ 'type' => GraphQL::type('Int!') ],
            'after' => [ 'type' => GraphQL::type('String') ],
        ];
    }

    public function resolve($root, array $args)
    {
        $limit = $args['first'];

        // Order by id asc and use the package macro to get [total, items]
        return Post::query()->lengthAwareCursorPaginate($limit, 'id');
    }
}
