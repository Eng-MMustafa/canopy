@php
    /** @var array<string, mixed> $branding */
    $title  = $branding['title'] ?? 'API Documentation';
    $logo   = $branding['logo']  ?? null;
    $accent = $branding['accent'] ?? '#6366f1';
    $theme  = $branding['theme']  ?? 'system';
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
        /* ── Tokens ─────────────────────────────────────────── */
        :root {
            --accent:        {{ $accent }};
            --accent-soft:   color-mix(in srgb, var(--accent) 14%, transparent);
            --accent-border: color-mix(in srgb, var(--accent) 30%, transparent);
            --bg:            #f8f9fc;
            --surface:       #ffffff;
            --border:        #e4e6f0;
            --text:          #111827;
            --text-2:        #6b7280;
            --hover:         #f1f2f8;
            --radius:        10px;
            --sidebar-w:     300px;
        }
        html[data-theme="dark"] {
            --bg:      #0d0f18;
            --surface: #13161f;
            --border:  #1e2235;
            --text:    #e8eaf6;
            --text-2:  #8b93b4;
            --hover:   #1a1e2e;
        }
        /* ── Reset ──────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            overflow: hidden;
            transition: background .25s, color .25s;
        }
        /* ── Sidebar shell ──────────────────────────────────── */
        #canopy-sidebar {
            width: var(--sidebar-w);
            min-width: var(--sidebar-w);
            height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            transition: background .25s, border-color .25s;
        }
        /* ── Header ─────────────────────────────────────────── */
        .c-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 16px 16px 0;
        }
        .c-logo { height: 28px; width: auto; }
        .c-title {
            flex: 1;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -.01em;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* dark / light toggle */
        .c-theme-btn {
            width: 32px; height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--hover);
            color: var(--text-2);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: background .2s, border-color .2s, color .2s;
        }
        .c-theme-btn:hover { background: var(--accent-soft); border-color: var(--accent-border); color: var(--accent); }
        .c-theme-btn svg { width: 16px; height: 16px; }
        .icon-sun  { display: none; }
        .icon-moon { display: block; }
        html[data-theme="dark"] .icon-sun  { display: block; }
        html[data-theme="dark"] .icon-moon { display: none; }
        /* ── Search ─────────────────────────────────────────── */
        .c-search-wrap { padding: 12px 16px 8px; }
        .c-search {
            width: 100%;
            padding: 8px 12px 8px 34px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text);
            font-size: 13px;
            outline: none;
            transition: border-color .2s, background .25s;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%236b7280' stroke-width='2' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 10px center;
            background-size: 15px;
        }
        .c-search:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
        /* ── Toolbar ────────────────────────────────────────── */
        .c-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 16px 6px;
            font-size: 11px;
            color: var(--text-2);
        }
        .c-toolbar button {
            background: none; border: none;
            color: var(--accent); cursor: pointer;
            font-size: 11px; padding: 2px 5px;
            border-radius: 5px;
            transition: background .15s;
        }
        .c-toolbar button:hover { background: var(--accent-soft); }
        /* ── Tree ───────────────────────────────────────────── */
        #canopy-tree { flex: 1; overflow-y: auto; padding: 2px 8px 24px; }
        #canopy-tree::-webkit-scrollbar { width: 4px; }
        #canopy-tree::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
        .c-group-label {
            display: flex; align-items: center; gap: 7px;
            padding: 7px 8px 5px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--text-2);
            user-select: none;
            transition: background .15s;
        }
        .c-group-label:hover { background: var(--hover); color: var(--text); }
        .c-caret {
            width: 13px; height: 13px; flex-shrink: 0;
            transition: transform .15s ease; opacity: .5;
        }
        .c-node.collapsed > .c-group-label .c-caret { transform: rotate(-90deg); }
        .c-node.collapsed > .c-children { display: none; }
        .c-children { margin-left: 10px; border-left: 1px solid var(--border); padding-left: 6px; margin-bottom: 2px; }
        .c-route {
            display: flex; align-items: center; gap: 7px;
            padding: 5px 8px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 12.5px;
            color: var(--text);
            text-decoration: none;
            user-select: none;
            transition: background .12s;
        }
        .c-route:hover { background: var(--hover); }
        .c-route.active {
            background: var(--accent-soft);
            color: var(--accent);
            font-weight: 500;
        }
        .c-label { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .c-count {
            font-size: 10px; color: var(--text-2);
            background: var(--hover);
            padding: 1px 6px; border-radius: 10px;
            margin-left: auto;
        }
        /* ── Method badges ──────────────────────────────────── */
        .c-method {
            font-size: 9px; font-weight: 700; text-transform: uppercase;
            padding: 2px 5px; border-radius: 4px; flex-shrink: 0;
            letter-spacing: .04em; min-width: 34px; text-align: center;
        }
        .m-get    { background: #dbeafe; color: #1d4ed8; }
        .m-post   { background: #dcfce7; color: #15803d; }
        .m-put    { background: #fef3c7; color: #92400e; }
        .m-patch  { background: #fde68a; color: #78350f; }
        .m-delete { background: #fee2e2; color: #991b1b; }
        html[data-theme="dark"] .m-get    { background: #1e3a5f; color: #93c5fd; }
        html[data-theme="dark"] .m-post   { background: #14532d; color: #86efac; }
        html[data-theme="dark"] .m-put    { background: #451a03; color: #fcd34d; }
        html[data-theme="dark"] .m-patch  { background: #3b1a00; color: #fde68a; }
        html[data-theme="dark"] .m-delete { background: #450a0a; color: #fca5a5; }
        /* ── Empty state ────────────────────────────────────── */
        .c-empty { padding: 24px 16px; color: var(--text-2); font-size: 13px; text-align: center; }
        /* ── Content area ───────────────────────────────────── */
        #canopy-content { flex: 1; height: 100vh; overflow: hidden; }
        #canopy-mount   { height: 100%; display: flex; }
        #canopy-mount elements-api { flex: 1; display: block; min-width: 0; }
        /* ── Hide Stoplight's internal sidebar (all known selectors) ── */
        /* The sidebar is the FIRST direct child of the root flex container */
        #canopy-mount .sl-flex.sl-overflow-y-auto.sl-flex-col { display: none !important; }
        #canopy-mount .sl-flex > .sl-flex.sl-overflow-y-auto   { display: none !important; }
        /* Stoplight v8 sidebar layout wrappers */
        #canopy-mount [class*="TableOfContents"]  { display: none !important; }
        #canopy-mount [data-testid="table-of-contents"] { display: none !important; }
        #canopy-mount aside { display: none !important; }
        /* Make the content panel fill remaining space */
        #canopy-mount .sl-flex.sl-flex-1.sl-overflow-y-auto:not([class*="TableOfContents"]) { flex: 1 !important; max-width: 100% !important; }

        /* ── Stoplight dark-mode code block overrides ─────────────────── */
        html[data-theme="dark"] #canopy-mount .sl-code-viewer,
        html[data-theme="dark"] #canopy-mount [class*="CodeViewer"],
        html[data-theme="dark"] #canopy-mount pre {
            background: #0f172a !important;
            border: 1px solid #1e293b !important;
            border-radius: 8px !important;
        }
        html[data-theme="dark"] #canopy-mount .sl-code-viewer code,
        html[data-theme="dark"] #canopy-mount pre code {
            color: #e2e8f0 !important;
        }
        /* String values — soft green */
        html[data-theme="dark"] #canopy-mount .sl-code-viewer .token.string,
        html[data-theme="dark"] #canopy-mount .sl-code-viewer .hljs-string { color: #86efac !important; }
        /* Keys / properties — soft blue */
        html[data-theme="dark"] #canopy-mount .sl-code-viewer .token.property,
        html[data-theme="dark"] #canopy-mount .sl-code-viewer .hljs-attr    { color: #93c5fd !important; }
        /* Numbers — soft orange */
        html[data-theme="dark"] #canopy-mount .sl-code-viewer .token.number,
        html[data-theme="dark"] #canopy-mount .sl-code-viewer .hljs-number  { color: #fdba74 !important; }
        /* Keywords (true/false/null) — soft purple */
        html[data-theme="dark"] #canopy-mount .sl-code-viewer .token.keyword,
        html[data-theme="dark"] #canopy-mount .sl-code-viewer .hljs-literal { color: #c4b5fd !important; }
        /* cURL --flags — accent color */
        html[data-theme="dark"] #canopy-mount .sl-code-viewer .hljs-symbol  { color: var(--accent) !important; }
        /* Stoplight panel/card backgrounds in dark mode */
        html[data-theme="dark"] #canopy-mount .sl-panel,
        html[data-theme="dark"] #canopy-mount [class*="sl-panel"],
        html[data-theme="dark"] #canopy-mount .sl-bg-canvas-100 { background: #1e293b !important; }
        html[data-theme="dark"] #canopy-mount .sl-bg-canvas-200 { background: #0f172a !important; }
    </style>
</head>
<body>
    <aside id="canopy-sidebar">
        <div class="c-header">
            @if($logo)<img class="c-logo" src="{{ $logo }}" alt="logo">@endif
            <span class="c-title">{{ $title }}</span>
            <button class="c-theme-btn" id="canopy-theme" title="Toggle theme" aria-label="Toggle dark/light mode">
                <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/>
                </svg>
                <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <path stroke-linecap="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
            </button>
        </div>
        <div class="c-search-wrap">
            <input id="canopy-search" class="c-search" type="search" placeholder="Search endpoints…" autocomplete="off">
        </div>
        <div class="c-toolbar">
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
            const THEME_KEY = 'canopy.theme';

            // ── Theme toggle ─────────────────────────────────
            const html      = document.documentElement;
            const themeBtn  = document.getElementById('canopy-theme');
            const savedTheme = localStorage.getItem(THEME_KEY);
            if (savedTheme) html.setAttribute('data-theme', savedTheme);
            themeBtn.addEventListener('click', () => {
                const isDark = html.getAttribute('data-theme') === 'dark';
                const next   = isDark ? 'light' : 'dark';
                html.setAttribute('data-theme', next);
                localStorage.setItem(THEME_KEY, next);
                // Sync Stoplight's internal theme
                const stEl = document.querySelector('elements-api');
                if (stEl) stEl.setAttribute('theme', next);
            });

            // Sync Stoplight's own theme with Canopy's theme
            const getStoplightTheme = () =>
                html.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';

            // Mount Stoplight once — sidebar layout, hash router
            const el = document.createElement('elements-api');
            el.setAttribute('layout', 'sidebar');
            el.setAttribute('router', 'hash');
            el.setAttribute('hideExport', 'true');
            el.setAttribute('theme', getStoplightTheme());
            mountEl.appendChild(el);
            el.apiDescriptionDocument = spec;

            // Hide Stoplight's internal sidebar via JS after it renders
            // (CSS alone can't reliably target the dynamic classes Stoplight generates)
            const hideStoplightSidebar = () => {
                // Find the sidebar: it's the first flex child of the root sl-elements-api container
                // that contains navigation links (a[href] with /operations/)
                const root = mountEl.querySelector('elements-api');
                if (!root) return false;
                // Try common selectors for the sidebar panel
                const candidates = root.querySelectorAll(
                    'aside, nav, [class*="TableOfContents"], [data-testid="table-of-contents"]'
                );
                // Also try: first direct child of the main flex wrapper that has overflow-y scroll
                const flexWrap = root.querySelector('.sl-flex');
                if (flexWrap) {
                    const firstChild = flexWrap.children[0];
                    if (firstChild && firstChild !== flexWrap.children[flexWrap.children.length - 1]) {
                        firstChild.style.setProperty('display', 'none', 'important');
                        // Make content panel fill width
                        const lastChild = flexWrap.children[flexWrap.children.length - 1];
                        if (lastChild) lastChild.style.setProperty('flex', '1', 'important');
                        return true;
                    }
                }
                candidates.forEach(n => n.style.setProperty('display', 'none', 'important'));
                return candidates.length > 0;
            };
            // Poll until sidebar appears then hide it
            let hideAttempts = 0;
            const hideInterval = setInterval(() => {
                if (hideStoplightSidebar() || ++hideAttempts > 60) clearInterval(hideInterval);
            }, 100);

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
                return `<a class="c-route canopy-route" href="${hashFor(route)}" data-path="${esc(pathFor(route))}" title="${esc(route.path)}">
                    <span class="c-method m-${esc(route.method)}">${esc(route.method)}</span>
                    <span class="c-label">${esc(label)}</span>
                </a>`;
            };

            const renderNode = (node, term) => {
                const childrenHtml = (node.children || []).map(c => renderNode(c, term)).join('');
                const routesHtml   = (node.routes   || []).map(r => renderRoute(r, term)).join('');
                if (term && !childrenHtml && !routesHtml && !node.name.toLowerCase().includes(term)) return '';
                const isCollapsed = !term && collapsed.has(node.id);
                const total = (node.routes || []).length;
                return `<div class="c-node canopy-group ${isCollapsed ? 'collapsed' : ''}" data-id="${esc(node.id)}">
                    <div class="c-group-label" role="button" tabindex="0">
                        <svg class="c-caret" viewBox="0 0 16 16" fill="currentColor"><path d="M6 4l4 4-4 4z"/></svg>
                        <span class="c-label">${esc(node.name)}</span>
                        ${total ? `<span class="c-count">${total}</span>` : ''}
                    </div>
                    <div class="c-children">${childrenHtml}${routesHtml}</div>
                </div>`;
            };

            const render = (term = '') => {
                routeCount = 0;
                const htm = tree.map(n => renderNode(n, term)).join('');
                treeRoot.innerHTML = htm || '<div class="c-empty">No endpoints found.</div>';
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
                const groupRow = e.target.closest('.canopy-group > .c-group-label');
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
                if ((e.key === 'Enter' || e.key === ' ') && e.target.classList.contains('c-group-label')) {
                    e.preventDefault(); e.target.click();
                }
            });

            search.addEventListener('input', (e) => render(e.target.value.trim().toLowerCase()));
            document.getElementById('canopy-expand').addEventListener('click', () => { collapsed.clear(); persist(); render(search.value.trim().toLowerCase()); });
            document.getElementById('canopy-collapse').addEventListener('click', () => {
                const collectIds = (nodes) => nodes.forEach(n => { collapsed.add(n.id); collectIds(n.children || []); });
                collectIds(tree); persist(); render(search.value.trim().toLowerCase());
            });

            render();
        })();
    </script>
</body>
</html>
