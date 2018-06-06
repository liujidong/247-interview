shopinterest.controllers.associate_profile = new function() {
    
    shopinterest.use('modules-password_editor', 'templates-password_editor', function(shopinterest){
        
        var password_editor = new shopinterest.modules.password_editor();
        password_editor.render($('#container'));          
        $('#changepswrd').click(function(){
            password_editor.show();
        });
   
    });
}
