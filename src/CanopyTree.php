<?php

namespace Canopy;

use Canopy\Grouping\GroupResolverPipeline;
use Canopy\Grouping\TreeBuilder;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;

/**
 * Builds the hierarchical explorer tree for a generated OpenAPI document by
 * resolving each documented operation back to its Laravel route and grouping
 * it via the resolver pipeline.
 */
class CanopyTree
{
    public function __construct(
        private readonly GroupResolverPipeline $pipeline,
        private readonly Router $router,
    ) {}

    /**
     * @param  array<string, mixed>  $spec  The generated OpenAPI document.
     * @return list<array<string, mixed>>
     */
    public function build(array $spec): array
    {
        $routesByKey = $this->indexRoutes();
        $builder = new TreeBuilder;

        $paths = is_array($spec['paths'] ?? null) ? $spec['paths'] : [];

        foreach ($paths as $path => $operations) {
            if (! is_string($path) || ! is_array($operations)) {
                continue;
            }

            foreach ($operations as $method => $operation) {
                if (! $this->isHttpMethod($method) || ! is_array($operation)) {
                    continue;
                }

                $route = $routesByKey[$this->key($method, $path)] ?? null;

                $groupPath = $route instanceof Route
                    ? $this->pipeline->resolve($route)
                    : $this->fallbackFromTags($operation);

                if ($groupPath === []) {
                    continue;
                }

                $builder->addRoute($groupPath, $this->routeNode($method, $path, $operation, $route));
            }
        }

        return $builder->toArray();
    }

    /**
     * @return array<string, Route>
     */
    private function indexRoutes(): array
    {
        $index = [];

        foreach ($this->router->getRoutes()->getRoutes() as $route) {
            $uri = '/'.ltrim($route->uri(), '/');

            foreach ($route->methods() as $method) {
                // Full URI key  e.g. get /api/admin/users
                $index[$this->key($method, $uri)] = $route;

                // Also index without leading api/ and optional version prefix
                // so spec paths like /admin/users (Scramble strips api/) still match
                $stripped = (string) preg_replace('#^/api/(?:v\d+/)?#i', '/', $uri);
                if ($stripped !== $uri) {
                    $index[$this->key($method, $stripped)] = $route;
                }
            }
        }

        return $index;
    }

    private function key(string $method, string $path): string
    {
        return strtolower($method).' '.$path;
    }

    private function isHttpMethod(mixed $method): bool
    {
        return is_string($method)
            && in_array(strtolower($method), ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'], true);
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>
     */
    private function routeNode(string $method, string $path, array $operation, ?Route $route): array
    {
        $summary = is_string($operation['summary'] ?? null) ? $operation['summary'] : '';
        $operationId = is_string($operation['operationId'] ?? null) ? $operation['operationId'] : null;

        $controller = $route?->getAction('controller');
        $controllerName = is_string($controller) ? class_basename(explode('@', $controller)[0]) : null;

        $id = $operationId ?: strtolower($method).'-'.Str::of($path)->replace(['/', '{', '}'], ['-', '', ''])->trim('-');

        return array_filter([
            'id' => $id,
            'name' => $summary !== '' ? $summary : strtoupper($method).' '.$path,
            'method' => strtolower($method),
            'path' => $path,
            'summary' => $summary,
            'operationId' => $operationId,
            'controller' => $controllerName,
            'deprecated' => ($operation['deprecated'] ?? false) === true ?: null,
            'type' => 'route',
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Fallback grouping for operations whose route can't be matched: use the
     * first OpenAPI tag, or "General".
     *
     * @param  array<string, mixed>  $operation
     * @return list<string>
     */
    private function fallbackFromTags(array $operation): array
    {
        $tags = $operation['tags'] ?? null;

        if (is_array($tags) && isset($tags[0]) && is_string($tags[0]) && $tags[0] !== '') {
            return [$tags[0]];
        }

        return ['General'];
    }
}
