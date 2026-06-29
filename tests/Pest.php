<?php

use Illuminate\Routing\Route;

/**
 * Build a lightweight Illuminate route for resolver tests without booting an app.
 *
 * @param  array<string, mixed>  $action
 */
function makeRoute(string $uri, array $action = [], array $methods = ['GET']): Route
{
    return new Route($methods, $uri, $action);
}
