shopinterest.controllers.selling_subscription = new function() {    
    
    var subscription_form = $('#subscription-form');
    var subscribe_button = $('#subscribe-button');
    var inprocess_button = $('#inprocess-button');
    var manage_button = $('#manage-button');
    
    subscription_form.submit(function(e) {
        e.preventDefault();
        
        $.post('/api/inprocess', {event: 'subscription'}, function() {
            subscription_form.unbind('submit');
            subscribe_button.trigger('click');
        });
        
    });
    
    setInterval(function() {
        $.post('/api/subscribed', function(response) {
            if((response === 'subscriber' && manage_button.length === 0) ||
                response === 'non-subscriber' && subscribe_button.length === 0 ||
                response === 'in-process' && inprocess_button === 0) {
                location.reload(true);
            }
        });
    },500);

    
    
    
};
