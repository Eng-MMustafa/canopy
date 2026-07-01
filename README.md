# 🌿 Canopy

**Beautiful, hierarchical API documentation for Laravel — powered by [Scramble](https://github.com/dedoc/scramble).**

Scramble generates a great OpenAPI document automatically. Canopy adds what large APIs need: a **nested, navigable, branded documentation explorer**. Group your endpoints into a tree (Admin → Users → Sessions…), search them instantly, and ship docs that look like *your* product.

Canopy is a **drop-in add-on**. It depends on Scramble and **does not modify or fork it** — it builds on Scramble's public extension points, so you keep upgrading Scramble normally.

---

## Why Canopy?

- **Hierarchical groups** — unlimited nesting, unlike flat tag lists.
- **Zero core changes** — pure add-on; your Scramble setup is untouched.
- **Your brand** — title, logo, accent color, light/dark theme.
- **Dark / Light mode toggle** — one-click switch with preference saved in `localStorage`.
- **Fast search** — filter the whole API tree as you type.
- **Automatic grouping** — by `#[Group]` attribute, config rules, route prefix/name, or controller name.
- **Bearer token support** — add `securitySchemes` once; TryIt shows an auth input on every endpoint.
- **Large app support** — serve a pre-exported JSON file instead of generating on every request.

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | `^8.1` |
| Laravel | `^10.0 \| ^11.0` |
| dedoc/scramble | `^0.13` |

---

## Installation

```bash
composer require eng-mmustafa/canopy
```

Laravel auto-discovers the service provider. Then open:

```
http://your-app.test/docs/canopy
```

Publish the config to customize:

```bash
php artisan vendor:publish --tag=canopy-config
```

---

## Quick start

### Step 1 — Install & visit

```bash
composer require eng-mmustafa/canopy
```

Visit `/docs/canopy` — Canopy auto-generates the tree from your existing Scramble setup.

### Step 2 — Add Bearer token support (recommended)

Add this to your `AppServiceProvider::boot()` so TryIt shows an auth input on every endpoint:

```php
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
    $openApi->secure(
        SecurityScheme::http('bearer', 'JWT')
    );
});
```

### Step 3 — (Large apps) Pre-export the document

For large codebases, generate the spec once instead of on every request:

```bash
php -d memory_limit=-1 artisan scramble:export --path=api-docs.json
```

Then add to `.env`:

```dotenv
CANOPY_DOCUMENT_PATH="/absolute/path/to/api-docs.json"
```

Re-run the export whenever your API changes (e.g. in your CI/CD pipeline).

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
    'accent' => '#10b981',   // any CSS color
    'theme'  => 'dark',      // light | dark (initial theme)
],
```

> The user can also toggle dark/light mode at any time via the **🌙 button** in the sidebar header. Preference is saved in `localStorage` and overrides the config value.

Publish and customize the view:

```bash
php artisan vendor:publish --tag=canopy-views
```

---

## Configuration reference

| Key | Default | Description |
|---|---|---|
| `enabled` | `true` | Master switch. When `false`, Canopy registers nothing. |
| `api` | `'default'` | Scramble API name (for multi-API setups). |
| `memory_limit` | `null` | PHP memory limit for the docs request only (e.g. `'1024M'`, `'-1'`). `null` keeps the environment default. |
| `document_path` | `null` | Absolute path to a pre-exported OpenAPI JSON file. When set, Canopy serves it directly. Reads from `CANOPY_DOCUMENT_PATH` env var. |
| `route.ui` | `'docs/canopy'` | Path of the explorer UI. |
| `route.document` | `'docs/canopy.json'` | Path of the raw JSON document. |
| `route.middleware` | `['web']` | Middleware applied to both routes. |
| `branding.title` | `'API Documentation'` | Sidebar heading and page title. |
| `branding.logo` | `null` | URL to a logo image shown in the sidebar. |
| `branding.accent` | `'#6366f1'` | Accent color (buttons, active states, focus rings). |
| `branding.theme` | `'system'` | Initial theme: `light` or `dark`. |
| `rules` | `[]` | Ordered grouping rules (see Grouping section). |
| `fallback` | `'General'` | Group name when no rule matches. |

---

## Large applications

On big codebases, generating the OpenAPI document on every web request can be slow or exhaust PHP memory.

**Option 1 — Raise the memory limit:**

```php
// config/canopy.php
'memory_limit' => '1024M', // or '-1' for unlimited
```

**Option 2 (Recommended) — Pre-export the document:**

```bash
# Generate once with no memory limit
php -d memory_limit=-1 artisan scramble:export --path=api-docs.json
```

```dotenv
CANOPY_DOCUMENT_PATH="/absolute/path/to/api-docs.json"
```

Add the export to your deploy script so docs stay in sync with your code.

---

## How it works

1. Scramble generates the OpenAPI document (unchanged).
2. Canopy maps each documented operation back to its Laravel route.
3. A resolver pipeline assigns each route a (possibly nested) group path.
4. A tree builder assembles the hierarchy.
5. The sidebar renders the tree; Stoplight Elements renders the operation detail.
6. Clicking a sidebar link fires `hashchange` / `popstate` events that Stoplight's internal React Router picks up — no page reload.

No `x-tree`, no patched `OpenApi`, no forked Scramble — everything happens inside Canopy.

---

## Testing

```bash
composer install
composer test       # Pest
composer analyse    # PHPStan
composer lint       # Pint
```

---

## Changelog

| Version | Highlights |
|---|---|
| **v0.2.2** | Fix: hide Stoplight internal sidebar via JS polling so Canopy's sidebar is the only navigation. |
| **v0.2.1** | Feat: dark/light mode toggle button with `localStorage` persistence; redesigned sidebar UI. |
| **v0.2.0** | Fix: mount `elements-api` once, navigate via `pushState` + `hashchange`/`popstate` events. |
| **v0.1.9** | Fix: recreate `elements-api` on each click with hash pre-set. |
| **v0.1.4** | Feat: `document_path` + `memory_limit` config options for large apps. |
| **v0.1.0** | Initial release: tree grouping, Stoplight Elements integration. |

---

## License

MIT © Mohammed Mostafa. Built on top of the excellent [Scramble](https://github.com/dedoc/scramble) by Roman Lytvynenko.
