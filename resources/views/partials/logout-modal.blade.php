@once
<link rel="stylesheet" href="/css/logout-modal.css">

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

<script src="/js/logout-modal.js" defer></script>
@endonce
