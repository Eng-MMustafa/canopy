@php
    /** @var array<string, mixed> $branding */
    $title = $branding['title'] ?? 'API Documentation';
    $logo = $branding['logo'] ?? null;
    $accent = $branding['accent'] ?? '#6366f1';
    $theme = $branding['theme'] ?? 'system';
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://unpkg.com/@stoplight/elements@8.5.2/styles.min.css">
    <script src="https://unpkg.com/@stoplight/elements@8.5.2/web-components.min.js"></script>
    <style>
        :root {
            --canopy-accent: {{ $accent }};
            --canopy-bg: #ffffff;
            --canopy-sidebar-bg: #fbfbfd;
            --canopy-border: #e6e6ef;
            --canopy-text: #1f2333;
            --canopy-muted: #6b7088;
            --canopy-hover: #f0f0f6;
            --canopy-active: color-mix(in srgb, var(--canopy-accent) 12%, transparent);
        }
        html[data-theme="dark"], html[data-theme="system"] {
            color-scheme: light dark;
        }
        @media (prefers-color-scheme: dark) {
            html[data-theme="system"] {
                --canopy-bg: #0f1117;
                --canopy-sidebar-bg: #14161f;
                --canopy-border: #262a38;
                --canopy-text: #e6e8f0;
                --canopy-muted: #9aa0b5;
                --canopy-hover: #1d2030;
            }
        }
        html[data-theme="dark"] {
            --canopy-bg: #0f1117;
            --canopy-sidebar-bg: #14161f;
            --canopy-border: #262a38;
            --canopy-text: #e6e8f0;
            --canopy-muted: #9aa0b5;
            --canopy-hover: #1d2030;
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color: var(--canopy-text);
            background: var(--canopy-bg);
            display: flex;
            overflow: hidden;
        }
        #canopy-sidebar {
            width: 320px;
            min-width: 320px;
            height: 100vh;
            background: var(--canopy-sidebar-bg);
            border-right: 1px solid var(--canopy-border);
            display: flex;
            flex-direction: column;
        }
        .canopy-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 18px 12px;
            font-weight: 700;
            font-size: 16px;
        }
        .canopy-brand img { height: 26px; width: auto; }
        .canopy-search-wrap { padding: 4px 14px 12px; }
        .canopy-search {
            width: 100%;
            padding: 9px 12px;
            border-radius: 9px;
            border: 1px solid var(--canopy-border);
            background: var(--canopy-bg);
            color: var(--canopy-text);
            font-size: 13px;
            outline: none;
        }
        .canopy-search:focus { border-color: var(--canopy-accent); }
        .canopy-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 18px 8px;
            font-size: 11px;
            color: var(--canopy-muted);
        }
        .canopy-tools button {
            background: none;
            border: none;
            color: var(--canopy-accent);
            cursor: pointer;
            font-size: 11px;
            padding: 2px 4px;
        }
        #canopy-tree {
            flex: 1;
            overflow-y: auto;
            padding: 0 8px 24px;
        }
        .canopy-group > .canopy-row {
            font-weight: 600;
            color: var(--canopy-text);
        }
        .canopy-row {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 6px 8px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            color: var(--canopy-text);
            user-select: none;
            text-decoration: none;
        }
        .canopy-row:hover { background: var(--canopy-hover); }
        .canopy-row.active { background: var(--canopy-active); color: var(--canopy-accent); }
        .canopy-caret {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
            transition: transform .15s ease;
            opacity: .55;
        }
        .canopy-node.collapsed > .canopy-row .canopy-caret { transform: rotate(-90deg); }
        .canopy-node.collapsed > .canopy-children { display: none; }
        .canopy-children { margin-left: 12px; border-left: 1px solid var(--canopy-border); padding-left: 4px; }
        .canopy-label { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .canopy-count { font-size: 10px; color: var(--canopy-muted); }
        .canopy-method {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 5px;
            flex-shrink: 0;
            letter-spacing: .03em;
        }
        .m-get { background: #e0f0ff; color: #0369a1; }
        .m-post { background: #dcfce7; color: #15803d; }
        .m-put, .m-patch { background: #fef3c7; color: #b45309; }
        .m-delete { background: #fee2e2; color: #b91c1c; }
        html[data-theme="dark"] .m-get,    html[data-theme="system"] .m-get    { background: #0b3a5e; color: #7dd3fc; }
        html[data-theme="dark"] .m-post,   html[data-theme="system"] .m-post   { background: #052e16; color: #86efac; }
        html[data-theme="dark"] .m-put,    html[data-theme="system"] .m-put    { background: #2d1a00; color: #fcd34d; }
        html[data-theme="dark"] .m-patch,  html[data-theme="system"] .m-patch  { background: #2d1a00; color: #fcd34d; }
        html[data-theme="dark"] .m-delete, html[data-theme="system"] .m-delete { background: #3b0a0a; color: #fca5a5; }
        .canopy-empty { padding: 24px 18px; color: var(--canopy-muted); font-size: 13px; }
        #canopy-content { flex: 1; height: 100vh; overflow: hidden; }
        #canopy-mount { height: 100%; display: flex; }
        #canopy-mount elements-api { flex: 1; display: block; min-width: 0; }
        /* Stoplight sidebar hidden — Canopy provides its own */
        .sl-overflow-y-auto.sl-flex-col.sl-flex-1 > .sl-flex.sl-overflow-y-auto { display: none !important; }
    </style>
</head>
<body>
    <aside id="canopy-sidebar">
        <div class="canopy-brand">
            @if($logo)<img src="{{ $logo }}" alt="logo">@endif
            <span>{{ $title }}</span>
        </div>
        <div class="canopy-search-wrap">
            <input id="canopy-search" class="canopy-search" type="search" placeholder="Search endpoints…" autocomplete="off">
        </div>
        <div class="canopy-tools">
            <span id="canopy-count"></span>
            <span>
                <button type="button" id="canopy-expand">Expand all</button>
                <button type="button" id="canopy-collapse">Collapse all</button>
            </span>
        </div>
        <nav id="canopy-tree" aria-label="API navigation"></nav>
    </aside>

    <main id="canopy-content">
        <div id="canopy-mount"></div>
    </main>

    <script>
        (() => {
            const tree      = @json($tree, JSON_UNESCAPED_SLASHES);
            const spec      = @json($spec, JSON_UNESCAPED_SLASHES);
            const treeRoot  = document.getElementById('canopy-tree');
            const search    = document.getElementById('canopy-search');
            const countEl   = document.getElementById('canopy-count');
            const mountEl   = document.getElementById('canopy-mount');
            const contentEl = document.getElementById('canopy-content');
            const STORAGE   = 'canopy.collapsed';

            // Mount Stoplight once — sidebar layout, hash router
            const el = document.createElement('elements-api');
            el.setAttribute('layout', 'sidebar');
            el.setAttribute('router', 'hash');
            el.setAttribute('hideExport', 'true');
            mountEl.appendChild(el);
            el.apiDescriptionDocument = spec;

            // Navigate to a path: update hash then fire events so React Router picks it up
            const navigateTo = (path) => {
                const newHash = '#' + path;
                const oldURL  = location.href;
                const newURL  = location.href.replace(/#.*$/, '') + newHash;
                history.pushState(null, '', newHash);
                // Fire both events — Stoplight's internal router listens to one of them
                window.dispatchEvent(new PopStateEvent('popstate', { state: null }));
                window.dispatchEvent(new HashChangeEvent('hashchange', { oldURL, newURL }));
            };

            let collapsed = new Set();
            try { collapsed = new Set(JSON.parse(localStorage.getItem(STORAGE) || '[]')); } catch (e) {}
            const persist = () => { try { localStorage.setItem(STORAGE, JSON.stringify([...collapsed])); } catch (e) {} };

            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

            let routeCount = 0;

            const pathFor = (route) => {
                if (route.operationId) return '/operations/' + route.operationId;
                const p = '~1' + String(route.path).replace(/^\//, '').replace(/\//g, '~1');
                return '/paths/' + p + '/' + route.method;
            };
            const hashFor = (route) => '#' + pathFor(route);

            const renderRoute = (route, term) => {
                const label = route.name || (route.method.toUpperCase() + ' ' + route.path);
                if (term && !label.toLowerCase().includes(term) && !String(route.path).toLowerCase().includes(term)) return '';
                routeCount++;
                return `<a class="canopy-row canopy-route" href="${hashFor(route)}" data-path="${esc(pathFor(route))}" title="${esc(route.path)}">
                    <span class="canopy-method m-${esc(route.method)}">${esc(route.method)}</span>
                    <span class="canopy-label">${esc(label)}</span>
                </a>`;
            };

            const renderNode = (node, term) => {
                const childrenHtml = (node.children || []).map(c => renderNode(c, term)).join('');
                const routesHtml = (node.routes || []).map(r => renderRoute(r, term)).join('');
                if (term && !childrenHtml && !routesHtml && !node.name.toLowerCase().includes(term)) return '';
                const isCollapsed = !term && collapsed.has(node.id);
                const total = (node.routes || []).length;
                return `<div class="canopy-node canopy-group ${isCollapsed ? 'collapsed' : ''}" data-id="${esc(node.id)}">
                    <div class="canopy-row" role="button" tabindex="0">
                        <svg class="canopy-caret" viewBox="0 0 16 16" fill="currentColor"><path d="M6 4l4 4-4 4z"/></svg>
                        <span class="canopy-label">${esc(node.name)}</span>
                        ${total ? `<span class="canopy-count">${total}</span>` : ''}
                    </div>
                    <div class="canopy-children">${childrenHtml}${routesHtml}</div>
                </div>`;
            };

            const render = (term = '') => {
                routeCount = 0;
                const html = tree.map(n => renderNode(n, term)).join('');
                treeRoot.innerHTML = html || '<div class="canopy-empty">No endpoints found.</div>';
                countEl.textContent = routeCount + ' endpoint' + (routeCount === 1 ? '' : 's');
                highlight();
            };

            const highlight = (activePath) => {
                const hash = activePath ? '#' + activePath : window.location.hash;
                treeRoot.querySelectorAll('.canopy-route').forEach(a => {
                    a.classList.toggle('active', a.getAttribute('href') === hash);
                });
            };

            treeRoot.addEventListener('click', (e) => {
                const groupRow = e.target.closest('.canopy-group > .canopy-row');
                if (groupRow) {
                    const node = groupRow.parentElement;
                    node.classList.toggle('collapsed');
                    const id = node.dataset.id;
                    node.classList.contains('collapsed') ? collapsed.add(id) : collapsed.delete(id);
                    persist();
                    return;
                }
                const routeLink = e.target.closest('.canopy-route');
                if (routeLink) {
                    e.preventDefault();
                    const path = routeLink.dataset.path;
                    highlight(path);
                    navigateTo(path);
                }
            });

            treeRoot.addEventListener('keydown', (e) => {
                if ((e.key === 'Enter' || e.key === ' ') && e.target.classList.contains('canopy-row')) {
                    e.preventDefault();
                    e.target.click();
                }
            });

            search.addEventListener('input', (e) => render(e.target.value.trim().toLowerCase()));
            document.getElementById('canopy-expand').addEventListener('click', () => { collapsed.clear(); persist(); render(search.value.trim().toLowerCase()); });
            document.getElementById('canopy-collapse').addEventListener('click', () => {
                const collectIds = (nodes) => nodes.forEach(n => { collapsed.add(n.id); collectIds(n.children || []); });
                collectIds(tree); persist(); render(search.value.trim().toLowerCase());
            });
            // no hashchange needed — we control navigation via mountAt()

            render();
        })();
    </script>
</body>
</html>
