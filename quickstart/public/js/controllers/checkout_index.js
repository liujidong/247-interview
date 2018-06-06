shopinterest.controllers.checkout_index = new function() {

    var US_STATES = {
        "AL": " Alabama",
        "AK": " Alaska",
        "AZ": " Arizona",
        "AR": " Arkansas",
        "CA": " California",
        "CO": " Colorado",
        "CT": " Connecticut",
        "DE": " Delaware",
        "FL": " Florida",
        "GA": " Georgia",
        "HI": " Hawaii",
        "ID": " Idaho",
        "IL": " Illinois",
        "IN": " Indiana",
        "IA": " Iowa",
        "KS": " Kansas",
        "KY": " Kentucky[C]",
        "LA": " Louisiana",
        "ME": " Maine",
        "MD": " Maryland",
        "MA": " Massachusetts[D]",
        "MI": " Michigan",
        "MN": " Minnesota",
        "MS": " Mississippi",
        "MO": " Missouri",
        "MT": " Montana",
        "NE": " Nebraska",
        "NV": " Nevada",
        "NH": " New Hampshire",
        "NJ": " New Jersey",
        "NM": " New Mexico",
        "NY": " New York",
        "NC": " North Carolina",
        "ND": " North Dakota",
        "OH": " Ohio",
        "OK": " Oklahoma",
        "OR": " Oregon",
        "PA": " Pennsylvania[E]",
        "RI": " Rhode Island[F]",
        "SC": " South Carolina",
        "SD": " South Dakota",
        "TN": " Tennessee",
        "TX": " Texas",
        "UT": " Utah",
        "VT": " Vermont",
        "VA": " Virginia[G]",
        "WA": " Washington",
        "WV": " West Virginia",
        "WI": " Wisconsin",
        "WY": " Wyoming"
    };

    var credit_card_section = $('.payment-creditcard .checkbox-extend');
    var paypal_section = $('.payment-paypal .checkbox-extend');
    var shipping_address = $('#shipping_addr_form');
    var billing_address = $('#billing_addr_form');
    var addr_type = $("#addr_type");

    $('#choose-paypal').on('click', function(e){
        credit_card_section.hide();
        credit_card_section.closest('.payment-item').removeClass('payment-checked');
        paypal_section.show();
        paypal_section.closest('.payment-item').addClass('payment-checked');
        billing_address.hide();
        addr_type.text("Shipping");
        shipping_address.show();
        $(".cc-input").removeAttr("required");
        $(".billing-input").removeAttr("required");
        $(".shipping-input").attr("required", "required");
        gat(e, "shopping-checkout", {label:"pay method change: paypal"});
    });
    $('#choose-creditcard').on('click', function(e){
        paypal_section.hide();
        paypal_section.closest('.payment-item').removeClass('payment-checked');
        credit_card_section.show();
        credit_card_section.closest('.payment-item').addClass('payment-checked');
        billing_address.show();
        addr_type.text("Billing");
        $(".cc-input").attr("required", "required");
        $(".billing-input").attr("required", "required");
        $(".shipping-input").attr("required", "required");
        gat(e, "shopping-checkout", {label:"pay method change: creditcard"});
    });
    var init_pay_method = $("input[name='pay_method']:radio:checked").val();
    $('#choose-'+init_pay_method).trigger('click');

    // addresses
    $("#copy-address").on('click', function(e){
        var fields = [
            "first_name", "last_name"
            , "addr1", "addr2", "country", "state", "city", "zip"
            //, "phone", "email"
        ];
        for(var i=0; i<fields.length; i++){
            var val = $("input[name='billing_"+ fields[i] +"']").val();
            console.log("===",fields[i],val);
            if(val){
                $("input[name='shipping_"+ fields[i] +"']").val(val);
            } else {
                val = $("select[name='billing_"+ fields[i] +"']").val();
                var target_select = $("select[name='shipping_"+ fields[i] +"']");
                target_select.val(val);
                target_select.trigger('change');
                if(val == 'US'){
                    val = $("select[name='billing_state']").val();
                    var target_state = $("select[name='shipping_state']");
                    target_state.val(val);
                    target_state.trigger('change');
                }
            }
        }
        gat(e, "shopping-checkout", {label:"adress: copy from bill-addr to shipping-addr"});
    });

    //********* shipping states
    var old_shipping_state = $("#shipping_state").val();
    var shipping_states_select_html = '<select name="shipping_state" id="shipping_state" class="shipping-input" required>';
    shipping_states_select_html += '<option value="">State / Province / Region</option>';
    for(var abbr in US_STATES) {
        var selected = abbr == old_shipping_state ? "selected" : "";
        shipping_states_select_html += '<option value="' + abbr + '" '+ selected + '>' + US_STATES[abbr] + '</option>';
    }
    shipping_states_select_html += '</select>';
    var shipping_states_input_html = '<input type="text" name="shipping_state" id="shipping_state"/>';
    var shipping_current_country = "";

    var init_shipping_us_states = function(text){
        $("#shipping_state").remove();
        $("#shipping_state_container").append(text);
    };
    $("#shipping_country").on('change', function(e){
        var new_country = $("#shipping_country").val();
        if(new_country == shipping_current_country) return;
        if(shipping_current_country == 'US'){
            init_shipping_us_states(shipping_states_input_html);
        }else if(new_country == 'US'){
            $("#shipping_state").remove();
            init_shipping_us_states(shipping_states_select_html);
        }
        shipping_current_country = new_country;
    });
    //init:
    if($("#shipping_country").val() == "US"){
        init_shipping_us_states(shipping_states_select_html);
    }

    //********* billing states
    var old_billing_state = $("#billing_state").val();
    var billing_states_select_html = '<select name="billing_state" id="billing_state" class="billing-input" required>';
    billing_states_select_html += '<option value="">State / Province / Region</option>';
    for(var abbr2 in US_STATES) {
        var selected2 = abbr2 == old_billing_state ? "selected" : "";
        billing_states_select_html += '<option value="' + abbr2 + '" '+ selected2 + '>' + US_STATES[abbr2] + '</option>';
    }
    billing_states_select_html += '</select>';
    var billing_states_input_html = '<input type="text" name="billing_state" id="billing_state"/>';
    var billing_current_country = "";

    var init_billing_us_states = function(text){
        $("#billing_state").remove();
        $("#billing_state_container").append(text);
    };
    $("#billing_country").on('change', function(e){
        var new_country = $("#billing_country").val();
        if(new_country == billing_current_country) return;
        if(billing_current_country == 'US'){
            init_billing_us_states(billing_states_input_html);
        }else if(new_country == 'US'){
            init_billing_us_states(billing_states_select_html);
        }
        billing_current_country = new_country;
    });
    //init:
    if($("#billing_country").val() == "US"){
        init_billing_us_states(billing_states_select_html);
    }
    $('input.checkout-payment-continue').click(gat_handler("shopping-checkout", {label:  "goto checkout confirm page"}));
};
