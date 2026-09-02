@extends('layouts.app')

@section('content')
    <div class='container'>
        <div class="row">
            <div class="col-12">
                <h1>Calendar Events</h1>
            </div>
        </div>
        <div class="row card hidden" id="eventForm">
            <div class="col-12">                
                <form  method='post' action="{{ route('calendar.saveEvent') }}" onsubmit="return validate(this);">
                    @csrf
                    <div style="margin-top: 5px;">
                        <label class='form-label' for="eventStart">Start Time: </label><input class='form-control' type="text" id="eventStart" name="start" value="" /><br />
                        <label class='form-label' for="eventEnd">End Time: </label><input class='form-control' type="text" id="eventEnd" name="end" value="" /><br />
                        <label class='form-label' for="title">Title: </label><input class='form-control' type="text" id="title" name="title" value="" /><br />
                        <label class='form-label' for="description">Description: </label><input class='form-control' style="min-width:500px;" type="text" id="description" name="description" value="" /><br />
                    </div>
                    <div>
                        <input type='submit' class='btn btn-primary' value='Save'>
                        <input type='button' class='btn btn-secondary' value='Cancel' onclick="$('#eventForm').toggleClass('hidden');">
                    </div>
                    <input type="hidden" id="eventID" name="eventID" value=""/>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <button type="button" class="btn btn-primary" onclick="$('#eventForm').toggleClass('hidden');">Add New Event</button>
            </div>
        </div>
        <div class="row  align-items-center">
            <div class="col-12">
                <table class="pfgaTable">
                    <thead>
                        <tr>
                            <th>Start</th>
                            <th>End</th>
                            <th>Title</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr>
                                <td>{{ $event->start?->format('Y-m-d H:i') }}</td>
                                <td>{{ $event->end?->format('Y-m-d H:i') }}</td>
                                <td>{{ $event->title }}</td>
                                <td>
                                    <button type="button" class="btn btn-primary" onclick='editEvent({{ $event->id }}, @json($event->start?->format('Y-m-d H:i')), @json($event->end?->format('Y-m-d H:i')), @json($event->title), @json($event->description))'>Edit</button>
                                    <form method="POST" action="{{ route('calendar.deleteEvent', ['id' => $event->id]) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No calendar events found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>            
        </div>        
    </div>

    <link rel="stylesheet" type="text/css" href="/js/dateTimePicker/jquery.datetimepicker.min.css" />
    <script src="/js/dateTimePicker/jquery.datetimepicker.full.min.js"></script>
    <script type="text/javascript">
        function editEvent(id, start, end, title, description) {            
            $('#eventID').val(id);
            $('#eventStart').val(start || '');
            $('#eventEnd').val(end || '');
            $('#title').val(title || '');
            $('#description').val(description || '');
            $('#eventForm').toggleClass('hidden');
        }

        $(document).ready(function () {
            $('#eventStart').datetimepicker({
                format: 'Y-m-d H:i',
                defaultTime: '9:00',
                closeOnDateSelect: true,
                onSelectDate: function (ct, $i) {
                    var start = moment(ct).format('YYYY-MM-DD 21:00')
                    $('#eventEnd').val(start);
                }
            });
            $('#eventEnd').datetimepicker({
                format: 'Y-m-d H:i',
                defaultTime: '21:00',
                closeOnDateSelect:true
            });
        });
    </script>
@endsection