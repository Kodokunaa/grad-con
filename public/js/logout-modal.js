(() => {
    if (window.gradConnLogoutModalInitialized) return;
    window.gradConnLogoutModalInitialized = true;

    const initialize = () => {
        const lightbox = document.getElementById('logoutLightbox');
        const cancel = document.getElementById('logoutLightboxCancel');
        if (!lightbox || !cancel) return;

        let trigger = null;
        const close = () => {
            lightbox.classList.remove('is-open');
            lightbox.setAttribute('aria-hidden', 'true');
            document.body.style.removeProperty('overflow');
            trigger?.focus();
        };
        const open = (element) => {
            trigger = element;
            lightbox.classList.add('is-open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            cancel.focus();
        };

        document.addEventListener('click', (event) => {
            const element = event.target instanceof Element ? event.target : event.target?.parentElement;
            const logoutTrigger = element?.closest('[data-logout-trigger], a[href$="/auth/logout.php"]');
            if (logoutTrigger) {
                event.preventDefault();
                event.stopPropagation();
                open(logoutTrigger);
                return;
            }
            if (event.target === lightbox) close();
        }, true);
        cancel.addEventListener('click', close);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && lightbox.classList.contains('is-open')) close();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, {once: true});
    } else {
        initialize();
    }
})();
