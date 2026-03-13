@extends('layouts.layout')
@section('page_heading','Календар распореда')
@section('section')

<div class="col-sm-12 col-lg-12">
<h2>Календар часова</h2>

    <form method="GET" action="{{ route('raspored.kalendar') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label>Школска година</label>
                <select name="skolska_godina_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Активна година --</option>
                    @foreach($skolskeGodine as $godina)
                        <option value="{{ $godina->id }}" {{ request('skolska_godina_id') == $godina->id ? 'selected' : '' }}>
                            {{ $godina->naziv }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>Студијски програм</label>
                <select name="studijski_program_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Сви програми --</option>
                    @foreach($studijskiProgrami as $program)
                        <option value="{{ $program->id }}" {{ request('studijski_program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->naziv }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
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
