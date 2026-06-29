<?php

namespace Canopy\Grouping;

/**
 * Builds a hierarchical documentation explorer tree from resolved group paths.
 *
 * The builder is deliberately decoupled from any generator objects: it accepts
 * plain group paths and route node arrays, which keeps it trivially
 * unit-testable and free of reflection or routing concerns.
 */
class TreeBuilder
{
    /** @var array<string, TreeNode> */
    protected array $nodes = [];

    /** @var list<TreeNode> */
    protected array $roots = [];

    /**
     * Insert a route under the given group path, creating intermediate group
     * nodes as needed. Duplicate group paths are merged automatically.
     *
     * @param  list<string>  $groupPath
     * @param  array<string, mixed>  $routeNode
     */
    public function addRoute(array $groupPath, array $routeNode): void
    {
        if ($groupPath === []) {
            return;
        }

        $this->ensureGroupPath($groupPath)->routes[] = $routeNode;
    }

    /**
     * @param  list<string>  $groupPath
     */
    protected function ensureGroupPath(array $groupPath): TreeNode
    {
        $parentKey = null;
        $depth = 0;

        foreach ($groupPath as $segment) {
            $key = $parentKey === null ? $segment : $parentKey.'/'.$segment;

            if (! isset($this->nodes[$key])) {
                $node = new TreeNode($key, $segment);
                $node->parent = $parentKey;
                $node->depth = $depth;

                $this->nodes[$key] = $node;

                if ($parentKey === null) {
                    $this->roots[] = $node;
                } else {
                    $this->nodes[$parentKey]->children[] = $node;
                }
            }

            $parentKey = $key;
            $depth++;
        }

        return $this->nodes[$parentKey];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(): array
    {
        $roots = $this->roots;

        usort($roots, fn (TreeNode $a, TreeNode $b) => $a->order <=> $b->order ?: strnatcasecmp($a->name, $b->name));

        return array_map(fn (TreeNode $node) => $node->toArray(), $roots);
    }
}
