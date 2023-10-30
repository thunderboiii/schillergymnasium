export default class {
    /**
     * @param {HTMLTableElement} root The Table element which will display the CSV data
     */
    constructor(root) {
        this.root = root;
    }

    /**
     * Clears existing data in the table and replaces it with new
     * 
     * @param {string[][]} data A 2D Array of data to be used as the tbody
     * @param {string[]} headerColumns List of headings to be used
     */
    update(data, headerColumns = []) {
        this.clear();
        this.setHeader(headerColumns);
        this.setBody(data);
    }

    // Clears all content of the table (incl. headers)
    clear() {
        this.root.innerHTML = "";
    }


    /**
     *  
     * @param {string[]} headerColumns List of headings to be used 
     */
    setHeader(headerColumns) {
        this.root.insertAdjacentHTML("afterbegin", `
            <thead>
                <tr>
                    ${ headerColumns.map(text => `<th>${ text }</th>`).join("") }
                </tr>
            </thead>
        `);
    }

    /**
     *  
     * @param {string[][]} data A 2D array of Data to be used as the tbody
     */
    setBody(data) {
        const rowsHtml = data.map(row => {
            return `
                <tr>
                    ${ row.map(text => `<td>${ text }</td>`).join("")}
                </tr>
            `;
        });

        this.root.insertAdjacentHTML("beforeend", `
            <tbody>
                ${ rowsHtml.join("")}
            </tbody>
        `);
    }
}
