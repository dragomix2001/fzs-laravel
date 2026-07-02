<button type="button" 
    class="inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2
    @if(isset($disabled) && $disabled) opacity-50 cursor-not-allowed @endif
    @if(isset($class) && str_contains($class, 'btn-outline'))
        @if(str_contains($class, 'primary')) text-primary-700 border border-primary-300 hover:bg-primary-50
        @elseif(str_contains($class, 'success')) text-success-700 border border-success-300 hover:bg-success-50
        @elseif(str_contains($class, 'warning')) text-warning-700 border border-warning-300 hover:bg-warning-50
        @elseif(str_contains($class, 'danger')) text-danger-700 border border-danger-300 hover:bg-danger-50
        @else text-secondary-700 border border-secondary-300 hover:bg-secondary-50
        @endif
    @elseif(isset($class) && str_contains($class, 'btn-o'))
        @if(str_contains($class, 'primary')) text-primary-600 border-2 border-primary-600 hover:bg-primary-600 hover:text-white
        @else text-secondary-600 border-2 border-secondary-300 hover:bg-secondary-200
        @endif
    @else
        @if(str_contains($class ?? '', 'primary')) bg-primary-600 hover:bg-primary-500 text-white focus:ring-primary-500
        @elseif(str_contains($class ?? '', 'success')) bg-success-600 hover:bg-success-500 text-white focus:ring-success-500
        @elseif(str_contains($class ?? '', 'warning')) bg-warning-500 hover:bg-warning-400 text-white focus:ring-warning-500
        @elseif(str_contains($class ?? '', 'danger')) bg-danger-600 hover:bg-danger-500 text-white focus:ring-danger-500
        @elseif(str_contains($class ?? '', 'info')) bg-cyan-600 hover:bg-cyan-500 text-white focus:ring-cyan-500
        @else bg-secondary-100 hover:bg-secondary-200 text-secondary-700 focus:ring-secondary-500
        @endif
    @endif
    @if(isset($size))
        @if(str_contains($size, 'lg')) px-5 py-2.5 text-sm
        @elseif(str_contains($size, 'sm')) px-3 py-1.5 text-xs
        @elseif(str_contains($size, 'xs')) px-2 py-1 text-xs
        @else px-4 py-2 text-sm
        @endif
    @else
        px-4 py-2 text-sm
    @endif
    {{ isset($disabled) ? 'disabled' : '' }}">
    {{ $value }}
</button>
