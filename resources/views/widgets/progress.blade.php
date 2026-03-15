<div class="progress">
  <div class="progress-bar bg-{{ isset($class) ? $class : 'primary' }} @if(isset($striped)) progress-bar-striped @endif @if(isset($animated)) progress-bar-animated @endif" role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $value }}%">
    @if(isset($badge)){{ $value }}@endif
  </div>
</div>
