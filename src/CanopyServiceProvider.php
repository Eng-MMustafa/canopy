<?php

namespace Canopy;

use Canopy\Grouping\GroupResolverPipeline;
use Canopy\Http\DocumentationController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\ServiceProvider;

class CanopyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/canopy.php', 'canopy');

        $this->app->singleton(GroupResolverPipeline::class, function ($app) {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('canopy', []);

            /** @var array<int, mixed> $rules */
            $rules = is_array($config['rules'] ?? null) ? array_values($config['rules']) : [];

            return GroupResolverPipeline::fromConfig(
                $rules,
                is_string($config['fallback'] ?? null) ? $config['fallback'] : 'General',
            );
        });

        $this->app->singleton(CanopyTree::class, fn ($app) => new CanopyTree(
            $app->make(GroupResolverPipeline::class),
            $app->make(Router::class),
        ));
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'canopy');

        $this->publishes([
            __DIR__.'/../config/canopy.php' => $this->app->configPath('canopy.php'),
        ], 'canopy-config');

        $this->publishes([
            __DIR__.'/../resources/views' => $this->app->resourcePath('views/vendor/canopy'),
        ], 'canopy-views');

        if (config('canopy.enabled', true)) {
            $this->registerRoutes();
        }
    }

    private function registerRoutes(): void
    {
        /** @var array<string, mixed> $route */
        $route = config('canopy.route', []);

        $middleware = is_array($route['middleware'] ?? null) ? $route['middleware'] : [];
        $uiPath = is_string($route['ui'] ?? null) ? $route['ui'] : 'docs/canopy';
        $documentPath = is_string($route['document'] ?? null) ? $route['document'] : 'docs/canopy.json';

        RouteFacade::middleware($middleware)->group(function () use ($uiPath, $documentPath) {
            RouteFacade::get($uiPath, [DocumentationController::class, 'ui'])->name('canopy.ui');
            RouteFacade::get($documentPath, [DocumentationController::class, 'document'])->name('canopy.document');
        });
    }
}
