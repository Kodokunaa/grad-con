<style>
    .logout-lightbox {
        position: fixed;
        inset: 0;
        z-index: 100000;
        display: none;
        place-items: center;
        padding: 24px;
        background: rgba(8, 12, 20, .72);
        backdrop-filter: blur(7px);
        -webkit-backdrop-filter: blur(7px);
    }

    .logout-lightbox.is-open {
        display: grid;
    }

    .logout-lightbox__dialog {
        width: min(390px, 100%);
        padding: 28px;
        color: #f8fafc;
        text-align: center;
        background: rgba(20, 26, 38, .96);
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 20px;
        box-shadow: 0 24px 80px rgba(0, 0, 0, .48);
        transform: translateY(8px) scale(.98);
        transition: transform .18s ease;
    }

    .logout-lightbox.is-open .logout-lightbox__dialog {
        transform: translateY(0) scale(1);
    }

    .logout-lightbox__icon {
        display: grid;
        width: 54px;
        height: 54px;
        margin: 0 auto 16px;
        place-items: center;
        color: #fdba74;
        font-size: 25px;
        background: rgba(249, 115, 22, .14);
        border-radius: 50%;
    }

    .logout-lightbox h2 {
        margin: 0 0 8px;
        color: #fff;
        font: 700 1.35rem/1.3 system-ui, sans-serif;
    }

    .logout-lightbox p {
        margin: 0 0 24px;
        color: #cbd5e1;
        font: 400 .95rem/1.55 system-ui, sans-serif;
    }

    .logout-lightbox__actions {
        display: flex;
        justify-content: center;
        gap: 12px;
    }

    .logout-lightbox__actions button {
        min-width: 120px;
        padding: 11px 18px;
        border: 0;
        border-radius: 10px;
        cursor: pointer;
        font: 700 .92rem/1 system-ui, sans-serif;
    }

    .logout-lightbox__cancel {
        color: #e2e8f0;
        background: #334155;
    }

    .logout-lightbox__confirm {
        color: #fff;
        background: #ea580c;
    }

    .logout-lightbox__cancel:hover { background: #475569; }
    .logout-lightbox__confirm:hover { background: #c2410c; }
</style>

<div class="logout-lightbox" id="logoutLightbox" aria-hidden="true">
    <div class="logout-lightbox__dialog" role="dialog" aria-modal="true" aria-labelledby="logoutLightboxTitle">
        <div class="logout-lightbox__icon" aria-hidden="true">↪</div>
        <h2 id="logoutLightboxTitle">Log out of GradConn?</h2>
        <p>Your current session will end and you will return to the login page.</p>
        <div class="logout-lightbox__actions">
            <button class="logout-lightbox__cancel" id="logoutLightboxCancel" type="button">Stay logged in</button>
            <form method="POST" action="{{ route('logout', absolute: false) }}">
                @csrf
                <button class="logout-lightbox__confirm" type="submit">Log out</button>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
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
    const open = (link) => {
        trigger = link;
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        cancel.focus();
    };

    document.addEventListener('click', (event) => {
        const logoutLink = event.target.closest('[data-logout-trigger], a[href$="/auth/logout.php"]');
        if (logoutLink) {
            event.preventDefault();
            event.stopPropagation();
            open(logoutLink);
            return;
        }
        if (event.target === lightbox) close();
    }, true);
    cancel.addEventListener('click', close);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && lightbox.classList.contains('is-open')) close();
    });
})();
</script>
