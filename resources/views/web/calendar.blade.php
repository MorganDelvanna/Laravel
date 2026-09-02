@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div id='script-warning'>
                    <code>get-events.php</code> must be running.
                </div>
                <div id="test"></div>
                <div id='loading'>loading...</div>
                <div id='calendar'></div>
            </div>
        </div>
    </div>
    <link rel='stylesheet' href='{{asset('css/fullcalendar.css')}}' />
    <link rel='stylesheet' href='{{asset('css/fullcalendar.print.css')}}' media='print' />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"  integrity="sha512-QoJS4DOhdmG8kbbHkxmB/rtPdN62cGWXAdAFWWJPvUFF1/zxcPSdAnn4HhYZSIlVoLVEJ0LesfNlusgm2bPfnA=="  crossorigin="anonymous"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="/js/jquery.scrollabletable.js_2.js" type="text/javascript"></script>
    <script src='/js/fullcalendar/index.global.min.js'></script>
    <script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.min.js"></script>
    <script src='https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js'></script>
    <script type="text/javascript">
        $(document).ready(function () {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: (function () { // Example using an IIFE for initial setup
                    if ($(window).width() <= 768) {
                        return 'listMonth';
                    } else {
                        return 'dayGridMonth';
                    }
                })(),
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: (function () {
                        if ($(window).width() <= 768) {
                            return 'listMonth';
                        } else {
                            return 'dayGridMonth,dayGridWeek,listMonth';
                        }
                    })()
                },
                editable: true,
                eventStartEditable: false,
                events: 'calendar/events',
                eventTextColor: '#000000',               
                eventContent: function (info) {
                    return { html: info.event.title };
                },
                eventDidMount: function (info) {
                    let calEvent = info.event;
                    let time
                    if (calEvent.allDay) {
                        time = 'All Day';
                    } else {
                        let dateStart = new Date(calEvent.startStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        let deteEnd = new Date(calEvent.endStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        time = dateStart + ' - ' + deteEnd;
                    }
                    var tooltipHtml = time + '<br />' + calEvent.title + '<br />' + calEvent.extendedProps.description;
                    let tooltip = new Tooltip(info.el, {
                        html: true,
                        title: tooltipHtml,
                        placement: 'bottom', // Adjust the placement as needed
                        trigger: 'hover', // Show the tooltip on hover
                        container: 'body',
                        sanitize: false,
                    });
                    // Check if it's a list view (optional, but good practice)
                    if (info.view.type.startsWith('list')) {
                        let parentWidth = $(info.el).parent().width();

                        $(info.el).css('width', `${parentWidth}px`); // Set desired width
                        // You can also add conditions to set different widths based on event data
                        // if (event.someProperty === 'value') {
                        //   $(element).css('width', '250px');
                        // }
                    }
                },
                loading: function (bool) {
                    $('#loading').toggle(bool);
                }
            });
            calendar.render();
        });
    </script>
@endsection