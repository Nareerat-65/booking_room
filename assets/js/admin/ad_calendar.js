document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        locale: 'th',
        firstDay: 0,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: 'ad_calendar_events.php',

        eventDidMount: function (info) {
            if (info.event.extendedProps.tooltip) {
                $(info.el).tooltip({
                    title: info.event.extendedProps.tooltip,
                    container: 'body',
                    placement: 'top',
                    trigger: 'hover',
                });
            }
        },

        eventClick: function (info) {
            var ev = info.event;
            var props = ev.extendedProps || {};

            $('#eventRoom').text(props.room || '-');
            $('#eventBooker').text(props.booker || '-');

            var start = props.start_real || ev.startStr;
            var end = props.end_real || (ev.end ? ev.end.toISOString().slice(0, 10) : '');
            var dateText = start;
            if (end) {
                dateText += ' ถึง ' + end;
            }
            $('#eventDates').text(dateText);

            $('#eventGuests').text(props.guests || 'ยังไม่มีรายชื่อผู้เข้าพัก');

            $('#eventDetailModal').modal('show');
        }
    });

    calendar.render();
});