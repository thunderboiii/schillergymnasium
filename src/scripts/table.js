import tableCons from "./tableCons.js";

const tableRoot = document.querySelector("#csvRoot");
const tableCSV = new tableCons(tableRoot);
const csvFileInput = document.querySelector("#csvFileInput");

const csvDataElement = document.getElementById("csvFile");
const dataAttribute = csvDataElement.getAttribute("data-table");
const csvFile = dataAttribute;


// Add an event listener for the "load" event of the window
window.addEventListener("load", () => {
    // Your code to parse the CSV and update the table
    Papa.parse(csvFile, {
        download: true,
        delimiter: "",
        skipEmptyLines: false,
        complete: results => {
            tableCSV.update(results.data.slice(1), results.data[0]);
        }
    });
});

