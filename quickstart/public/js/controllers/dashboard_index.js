
shopinterest.controllers.dashboard_index = (function() {

    var utils = shopinterest.common.utils,
    $dashboard = $('#dashboard-home');

    // expand or compress dashboard item
    $dashboard.find('.compress').click(function() {
        var $item = $(this).parents('.dashboard-item').find('.item-list');

        if ( $(this).find('.fi-arrows-compress')[0] ) {
            $item.slideUp();
            $(this).find('.fi-arrows-compress')
            .removeClass('fi-arrows-compress')
            .addClass('fi-arrows-expand');
        } else {
            $item.slideDown();
            $(this).find('.fi-arrows-expand')
            .removeClass('fi-arrows-expand')
            .addClass('fi-arrows-compress');
        }

        return false;
    });

    $(".launch-store").click(function(){
        var action = $(this).hasClass("unlaunch") ? "false" : "true";
        $.post('/api/launch-store', {
            launch: action
        }, function(response) {
                response = $.parseJSON(response);
                if(response.status === 'success') {
                    window.location.reload();
                } else {
                    alert("Can not change your store status!");
                }
        });
    });

})();
