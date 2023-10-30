import tableCons from "./tableCons.js";

const tableRoot = document.querySelector("#csvRoot");
const tableCSV = new tableCons(tableRoot);
const csvFileInput = document.querySelector("#csvFileInput");

csvFileInput.addEventListener("change", e => {
    Papa.parse(csvFileInput.files[0], {
        delimter: ",",
        skipEmptyLines: false,
        complete: results => {
            tableCSV.update(results.data.slice(1), results.data[0]);
        }
    });
});

