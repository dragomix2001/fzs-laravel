<div class="alert alert-{{$class}} @if (isset($dismissable)) alert-dismissible @endif" role="alert">
@if (isset($dismissable)) <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> @endif <i class="fas fa-{{ (isset($icon)) ? $icon : $class }}"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; @if (isset($link)) <a href="#" class="alert-link">{{ $link }}</a> @endif {{ $message }}.
</div>
