var members = {

    init: function() {
        members.tableSort();
    },

    tableSort : function() {
        var table = $("table.members");
        if(table.length > 0) {
            table.tablesorter({debug: true}); 
        }
    }

}