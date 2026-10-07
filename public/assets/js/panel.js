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

    // Confirm before destructive actions: <form data-confirm="Delete this service?"> opens #confirmModal
    // (falls back to the browser's confirm box if the dialog is missing).
    const confirmEl = document.getElementById('confirmModal');
    const confirmModal = confirmEl && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(confirmEl) : null;
    let confirmTarget = null;

    const DANGER = /^(delete|remove|deactivate|cancel|disable|revoke|reject|archive)\b/i;
    const ICONS = { delete: 'bi-trash3', remove: 'bi-x-circle', deactivate: 'bi-person-slash', cancel: 'bi-x-octagon', disable: 'bi-slash-circle', mark: 'bi-check2-circle', confirm: 'bi-check2-circle', create: 'bi-person-plus', activate: 'bi-person-check' };

    const openConfirm = (form, submitter) => {
        const msg = form.dataset.confirm;
        const verb = (msg.match(/^\w+/) || ['confirm'])[0].toLowerCase();
        const danger = form.dataset.confirmVariant ? form.dataset.confirmVariant === 'danger' : DANGER.test(msg);
        const icon = ICONS[verb] || (danger ? 'bi-exclamation-triangle' : 'bi-question-circle');
        const isDelete = (form.querySelector('input[name=_method]')?.value || '').toUpperCase() === 'DELETE';

        // Name of the record: explicit, else the bold name in the table row / card the button sits in.
        const row = form.closest('tr, .card, .list-group-item');
        const item = form.dataset.confirmItem || row?.querySelector('.fw-semibold, .text-navy, strong')?.textContent.trim() || '';

        confirmEl.classList.toggle('is-danger', danger);
        confirmEl.querySelector('[data-confirm-icon]').className = 'bi ' + icon;
        confirmEl.querySelector('[data-confirm-go-icon]').className = 'bi ' + icon;
        confirmEl.querySelector('[data-confirm-title]').textContent = msg;
        confirmEl.querySelector('[data-confirm-body]').textContent = form.dataset.confirmText
            || (isDelete ? 'This will permanently remove it. This action cannot be undone.' : danger ? 'Please make sure — this may not be reversible.' : 'Please confirm to continue.');
        confirmEl.querySelector('[data-confirm-go-label]').textContent = form.dataset.confirmButton || 'Yes, ' + verb;
        const box = confirmEl.querySelector('[data-confirm-item-box]');
        box.hidden = !item || msg.includes(item);
        confirmEl.querySelector('[data-confirm-item-name]').textContent = item;
        confirmEl.querySelector('[data-confirm-item-icon]').className = 'bi ' + (row?.querySelector('td .bi:not(.bi-star-fill)')?.className.match(/bi-[\w-]+/)?.[0] || 'bi-file-earmark-text');

        const go = confirmEl.querySelector('[data-confirm-go]');
        go.disabled = false;
        go.classList.remove('loading');
        confirmTarget = { form, submitter };
        confirmModal.show();
    };

    document.addEventListener('submit', (e) => {
        const form = e.target;
        const msg = form.getAttribute('data-confirm');
        if (!msg) return;
        if (form.dataset.confirmed) { delete form.dataset.confirmed; return; } // already confirmed in the dialog
        e.preventDefault();
        if (!confirmModal) { if (window.confirm(msg)) { form.dataset.confirmed = '1'; form.requestSubmit(e.submitter || undefined); } return; }
        openConfirm(form, e.submitter);
    });

    if (confirmModal) {
        const go = confirmEl.querySelector('[data-confirm-go]');
        go.addEventListener('click', () => {
            if (!confirmTarget) return;
            const { form, submitter } = confirmTarget;
            go.disabled = true;
            go.classList.add('loading');
            form.dataset.confirmed = '1';
            // requestSubmit runs the normal submit flow (button spinner, double-submit guard).
            form.requestSubmit(submitter && submitter.form === form ? submitter : undefined);
            confirmModal.hide();
        });
        confirmEl.addEventListener('shown.bs.modal', () => confirmEl.querySelector('.logout-cancel').focus());
        confirmEl.addEventListener('hidden.bs.modal', () => { confirmTarget = null; });
    }

    // Logout asks first: <form data-logout> opens #logoutModal; "Yes, log out" submits that form.
    const logoutModalEl = document.getElementById('logoutModal');
    if (logoutModalEl && window.bootstrap) {
        const logoutModal = bootstrap.Modal.getOrCreateInstance(logoutModalEl);
        const confirmBtn = logoutModalEl.querySelector('[data-logout-confirm]');
        let pendingForm = null;

        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!form.hasAttribute('data-logout') || form.dataset.logoutConfirmed) return;
            e.preventDefault();
            pendingForm = form;
            // Close an open user dropdown first so it doesn't sit on top of the dialog.
            document.querySelectorAll('.dropdown-menu.show').forEach((menu) => {
                const toggle = menu.parentElement.querySelector('[data-bs-toggle="dropdown"]');
                if (toggle) bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
            });
            logoutModal.show();
        }, true);

        confirmBtn.addEventListener('click', () => {
            if (!pendingForm) return;
            confirmBtn.classList.add('loading');
            confirmBtn.disabled = true;
            pendingForm.dataset.logoutConfirmed = '1';
            pendingForm.submit();
        });

        // Focus the safe choice when the dialog opens; reset if it is dismissed.
        logoutModalEl.addEventListener('shown.bs.modal', () => logoutModalEl.querySelector('.logout-cancel').focus());
        logoutModalEl.addEventListener('hidden.bs.modal', () => {
            if (confirmBtn.classList.contains('loading')) return;
            pendingForm = null;
        });
    }

    // Loading state + double-submit guard for every submit button (buttons with no type are submit buttons too).
    // Opt out with data-no-lock; set the wording with data-loading-text="Uploading…".
    const loadingText = (btn) => {
        if (btn.dataset.loadingText) return btn.dataset.loadingText;
        const label = btn.textContent.trim().toLowerCase();
        const verbs = [['save', 'Saving…'], ['update', 'Saving…'], ['delete', 'Deleting…'], ['remove', 'Removing…'], ['send', 'Sending…'],
            ['upload', 'Uploading…'], ['submit', 'Submitting…'], ['create', 'Creating…'], ['add', 'Adding…'], ['pay', 'Processing…'], ['apply', 'Submitting…']];
        const hit = verbs.find(([word]) => label.includes(word));
        return hit ? hit[1] : 'Please wait…';
    };

    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;
        const form = e.target;
        const btn = (e.submitter && e.submitter.tagName === 'BUTTON' && e.submitter)
            || form.querySelector('button:not([type=button]):not([type=reset])');
        if (!btn || btn.hasAttribute('data-no-lock') || btn.classList.contains('is-loading')) return;

        // Freeze the width so the button does not jump, then swap the label for a spinner.
        btn.style.width = btn.getBoundingClientRect().width + 'px';
        btn.style.setProperty('--btn-loader-color', getComputedStyle(btn).color);
        if (!btn.querySelector('.btn-loader')) {
            btn.insertAdjacentHTML('beforeend', '<span class="btn-loader" aria-hidden="true"><span class="btn-spinner"></span><span class="btn-loader-text"></span></span>');
        }
        btn.querySelector('.btn-loader-text').textContent = loadingText(btn);
        btn.setAttribute('aria-busy', 'true');
        requestAnimationFrame(() => btn.classList.add('is-loading'));
        // Disable after the browser has read the clicked button's name/value for the request.
        setTimeout(() => { btn.disabled = true; }, 0);
    });

    // Coming back with the browser's Back button can restore a frozen page; reset any loading buttons.
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        document.querySelectorAll('.is-loading').forEach((btn) => {
            btn.classList.remove('is-loading');
            btn.disabled = false;
            btn.removeAttribute('aria-busy');
            btn.style.width = '';
        });
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

    // Image upload fields (<x-form.image>): instant preview, drag & drop, type/size check, undo and "remove".
    document.querySelectorAll('[data-img-up]').forEach((box) => {
        const input = box.querySelector('[data-input]');
        const drop = box.querySelector('[data-drop]');
        const img = box.querySelector('[data-img]');
        const empty = box.querySelector('[data-empty]');
        const badge = box.querySelector('[data-badge]');
        const info = box.querySelector('[data-info]');
        const undo = box.querySelector('[data-undo]');
        const remove = box.querySelector('[data-remove]');
        const original = { src: img.getAttribute('src'), shown: !img.hidden, badge: badge.textContent, badgeShown: !badge.hidden };
        const maxKb = parseInt(box.dataset.maxKb || '0', 10);
        const allowed = (input.accept || '').split(',').map((s) => s.trim().toLowerCase()).filter(Boolean);
        let objectUrl = null;

        const show = (src, label, state) => {
            img.hidden = !src;
            if (src) img.src = src;
            empty.hidden = !!src;
            badge.hidden = !label;
            badge.textContent = label || '';
            box.classList.toggle('is-new', state === 'new');
            box.classList.toggle('is-removed', state === 'removed');
        };
        const setInfo = (text, isError) => {
            info.textContent = text;
            info.classList.toggle('is-error', !!isError);
        };
        const restore = () => {
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = null;
            show(original.shown ? original.src : null, original.badgeShown ? original.badge : null, null);
            setInfo(info.dataset.help);
            undo.hidden = true;
        };
        const okType = (file) => {
            const ext = '.' + file.name.split('.').pop().toLowerCase();
            if (!allowed.length) return file.type.startsWith('image/');
            return allowed.some((a) => a === ext || (a.endsWith('/*') ? file.type.startsWith(a.slice(0, -1)) : a === file.type));
        };
        const kb = (bytes) => (bytes >= 1024 * 1024 ? (bytes / 1024 / 1024).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB');

        input.addEventListener('change', () => {
            const file = input.files[0];
            restore();
            if (!file) return;
            const exts = allowed.filter((a) => a.startsWith('.')).map((a) => a.slice(1).toUpperCase());
            const problem = !okType(file) ? 'That file type is not allowed. Use ' + exts.join(', ') + '.'
                : (maxKb && file.size > maxKb * 1024) ? 'This image is ' + kb(file.size) + ' — the limit is ' + kb(maxKb * 1024) + '.'
                : null;
            if (problem) {
                input.value = '';
                setInfo(problem, true);
                box.classList.add('shake');
                setTimeout(() => box.classList.remove('shake'), 500);
                return;
            }
            if (remove) remove.checked = false;
            objectUrl = URL.createObjectURL(file);
            show(objectUrl, 'New — not saved yet', 'new');
            undo.hidden = false;
            setInfo(file.name + ' · ' + kb(file.size));
            img.onload = () => {
                if (img.src === objectUrl && img.naturalWidth) setInfo(file.name + ' · ' + img.naturalWidth + '×' + img.naturalHeight + ' px · ' + kb(file.size));
            };
        });

        const pick = () => input.click();
        drop.addEventListener('click', pick);
        box.querySelector('[data-pick]').addEventListener('click', pick);
        drop.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); pick(); } });
        undo.addEventListener('click', () => { input.value = ''; restore(); });

        ['dragenter', 'dragover'].forEach((t) => drop.addEventListener(t, (e) => { e.preventDefault(); box.classList.add('is-drag'); }));
        ['dragleave', 'dragend', 'drop'].forEach((t) => drop.addEventListener(t, () => box.classList.remove('is-drag')));
        drop.addEventListener('drop', (e) => {
            e.preventDefault();
            const file = e.dataTransfer.files[0];
            if (!file) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });

        remove?.addEventListener('change', () => {
            input.value = '';
            restore();
            if (remove.checked) show(box.dataset.default || null, box.dataset.default ? 'Default image — after saving' : 'Removed — after saving', 'removed');
        });
        if (remove?.checked) remove.dispatchEvent(new Event('change'));
    });

    // Quick date chips: <div class="quick-dates" data-for="f_due_date"><button type="button" data-days="1">Tomorrow</button></div>
    document.querySelectorAll('.quick-dates[data-for]').forEach((group) => {
        const input = document.getElementById(group.dataset.for);
        if (!input) return;
        const iso = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        const buttons = [...group.querySelectorAll('button[data-days]')];
        buttons.forEach((b) => {
            const d = new Date();
            d.setDate(d.getDate() + parseInt(b.dataset.days, 10));
            b.dataset.value = iso(d);
            b.addEventListener('click', () => {
                input.value = b.dataset.value;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
        const mark = () => buttons.forEach((b) => b.classList.toggle('active', b.dataset.value === input.value));
        input.addEventListener('change', mark);
        input.addEventListener('input', mark);
        mark();
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

    // Live filtering: typing in a .filter-bar search box (or changing a select/date) refreshes the page content
    // around the form without a reload, so focus stays in the box. Opt out with <form data-no-live>.
    document.querySelectorAll('form.filter-bar').forEach((form, index) => {
        if ((form.getAttribute('method') || 'get').toLowerCase() !== 'get' || form.hasAttribute('data-no-live')) return;
        let timer = null;
        let controller = null;
        let lastUrl = location.href;

        const buildUrl = () => {
            const params = new URLSearchParams(new FormData(form));
            [...params.keys()].forEach((k) => { if (params.get(k) === '') params.delete(k); });
            const qs = params.toString();
            return new URL((form.getAttribute('action') || location.pathname) + (qs ? '?' + qs : ''), location.href).href;
        };

        const swap = (fresh) => {
            const parent = form.parentNode;
            [...parent.children].forEach((el) => { if (el !== form) el.remove(); });
            const before = [];
            for (let el = fresh.previousElementSibling; el; el = el.previousElementSibling) before.unshift(el);
            const after = [];
            for (let el = fresh.nextElementSibling; el; el = el.nextElementSibling) after.push(el);
            form.before(...before.map((el) => document.importNode(el, true)));
            form.after(...after.map((el) => document.importNode(el, true)));
            parent.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => bootstrap.Tooltip.getOrCreateInstance(el));
        };

        const refresh = () => {
            clearTimeout(timer);
            const url = buildUrl();
            if (url === lastUrl) return;
            lastUrl = url;
            controller?.abort();
            controller = new AbortController();
            form.classList.add('is-searching');
            fetch(url, { signal: controller.signal, credentials: 'same-origin' })
                .then((r) => {
                    if (!r.ok || r.redirected) throw new Error('reload');
                    return r.text();
                })
                .then((html) => {
                    const fresh = new DOMParser().parseFromString(html, 'text/html').querySelectorAll('form.filter-bar')[index];
                    if (!fresh) throw new Error('reload');
                    swap(fresh);
                    history.replaceState(null, '', url);
                    form.classList.remove('is-searching');
                })
                .catch((err) => {
                    if (err.name === 'AbortError') return;
                    location.href = url; // fall back to a normal page load
                });
        };

        form.addEventListener('input', (e) => {
            if (!e.target.matches('input[type=search], input[type=text], input:not([type])')) return;
            clearTimeout(timer);
            timer = setTimeout(refresh, 400);
        });
        form.addEventListener('change', (e) => {
            if (e.target.matches('input[type=search], input[type=text], input:not([type])')) return; // handled on input
            refresh();
        });
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            lastUrl = null; // the Go/Filter button always refreshes
            refresh();
        });
    });

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
