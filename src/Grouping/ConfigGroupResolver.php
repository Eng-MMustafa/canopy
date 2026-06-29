<?php

namespace Canopy\Grouping;

use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Resolves a group hierarchy from the user-defined `canopy.rules` configuration.
 * Rules are evaluated in order; the first one whose match conditions are all
 * satisfied wins. Conditions: prefix, name, middleware, namespace (glob).
 */
class ConfigGroupResolver implements GroupResolver
{
    /**
     * @param  array<int, mixed>  $rules  Raw, user-provided rule definitions.
     */
    public function __construct(
        private readonly array $rules = [],
    ) {}

    public function resolve(Route $route): ?array
    {
        foreach ($this->rules as $rule) {
            if (! is_array($rule) || ! isset($rule['group'], $rule['match'])) {
                continue;
            }

            if (! is_array($rule['match']) || ! is_string($rule['group'])) {
                continue;
            }

            if ($this->matches($route, $rule['match'])) {
                $path = GroupPathSplitter::split($rule['group']);

                if ($path !== []) {
                    return $path;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $match
     */
    private function matches(Route $route, array $match): bool
    {
        if (isset($match['prefix']) && ! $this->matchesGlob($match['prefix'], trim((string) $route->getPrefix(), '/'))) {
            return false;
        }

        if (isset($match['name']) && ! $this->matchesGlob($match['name'], (string) $route->getName())) {
            return false;
        }

        if (isset($match['middleware']) && ! $this->matchesAny($match['middleware'], $route->gatherMiddleware())) {
            return false;
        }

        if (isset($match['namespace'])) {
            $controller = $route->getAction('controller');
            if (! is_string($controller) || ! $this->matchesGlob($match['namespace'], $controller)) {
                return false;
            }
        }

        return true;
    }

    private function matchesGlob(mixed $patterns, string $value): bool
    {
        foreach (Arr::wrap($patterns) as $pattern) {
            if (is_string($pattern) && Str::is($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $values
     */
    private function matchesAny(mixed $patterns, array $values): bool
    {
        foreach (Arr::wrap($patterns) as $pattern) {
            foreach ($values as $value) {
                if (is_string($pattern) && Str::is($pattern, (string) $value)) {
                    return true;
                }
            }
        }

        return false;
    }
}
