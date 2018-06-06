shopinterest.controllers.admin_abtests = new function() {
    
    var bind_delete_test = function() {
        $('.delete_test').unbind('click');
        $('.delete_test').click(function(e) {
            var _this = $(this);
            var item = _this.closest('tr');
            var name = item.find('td:first').html();
            // make an AJAX call to delete this item
            $.post('/api/delete-abtest', {
                'name': name
            }, function(response) {
                var response_obj = $.parseJSON(response);
                if(response_obj.status === 'success') {
                    _this.closest('tr').remove();
                }
            });
            
        });
    };
    
    bind_delete_test();
    
    var append_test = function(name, num_shards) {
        var template = "<tr>\n\
                    <td>{{test_name}}</td>\n\
                    <td>{{test_shards}}</td>\n\
                    <td><button style=\"margin-left:20%;\" class=\"tiny button radius alert delete_test\">Delete</button></td>\n\
                    </tr>";
        var substitute = shopinterest.common.utils.substitute;
        var html = substitute(template, {
            test_name: name,
            test_shards: num_shards
        });
        $('#test_lists').append(html);
        bind_delete_test();
    };
    
    var add_test = function(name, num_shards) {
        
        // make an AJAX call to add this item
        $.post('/api/add-abtest', {
            'name': name,
            'num_shards': num_shards
        }, function(response) {
            var response_obj = $.parseJSON(response);
            if(response_obj.status === 'success') {
                append_test(name, num_shards);
            }
        });
        
        
    };
    
    var bind_add_test = function() {
        $('#add_test').unbind('click');
        $('#add_test').click(function(e) {
            add_test($('#tname').val(), $('#shardsnb').val());
        });
    };
    
    bind_add_test();
    
    // get the existing abtests through Ajax API call
    $.getJSON('/api/get-abtests', function(response) {
        $.each(response, function(index, abtest) {
            append_test(abtest.name, abtest.num_shards);
        });
    });
    
    
    
    
    
    
};