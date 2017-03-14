
define([
    "jquery",
    "jquery/ui",
    "productListToolbarForm"
], function($, ui, toolbar) {
    $.widget('mage.productListAjaxToolbarForm', $.mage.productListToolbarForm, {
        _bind: function (element, paramName, defaultValue) {
            if (element.is("select")) {
                element.on('change', {paramName: paramName, default: defaultValue}, $.proxy(this._processSelect, this));
            } else {
                element.on('click', {paramName: paramName, default: defaultValue}, $.proxy(this._processLink, this));
            }
        },
        changeUrl: function (paramName, paramValue, defaultValue) {
            var decode = window.decodeURIComponent;
            var urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters;
            for (var i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined
                    ? decode(parameters[1].replace(/\+/g, '%20'))
                    : '';
            }
            paramData[paramName] = paramValue;
            if (paramValue == defaultValue) {
                delete paramData[paramName];
            }
            paramData = $.param(paramData);

            location.href = baseUrl + (paramData.length ? '?' + paramData : '');
        }
    });

    return $.mage.productListAjaxToolbarForm;
});
