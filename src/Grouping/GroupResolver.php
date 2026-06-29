<?php

namespace Canopy\Grouping;

use Illuminate\Routing\Route;

interface GroupResolver
{
    /**
     * Resolve the group path (top-down hierarchy of group names) for the given
     * route. Return null when this resolver cannot determine a path, allowing
     * the next resolver in the pipeline to attempt resolution.
     *
     * @return list<string>|null
     */
    public function resolve(Route $route): ?array;
}
