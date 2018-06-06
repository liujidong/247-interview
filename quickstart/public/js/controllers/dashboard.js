shopinterest.controllers.dashboard = (function() {

    var $dashboard = $('#dashboard'),
    Dashboard = {},
    toggleSearch, checkboxAction, copyToClipboard,
    imageItemTpl, customFieldTpl

    /**
     * checkbox interaction
     */
    checkboxAction = function() {
        var $table = $dashboard.find('.dashboard-table'),
        $checkAll =  $table.find('th input[type="checkbox"]'),
        $action = $table.find('th.action .module-dropdown'),
        $checkbox = $table.find('td input[type="checkbox"]')

        $checkAll.click(function() {
            if ( $(this).prop('checked') === true ) {
                $checkbox.prop('checked', true);
                $action.removeClass('no-dropdown');
                $('#' + $action.data('dropdown')).removeClass('no-dropdown');
            } else {
                $checkbox.prop('checked', false);
                $action.addClass('no-dropdown');
                $('#' + $action.data('dropdown')).addClass('no-dropdown');
            }
        })
    }

    /**
     * copy to clipboard
     */
    copyToClipboard = function() {
        var $content = $dashboard.find('input[data-copy]'),
        $copyButton, clip

        if ( !$content.length ) return;

        $copyButton = $dashboard.find('.copy-to-clipboard');
        $copyButton.each(function() {
            var val = $('input[data-copy="' + this.id + '"]').val();

            $(this).attr('data-clipboard-text', val);
        });

        // Zero Clipboard
        ZeroClipboard.config({ moviePath: "/js/ZeroClipboard.swf" });
        $content.on('input', function() {
            $('#' + $(this).data('copy')).attr('data-clipboard-text', this.value);
        });
        clip = new ZeroClipboard($copyButton);
    }


    // implement functions
    checkboxAction();
    copyToClipboard();
    deleteProduct();

})();