shopinterest.controllers.profile_index = new function() {
    
    var utils = shopinterest.common.utils,
        get_upload_dst = utils.get_product_image_upload_dst2,
        store_id = $('#my_store_id').val(),
        uniqid = utils.uniqid;

    var old_currency = $("#store_currency").val();
    $("#getstartedbutton").click(function(e){
        var new_currency = $("#store_currency").val();
        if(new_currency === old_currency) return;
        var c = confirm("You are changing the CURRENCY of your store, do you really want to do this?");
        if(!c){
            e.preventDefault();
            $("#store_currency").val(old_currency);
        }
    });
    
    $('.upload_image').click(function() {
                        
        filepicker.pick({
            mimetypes: ['image/*']
          },
          function(InkBlob) {
            $('#profile_img').attr({
                src : InkBlob.url
            }); 
            
            var counter = 0;
            var max = 2;
            var salt = uniqid(); 
            var store_logo = {};
            var dst_store_avatar = get_upload_dst(store_id, salt, 'store_avatar');            
            filepicker.storeUrl(InkBlob.url, {location: 'S3', path: dst_store_avatar, access: 'public'},
                function(new_InkBlob) {
                    var pic_url = shopinterest.constants.s3_base_url + '/'+ new_InkBlob.key;
                    store_logo.logo = pic_url;
                    counter++;             
                }
            );                 
            var dst_store_avatar_converted = get_upload_dst(store_id, salt, 'store_avatar_converted');
            filepicker.convert(InkBlob, {width: 120, height: 120, format: 'jpg', quality: 100, fit: 'crop'}, {location: 'S3', path: dst_store_avatar_converted, access: 'public'},
                function(new_InkBlob) {
                    var pic_url = shopinterest.constants.s3_base_url + '/'+ new_InkBlob.key;
                    store_logo.converted_logo = pic_url;
                    counter++;
                }
            );       
            var t=setInterval(function(){
                if(counter >= max) {
                    console.log(store_logo);
                    clearInterval(t);
                    //post retured image url to backend            
                    $.post('/api/setstoreavatar',{store_logo: JSON.stringify(store_logo)},function(response) {

                    });                       
                }
            }, 100);                           
          }
        );    
    });    
};
