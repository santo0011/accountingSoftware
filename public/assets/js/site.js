/* Public website behaviour — no framework, progressive enhancement only. */
(function () {
    'use strict';

    // Header shadow once the page scrolls.
    const header = document.querySelector('.site-header');
    if (header) {
        const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 8);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    // Desktop menus open on hover; clicking a top-level item goes to its page.
    const desktop = window.matchMedia('(min-width: 992px) and (hover: hover)');
    document.querySelectorAll('.site-header .navbar-nav > .dropdown').forEach((item) => {
        const toggle = item.querySelector('[data-bs-toggle="dropdown"]');
        let openTimer = null;
        let closeTimer = null;
        const dd = () => window.bootstrap && bootstrap.Dropdown.getOrCreateInstance(toggle);
        const openOthers = () => [...document.querySelectorAll('.site-header .navbar-nav > .dropdown > .show[data-bs-toggle]')].filter((t) => t !== toggle);

        item.addEventListener('mouseenter', () => {
            if (!desktop.matches) return;
            clearTimeout(closeTimer);
            const others = openOthers();
            // Hover intent: wait briefly before opening, but switch instantly if another menu is already open.
            openTimer = setTimeout(() => {
                others.forEach((t) => bootstrap.Dropdown.getOrCreateInstance(t).hide());
                dd()?.show();
            }, others.length ? 60 : 150);
        });
        item.addEventListener('mouseleave', () => {
            if (!desktop.matches) return;
            clearTimeout(openTimer);
            closeTimer = setTimeout(() => dd()?.hide(), 280);
        });
        toggle.addEventListener('click', (e) => {
            const href = toggle.getAttribute('href');
            if (desktop.matches && href && href !== '#') {
                e.preventDefault();
                e.stopPropagation();
                window.location = href;
            }
        });
    });

    // Statistics: count up from 0 when the band scrolls into view (keeps "+", "/5", commas, decimals).
    const statNums = document.querySelectorAll('.stat-item .num');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (statNums.length && 'IntersectionObserver' in window && !reduceMotion) {
        const animate = (el) => {
            const match = el.dataset.final.match(/^(\D*)([\d,]*\.?\d+)(.*)$/);
            if (!match) return;
            const [, prefix, raw, suffix] = match;
            const target = parseFloat(raw.replace(/,/g, ''));
            const decimals = raw.includes('.') ? raw.split('.')[1].length : 0;
            const grouped = raw.includes(',');
            const duration = 1800;
            const start = performance.now();
            const ease = (t) => 1 - Math.pow(1 - t, 4); // fast start, soft finish

            const frame = (now) => {
                const t = Math.min((now - start) / duration, 1);
                const value = target * ease(t);
                const text = grouped
                    ? Math.round(value).toLocaleString('en-IN')
                    : value.toFixed(decimals);
                el.textContent = prefix + text + suffix;
                if (t < 1) requestAnimationFrame(frame);
                else el.textContent = el.dataset.final;
            };
            requestAnimationFrame(frame);
        };

        statNums.forEach((el) => {
            el.dataset.final = el.textContent.trim();
            const m = el.dataset.final.match(/^(\D*)([\d,]*\.?\d+)(.*)$/);
            if (m) el.textContent = m[1] + (m[2].includes('.') ? (0).toFixed(m[2].split('.')[1].length) : '0') + m[3];
        });

        const band = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('in-view');
                entry.target.querySelectorAll('.stat-item .num').forEach((el, i) => setTimeout(() => animate(el), i * 150));
                obs.unobserve(entry.target);
            });
        }, { threshold: 0.35 });

        document.querySelectorAll('.stats-band').forEach((el) => { el.classList.add('will-animate'); band.observe(el); });
    } else {
        document.querySelectorAll('.stats-band').forEach((el) => el.classList.add('in-view'));
    }

    // Business journey carousel: native scroll-snap + arrows, dots, progress bar and gentle autoplay.
    document.querySelectorAll('[data-journey]').forEach((carousel) => {
        const track = carousel.querySelector('.journey-track');
        const cards = [...track.children];
        const section = carousel.closest('section') || document;
        const prev = section.querySelector('[data-journey-prev]');
        const next = section.querySelector('[data-journey-next]');
        const dotsBox = carousel.querySelector('.journey-dots');
        const bar = carousel.querySelector('.journey-progress span');
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const INTERVAL = 4500;
        let autoplay = null;
        let paused = false;
        let visible = false;

        const step = () => cards.length > 1 ? cards[1].offsetLeft - cards[0].offsetLeft : track.clientWidth;
        const maxScroll = () => track.scrollWidth - track.clientWidth;
        const stops = () => Math.max(1, Math.round(maxScroll() / step()) + 1);
        const current = () => Math.round(track.scrollLeft / step());
        const goTo = (i) => track.scrollTo({ left: Math.max(0, Math.min(i, stops() - 1)) * step(), behavior: reduce ? 'auto' : 'smooth' });

        const buildDots = () => {
            dotsBox.innerHTML = '';
            for (let i = 0; i < stops(); i++) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'journey-dot';
                dot.setAttribute('role', 'tab');
                dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                dot.addEventListener('click', () => { goTo(i); restart(); });
                dotsBox.appendChild(dot);
            }
            update();
        };

        const update = () => {
            const i = current();
            dotsBox.querySelectorAll('.journey-dot').forEach((d, n) => {
                d.classList.toggle('active', n === i);
                d.setAttribute('aria-selected', n === i ? 'true' : 'false');
            });
            const max = maxScroll();
            if (bar) bar.style.width = (max > 0 ? Math.min(100, (track.scrollLeft / max) * 100) : 100) + '%';
            if (prev) prev.disabled = track.scrollLeft <= 4;
            if (next) next.disabled = track.scrollLeft >= max - 4;
        };

        const advance = () => (current() >= stops() - 1 ? goTo(0) : goTo(current() + 1));
        const start = () => {
            if (reduce || autoplay || stops() < 2) return;
            autoplay = setInterval(() => { if (!paused && visible && !document.hidden) advance(); }, INTERVAL);
        };
        const stop = () => { clearInterval(autoplay); autoplay = null; };
        const restart = () => { stop(); start(); };

        prev?.addEventListener('click', () => { goTo(current() - 1); restart(); });
        next?.addEventListener('click', () => { goTo(current() + 1); restart(); });

        let ticking = false;
        track.addEventListener('scroll', () => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(() => { update(); ticking = false; });
        }, { passive: true });

        track.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') { e.preventDefault(); goTo(current() + 1); restart(); }
            if (e.key === 'ArrowLeft') { e.preventDefault(); goTo(current() - 1); restart(); }
        });

        // Pause while the visitor is interacting with it.
        carousel.addEventListener('mouseenter', () => { paused = true; });
        carousel.addEventListener('mouseleave', () => { paused = false; });
        carousel.addEventListener('focusin', () => { paused = true; });
        carousel.addEventListener('focusout', () => { paused = false; });
        track.addEventListener('touchstart', () => { paused = true; restart(); }, { passive: true });
        track.addEventListener('touchend', () => { paused = false; }, { passive: true });

        // Only autoplay while on screen.
        if ('IntersectionObserver' in window) {
            new IntersectionObserver((entries) => entries.forEach((en) => { visible = en.isIntersecting; }), { threshold: 0.4 }).observe(carousel);
        } else {
            visible = true;
        }

        let resizeTimer;
        window.addEventListener('resize', () => { clearTimeout(resizeTimer); resizeTimer = setTimeout(buildDots, 150); });

        buildDots();
        start();
    });

    // Hero photo: subtle 3D tilt that follows the mouse (desktop only).
    const tilt = document.querySelector('[data-tilt]');
    if (tilt && window.matchMedia('(hover: hover) and (min-width: 992px)').matches
        && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const MAX = 5; // degrees
        let frame = null;
        tilt.addEventListener('mousemove', (e) => {
            const r = tilt.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width - 0.5;
            const y = (e.clientY - r.top) / r.height - 0.5;
            cancelAnimationFrame(frame);
            frame = requestAnimationFrame(() => {
                tilt.style.setProperty('--ry', (x * MAX * 2).toFixed(2) + 'deg');
                tilt.style.setProperty('--rx', (-y * MAX * 2).toFixed(2) + 'deg');
            });
        });
        tilt.addEventListener('mouseleave', () => {
            cancelAnimationFrame(frame);
            tilt.style.setProperty('--rx', '0deg');
            tilt.style.setProperty('--ry', '0deg');
        });
    }

    // Back-to-top button: shown after scrolling down one screen.
    const toTop = document.querySelector('.back-to-top');
    if (toTop) {
        const ring = toTop.querySelector('.btt-progress');
        const length = 2 * Math.PI * 22; // circumference of the SVG ring (r = 22)
        const toggle = () => {
            const max = document.documentElement.scrollHeight - window.innerHeight;
            const progress = max > 0 ? Math.min(window.scrollY / max, 1) : 0;
            if (ring) ring.style.strokeDashoffset = String(length * (1 - progress));
            toTop.classList.toggle('show', window.scrollY > window.innerHeight * 0.6);
        };
        toggle();
        window.addEventListener('scroll', toggle, { passive: true });
        toTop.addEventListener('click', () => {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
        });
    }

    // Fade sections up as they scroll into view. Content stays visible if this never runs.
    const reveals = document.querySelectorAll('[data-reveal]');
    if (reveals.length && 'IntersectionObserver' in window && !reduceMotion) {
        document.documentElement.classList.add('reveal-ready');
        const revealObs = new IntersectionObserver((entries, obs) => {
            entries.forEach((en) => {
                if (!en.isIntersecting) return;
                en.target.classList.add('is-visible');
                obs.unobserve(en.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
        reveals.forEach((el) => revealObs.observe(el));
    }

    // Service search (AJAX autocomplete).
    document.querySelectorAll('[data-service-search]').forEach((wrap) => {
        const input = wrap.querySelector('input');
        const box = wrap.querySelector('.search-results');
        const url = wrap.dataset.serviceSearch;
        let timer = null;
        let controller = null;
        let active = -1;

        const escape = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

        const render = (items) => {
            active = -1;
            if (!items.length) {
                box.innerHTML = '<div class="p-3 text-muted small">No matching service. <a href="' + wrap.dataset.contactUrl + '">Talk to an expert</a></div>';
            } else {
                box.innerHTML = items.map((i) =>
                    '<a href="' + escape(i.url) + '"><span class="icon-bubble sm"><i class="bi ' + escape(i.icon) + '"></i></span>' +
                    '<span class="min-w-0"><strong class="d-block text-truncate">' + escape(i.name) + '</strong><small class="text-muted">' + escape(i.category) + '</small></span>' +
                    (i.price ? '<span class="price">' + escape(i.price) + '</span>' : '') + '</a>').join('');
            }
            box.classList.add('show');
        };

        input.addEventListener('input', () => {
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { box.classList.remove('show'); return; }
            timer = setTimeout(() => {
                if (controller) controller.abort();
                controller = new AbortController();
                fetch(url + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }, signal: controller.signal })
                    .then((r) => r.json()).then((d) => render(d.data || [])).catch(() => {});
            }, 220);
        });

        input.addEventListener('keydown', (e) => {
            const links = box.querySelectorAll('a');
            if (!links.length) return;
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                active = (active + (e.key === 'ArrowDown' ? 1 : -1) + links.length) % links.length;
                links.forEach((l, i) => l.classList.toggle('active', i === active));
            } else if (e.key === 'Enter' && active >= 0) {
                e.preventDefault();
                window.location = links[active].href;
            } else if (e.key === 'Escape') {
                box.classList.remove('show');
            }
        });

        document.addEventListener('click', (e) => { if (!wrap.contains(e.target)) box.classList.remove('show'); });
    });

    // Highlight the in-page section nav on service pages.
    const navLinks = document.querySelectorAll('.service-nav .nav-link');
    if (navLinks.length && 'IntersectionObserver' in window) {
        const map = new Map();
        navLinks.forEach((l) => { const t = document.querySelector(l.getAttribute('href')); if (t) map.set(t, l); });
        const obs = new IntersectionObserver((entries) => {
            entries.forEach((en) => {
                if (en.isIntersecting) {
                    navLinks.forEach((l) => l.classList.remove('active'));
                    map.get(en.target)?.classList.add('active');
                }
            });
        }, { rootMargin: '-140px 0px -60% 0px' });
        map.forEach((_, target) => obs.observe(target));
    }
})();
