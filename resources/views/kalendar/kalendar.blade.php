@extends('layouts.layout')
@section('page_heading','Календар')
@section('section')

<div class="w-full lg:w-10/12">
    <a href="{{"/"}}kalendar/indexRok/" class="inline-flex items-center gap-1 text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm font-medium">
        <span class="fa fa-envelope-square"></span>
        Сви рокови
    </a>

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
