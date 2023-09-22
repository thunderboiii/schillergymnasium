import dav from '../assets/dav/dav.min.js';

const xhr = new dav.transport.Basic(
    new dav.Credentials({
        username: 'homepage',
        password: '*pagehome*',
    })
);

const client = new dav.Client(xhr);

const calendarUrl = 'https://schiller.ms.de/caldav/+public/calendar';

client.createAccount({
    server: calendarUrl,
}).then((account) => {
    return account.calendarObjects.list();
}).then ((calendarObjects) => {
    console.log(calendarObjects);
}).catch((error) => {
    console.error(error);
});

