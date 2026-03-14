<div class="card border-{{{ isset($class) ? $class : 'secondary' }}}">
    @if( isset($header))  
        <div class="card-header">
            <h3 class="card-title">@yield ($as . '_panel_title')
            @if( isset($controls))  
                <div class="float-end">
                    <button class="btn btn-sm btn-light"><i class="fas fa-arrows-rotate"></i></button>
                    <button class="btn btn-sm btn-light" data-bs-toggle="collapse"><i class="fas fa-minus"></i></button>
                    <button class="btn btn-sm btn-light" data-bs-dismiss="alert"><i class="fas fa-xmark"></i></button>
                </div>
            @endif
            </h3>
        </div>
    @endif
    
    <div class="card-body">
        @yield ($as . '_panel_body')
    </div>
    @if( isset($footer))
        <div class="card-footer">@yield ($as . '_panel_footer')</div>
    @endif
</div>
