const ical = require('node-ical');

const events = ical.sync.parseFile('https://calendar.google.com/calendar/ical/r59d3v49824ce551us502etp002dkdec%40import.calendar.google.com/public/basic.ics');

for (const event of Object.values(events)) {
    console.log(
        'Summary: ' + event.summary +
        '\nDescription: ' + event.description +
        '\nStart Date: ' + event.start.toISOString() +
        '\n'
    );
}
