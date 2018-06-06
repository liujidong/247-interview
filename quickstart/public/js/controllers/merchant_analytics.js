shopinterest.controllers.merchant_analytics = new function() {

    $( "#from" ).datepicker({
        defaultDate: "-7d",
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd",
        onClose: function( selectedDate ) {
            if(selectedDate === '') {
                $( "#from" ).datepicker( "setDate", "-7d" );            
            }
            $( "#to" ).datepicker( "option", "minDate", selectedDate );
        }
    });
    
    $( "#to" ).datepicker({
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd",
        onClose: function( selectedDate ) {
            if(selectedDate === '') {
                $( "#to" ).datepicker( "setDate", "-0d" );
            }  
            $( "#from" ).datepicker( "option", "maxDate", selectedDate );
        }
    });

    $("#admin_table").tablesorter(
     {
         header     : 'ui-widget-header ui-corner-all ui-state-default', // header classes
         footerCells: '',
         sortNone   : 'ui-icon-carat-2-n-s',
         sortAsc    : 'headerSortUp',
         sortDesc   : 'ui-icon-carat-1-s'
     }
    );
};
