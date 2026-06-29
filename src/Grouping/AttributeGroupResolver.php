<?php

namespace Canopy\Grouping;

use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Route;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

/**
 * Resolves a group hierarchy from Scramble's #[Group] attribute on the
 * controller class and/or the invoked method.
 *
 * Nesting is expressed inside the group name using "/" or ">", e.g.
 * #[Group('Admin / Users')] or #[Group('Billing > Invoices')]. A class-level
 * group becomes the parent of any method-level group.
 */
class AttributeGroupResolver implements GroupResolver
{
    /** @var class-string */
    private const SCRAMBLE_GROUP = Group::class;

    public function resolve(Route $route): ?array
    {
        if (! class_exists(self::SCRAMBLE_GROUP)) {
            return null;
        }

        $controller = $route->getAction('controller');
        if (! is_string($controller) || $controller === '') {
            return null;
        }

        try {
            [$className, $methodName] = array_pad(explode('@', $controller, 2), 2, null);

            if (! is_string($className) || ! class_exists($className)) {
                return null;
            }

            $classRef = new ReflectionClass($className);
            $classGroup = $classRef->getAttributes(self::SCRAMBLE_GROUP)[0] ?? null;

            $methodGroup = null;
            if (is_string($methodName) && $methodName !== '' && $classRef->hasMethod($methodName)) {
                $methodRef = new ReflectionMethod($className, $methodName);
                $methodGroup = $methodRef->getAttributes(self::SCRAMBLE_GROUP)[0] ?? null;
            }

            if (! $classGroup && ! $methodGroup) {
                return null;
            }

            $path = array_merge(
                $this->pathFromAttribute($classGroup),
                $this->pathFromAttribute($methodGroup),
            );

            return $path === [] ? null : $path;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  ReflectionAttribute<object>|null  $attribute
     * @return list<string>
     */
    private function pathFromAttribute(?ReflectionAttribute $attribute): array
    {
        if ($attribute === null) {
            return [];
        }

        $arguments = $attribute->getArguments();
        $name = $arguments['name'] ?? $arguments[0] ?? null;

        if (! is_string($name) || $name === '') {
            return [];
        }

        return GroupPathSplitter::split($name);
    }
}
