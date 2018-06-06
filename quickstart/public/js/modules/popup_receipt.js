shopinterest.modules.popup_receipt = function() {

    var utils = shopinterest.common.utils;
    var container = null;
    var substitute = utils.substitute;
    var _this = this;
    var order_id = 0;

    _this.render = function(tgt, data) {
        var template = shopinterest.templates.popup_receipt;
        var html = substitute(template, data);
        order_id = parseInt(data.order_id.replace(/.*-0*/,''));
        tgt.html(html);
        container = $('#receipt');
        bindUI();
    };

    _this.show = function(requester) {
        container.reveal({
            closeOnBackgroundClick: false
        });
    };

    var bindUI = function() {
        $("#receipt-close").click(function(){
            window.location.href = "/";
        });
    };
};
