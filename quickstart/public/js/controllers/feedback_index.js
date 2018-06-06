shopinterest.controllers.feedback_index = new function() {
    // feedback star
    $('.review_star').mouseover(function(){
        $(this).addClass('active');
        var current_score = $(this).attr('review_star');
        $('#review_score').val(current_score);
        for(var i=5;i>current_score;i--){
            $('#review_'+i).removeClass('active');
        }
        for(var j=1;j<=current_score;j++){
            $('#review_'+j).addClass('active');
        }
    });

    $('.review_star').click( function (event) {
        event.preventDefault();
    });
    
    
}
