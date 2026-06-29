<?php

namespace Canopy\Grouping;

use Illuminate\Routing\Route;

/**
 * Runs an ordered set of {@see GroupResolver} instances and returns the first
 * non-empty group path. A terminal resolver (typically
 * {@see FallbackGroupResolver}) guarantees a non-empty result.
 */
class GroupResolverPipeline
{
    /** @var list<GroupResolver> */
    protected array $resolvers;

    /**
     * @param  iterable<GroupResolver>  $resolvers
     */
    public function __construct(iterable $resolvers)
    {
        $this->resolvers = is_array($resolvers)
            ? array_values($resolvers)
            : iterator_to_array($resolvers, false);
    }

    /**
     * Build the default, ordered pipeline from configuration.
     *
     * @param  array<int, mixed>  $rules  Raw, user-provided rule definitions.
     */
    public static function fromConfig(array $rules = [], string $fallback = 'General'): self
    {
        return new self([
            new AttributeGroupResolver,
            new ConfigGroupResolver($rules),
            new RouteGroupResolver,
            new FallbackGroupResolver($fallback),
        ]);
    }

    public function registerResolver(GroupResolver $resolver, bool $prepend = false): void
    {
        if ($prepend) {
            array_unshift($this->resolvers, $resolver);
        } else {
            $this->resolvers[] = $resolver;
        }
    }

    /**
     * @return list<string>
     */
    public function resolve(Route $route): array
    {
        foreach ($this->resolvers as $resolver) {
            $path = $resolver->resolve($route);

            if ($path !== null && $path !== []) {
                return $path;
            }
        }

        return [];
    }
}
