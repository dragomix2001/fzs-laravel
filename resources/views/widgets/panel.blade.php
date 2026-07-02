<div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden mb-4">
    @if(isset($header))
        <div class="px-4 py-3 border-b border-secondary-200 @if(isset($class) && $class === 'primary') bg-primary-600 text-white @else bg-secondary-50 @endif">
            <h3 class="text-base font-semibold @if(isset($class) && $class === 'primary') text-white @else text-secondary-900 @endif flex items-center justify-between">
                <span>@yield ($as . '_panel_title')</span>
                @if(isset($controls))
                    <div class="flex gap-1">
                        <button class="inline-flex items-center p-1.5 rounded hover:bg-black/10 transition-colors"><i class="fas fa-rotate"></i></button>
                        <button class="inline-flex items-center p-1.5 rounded hover:bg-black/10 transition-colors"><i class="fas fa-minus"></i></button>
                        <button class="inline-flex items-center p-1.5 rounded hover:bg-black/10 transition-colors"><i class="fas fa-xmark"></i></button>
                    </div>
                @endif
            </h3>
        </div>
    @endif
    
    <div class="p-4">
        @yield ($as . '_panel_body')
    </div>
    @if(isset($footer))
        <div class="px-4 py-3 border-t border-secondary-200 bg-secondary-50 text-sm text-secondary-600">
            @yield ($as . '_panel_footer')
        </div>
    @endif
</div>
