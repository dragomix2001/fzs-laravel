// Global AJAX setup with loading spinner
$(document).ajaxStart(function() {
    // Show loading spinner
    if (!$('#ajax-loader').length) {
        $('body').append('<div id="ajax-loader" class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999; background: rgba(255,255,255,0.9); padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.2);"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i><div class="mt-2 text-muted">Учитавање...</div></div>');
    }
    $('#ajax-loader').show();
});

$(document).ajaxStop(function() {
    // Hide loading spinner
    $('#ajax-loader').hide();
});

$(document).ajaxError(function(event, xhr, settings, thrownError) {
    // Hide spinner on error
    $('#ajax-loader').hide();
    
    // Show error toast
    if (typeof bootstrap !== 'undefined') {
        var errorHtml = '<div class="toast show" role="alert" data-bs-delay="5000">' +
            '<div class="toast-header bg-danger text-white">' +
            '<i class="fas fa-exclamation-circle me-2"></i>' +
            '<strong class="me-auto">Грешка</strong>' +
            '<button type="button" class="btn-close" data-bs-dismiss="toast"></button>' +
            '</div>' +
            '<div class="toast-body">Дошло је до грешке при комуникацији са сервером.</div>' +
            '</div>';
        $('#toast-container').append(errorHtml);
    }
});
