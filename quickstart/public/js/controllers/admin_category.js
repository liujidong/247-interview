shopinterest.controllers.admin_category = new function() {
    $('#add_category').click(function(e) {
        $('.alert-box.error').hide();
        var category = $('#category_value').val();
        if(category != '') {
            $.post('/api/adminsavecategory', {category: category}, function(response) {
                var response_obj = $.parseJSON(response);
                if(response_obj.status === 'success') {
                    window.location.reload();
                    return;
                } else {
                    $('.alert-box.error').show();
                }
            });
        } else {
            $('.alert-box.error').show();
        }
    });

    var bind_delete_category = function() {
        $('.alert-box.error').hide();
        var delete_category = $('.delete_category');
        delete_category.unbind('click');
        delete_category.on('click', function() {
            var cont = confirm('Do you really want to delete this category?');
            if(!cont){
                return true;
            }
            var id = $(this).parent().parent().children().first().attr('data-id');
            var _this = $(this);
            $.post('/api/admindeletecategory', {id: id}, function(response) {
                response = $.parseJSON(response);
                if(response.status === 'success') {
                    var tr = _this.closest('tr');
                    var need_not_reload = $('.up_category', tr).length && $('.down_category', tr).length;
                    tr.remove();
                    if(!need_not_reload){
                        window.location.reload();
                    }
                } else {
                    $('.alert-box.error').show();
                }
            });
        });
    };
    bind_delete_category();

    var item_start = 0;
    var item_stop = 0;

    $(".category-item").sortable({
        
        start: function(event, ui){
            var item = ui.item;
            item_start = $('.category-item tr').index(item);
        },
        
        stop: function(event, ui) {
            var item = ui.item;
            item_stop = $('.category-item tr').index(item);
            
            var _this = $(this);
            var category_list = _this.find('tr');

            var categories = [];
            if(item_start > item_stop) {
                var temp = item_start;
                item_start = item_stop;
                item_stop = temp;
            }

            for(var i = item_start; i <= item_stop; i++ ) {
                var category_obj = {};

                var item = category_list[i];
                category_obj.id = $(item).find('td').attr('data-id');
                category_obj.rank = i;
                categories.push(category_obj);
            }

            $.post('/api/adminexchangecategoryrank', {categories: JSON.stringify(categories)}, function(response){});
        }
    });
};
