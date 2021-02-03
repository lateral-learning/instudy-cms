const TableFilter = require("tablefilter");

/* $.fn.dataTable.Api.register("column().title()", function () {
    var colheader = this.header();
    return $(colheader).text().trim();
}); */

document.addEventListener("DOMContentLoaded", () => {
    const tableStudies = document.querySelector(".tableStudies");
    if (tableStudies) {
        /* const tf = new TableFilter(tableStudies, {
            base_path: "./node_modules/tablefilter/",
            paging: {
                results_per_page: ["Records: ", [10, 25, 50, 100]],
            },
        });
        tf.init(); */
    }
});
