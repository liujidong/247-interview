
shopinterest.modules.shiptrack_lightbox = function() {
    var service_url = shopinterest.constants.base_service_url+'/updateorder';
    var module_name = 'shiptrack_lightbox';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var validate = utils.validate;
    var id = utils.getModuleId(module_name);
    var container = null;
    var provider = null;
    var other_provider = null;
    var track_number = null;
    var arrival_date = null;
    var save = null;
    var select = null;
    var alert_box_error =null;
    var _this = this;
    var orderid = 0;

    _this.render = function(tgt, order_id) {
        var template = shopinterest.templates.shiptrack_lightbox;
        var html = substitute(template, {id: id}); 
        tgt.append(html);
        container = $('#'+id);
        provider = container.find('.provider');
        other_provider = container.find('.other_provider');
        track_number = container.find('.track_number');
        arrival_date = container.find('.arrival_date');        
        save = container.find('.save');
        select = container.find('select');
        alert_box_error = container.find('.alert-box.alert');
        orderid = order_id;
        bindUI();
    };
    
    _this.show = function() {
        alert_box_error.hide();   
        arrival_date.datepicker();
        arrival_date.datepicker("option", "minDate", 0);
        arrival_date.datepicker("option", "maxDate", 90);           
        container.reveal();
    };    
    
    var checkDate = function(value) {
        function getDateDiff(startTime, endTime) {
            var sTime = new Date(startTime);
            var eTime = new Date(endTime);
            if(eTime.getTime() <= sTime.getTime()) {
                return false;
            }
            divNum = 1000 * 3600 * 24;
            return parseInt((eTime.getTime() - sTime.getTime()) / parseInt(divNum));
        }  
        if(value === '') {
            return true;
        }        
        var startTime = new Date();
        if(/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(value) && getDateDiff(startTime, value) && getDateDiff(startTime, value) < 90) {
            return true;
        }
        return false;
    };  
    
    var bindUI = function() {
        save.bind('click', function(e) {
            e.preventDefault();
            var provider_var = $.trim(provider.val());
            var other_provider_var = $.trim(other_provider.val());
            var track_number_var = $.trim(track_number.val());
            var arrival_date_var = $.trim(arrival_date.val());
            if(!checkDate(arrival_date_var)) {
                alert_box_error.html('Your expected arrival date is invalid');
                alert_box_error.show();   
                return;
            }
            $.post(service_url, {order_id : orderid, provider : provider_var, other_provider : other_provider_var, track_number : track_number_var, arrival_date : arrival_date_var}, function(response) {
                var response_obj = $.parseJSON(response);
                if(response_obj.status === 'success') {
                    container.trigger('fullfill:remove');
                    container.trigger('reveal:close');
                }
            });                  
            
        });
        
        select.bind('change', function() {
            var provider = $(this).val();  
            other_provider.val('');
            other_provider.hide();
            if(provider === 'Other') {
                other_provider.show();
            }
        });
        
    };
};


