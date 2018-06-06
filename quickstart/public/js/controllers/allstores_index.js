shopinterest.controllers.allstores_index = (function() {

    var utils = shopinterest.common.utils,
    $loading = $('#loading'),
    storesPage = 1,
    isLoading = false,
    isLastPage = false,
    storeItemTpl = '{{#stores}}\n' +
        '<li class="store-item">\n' +
        '<img class="store-logo" src="{{image}}" width="100">\n' +
        '<div class="store-name">{{name}}</div>\n' +
        '<div class="f-dropdown content">\n' +
        '<strong>{{name}}</strong><br>\n' +
        '<div>{{description}}</div>\n' +
        '</div>\n</li>\n' +
        '{{/stores}}';

    var getStore = function() {
        if ( isLoading ) return;

        $loading.css('visibility', 'visible');
        isLoading = true;
        storesPage += 1;
        $.ajax({
            url: '',
            data: { page: storesPage }
        })
        .done(function(res) {
            // need isLastPage here
            // if isLastPage, set isLastPage = true

            $('.store-list').append( Mustache.to_html(storeItemTpl, { stores: res.data }) );
            $loading.css('visibility', 'hidden');
            isLoading = false;
        })
        .fail(function() {
            storesPage -= 1;
            $loading.css('visibility', 'hidden');
            isLoading = false;
        });
    };

    $('.store-list').on('mouseover', '.store-item', function() {
        $(this).find('.content').addClass('show');
    });

    $('.store-list').on('mouseout', '.store-item', function() {
        $(this).find('.content').removeClass('show');
    });

    $(".search-submit").click(function(e){
        var input = $(".search-input[name='store_query']");
        if(input.val().length <= 0) return;
        $(this).parents('form').submit();
    });
})();
