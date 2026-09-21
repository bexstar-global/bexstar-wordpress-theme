(function () {
    'use strict';
    const mobile = window.matchMedia('(max-width: 1099px)');
    document.querySelectorAll('[data-bex-header]').forEach(function (header) {
        const toggle = header.querySelector('.bex-menu-toggle');
        const nav = header.querySelector('.bex-nav');
        const menus = Array.from(header.querySelectorAll('.bex-mega'));
        function closeMenus() {
            header.querySelectorAll('details[open]').forEach(function (d) { d.open = false; });
        }
        function setMobileOpen(open, focusToggle) {
            toggle.setAttribute('aria-expanded', String(open));
            nav.hidden = mobile.matches && !open;
            if (!open) closeMenus();
            if (focusToggle) toggle.focus();
        }
        function resize() {
            const hiddenFocus = nav.contains(document.activeElement) && mobile.matches;
            toggle.hidden = !mobile.matches;
            setMobileOpen(false, hiddenFocus);
        }
        toggle.addEventListener('click', function () {
            setMobileOpen(toggle.getAttribute('aria-expanded') !== 'true', false);
        });
        menus.forEach(function (details) {
            details.addEventListener('toggle', function () {
                if (details.open) menus.forEach(function (other) { if (other !== details) other.open = false; });
            });
        });
        header.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;
            const active = document.activeElement.closest('details[open]');
            if (active) {
                active.open = false;
                active.querySelector('summary').focus();
            } else if (mobile.matches) {
                setMobileOpen(false, true);
            } else {
                closeMenus();
            }
        });
        document.addEventListener('click', function (event) {
            if (!header.contains(event.target)) setMobileOpen(false, false);
        });
        header.addEventListener('focusout', function () {
            window.setTimeout(function () {
                if (!header.contains(document.activeElement)) setMobileOpen(false, false);
            }, 0);
        });
        nav.addEventListener('click', function (event) {
            if (event.target.closest('a')) setMobileOpen(false, mobile.matches);
        });
        mobile.addEventListener('change', resize);
        resize();
    });
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    document.querySelectorAll('.bex-media').forEach(function (figure) {
        const image = figure.querySelector('img');
        if (image) image.addEventListener('error', function () { image.hidden = true; });
        const video = figure.querySelector('video');
        const button = figure.querySelector('.bex-video-toggle');
        if (!video || !button) return;
        button.hidden = false;
        const hero = figure.closest('.bex-hero');
        if (hero) hero.appendChild(button);
        function paused() { button.textContent = 'Play video'; button.setAttribute('aria-pressed','false'); }
        button.addEventListener('click', function () {
            if (!video.paused) { video.pause(); return; }
            const source = video.querySelector('source');
            if (!source.src) { source.src = source.dataset.src; video.load(); }
            video.play().catch(function () { video.hidden = true; button.hidden = true; });
        });
        video.addEventListener('play', function () {button.textContent = 'Pause video';button.setAttribute('aria-pressed','true');});
        video.addEventListener('pause', paused);
        function fail() {video.hidden = true; button.hidden = true;}
        video.addEventListener('error', fail);
        video.querySelector('source').addEventListener('error', fail);
        reduced.addEventListener('change', function () {if (reduced.matches) video.pause();});
        document.addEventListener('visibilitychange', function () {if (document.hidden) video.pause();});
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function (entries) {
                if (!entries[0].isIntersecting) video.pause();
            });
            observer.observe(figure);
        }
    });
}());
