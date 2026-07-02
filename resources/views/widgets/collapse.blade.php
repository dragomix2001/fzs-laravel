<div x-data="{ open: {{ isset($collapseIn) ? 'true' : 'false' }} }" class="border border-secondary-200 rounded-lg overflow-hidden mb-2">
    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 bg-secondary-50 hover:bg-secondary-100 text-sm font-medium text-secondary-900 transition-colors">
        <span>{{ $header }}</span>
        <i class="fas fa-chevron-down text-xs text-secondary-400 transition-transform" :class="{'rotate-180': open}"></i>
    </button>
    <div x-show="open" x-transition class="px-4 py-3 text-sm text-secondary-700 border-t border-secondary-200">
        {{ $body }}
    </div>
</div>
