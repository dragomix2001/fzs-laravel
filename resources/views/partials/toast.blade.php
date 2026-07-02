<div id="toast-container" class="fixed top-0 right-0 p-3 z-50 space-y-2">
    @if(Session::has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="flex items-center gap-2 bg-success-600 text-white px-4 py-3 rounded-lg shadow-lg text-sm" role="alert">
            <i class="fas fa-check-circle"></i>
            <span class="font-medium mr-auto">Успешно</span>
            <button @click="show = false" class="text-white/80 hover:text-white">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
        </div>
    @endif

    @if(Session::has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="flex items-center gap-2 bg-danger-600 text-white px-4 py-3 rounded-lg shadow-lg text-sm" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <span class="font-medium mr-auto">Грешка</span>
            <button @click="show = false" class="text-white/80 hover:text-white">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
        </div>
    @endif

    @if(Session::has('warning'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="flex items-center gap-2 bg-warning-500 text-white px-4 py-3 rounded-lg shadow-lg text-sm" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <span class="font-medium mr-auto">Упозорење</span>
            <button @click="show = false" class="text-white/80 hover:text-white">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
        </div>
    @endif

    @if(Session::has('info'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="flex items-center gap-2 bg-cyan-600 text-white px-4 py-3 rounded-lg shadow-lg text-sm" role="alert">
            <i class="fas fa-info-circle"></i>
            <span class="font-medium mr-auto">Инфо</span>
            <button @click="show = false" class="text-white/80 hover:text-white">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
        </div>
    @endif
</div>
