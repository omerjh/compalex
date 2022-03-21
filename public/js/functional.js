Data = {
    showAll: function (elem) {
        $('table.table tr.data').show();
        $('div.sql').hide();
        Data.selectElement(elem);
    },

    showDiff: function (elem) {
        $('table.table tr.data').hide();
        $('table.table li.new').parent().parent().parent().show();
        $('div.sql').hide();
        Data.selectElement(elem);
    },

    showSql: function (elem) {
        $('table.table tr.data').hide();
        $('div.sql').show();
        // $('table.table li.new').parent().parent().parent().show();
        Data.selectElement(elem);
		console.log(elem)
    },

    selectElement: function (elem) {
        $('.panel .sp a').removeClass('active');
        $(elem).addClass('active');
    },

    getTableData: function (url) {
        $('div.modal-background iframe').attr('src', url);
        $('div.modal-background').addClass('visible');
    },

    hideTableData: function () {
        $('div.modal-background').removeClass('visible');
    }
}