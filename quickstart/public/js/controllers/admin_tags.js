shopinterest.controllers.admin_category = new function() {
    $('#add_category').click(function(e) {
            $('.alert-box').hide();
            var category = $('#category_value').val();
            if(category != '' && category.length<50) {
                $.post('/api/adminsavetag', {category: category}, function(response) {
                    var response_obj = $.parseJSON(response);
                    if(response_obj.status === 'success') {
                        $('tr.add_category').before('<tr><td>'+category+'</td><td><a class="small secondary button radius delete_category">Delete</a></td></tr>');
                        $('#category_value').val("");
                        delete_category();
                    }
                });
            } else {
                $('.alert-box.error').show();
            }
        });
    
    var delete_category = function() {
        var delete_category = $('.delete_category');
        delete_category.unbind('click');
        delete_category.on('click', function() {
            var cont = confirm('Do you really want to delete this category?');
            if(!cont){         
                return true; 
            }
            var category = $(this).parent().siblings('td').html();
            var _this = $(this);
            $.post('/api/admindeletetag', {category: category}, function(response) {
                if(response === 'success') {
                    _this.closest('tr').remove();
                }
            });
        });
    }
    delete_category();
}
