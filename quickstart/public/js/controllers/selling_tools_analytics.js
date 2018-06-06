shopinterest.controllers.selling_tools_analytics = new function() {

    var from = $('#analytics-from');
    var to = $('#analytics-to');
    
    from.datepicker({
        defaultDate: "-7d",
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd",
        onClose: function( selectedDate ) {
            if(selectedDate === '') {
                from.datepicker( "setDate", "-7d" );            
            }
            to.datepicker( "option", "minDate", selectedDate );
        }
    });
    
    to.datepicker({
        changeMonth: true,
        numberOfMonths: 1,
        dateFormat: "yy-mm-dd",
        onClose: function( selectedDate ) {
            if(selectedDate === '') {
                to.datepicker( "setDate", "-0d" );
            }  
            from.datepicker( "option", "maxDate", selectedDate );
        }
    });
    
};
