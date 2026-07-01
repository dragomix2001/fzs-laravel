@extends('layouts.layout')
@section('page_heading','Календар распореда')
@section('section')

<div class="w-full">
    <h2>Календар часова</h2>

    <form method="GET" action="{{ route('raspored.kalendar') }}" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form-select label="Школска година" name="skolska_godina_id"
                           :options="collect(['' => '-- Активна година --'] + $skolskeGodine->pluck('naziv','id')->toArray())->toArray()"
                           :selected="request('skolska_godina_id')"
                           onchange="this.form.submit()" />
            <x-form-select label="Студијски програм" name="studijski_program_id"
                           :options="collect(['' => '-- Сви програми --'] + $studijskiProgrami->pluck('naziv','id')->toArray())->toArray()"
                           :selected="request('studijski_program_id')"
                           onchange="this.form.submit()" />
        </div>
    </form>

    <x-card>
        <div id="calendar"></div>
    </x-card>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        locale: 'sr-cyrl',
        firstDay: 1,
        slotMinTime: '07:00:00',
        slotMaxTime: '21:00:00',
        allDaySlot: false,
        height: 'auto',
        events: {
            url: '{{ route("raspored.kalendar.events") }}',
            method: 'GET',
            extraParams: {
                skolska_godina_id: '{{ request("skolska_godina_id") }}',
                studijski_program_id: '{{ request("studijski_program_id") }}'
            }
        },
        eventClick: function(info) {
            alert('Предмет: ' + info.event.title + '\nПрофесор: ' + info.event.extendedProps.profesor + '\nПросторија: ' + info.event.extendedProps.prostorija + '\nГрупа: ' + info.event.extendedProps.grupa);
        }
    });

    calendar.render();
});
</script>
@endsection
