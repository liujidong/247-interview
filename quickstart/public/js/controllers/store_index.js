shopinterest.controllers.store_index = new function() {

    var aid = $.query.get('aid').toString();
    var currency_symbol = $("#currency_symbol").val();

    /* ga tracking */
    $(".gat-product-item").click(gat_handler("product-view-common", {label: "PRODUCT - from store homepage"}));
    $('.nav-item.infos').click(gat_handler("shopping-return-policy", {label:  "From Store Page"}));
    /* end ga tracking */

};
