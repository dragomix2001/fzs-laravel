<div class="btn-group @if (isset($up)) dropup @endif">
    @if (isset($split)) 

        <button type="button" class="btn btn-{{ isset($class) ? $class : 'secondary' }} {{ isset($rounded) ? 'rounded-pill' : '' }} {{ isset($bordered) ? 'btn-outline-secondary' : '' }} @if (isset($size)) btn-{{$size}} @endif {{ isset($disabled) ? 'disabled' : '' }}">{{ $value }}</button>
        <button type="button" class="btn btn-{{ isset($class) ? $class : 'secondary' }} dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="caret"></span>
            <span class="visually-hidden"></span>
        </button> 

    @else

    <button type="button" class="btn btn-{{ isset($class) ? $class : 'secondary' }} {{ isset($rounded) ? 'rounded-pill' : '' }} {{ isset($bordered) ? 'btn-outline-secondary' : '' }} @if (isset($size)) btn-{{$size}} @endif {{ isset($disabled) ? 'disabled' : '' }} dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">{{ $value }}
        <span class="caret"></span>
    </button>

@endif

    <ul class="dropdown-menu" role="menu">
        @if (isset($submenu))
            @foreach ($submenu as $menu)
                <li><a href="{{ $menu['link'] }}"> {{ $menu['name']  }}</a></li>
            @endforeach
        @endif
    </ul>
</div>
