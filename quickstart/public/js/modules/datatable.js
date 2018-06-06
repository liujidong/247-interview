shopinterest.modules.datatable = function(tgt, table_object) {

    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var inline_substitute = utils.inline_substitute;
    var conditions = {}; // {elem0_xpath: condition0, elem1_xpath: condition1 ...}
    var post_fields = {table_object: table_object, page: $.query.get('page').toString() || 1,  action_params: {}}; // {table_object: xxx, action: create/update/search, action_params: {field1: value1, field2: value2, ..., conditions: {...}, condition_string: xxx}}
    var create_form = $('#datatable-view-create_form');
    var update_form = $('#datatable-view-update_form');
    var delete_form = $('#datatable-view-delete_form');
    var email_lightbox = null;
    var model = {rows: {}, total_rows: 0, current_page: 0, views: {}};

    var init = function() {

        utils.post('/api/datatable', post_fields, function(response) {
            model = response.data;
        });
        delete post_fields.page;
        if($('.send_email_link').length > 0) {
            shopinterest.use('modules-email_lightbox', function(){
                email_lightbox = new shopinterest.modules.email_lightbox();
                email_lightbox.render($('#tgt_email_lightbox'));
            });
        }
    };

    init();

    var clear_form = function(ctn){
        $(ctn).find('form').find('input[type=text]').val('');
        $(ctn).find('form').find('input[type=number]').val('');
        $(ctn).find('form').find('input[type=email]').val('');
    };

    tgt.find('*[datatable-trigger]').click(function(e) {

        var _this = $(this);
        var current_form = _this.parents('form');
        var error_indicator = current_form.find('.data-abide-input-container.error:visible');
        if(error_indicator.length !== 0) {
            utils.alertBox({
                container: current_form.find('.alert-field'),
                type: 'error',
                autohide: true,
                timeout: 1000
            });
            return;
        }
        var elem = _this.get(0);
        var action = _this.attr('datatable-action');

        // check if the action is search/create/update
        if(action === 'search' || action === 'create' || action === 'update' || action === 'delete' || action === 'send_email') {
            post_fields.action = action;
            post_fields.render = ['table_body'];
            if(action === 'update' || action === 'delete') {
                post_fields.action_params.row_id = _this.parents('tr').attr('row-id');
            }
            if(action === 'search') {
                post_fields.render = ['container'];
            }
            post_fields.action_params = $.extend(post_fields.action_params, utils.get_post_data(current_form));
        } else if(action === 'cancel') {
            delete_form.foundation('reveal', 'close');
            return;
        } else {
            var datatable_condition = _this.attr('datatable-condition');

            if(!datatable_condition) {
                var forms = _this.parents('form');
                if(forms.length !== 0) {
                    var form = $(forms[0]);
                    conditions = utils.get_datatable_conditions(form);
                }
            } else {
                conditions[utils.getXPath(elem)] = datatable_condition;
            }

            // object to array
            var conditions_array = $.map(conditions, function(value, index) {
                return [value];
            });
            var condition_string = conditions_array.join('&');
            post_fields = $.extend(post_fields, {conditions: conditions, condition_string: condition_string});
        }

        console.log(post_fields);
        // ready to post to the server
        utils.post('/api/datatable', post_fields, function(response) {
            console.log(response);
            if(response.status === 'success') {
                var views = response.data.views;
                model = response.data;
                if(action === 'update' || action === 'create' || action === 'delete') {
                    $.each(views, function(index, view) {
                        var selector = '#datatable-view-'+index;
                        $(selector).html(view);

                        bind_update_link();
                        bind_delete_link();
                    });
                }

                if(action === 'search') {
                    $('#datatable-view-container').html(views.container);
                    bind_update_link();
                }
                utils.alertBox({
                    container: current_form.find('.alert-field'),
                    type: 'success',
                    autohide: true,
                    timeout: 1000,
                    cb: function() {
                        clear_form(create_form);
                        clear_form(update_form);
                        clear_form(delete_form);
                        update_form.foundation('reveal', 'close');
                        delete_form.foundation('reveal', 'close');
                        email_lightbox && email_lightbox.close();
                    }
                });
            } else {
                utils.alertBox({
                    container: current_form.find('.alert-field'),
                    type: 'error',
                    autohide: true,
                    timeout: 2000,
                    message: response.errors.msg || ''
                });
            }
        });
    });

    var bind_update_link = function() {
        $('.update_link').click(function(e) {
            e.preventDefault();
            var _this = $(this);
            var row_id = _this.closest('tr').attr('row-id');
            inline_substitute(update_form, model.rows[row_id]);
            update_form.foundation('reveal', 'open');
        });
    };
    bind_update_link();

    var bind_delete_link = function() {
        $('.delete_link').click(function(e) {
            e.preventDefault();
            var _this = $(this);
            var row_id = _this.closest('tr').attr('row-id');
            inline_substitute(delete_form, model.rows[row_id]);
            delete_form.foundation('reveal', 'open');
        });
    };
    bind_delete_link();


    $('.create_link').click(function(e) {
        create_form.foundation('reveal', 'open');
    });

    // reveal events handler

    update_form.on('closed', function(e) {

        $('*[old-text]').each(function(index) {
            var _this = $(this);
            _this.html(_this.attr('old-text'));
            _this.removeAttr('old-text');
        });

        $('*[old-value]').each(function(index) {
            var _this = $(this);
            _this.val(_this.attr('old-value'));
            _this.removeAttr('old-value');
        });

    });

    create_form.on('closed', function(e) {
        //console.log('create form closed');
    });

    $('.select_all').click(function(){
        var is_checked = this.checked;
        var form_checkbox = $(this).parents('table').find(':checkbox');
        form_checkbox.each(function() {
            this.checked = is_checked;
        });
    });

    $('.send_email_link').click(function(e){
        e.preventDefault();

        var emailtoname = '';
        var emailto = '';
        var row_ids = '';
        $('#datatable-view-table').find('.select_node:checked').each(function() {
            if(this.checked) {
                var _this = $(this);
                var _tr = _this.closest('tr');

                emailtoname += _tr.find('.name').html() + ',';
                emailto += _tr.find('.email').html() + ',';
                row_ids += _tr.attr('row-id') + ',';
            }
        });

        emailtoname = emailtoname.substring(0,emailtoname.length-1);
        emailto = emailto.substring(0,emailto.length-1);
        row_ids = row_ids.substring(0,row_ids.length-1);
        if(emailto.length === 0) {
            alert('You need to at least select one contact!');
            return;
        }
        email_lightbox.show(emailto, emailtoname, row_ids);
    });
};
