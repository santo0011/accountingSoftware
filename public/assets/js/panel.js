/* Portal / admin shell behaviour. */
(function () {
    'use strict';

    const body = document.body;

    // Sidebar: collapse on desktop (remembered), slide-in on mobile.
    try {
        if (localStorage.getItem('sidebar-collapsed') === '1' && window.innerWidth >= 992) body.classList.add('sidebar-collapsed');
    } catch (e) { /* storage unavailable */ }

    document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => btn.addEventListener('click', () => {
        if (window.innerWidth < 992) {
            body.classList.toggle('sidebar-open');
        } else {
            body.classList.toggle('sidebar-collapsed');
            try { localStorage.setItem('sidebar-collapsed', body.classList.contains('sidebar-collapsed') ? '1' : '0'); } catch (e) { /* ignore */ }
        }
    }));
    document.querySelector('.sidebar-backdrop')?.addEventListener('click', () => body.classList.remove('sidebar-open'));

    // Confirm before destructive actions: <form data-confirm="Are you sure?">
    document.addEventListener('submit', (e) => {
        const msg = e.target.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) e.preventDefault();
    });

    // Prevent double submits.
    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;
        const btn = e.target.querySelector('button[type=submit]:not([data-no-lock])');
        if (btn) setTimeout(() => { btn.disabled = true; btn.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-1"></span>'); }, 0);
    });

    // File inputs inside .upload-box show the chosen file name.
    document.addEventListener('change', (e) => {
        const input = e.target;
        if (input.type !== 'file') return;
        const box = input.closest('.upload-box');
        if (!box) return;
        const label = box.querySelector('[data-file-name]');
        const file = input.files[0];
        box.classList.toggle('has-file', !!file);
        if (label) label.textContent = file ? file.name + ' (' + Math.round(file.size / 1024) + ' KB)' : label.dataset.empty || 'No file chosen';
        if (file && file.size > 5 * 1024 * 1024) {
            alert('This file is larger than 5 MB. Please choose a smaller file.');
            input.value = '';
            box.classList.remove('has-file');
            if (label) label.textContent = label.dataset.empty || 'No file chosen';
        }
    });

    // Multi-step wizard: <div data-wizard> with .wizard-pane children and [data-step] indicators.
    document.querySelectorAll('[data-wizard]').forEach((wizard) => {
        const panes = [...wizard.querySelectorAll('.wizard-pane')];
        const steps = [...wizard.querySelectorAll('.wizard-steps .ws')];
        let current = 0;

        const show = (i) => {
            panes.forEach((p, n) => p.classList.toggle('active', n === i));
            steps.forEach((s, n) => { s.classList.toggle('active', n === i); s.classList.toggle('done', n < i); });
            current = i;
            if (i === panes.length - 1) buildReview();
            wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        const validPane = (pane) => {
            const fields = [...pane.querySelectorAll('input, select, textarea')];
            for (const f of fields) {
                if (!f.checkValidity()) { f.reportValidity(); return false; }
            }
            return true;
        };

        const buildReview = () => {
            const target = wizard.querySelector('[data-review]');
            if (!target) return;
            const rows = [];
            wizard.querySelectorAll('[data-review-label]').forEach((f) => {
                let value = f.type === 'file' ? (f.files[0]?.name || '— not uploaded (you can upload later)') : (f.tagName === 'SELECT' ? f.options[f.selectedIndex]?.text : f.value);
                if (!value) value = '—';
                const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                rows.push('<dt>' + esc(f.dataset.reviewLabel) + '</dt><dd>' + esc(value) + '</dd>');
            });
            target.innerHTML = rows.join('');
        };

        wizard.querySelectorAll('[data-next]').forEach((b) => b.addEventListener('click', () => { if (validPane(panes[current])) show(Math.min(current + 1, panes.length - 1)); }));
        wizard.querySelectorAll('[data-prev]').forEach((b) => b.addEventListener('click', () => show(Math.max(current - 1, 0))));

        // If the server returned validation errors, open the first pane that contains one.
        const errorPane = panes.findIndex((p) => p.querySelector('.is-invalid'));
        show(errorPane >= 0 ? errorPane : 0);
    });

    // Collapsible sidebar groups (admin), accordion style:
    // on page load only the group holding the current page is open,
    // and opening a group closes the others.
    const navGroups = document.querySelector('[data-nav-groups]');
    if (navGroups) {
        const groups = [...navGroups.querySelectorAll('[data-nav-group]')].filter((g) => !g.classList.contains('pinned'));

        const setOpen = (g, open) => {
            g.classList.toggle('open', open);
            g.querySelector('.nav-group-toggle').setAttribute('aria-expanded', open ? 'true' : 'false');
        };
        const syncAllButton = () => {
            const btn = navGroups.querySelector('[data-nav-toggle-all] i');
            const anyOpen = groups.some((g) => g.classList.contains('open'));
            if (btn) btn.className = 'bi ' + (anyOpen ? 'bi-arrows-collapse' : 'bi-arrows-expand');
        };

        // First paint: only the current page's group is open (no animation).
        navGroups.classList.add('no-anim');
        groups.forEach((g) => setOpen(g, g.classList.contains('has-active')));
        requestAnimationFrame(() => requestAnimationFrame(() => navGroups.classList.remove('no-anim')));
        syncAllButton();

        groups.forEach((g) => g.querySelector('.nav-group-toggle').addEventListener('click', () => {
            if (body.classList.contains('sidebar-collapsed')) return;
            const willOpen = !g.classList.contains('open');
            groups.forEach((other) => setOpen(other, other === g ? willOpen : false));
            syncAllButton();
        }));

        // Toolbar button: collapse everything, or re-open the current page's group.
        navGroups.querySelector('[data-nav-toggle-all]')?.addEventListener('click', () => {
            const anyOpen = groups.some((g) => g.classList.contains('open'));
            groups.forEach((g) => setOpen(g, !anyOpen && g.classList.contains('has-active')));
            syncAllButton();
        });

        // In icon-only mode, show the page name as a tooltip on hover.
        navGroups.querySelectorAll('.nav-group-inner a').forEach((a) => {
            const tip = () => (window.bootstrap ? bootstrap.Tooltip.getOrCreateInstance(a, { trigger: 'manual', placement: 'right' }) : null);
            a.addEventListener('mouseenter', () => { if (body.classList.contains('sidebar-collapsed')) tip()?.show(); });
            a.addEventListener('mouseleave', () => tip()?.hide());
            a.addEventListener('click', () => tip()?.hide());
        });

        // Keep the active link in view when the page loads.
        if (window.innerWidth >= 992) {
            const active = navGroups.querySelector('a.active');
            if (active) navGroups.scrollTop = Math.max(0, active.offsetTop - navGroups.clientHeight / 2);
        }
    }

    // Ctrl/Cmd + K focuses the global search.
    const globalSearch = document.getElementById('globalSearch');
    if (globalSearch) {
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); globalSearch.focus(); globalSearch.select(); }
        });
    }

    // Bootstrap tooltips.
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));

    // Keep the active tab after reload (e.g. after posting a form in a tab).
    const hash = window.location.hash;
    if (hash) {
        const trigger = document.querySelector('[data-bs-toggle="tab"][data-bs-target="' + hash + '"]');
        if (trigger) bootstrap.Tab.getOrCreateInstance(trigger).show();
    }
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach((t) => t.addEventListener('shown.bs.tab', () => history.replaceState(null, '', t.dataset.bsTarget)));
})();
