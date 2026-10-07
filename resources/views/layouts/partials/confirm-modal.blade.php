{{-- Confirmation dialog for any <form data-confirm="Delete this service?"> (see panel.js).
     Optional: data-confirm-text="…" (detail line), data-confirm-item="…" (name shown in the card),
     data-confirm-button="…" (confirm label), data-confirm-variant="danger|primary". --}}
<div class="modal fade logout-modal confirm-modal" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-describedby="confirmModalText" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="logout-close" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>

            <div class="confirm-icon" aria-hidden="true">
                <span class="confirm-icon-ring"></span>
                <i class="bi bi-trash3" data-confirm-icon></i>
            </div>

            <h2 class="logout-title" id="confirmModalTitle" data-confirm-title>Are you sure?</h2>
            <p class="logout-text" id="confirmModalText" data-confirm-body></p>

            <div class="confirm-item" data-confirm-item-box hidden>
                <span class="confirm-item-ico"><i class="bi bi-file-earmark-text" data-confirm-item-icon></i></span>
                <strong data-confirm-item-name></strong>
            </div>

            <div class="logout-actions">
                <button type="button" class="btn logout-cancel" data-bs-dismiss="modal">No, go back</button>
                <button type="button" class="btn confirm-go" data-confirm-go>
                    <i class="bi bi-trash3" data-confirm-go-icon></i>
                    <span data-confirm-go-label>Yes, delete</span>
                </button>
            </div>
        </div>
    </div>
</div>
