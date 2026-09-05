(() => {
    if (window.gradConnSidebarInitialized) return;
    window.gradConnSidebarInitialized = true;

    const sidebar = () => document.getElementById('appSidebar');
    const overlay = () => document.querySelector('.mobile-sidebar-overlay');
    const toggles = () => document.querySelectorAll('.mobile-sidebar-toggle, [data-sidebar-toggle]');
    const setOpen = (open) => {
        sidebar()?.classList.toggle('open', open);
        overlay()?.classList.toggle('visible', open);
        document.body.classList.toggle('sidebar-open', open);
        toggles().forEach((button) => {
            button.classList.toggle('open', open);
            button.setAttribute('aria-expanded', String(open));
            if (button.hasAttribute('data-sidebar-toggle')) button.textContent = open ? '✕' : '☰';
        });
    };

    window.toggleSidebar = (show) => setOpen(typeof show === 'boolean' ? show : !sidebar()?.classList.contains('open'));
    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : event.target?.parentElement;
        if (target?.closest('[data-sidebar-toggle]')) {
            event.preventDefault();
            window.toggleSidebar();
        } else if (target?.classList.contains('mobile-sidebar-overlay')) {
            setOpen(false);
        } else if (window.innerWidth <= 992 && target?.closest('#appSidebar a') && !target.closest('[data-logout-trigger]')) {
            setOpen(false);
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setOpen(false);
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth > 992) setOpen(false);
    });
})();
