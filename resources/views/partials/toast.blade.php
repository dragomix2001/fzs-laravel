<!-- Toast Notifications -->
<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    @if(Session::has('success'))
        <div class="toast show" role="alert" data-bs-delay="3000">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Успешно</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ Session::get('success') }}
            </div>
        </div>
    @endif

    @if(Session::has('error'))
        <div class="toast show" role="alert" data-bs-delay="5000">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">Грешка</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ Session::get('error') }}
            </div>
        </div>
    @endif

    @if(Session::has('warning'))
        <div class="toast show" role="alert" data-bs-delay="4000">
            <div class="toast-header bg-warning text-dark">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong class="me-auto">Упозорење</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ Session::get('warning') }}
            </div>
        </div>
    @endif

    @if(Session::has('info'))
        <div class="toast show" role="alert" data-bs-delay="3000">
            <div class="toast-header bg-info text-white">
                <i class="fas fa-info-circle me-2"></i>
                <strong class="me-auto">Инфо</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ Session::get('info') }}
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide toasts after delay
    setTimeout(function() {
        document.querySelectorAll('.toast').forEach(function(toast) {
            var bsToast = new bootstrap.Toast(toast);
            bsToast.hide();
        });
    }, 5000);
});
</script>
