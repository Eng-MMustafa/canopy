<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    /*
    |--------------------------------------------------------------------------
    | Enable Canopy
    |--------------------------------------------------------------------------
    |
    | When disabled, Canopy registers no routes and does not influence Scramble
    | in any way. Your existing Scramble documentation keeps working untouched.
    |
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Scramble API
    |--------------------------------------------------------------------------
    |
    | The name of the Scramble API document Canopy should render. Use this if
    | you registered additional APIs via Scramble::registerApi().
    |
    */
    'api' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Memory Limit
    |--------------------------------------------------------------------------
    |
    | Scramble analyses your whole route surface to build the OpenAPI document,
    | which can be memory intensive on large applications and may fail with a
    | fatal "allowed memory size exhausted" error. Set a PHP memory limit (e.g.
    | "1024M" or "-1") to apply for the docs request only. Leave null to keep
    | the environment's default.
    |
    */
    'memory_limit' => null,

    /*
    |--------------------------------------------------------------------------
    | Pre-exported Document
    |--------------------------------------------------------------------------
    |
    | For large applications, generating the OpenAPI document on every request
    | can be slow or hit memory/tooling limits in the web context. Instead you
    | can export it once via `php artisan scramble:export --path=...` and point
    | this at the resulting file. When set to an existing file, Canopy serves it
    | directly instead of regenerating. Leave null to generate on the fly.
    |
    */
    'document_path' => env('CANOPY_DOCUMENT_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Documentation Route
    |--------------------------------------------------------------------------
    |
    | The path where the Canopy explorer is served, and the path of the JSON
    | OpenAPI document it consumes. Middleware is applied to both routes.
    |
    */
    'route' => [
        'ui' => 'docs/canopy',
        'document' => 'docs/canopy.json',
        'middleware' => [
            RestrictedDocsAccess::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    |
    | Make the explorer your own. The logo accepts any URL (or null to show the
    | title only). The accent color drives links, the active item and badges.
    |
    */
    'branding' => [
        'title' => 'API Documentation',
        'logo' => null,
        'accent' => '#6366f1',
        'theme' => 'system', // light | dark | system
    ],

    /*
    |--------------------------------------------------------------------------
    | Grouping Rules
    |--------------------------------------------------------------------------
    |
    | Optional, ordered rules used to assign a (possibly nested) group to a
    | route when no #[Group] attribute is present. The first matching rule wins.
    | Use "/" or ">" in the group name to express nesting.
    |
    |   ['group' => 'Admin > Users', 'match' => ['prefix' => 'admin/users/*']],
    |   ['group' => 'Billing',       'match' => ['middleware' => 'subscribed']],
    |   ['group' => 'Internal',      'match' => ['namespace' => 'App\\Http\\Controllers\\Internal\\*']],
    |
    */
    'rules' => [],

    /*
    |--------------------------------------------------------------------------
    | Fallback Group
    |--------------------------------------------------------------------------
    |
    | The group used when nothing else resolves and the controller name cannot
    | be derived.
    |
    */
    'fallback' => 'General',
];
