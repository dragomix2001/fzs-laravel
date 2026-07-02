@php
    $btnBase = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 px-4 py-2 text-sm';
    $btnColor = 'bg-secondary-100 hover:bg-secondary-200 text-secondary-700 focus:ring-secondary-500';
    if(isset($class)) {
        if(str_contains($class, 'primary')) $btnColor = 'bg-primary-600 hover:bg-primary-500 text-white focus:ring-primary-500';
        elseif(str_contains($class, 'success')) $btnColor = 'bg-success-600 hover:bg-success-500 text-white focus:ring-success-500';
        elseif(str_contains($class, 'warning')) $btnColor = 'bg-warning-500 hover:bg-warning-400 text-white focus:ring-warning-500';
        elseif(str_contains($class, 'danger')) $btnColor = 'bg-danger-600 hover:bg-danger-500 text-white focus:ring-danger-500';
        elseif(str_contains($class, 'info')) $btnColor = 'bg-cyan-600 hover:bg-cyan-500 text-white focus:ring-cyan-500';
    }
    $disabledClass = isset($disabled) ? 'opacity-50 cursor-not-allowed' : '';
@endphp
<div class="inline-flex items-center gap-px" x-data="{ open: false }" @if(isset($up)) x-init="$el.classList.add('flex-col-reverse')" @endif>
    @if(isset($split))
        <button type="button" class="{{ $btnBase }} {{ $btnColor }} {{ $disabledClass }} rounded-r-none">{{ $value }}</button>
        <button type="button" @click="open = !open" class="{{ $btnBase }} {{ $btnColor }} {{ $disabledClass }} rounded-l-none border-l border-white/20">
            <i class="fas fa-chevron-down text-xs"></i>
        </button>
    @else
        <button type="button" @click="open = !open" class="{{ $btnBase }} {{ $btnColor }} {{ $disabledClass }}">
            {{ $value }} <i class="fas fa-chevron-down ml-2 text-xs"></i>
        </button>
    @endif
    <ul x-show="open" @click.away="open = false" class="absolute mt-1 w-48 bg-white rounded-lg shadow-lg border border-secondary-200 py-1 z-50" x-transition @if(isset($up)) style="bottom: 100%; margin-bottom: 0.25rem;" @endif>
        @if(isset($submenu))
            @foreach ($submenu as $menu)
                <li><a href="{{ $menu['link'] }}" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-50">{{ $menu['name'] }}</a></li>
            @endforeach
        @endif
    </ul>
</div>
