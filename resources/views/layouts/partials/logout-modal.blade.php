{{-- Logout confirmation. Any <form data-logout> opens this instead of submitting straight away (see panel.js). --}}
@php($user = auth()->user())
<div class="modal fade logout-modal" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalTitle" aria-describedby="logoutModalText" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="logout-close" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>

            <div class="logout-icon" aria-hidden="true">
                <span class="logout-icon-ring"></span>
                <i class="bi bi-box-arrow-right"></i>
            </div>

            <h2 class="logout-title" id="logoutModalTitle">Ready to log out?</h2>
            <p class="logout-text" id="logoutModalText">You will be signed out of your account on this device. Any unsaved changes will be lost.</p>

            <div class="logout-user">
                <span class="avatar">{{ $user->initials() }}</span>
                <span class="logout-user-info">
                    <strong>{{ $user->name }}</strong>
                    <small>{{ $user->email }}</small>
                </span>
                <span class="logout-user-badge"><i class="bi bi-circle-fill"></i> Signed in</span>
            </div>

            <div class="logout-actions">
                <button type="button" class="btn logout-cancel" data-bs-dismiss="modal">Stay signed in</button>
                <button type="button" class="btn logout-confirm" data-logout-confirm>
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Yes, log out</span>
                </button>
            </div>
        </div>
    </div>
</div>
