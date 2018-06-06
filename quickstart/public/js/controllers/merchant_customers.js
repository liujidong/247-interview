shopinterest.controllers.merchant_customers = new function() {
    
    bind_add_contact();

    bind_delete_contact();

    shopinterest.use('modules-email_lightbox', 'templates-email_lightbox', function(shopinterest) {
        
        var email_lightbox = new shopinterest.modules.email_lightbox();
        email_lightbox.render($('#container'));
        
        $('.emailto').click(function(e) {
            e.preventDefault();
            email_lightbox.show($(this).html(), $(this).closest('.contact_list_tr').find('.emailtoname').html());
        });
            
        $('.select_all').click(function(){
            if(this.checked) {
                $(':checkbox').each(function() {
                    this.checked = true;                        
                });                
            } else {
                $(':checkbox').each(function() {
                    this.checked = false;                        
                });             
            }
        });    

        $('.send_to_selected').click(function(e){
            e.preventDefault();   

            var emailtoname = '';
            var emailto = '';
            $('.select_node:checkbox').each(function() {
                if(this.checked) {
                    var _this = $(this);
                    var container = _this.closest('.contact_list_tr');
                    emailtoname += container.find('.emailtoname').html() + ',';
                    emailto += container.find('.emailto').html() + ',';
                }                  
            });

            emailtoname = emailtoname.substring(0,emailtoname.length-1);
            emailto = emailto.substring(0,emailto.length-1);  
            if(emailto.length === 0) {
                alert('You need to at least select one contact!');
                return;
            }
            email_lightbox.show(emailto, emailtoname);            

        });

    });
};
