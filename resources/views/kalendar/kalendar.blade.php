@extends('layouts.layout')
@section('page_heading','Календар')
@section('section')

<div class="w-full lg:w-10/12">
    <x-button variant="primary" size="md" href="{{"/"}}kalendar/indexRok/">
        <span class="fa fa-envelope-square"></span>
        Сви рокови
    </x-button>

    <div class="mt-4">
        <div id='calendar'></div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('#calendar').fullCalendar({
            theme: true,
            lang: 'sr-cyrl',
            header:
            {
                left:   'basicDay,basicWeek,month',
                center: 'title',
                right:  'today prev,next'
            },
            editable: true,
            fixedWeekCount: false,
            height: 600,
            eventSources: [
                {
                    url: '/kalendar/eventSource',
                    color: 'blue',
                    textColor: 'white'
                }
            ],
            eventClick: function(calEvent, jsEvent, view) {
                window.location.href = '{{"/"}}kalendar/editRok/' + calEvent.id;
            }
        })
    });
</script>
@endsection
