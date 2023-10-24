function formatDate(date) {
    const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
    return date.toJSDate().toLocaleDateString('de-DE', options);
}

function formatTime(startDate, endDate) {
    const startTime = startDate.toJSDate().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
    const endTime = endDate.toJSDate().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
    return `${startTime} - ${endTime}`;
}

fetch('/assets/public.ics')
    .then(response => response.text())
    .then(data => {
        const jcalData = ICAL.parse(data);
        const comp = new ICAL.Component(jcalData);

        displayEvents(comp.getAllProperties('vevent'));
    })
    .catch(error => {
        console.error('Error fetching ICS file: ', error);
    });

function displayEvents(events) {
    const tableBody = document.querySelector('#eventTable tbody');
    const calendarTable = document.getElementById('eventTable');

    events.forEach(event => {
        const startDate = event.getFirstValue('dtstart');
        const dayOfWeek = startDate.toJSDate().getDay();

        const title = event.getFirstValue('summary');
        const description = event.getFirstValue('description');

        // Get the row for this event based on the day of the week
        const row = tableBody.rows[0];
        const cell = row.cells[dayOfWeek];
        if (!cell.textContent) {
            cell.textContent = `${formatDate(startDate)}\n${title}\n${description}\n${formatTime(startDate, startDate)}`;
        } else {
            // If there are multiple events on the same day, append them to the same cell
            cell.textContent += `\n${title}\n${description}\n${formatTime(startDate, startDate)}`;
        }
    });
}
