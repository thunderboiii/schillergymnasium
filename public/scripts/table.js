window.onload = function () {
    function handleFile(file) {
        const reader = new FileReader();

        reader.onload = function(e)  {
            const csvData = e.target.result;
            const rows = csvData.split("\n");
            const table = document.createElement("table");

            for (let i = 0; i < rows.length; i++) {
                const row = document.createElement(i === 0 ? "th" : "tr");
                const cells = rows[i].split(",");

                for (let j = 0; j < cells.length; j++) {
                    const cell = i === 0 ? "th" : "td";
                    const td = document.createElement(cell);
                    td.textContent = cells[j];
                    row.appendChild(td); 
                }
                
                table.appendChild(row);
            }

            document.getElementById("tableContainer").innerHTML = "";
            document.getElementById("tableContainer").appendChild(table);
        };   

        reader.readAsText(file);
    }

    const sampleCSVFilePath = "/img/test/test_excel.CSV";
    fetch (sampleCSVFilePath)
        .then(response => response.text())
        .then(data => {
            const blob = new Blob([data], {type: "text/csv"});
            const file = new File([blob], sampleCSVFilePath);
            handleFile(file);
        })
        .catch(error => {
            console.error("Error loading sample CSV file:", error);
        });
};