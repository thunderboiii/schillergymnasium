const icsFilePath = '/assets/schiller.ics';

  // Fetch the ICS file content
  fetch(icsFilePath)
    .then(response => {
      if (!response.ok) {
        throw new Error(`Failed to fetch ICS file. Status: ${response.status}`);
      }
      return response.text();
    })
    .then(icsData => {
      // Parse the ICS data
      const jcalData = ICAL.parse(icsData);
      const comp = new ICAL.Component(jcalData);
      const vevents = comp.getAllProperties('vevent');

      if (vevents.length === 0) {
        throw new Error('No events found in the ICS file.');
      }

      // Iterate through each event
      vevents.forEach((vevent, index) => {
        const uid = vevent.getFirstPropertyValue('uid');
        const summary = vevent.getFirstPropertyValue('summary');
        const startDate = vevent.getFirstPropertyValue('dtstart');

        if (!uid) {
          console.warn(`Event ${index + 1} has no UID. Skipping.`);
          return;
        }

        if (!summary) {
          console.warn(`Event ${index + 1} has no SUMMARY. Skipping.`);
          return;
        }

        console.log(`Event ${index + 1}:`);
        console.log(`UID: ${uid}`);
        console.log(`Summary: ${summary}`);
        console.log(`Start Date: ${startDate.toJSDate()}`);
        console.log('-----------------------------');
      });
    })
    .catch(error => {
      console.error('Error:', error);
    });