
define([
    'jquery',
    'jquery/ui',
    'sdAjaxList'
], function($, ui, ajaxList) {
    "use strict";
    function init(config, $loader) {
        $('#layered-filter-block').on('click', 'a, .filter-item-checkbox, .swatch-option-link-layered', ajaxList.bindAjaxUpdate);
        $('#product-listing').on('click', '.pages a', ajaxList.bindAjaxUpdate);
    };
    return init;
});
