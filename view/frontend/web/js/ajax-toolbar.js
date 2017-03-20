
define([
    "jquery",
    "jquery/ui",
    "productListToolbarForm",
    "sdAjaxList"
], function($, ui, toolbar, ajaxList) {
    $.widget('mage.sdProductListAjaxToolbarForm', $.mage.productListToolbarForm, {
        _bind: function (element, paramName, defaultValue) {
            if (element.data('bound')) {
                // avoid double binding (original magento behavior)
                return;
            }
            if (element.is("select")) {
                element.on('change', {paramName: paramName, default: defaultValue}, $.proxy(this._processSelect, this));
            } else {
                element.on('click', {paramName: paramName, default: defaultValue}, $.proxy(this._processLink, this));
            }
            element.data('bound', true);
        },
        changeUrl: function (paramName, paramValue, defaultValue, focusData) {
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

            ajaxList.updateContent(baseUrl + (paramData.length ? '?' + paramData : ''), focusData);
        },
        _processLink: function (event) {
            event.preventDefault();
            var focusData = {
                eventSourceId: $(event.currentTarget).attr('id'),
                eventSourceAreaId: 'product-listing',
                focusTarget: 'product'
            };
            this.changeUrl(
                event.data.paramName,
                $(event.currentTarget).data('value'),
                event.data.default,
                focusData
            );
        },
        _processSelect: function (event) {
            var focusData = {
                eventSourceId: $(event.currentTarget).attr('id'),
                eventSourceAreaId: 'product-listing',
                focusTarget: ($(event.currentTarget).attr('id') == 'sorter') ? 'self' : 'product'
            };
            this.changeUrl(
                event.data.paramName,
                event.currentTarget.options[event.currentTarget.selectedIndex].value,
                event.data.default,
                focusData
            );
        },
    });

    return $.mage.sdProductListAjaxToolbarForm;
});
