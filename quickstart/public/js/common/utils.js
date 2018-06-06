
shopinterest.common.utils = {
    get_controller_action: function() {
        return $('body').attr('id').split('_');
    },
    getPageControllerName: function() {
        var controller_action = this.get_controller_action();
        var controller = controller_action[0];
        var action = controller_action[1];
        var pageControllerName = 'controllers-'+controller+'_'+action;
        return pageControllerName;
    },
    getPageControllerObjectName: function() {
        var path = window.location.pathname;
        var parts = path.split('/');
        var controller = 'index', action = 'index';
        if(parts[1]) controller = parts[1];
        if(parts[2]) action = parts[2];
        var pageControllerObjectName = controller+'_'+action;
        return pageControllerObjectName;
    },
    getSubdomainType: function() {
        return $('#subdomain_type').val();
    },
    isUser: function() {
        return !($('#user_id').val() === "0");
    },
    isAssociate: function() {
        return !($('#associate_id').val() === "0");
    },
    isMerchant: function() {
        return !($('#merchant_id').val() === "0");
    },
    isAnonymous: function() {
        return $('#user_id').val() === "0";
    },
    getUserId: function() {
        return parseInt($('#user_id').val());
    },
    getMerchantId: function() {
        return parseInt($('#merchant_id').val());
    },
    getAssociateId: function() {
        return parseInt($('#associate_id').val());
    },
    isStoreLaunched: function() {
        return $('#store_status').val() === "2";
    },
    isActiveAssociate: function() {
        return $('#associate_status').val() === "2";
    },
    getPathFromURL: function(url) {

    },
    substitute: function(template, dataObj, partials, delimiter) {
        var site_version = $('body').attr('siteversion');
        dataObj.site_v1 = (site_version == 1);
        dataObj.site_v2 = (site_version == 2);
        dataObj.site_v3 = (site_version == 3);
        if(delimiter) {
            var prefix = '{{='+delimiter+'=}}';
            var parts = delimiter.split(' ');
            if(parts.length === 2) {
                var suffix = parts[0]+'={{ }}='+parts[1];
            }
        }
        return prefix&&suffix ? Mustache.to_html(prefix+template+suffix, dataObj, partials) : Mustache.to_html(template, dataObj, partials);
    },
    setCookie: function(name,value,days) {
        if (days) {
            var date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            var expires = "; expires="+date.toGMTString();
        }
        else var expires = "";
        document.cookie = name+"="+value+expires+"; path=/";
    },
    getCookie: function (name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    },
    deleteCookie: function (name) {
        setCookie(name,"",-1);
    },
    getModuleId: function(module_name) {
        return module_name+'_'+(new Date()).getTime()+String(Math.floor((Math.random()*1000)+1));
    },
    bindInfiniteScrolling: function(button) {
        $(window).scroll(function(){
            if($(document).height() - $(document).scrollTop() - $(window).height() < 500){
                button.click();
            }
        });
    },
    init_commission_validate: function() {
        function is_digit(value){
            return /^[+]?[0-9]+$/.test(value);
        }
        function commission_validate(product_commission, product_price) {
            if(isNaN(product_commission) || isNaN(product_price) || !is_digit(product_commission) || product_commission*product_price/100 < 1 || product_commission > 60 )   return false;
            return true;
        }
        var commission_field = $('.text_commission');
        if(commission_field.length !== 0) {
            commission_field.unbind('blur');
            commission_field.blur(function(e) {
                elem_obj = $(e.currentTarget);
                var product_commission = elem_obj.val();
                var product_price = elem_obj.closest('.pinitem').find('.text_price').val();
                var errorbox = elem_obj.closest('.pinitem').find('.commisson_error');
                if(errorbox) {
                    errorbox.remove();
                }
                if(!commission_validate(product_commission, product_price)) {
                    elem_obj.val(0);
                    elem_obj.focus(function(){
                        elem_obj.css("border", "2px solid #800 !important");
                    });
                    elem_obj.after('<span class="commisson_error" style="color: red;">Invalid product commission.</span>');
                }
            });
        }
    },
    init_shipping_widget: function() {
        function generate_product_shipping_fee(input_elem, store_shipping, store_additional_shipping) {
            var extra_shipping = isNaN(parseFloat(input_elem.val()))?0:parseFloat(input_elem.val());
            var product_total_shipping = store_shipping + extra_shipping;
            var product_additional_shipping = store_additional_shipping + extra_shipping;
            input_elem.parent().siblings('.calc').find('.product_total_shipping').html($("#currency_symbol").val()+product_total_shipping);
            input_elem.parent().siblings('.calc').find('.product_additional_shipping').html($("#currency_symbol").val()+product_additional_shipping);
        }
        var store_shipping_input = $('#store_shipping');
        var store_additional_shipping_input = $('#store_additional_shipping');
        var product_shipping_input = $('.text_shipping');

        if(store_shipping_input.length !==0 && store_additional_shipping_input !== 0 &&
            product_shipping_input.length !== 0) {
            var store_shipping = parseFloat(store_shipping_input.val());
            var store_additional_shipping = parseFloat(store_additional_shipping_input.val());
            product_shipping_input.each(function(index) {
                generate_product_shipping_fee($(this), store_shipping, store_additional_shipping);

                // bind keyup event handler
                $(this).unbind('keyup');
                $(this).keyup(function(e) {
                    generate_product_shipping_fee($(this), store_shipping, store_additional_shipping);
                });

            });
        }
    },

    init_masonry: function() {
        /* activate mason to re-arrange product boxes */
        $('#loading').hide();
        var origin_data = $('#masonholder .cart_list01_content');

        var load_masonry_page = function(data, page) {
            var cnt_per_page = 6;
            var index = page * cnt_per_page + 0;
            var end = index + cnt_per_page;
            var count = 0;

            if(index >= data.length) return;

            for(; index < end; index++) {
                var new_elem = $(data[index]);
                new_elem.imagesLoaded(function() {
                    count++;
                    this.fadeIn("slow");
                    if(count >= cnt_per_page) {
                        $('#masonholder').masonry({
                            // options
                            itemSelector : '#masonholder .cart_list01_content',
                            gutterWidth: 12,
                            isFitWidth: true
                        });
                        load_masonry_page(data, page+1);
                    }
                });
            }
        };

        load_masonry_page(origin_data, 0);
    },
    show_pagination: function() {
        var pagination = $('#prevnextnav');
        if(pagination.css('display') === 'none') {
            $('#prevnextnav').show();
        }
    },
    toggle_code_section: function() {
        $('.get_code').click(function (e) {
            var get_code_button = $(this);
            var product_item_section = $($(this).closest('.product_item'));
            var code_section = $(product_item_section.children('.thecode'));

            code_section.toggle();
        });
    },
    change_password : function() {
        $('#changepswrd').click(function() {
            $('#myModal').reveal();
            var current_password_elem = $('#current_password');
            var new_password_elem = $('#new_password');
            var confirm_password_elem = $('#confirm_password');
            $('#save_password').unbind('click');
            $('#save_password').click(function(e) {
                var current_password = $.trim(current_password_elem.val());
                var new_password = $.trim(new_password_elem.val());
                var confirm_password = $.trim(confirm_password_elem.val());

                if(current_password === '' || new_password === '' || confirm_password === '') {
                    $('#save_password_error_box').show();
                } else {
                    $.post('/api/updatepassword', {
                        current_password: current_password,
                        new_password: new_password,
                        confirm_password: confirm_password
                    }, function(response) {
                        if(response === 'success') {
                            $('#save_password_error_box').hide();
                            current_password_elem.val('');
                            new_password_elem.val('');
                            confirm_password_elem.val('');
                            $('.close-reveal-modal').trigger('click');
                            $('#profile').prepend('<div class="alert-box success row">Your password gets updated successfully.<a href="" class="close">×</a></div>');
                        } else {
                            $('#save_password_error_box').show();
                        }
                    });
                }
            });

            $('.close-reveal-modal').click(function(e) {
                $('#save_password_error_box').hide();
                current_password_elem.val('');
                new_password_elem.val('');
                confirm_password_elem.val('');
            });

        });
    },
    uniqid : function() {
        function S4() {
            return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
        }
        return (S4()+S4()+S4());
    },
    get_product_image_upload_dst : function(store_id, uniqid, picture_id, image_type) {

        var get_product_image_name = function(uniqid, salt, image_type) {
            return (uniqid+'_'+salt+'_'+image_type+'.jpg');
        };
        var file_name = get_product_image_name(uniqid, picture_id, image_type);
        return ('/store/'+store_id+'/'+file_name);
    },
    get_product_image_upload_dst2 : function(store_id, uniqid, image_type) {

        var get_product_image_name = function(uniqid, image_type) {
            return (uniqid+'_'+image_type+'.jpg');
        };
        var file_name = get_product_image_name(uniqid, image_type);
        return '/stores/'+store_id+'/'+file_name;
    },
    get_csv_upload_dst : function(store_id){
        var get_current_time = function() {
            var now = new Date();
            return now.getFullYear()+'-'+(now.getMonth()+1)+'-'+now.getDay() + '-'+now.getHours()+':'+now.getMinutes()+':'+now.getSeconds();
        };
        var file_name = get_current_time() + '.csv';
        return '/stores/'+store_id+'/'+ file_name;
    },
    get_pinterest_image_urls : function(url) {
        var pattern = /\d{2,3}x\d{0,2}/;
        var urls = [];
        urls.push(url.replace(pattern, '45x45'));
        urls.push(url.replace(pattern, '70x'));
        urls.push(url.replace(pattern, '192x'));
        urls.push(url.replace(pattern, '236x'));
        urls.push(url.replace(pattern, '550x'));
        urls.push(url.replace(pattern, '736x'));

        return urls;
    },
    show_message : function(type, tip_container, message, show_time) {

        if(message === '') {
            return;
        }

        tip_container.children().remove();
        tip_container.show();
        shopinterest.use('templates-green_tips', 'templates-red_tips', 'templates-spinner_tips', function() {
            var template = '';
            if(type === 'success') {
                template = shopinterest.templates.green_tips;
            } else if(type === 'spinner'){
                template = shopinterest.templates.spinner_tips;
            } else {
                template = shopinterest.templates.red_tips;
            }
            var html = shopinterest.common.utils.substitute(template, {message: message});
            tip_container.append(html);
            if(!show_time)show_time = 3000;
            if(show_time<=0) return;
            setTimeout(function() {
                tip_container.fadeOut("slow", function () {
                    tip_container.hide();
                });
            }, show_time);
        });
    },

    /**
     * alertBox for submiting response
     * @param  {String} [type] [required, args is 'success' || 'warning' || 'info' || 'error']
     * @param  {String} [message] [alert message]
     * @param  {Object} [container] [required, where the alertBox is appended]
     * @param  {Boolean} [autohide] [default is false, auto hide alert box or not]
     * @param  {Number} [timeout] [default 3s]
     * @params {Function} [cb]
     */
    alertBox : function(args) {
        var html, msg, type,
        defaultTimout = 3000;

        msg = args.message;
        type = args.type;
        cb = args.cb;

        if ( !msg ) {
            if ( type === 'success' ) {
                msg = 'Save succeeded.';
            } else if ( type === 'error' ) {
                msg = 'Save failed. Please fix errors in data and retry';
            } else {
                msg = 'Save with warnings.';
            }
        }

        html = '<div data-alert class="alert-box radius ' + type + '">\n' +
            msg + '<a href="javascript:;" class="close">&times;</a>\n' +
            '</div>';

        args.container.html(html);

        if ( args.autohide ) {
            setTimeout(function() {
                args.container.find('.alert-box').fadeOut('400');
                if(cb) {
                    cb();
                }
            }, args.timeout || defaultTimout);
        } else if(cb) {
            cb();
        }

        args.container.find('.close').click(function() {
            args.container.find('.alert-box').fadeOut('200');
            return false;
        });
    },

    spinner : {

        /**
         * Show spinner
         * @param {Boolean} [mask] [default is true]
         * @param {string} [msg] [display message when showing spinner]
         * @param {Object} [position] [where the spinner would be show up,
         *                            default is on middle of the screen]
         */
        show : function(args) {

            var $spinner, $mask, scrollTop,
            spinnerH, spinnerW, screenH, screenW,
            self = this,
            settings = {
                mask: true,
                position: {
                    top: 0,
                    left: 0
                }
            };

            args = args || {};
            $.extend(settings, args);
            $spinner = $('#spinner');
            $mask = $('#spinner-mask');

            if ( settings.mask ) {
                if ( !$mask[0] ) {
                    $('body').append('<div id="spinner-mask" />');
                    $mask = $('#spinner-mask');
                }

                $mask.show();
            }

            if ( !$spinner[0] ) {
                var spinnerHtml = args.msg ?
                    '<div id="spinner" class="reveal-modal has-msg">\n' +
                    '<img src="/img/spinner.gif" width="24">\n' +
                    args.msg + '\n</div>' :
                    '<div id="spinner" />';

                $('body').append(spinnerHtml);
                $spinner = $('#spinner');
            } else {
                if ( args.msg ) {
                    $spinner.attr('class', 'reveal-modal has-msg')
                    .html('<img src="/img/spinner.gif" width="24">\n' + args.msg);
                } else {
                    $spinner.attr('class', '').html('');
                }
            }

            $spinner.show();
            spinnerW = $spinner.outerWidth();
            spinnerH = $spinner.outerHeight();

            if ( window.innerHeight ) {
                // except IE
                screenH = window.innerHeight;
                screenW = window.innerWidth;
            } else if ( document.documentElement && document.documentElement.clientHeight ) {
                // IE
                screenH = document.documentElement.clientHeight;
                screenW = document.documentElement.clientWidth;
            } else {
                // other
                screenH = document.body.clientHeight;
                screenW = document.body.clientWidth;
            }

            scrollTop = document.documentElement.scrollTop || document.body.scrollTop;

            if ( spinnerH > screenH ) {
                $spinner.css('top', settings.position.top);
            } else {
                if ( settings.position.top !== 0 ) {
                    $spinner.css('top', settings.position.top);
                } else {
                    $spinner.css({
                        top: scrollTop + ( screenH / 2 ) + 'px',
                        marginTop: '-' + ( spinnerH / 2 ) + 'px'
                    });
                }
            }

            if ( spinnerW > screenW ) {
                $spinner.css('left', 0);
            } else {
                $spinner.css({
                    left: ( screenW / 2 ) + 'px',
                    marginLeft: '-' + ( spinnerW / 2 ) + 'px'
                });
            }

            // self.startLoad();
            // $('body').one('click', function(e) {
            //     if ( e.target.id !== 'spinner' ) {
            //         self.close();
            //     }
            // });
        },

        close : function() {
            // this.stopLoad();
            $('#spinner').hide();
            $('#spinner-mask').hide();
        },

        startLoad: function() {
            var self = this,
            cSpeed = 9,
            FPS,
            $spinner = $('#spinner');

            self.cXpos = 0;
            self.cFrameWidth = $spinner.outerWidth();
            self.cIndex = 0;
            self.cTotalFrames = 18;
            self.SECONDS_BETWEEN_FRAMES = 0;
            self.cPreloaderTimeout = false;

            FPS = Math.round(100 / cSpeed);
            self.SECONDS_BETWEEN_FRAMES = 1 / FPS;

            self.cPreloaderTimeout = setTimeout(function() {
                self.continueAnimation();
            }, self.SECONDS_BETWEEN_FRAMES / 1000);

        },

        continueAnimation: function() {
            var self = this,
            $spinner = $('#spinner');

            self.cXpos += self.cFrameWidth;
            self.cIndex += 1;

            //if our cIndex is higher than our total number of frames, we're at the end and should restart
            if ( self.cIndex >= self.cTotalFrames) {
                self.cXpos =0;
                self.cIndex=0;
            }

            if( $spinner[0] ) {
                $spinner.css('backgroundPosition', (-self.cXpos) + 'px 0');
            }

            self.cPreloaderTimeout = setTimeout(function() {
                self.continueAnimation();
            }, self.SECONDS_BETWEEN_FRAMES * 1000);
        },

        stopLoad: function() {
            clearTimeout(this.cPreloaderTimeout);
            this.cPreloaderTimeout = false;
        }

    },

    validate : function(field, value) {

        function is_date(data){
            return /^\d{1,2}\/\d{1,2}\/\d{4}$/.test(data);
        }

        function is_digit(value){
            return /^[+]?[0-9]+$/.test(value);
        }

        function is_integer(value) {
            return  Math.floor(value) == value && $.isNumeric(value);
        }

        function commission_validate(product_commission, product_price) {
            if(isNaN(product_commission) || isNaN(product_price) || !is_digit(product_commission)
                    || (product_commission!= 0 && product_commission*product_price/100 < 1) || product_commission > 60 )   return false;
            return true;
        }

        function is_valid_url(value) {
            return /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/.test(value);
        }

        if(field ===  'date') {
            if(is_date(value)) {
                return false;
            }
            return true;
        }

        if(field === 'name' || field === 'description') {
            if($.trim(value) === "") {
                return false;
            }
            return true;
        }

        if(field === 'quantity') {
            if(value < 0 || !is_integer(value)) {
                return false;
            }
            return true;
        }

        if(field === 'commission') {
            var commission = value.commission;
            var price = value.price;
            return commission_validate(commission, price);
        }

        if(field === 'price' || field === 'shipping') {
            if(!$.isNumeric(value)) {
                return false;
            }
            return true;
        }

        if(field === 'global_category_id') {
            if(value == 0) {
                return false;
            }
            return true;
        }

        if(field === 'purchase_url') {
            if(is_valid_url(value)) {
                return true;
            } else {
                return false;
            }
        }

    },

    convert : function(url, options) {
        return (url+'/convert?'+$.param(options));
    },

    get_post_data : function(container) {
        var data = {};
        var fields = this.get_form_fields(container);

        $.each(fields, function(i, item){
            item = $(item);
            if(item.attr('name')) {
                if(item.is(':checkbox')) {
                    data[item.attr('name')] = item.prop('checked') ? 1 : 0;
                } else {
                    data[item.attr('name')] = item.val();
                }
            }
        });
        return data;
    },

    get_datatable_conditions : function(container) {
        var conditions = {};
        var fields = this.get_form_fields(container);
        var _this = this;

        $.each(fields, function(i, item){
            item = $(item);
            if(item.attr('name')) {
                conditions[_this.getXPath(item.get(0))] = _this.substitute(item.attr('datatable-condition'), {value: item.val()}, null, '<% %>');
            }
        });
        return conditions;
    },

    get_form_fields : function(container) {
        var form_fields = [];

        if(!container) {
            return form_fields;
        }

        var input_fields = container.find('input:not([type=submit]):not([type=radio]), input:checked');
        var select_fields = container.find('select');
        var textarea_fields = container.find('textarea');

        $.merge(form_fields, input_fields);
        $.merge(form_fields, select_fields);
        $.merge(form_fields, textarea_fields);

        return form_fields;
    },

    post : function(service_url, data, cb){
        if($.isFunction(data)) {
            cb = data;
            data = {};
        }
        $.post(service_url, data, function(response) {
            var response_obj = $.parseJSON(response);
            cb(response_obj);
        });
    },
    getXPath: function( element ){
        var xpath = '';
        for ( ; element && element.nodeType == 1; element = element.parentNode )
        {
            var id = $(element.parentNode).children(element.tagName).index(element) + 1;
            id > 1 ? (id = '[' + id + ']') : (id = '');
            xpath = '/' + element.tagName.toLowerCase() + id + xpath;
        }
        return xpath;
    },
    inline_substitute: function(container, data, delimiter) {
        var htmlDecode = function (value) {
            if (value) {
                return $('<div />').html(value).text();
            } else {
                return '';
            }
        };

        if(!delimiter) {
            var delimiter = '<% %>';
        }
        if(!data) {
            data = {};
        }

        var open_tag_close_tag = delimiter.split(' ');
        var open_tag = open_tag_close_tag[0];
        var close_tag = open_tag_close_tag[1];

        var elements = container.find('*:not(:has(*)):contains("'+open_tag+'"):contains("'+close_tag+'")');
        if(elements.length > 0) {
            elements.each(function(index) {
                var element = $(this);
                var innerHTML = htmlDecode(element.html());
                var innerHTML_new = '';
                var matches = innerHTML.match(/<%\w+%>/g);
                if(matches) {
                    for(var i=0;i<matches.length;i++) {
                        var variable = matches[i];
                        var key = matches[i].replace('<%', '').replace('%>', '');
                        var replacement = data[key];
                        if(replacement !== undefined) {
                            innerHTML_new = innerHTML.replace(variable, replacement);
                        }
                    }
                    element.html(innerHTML_new);
                    element.attr('old-text', innerHTML);
                }

            });
        }


        elements =container.find('*[value^="'+open_tag+'"][value$="'+close_tag+'"]');
        if(elements.length > 0) {
            elements.each(function(index) {
                var element = $(this);
                var value = element.attr('value');
                var value_new = '';
                var matches = value.match(/<%\w+%>/g);
                if(matches) {
                    for(var i=0;i<matches.length;i++) {
                        var variable = matches[i];
                        var key = matches[i].replace('<%', '').replace('%>', '');
                        var replacement = data[key];
                        if(replacement !== undefined) {
                            value_new = value.replace(variable, replacement);
                        }
                    }
                    element.val(value_new);
                    if(value_new === '1' && element.is(":checkbox")) {
                        element.attr('checked', 'checked');
                    }
                    element.attr('old-value', value);
                }


            });
        }
    },
    htmlEncode: function (value){
        if (value) {
            return $('<div />').text(value).html();
        } else {
            return '';
        }
    },

    htmlDecode: function (value) {
        if (value) {
            return $('<div />').html(value).text();
        } else {
            return '';
        }
    }

};
