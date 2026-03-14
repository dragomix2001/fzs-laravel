
<div class="progress">
  <div class="progress-bar bg-{{ isset($class) ? $class : 'primary' }} {{ isset($striped) ? 'progress-bar-striped' : '' }} {{ isset($animated) ? 'progress-bar-animated' : '' }}" role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $value }}%">
    {{ isset($badge) ? $value : '' }}
  </div>
</div>
