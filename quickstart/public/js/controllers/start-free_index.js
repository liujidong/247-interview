

shopinterest.controllers['start-free_index'] = new function() {
    


    
    
    /* ga tracking */
    if(typeof _gaq !== "undefined") {
        var categories = shopinterest.constants.categories;
        $('#getstartedbutton').click(function(e) {
            _gaq.push(['_trackEvent', categories.signup, 'click', 'Get Started (Start Free)']);
        });
    }
    /* end ga tracking */
    
};







