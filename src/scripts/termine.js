// Define your date range (current date to one week from now)
const currentDate = new Date();
const oneWeekLater = new Date();
oneWeekLater.setDate(currentDate.getDate() + 7);

// Use a CORS proxy to fetch the iCal data
const proxyUrl = 'https://cors-anywhere.herokuapp.com/';
const calendarUrl = 'https://calendar.google.com/calendar/ical/r59d3v49824ce551us502etp002dkdec%40import.calendar.google.com/public/basic.ics';

fetch(proxyUrl + calendarUrl)
    .then(response => response.text())
    .then(data => {
        // Parse iCal data to extract events within the date range
        const jcalData = ICAL.parse(data);
        const comp = new ICAL.Component(jcalData);
        const vevents = comp.getAllProperties('vevent');
        const events = vevents
            .map(vevent => {
                const dtstart = vevent.getFirstValue('dtstart').toJSDate();
                return {
                    summary: vevent.getFirstValue('summary'),
                    start: dtstart,
                };
            })
            .filter(event => event.start >= currentDate && event.start <= oneWeekLater);

        // Generate and append HTML for each event
        const scrollElement = document.querySelector('.scroll');
        for (const event of events) {
            const eventHtml = generateEventHTML(event);
            scrollElement.innerHTML += eventHtml;
        }
    });

// Function to generate HTML for an event
function generateEventHTML(event) {
    const eventDate = event.start;
    const day = eventDate.getDate();
    const month = eventDate.getMonth() + 1;
    const year = eventDate.getFullYear();
    const eventTitle = event.summary;

    return `
        <div class="termin scroll-item">
            <div class="date">
                <p class="day">${day}.</p>
                <p>${month}. ${year}.</p>
            </div>
            <h3>${eventTitle}</h3>
        </div>
    `;
}