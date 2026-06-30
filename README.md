# 🌿 Canopy

**Beautiful, hierarchical API documentation for Laravel — powered by [Scramble](https://github.com/dedoc/scramble).**

Scramble generates a great OpenAPI document automatically. Canopy adds what large APIs need: a **nested, navigable, branded documentation explorer**. Group your endpoints into a tree (Admin → Users → Sessions…), search them instantly, and ship docs that look like *your* product.

Canopy is a **drop-in add-on**. It depends on Scramble and **does not modify or fork it** — it builds on Scramble's public extension points, so you keep upgrading Scramble normally.

---

## Why Canopy?

- **Hierarchical groups** — unlimited nesting, unlike flat tag lists.
- **Zero core changes** — pure add-on; your Scramble setup is untouched.
- **Your brand** — title, logo, accent color, light/dark/system theme.
- **Fast search** — filter the whole API tree as you type.
- **Automatic grouping** — by `#[Group]` attribute, config rules, route prefix/name, or controller name.
- **Familiar rendering** — endpoint detail powered by Stoplight Elements.

---

## Installation

```bash
composer require eng-mmustafa/canopy
```

Laravel auto-discovers the service provider. Publish the config if you want to customize it:

```bash
php artisan vendor:publish --tag=canopy-config
```

Then open:

```
/docs/canopy
```

> Requires `dedoc/scramble` `^0.13` and PHP `^8.1`.

---

## Grouping

Canopy resolves each endpoint's group in priority order. The **first** that matches wins:

### 1. `#[Group]` attribute (nested with `/` or `>`)

```php
use Dedoc\Scramble\Attributes\Group;

#[Group('Admin / Users')]
class UserController
{
    #[Group('Admin / Users / Sessions')]
    public function sessions() {}
}
```

### 2. Config rules

```php
// config/canopy.php
'rules' => [
    ['group' => 'Admin > Users', 'match' => ['prefix' => 'admin/users/*']],
    ['group' => 'Billing',       'match' => ['middleware' => 'subscribed']],
    ['group' => 'Internal',      'match' => ['namespace' => 'App\\Http\\Controllers\\Internal\\*']],
],
```

Match conditions: `prefix`, `name`, `middleware`, `namespace` (all glob-matched).

### 3. Route prefix / name

`Route::prefix('admin/billing')` → `Admin → Billing`. Falls back to the dotted route name (`admin.users.index` → `Admin → Users`).

### 4. Controller name (fallback)

`InvoiceController` → `Invoice`. Finally, the configurable `fallback` group (default `General`).

---

## Branding

```php
// config/canopy.php
'branding' => [
    'title'  => 'Acme API',
    'logo'   => 'https://acme.test/logo.svg',
    'accent' => '#10b981',
    'theme'  => 'system', // light | dark | system
],
```

Customize the UI further:

```bash
php artisan vendor:publish --tag=canopy-views
```

---

## Configuration reference

| Key | Description |
| --- | --- |
| `enabled` | Master switch. When `false`, Canopy registers nothing. |
| `api` | Scramble API name (for multi-API setups). |
| `memory_limit` | PHP memory limit applied to the docs request only (e.g. `1024M`, `-1`). Useful for large apps where generation is memory heavy. `null` keeps the environment default. |
| `route.ui` | Path of the explorer UI (default `docs/canopy`). |
| `route.document` | Path of the JSON document Canopy serves. |
| `route.middleware` | Middleware applied to both routes. |
| `branding.*` | Title, logo, accent, theme. |
| `rules` | Ordered grouping rules. |
| `fallback` | Group used when nothing else resolves. |

---

## How it works

1. Scramble generates the OpenAPI document (unchanged).
2. Canopy maps each documented operation back to its Laravel route.
3. A resolver pipeline assigns each route a (possibly nested) group path.
4. A tree builder assembles the hierarchy.
5. The explorer renders the tree on the left and Stoplight Elements on the right.

No `x-tree`, no patched `OpenApi`, no forked Scramble — everything happens in Canopy.

---

## Testing

```bash
composer install
composer test       # Pest
composer analyse    # PHPStan
composer lint       # Pint
```

---

## License

MIT © Mohammed Mostafa. Built on top of the excellent [Scramble](https://github.com/dedoc/scramble) by Roman Lytvynenko.
