<?php

use Canopy\Grouping\ConfigGroupResolver;
use Canopy\Grouping\FallbackGroupResolver;
use Canopy\Grouping\GroupPathSplitter;
use Canopy\Grouping\GroupResolverPipeline;
use Canopy\Grouping\RouteGroupResolver;
use Canopy\Grouping\TreeBuilder;

it('splits group paths on / and >', function () {
    expect(GroupPathSplitter::split('Admin / Users'))->toBe(['Admin', 'Users'])
        ->and(GroupPathSplitter::split('Billing > Invoices > Lines'))->toBe(['Billing', 'Invoices', 'Lines'])
        ->and(GroupPathSplitter::split('  Single  '))->toBe(['Single'])
        ->and(GroupPathSplitter::split(''))->toBe([]);
});

it('resolves a group from the route prefix, stripping api/version', function () {
    $resolver = new RouteGroupResolver;

    expect($resolver->resolve(makeRoute('api/v1/admin/users', ['prefix' => 'api/v1/admin/users'])))
        ->toBe(['Admin', 'Users']);
});

it('resolves a group from the dotted route name when no prefix', function () {
    $resolver = new RouteGroupResolver;

    expect($resolver->resolve(makeRoute('whatever', ['as' => 'admin.users.index'])))
        ->toBe(['Admin', 'Users']);
});

it('resolves config rules in order with glob matching', function () {
    $resolver = new ConfigGroupResolver([
        ['group' => 'Admin > Users', 'match' => ['prefix' => 'admin/users/*']],
        ['group' => 'Catch All', 'match' => ['prefix' => 'admin/*']],
    ]);

    expect($resolver->resolve(makeRoute('admin/users/x', ['prefix' => 'admin/users/x'])))
        ->toBe(['Admin', 'Users'])
        ->and($resolver->resolve(makeRoute('admin/other', ['prefix' => 'admin/other'])))
        ->toBe(['Catch All']);
});

it('falls back to the controller name without the Controller suffix', function () {
    $resolver = new FallbackGroupResolver('General');

    expect($resolver->resolve(makeRoute('x', ['controller' => 'App\\Http\\Controllers\\InvoiceController@index'])))
        ->toBe(['Invoice'])
        ->and($resolver->resolve(makeRoute('x', [])))
        ->toBe(['General']);
});

it('runs the pipeline and returns the first non-empty resolver', function () {
    $pipeline = GroupResolverPipeline::fromConfig(
        [['group' => 'From Config', 'match' => ['prefix' => 'special/*']]],
        'General',
    );

    expect($pipeline->resolve(makeRoute('special/x', ['prefix' => 'special/x'])))->toBe(['From Config'])
        ->and($pipeline->resolve(makeRoute('plain', ['controller' => 'App\\WidgetController@show'])))->toBe(['Widget']);
});

it('builds a merged, nested tree from group paths', function () {
    $builder = new TreeBuilder;
    $builder->addRoute(['Admin', 'Users'], ['id' => 'a', 'type' => 'route', 'method' => 'get', 'path' => '/users']);
    $builder->addRoute(['Admin', 'Users'], ['id' => 'b', 'type' => 'route', 'method' => 'post', 'path' => '/users']);
    $builder->addRoute(['Admin', 'Roles'], ['id' => 'c', 'type' => 'route', 'method' => 'get', 'path' => '/roles']);

    $tree = $builder->toArray();

    expect($tree)->toHaveCount(1)
        ->and($tree[0]['name'])->toBe('Admin')
        ->and($tree[0]['children'])->toHaveCount(2);

    $users = collect($tree[0]['children'])->firstWhere('name', 'Users');
    expect($users['routes'])->toHaveCount(2)
        ->and($users['depth'])->toBe(1);
});

it('ignores empty group paths', function () {
    $builder = new TreeBuilder;
    $builder->addRoute([], ['id' => 'x']);

    expect($builder->toArray())->toBe([]);
});
