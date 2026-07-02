<script>
// Global AJAX setup with loading spinner
$(document).ajaxStart(function() {
    // Show loading spinner
    if (!$('#ajax-loader').length) {
        $('body').append('<div id="ajax-loader" class="fixed inset-0 flex items-center justify-center" style="z-index: 9999;">' +
            '<div class="bg-white/90 backdrop-blur-sm rounded-xl shadow-xl px-8 py-6 text-center border border-secondary-200">' +
                '<i class="fas fa-spinner fa-spin fa-3x text-primary-600"></i>' +
                '<div class="mt-3 text-sm text-secondary-500">Учитавање...</div>' +
            '</div></div>');
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
    
    // Show error toast (using the Tailwind-based toast system)
    if (typeof $ !== 'undefined') {
        var toastContainer = $('#toast-container');
        if (toastContainer.length) {
            var errorToast = $(
                '<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="flex items-center gap-2 bg-danger-600 text-white px-4 py-3 rounded-lg shadow-lg text-sm" role="alert">' +
                    '<i class="fas fa-exclamation-circle"></i>' +
                    '<span class="font-medium mr-auto">Грешка</span>' +
                    '<span class="text-xs">Дошло је до грешке при комуникацији са сервером.</span>' +
                    '<button @click="show = false" class="text-white/80 hover:text-white">' +
                        '<svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>' +
                    '</button>' +
                '</div>'
            );
            toastContainer.prepend(errorToast);
        }
    }
});
</script>
