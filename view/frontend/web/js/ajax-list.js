
define([
    'jquery',
    "jquery/ui"
], function($) {
    "use strict"; 
    function retrieveUrl($element) {
        if ($element.is('a')) {
            return $element.attr('href');
        } else if ($element.is('input')) {
            return $element.data('href');
        }
        return null;
    };
    var ajaxList = {
        bindAjaxUpdate: function(e) {
            var $this = $(this);
            if ($this.prop('disabled')) {
                return true;
            }
            var url = retrieveUrl($this);
            ajaxList.updateContent(url);
            if ($this.is('a')) {
                return false;
            }
            return true;
        },
        updateContent: function(url) {
            var data = '';
            if (url.indexOf('?') > 0) {
                var urlParts = url.split('?');
                url = urlParts[0];
                data = urlParts[1];
            }
            if (!data) {
                data = 'is_ajax=1';
            } else {
                data += '&is_ajax=1';
            }
            $.ajax({
                url: url,
                data: data,
                cache: true,
                complete: function() {
                    // hide loader
                },
                success: function(data) {
                    if (data.product_list && data.filters) {
                        $('#product-listing').html($('<div>').html(data.product_list).find('#product-listing').html());
                        $('#layered-filter-block').html($('<div>').html(data.filters).find('#layered-filter-block').html());
                        // custom event after updating product list content via ajax
                        $(document).trigger('ajaxProductListUpdated');
                        $('#layered-filter-block').trigger('contentUpdated');
                    } else {
                        // show error or refresh page
                    }
                },
                error: function() {
                    // refresh page
                }
            });
        }
    };
    return ajaxList;
});
