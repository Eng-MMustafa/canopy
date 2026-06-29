<?php

use Canopy\CanopyTree;
use Canopy\Grouping\GroupResolverPipeline;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Router;

#[Group('Admin / Users')]
class CanopyTreeTest_UserController
{
    public function index() {}

    public function store() {}
}

class CanopyTreeTest_HealthController
{
    public function __invoke() {}
}

function makeTreeBuilder(): array
{
    $router = new Router(new Dispatcher);

    $router->get('api/admin/users', [CanopyTreeTest_UserController::class, 'index']);
    $router->post('api/admin/users', [CanopyTreeTest_UserController::class, 'store']);
    $router->get('api/health', CanopyTreeTest_HealthController::class);

    $tree = new CanopyTree(GroupResolverPipeline::fromConfig([], 'General'), $router);

    // A minimal OpenAPI-shaped document as Scramble would generate.
    $spec = [
        'paths' => [
            '/api/admin/users' => [
                'get' => ['summary' => 'List users', 'operationId' => 'users.index'],
                'post' => ['summary' => 'Create user', 'operationId' => 'users.store'],
            ],
            '/api/health' => [
                'get' => ['summary' => 'Health check', 'operationId' => 'health'],
            ],
        ],
    ];

    return $tree->build($spec);
}

it('builds a hierarchical tree from a real spec and registered routes', function () {
    $tree = makeTreeBuilder();

    $names = collect($tree)->pluck('name')->all();
    expect($names)->toContain('Admin')->toContain('CanopyTreeTest_Health');

    $admin = collect($tree)->firstWhere('name', 'Admin');
    $users = collect($admin['children'])->firstWhere('name', 'Users');

    expect($users)->not->toBeNull()
        ->and($users['routes'])->toHaveCount(2)
        ->and(collect($users['routes'])->pluck('method')->sort()->values()->all())->toBe(['get', 'post']);
});

it('groups an invokable controller by its name when no attribute or prefix group applies', function () {
    $tree = makeTreeBuilder();

    $health = collect($tree)->firstWhere('name', 'CanopyTreeTest_Health');

    expect($health)->not->toBeNull()
        ->and($health['routes'])->toHaveCount(1)
        ->and($health['routes'][0]['operationId'])->toBe('health');
});
