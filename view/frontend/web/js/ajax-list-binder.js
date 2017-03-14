
define([
    'jquery',
    'jquery/ui',
    'SomethingDigital_AjaxLayeredNav/js/ajax-list'
], function($, ui, ajaxList) {
    "use strict";
    function init(config, $loader) {
        $('#layered-filter-block').on('click', 'a, .filter-item-checkbox', ajaxList.bindAjaxUpdate);
        $('#product-listing').on('click', '.pages a', ajaxList.bindAjaxUpdate);
    };
    return init;
});
