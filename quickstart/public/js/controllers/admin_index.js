shopinterest.controllers.admin_index = new function() {
    
    $('select.admin_tools').change(function(e) {
        var value = $(this).val();
        window.location.href = '?table_object='+value;
    });
    
    var query = $.query.get('table_object');
    var table_object = query==''?$('select.admin_tools option:first').attr('value'):query;
    
    var datatable = new shopinterest.modules.datatable($('#tgt_datatable'), table_object);
    
    
    
};
