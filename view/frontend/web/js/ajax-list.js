
define([
    'jquery',
    "jquery/ui"
], function($) {
    "use strict"; 
    function retrieveUrl($element) {
        if ($element.is('a')) {
            return $element.attr('href');
        } else {
            return $element.data('href');
        }
    };
    var ajaxList = {
        bindAjaxUpdate: function(e) {
            var $this = $(this);
            if ($this.prop('disabled')) {
                return true;
            }
            var url = retrieveUrl($this);
            var focusData = {};
            if ($this.parents('#layered-filter-block').length) {
                focusData = {
                    eventSourceId: $this.attr('id'),
                    eventSourceAreaId: 'layered-filter-block',
                    focusTarget: 'self'
                };
            } else {
                focusData = {
                    eventSourceId: $this.attr('id'),
                    eventSourceAreaId: 'product-listing',
                    focusTarget: 'product'
                };
            }
            ajaxList.updateContent(url, focusData);
            if ($this.is('a')) {
                return false;
            }
            return true;
        },
        updateContent: function(url, focusData) {
            var self = this;
            var data = '';
            var historyUrl = url.replace('?is_ajax=1&', '?').replace('?is_ajax=1', '').replace('&is_ajax=1', '');

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
                showLoader: true,
                success: function(data) {
                    if (data.product_list) {
                        $('#product-listing').html($('<div>').html(data.product_list).find('#product-listing').html());
                        $('#layered-filter-block').html($('<div>').html(data.filters).find('#layered-filter-block').html());
                        // custom event after updating product list content via ajax
                        $(document).trigger('ajaxProductListUpdated');
                        if ($('#layered-filter-block').length > 0) {
                            $('#layered-filter-block').trigger('contentUpdated');
                        } else { 
                            $('#product-listing').trigger('contentUpdated');
                        }

                        if (focusData.eventSourceAreaId == 'product-listing') {
                            self.setFocus(focusData);
                        } else {
                            $('#layered-filter-block .filter-options-item').collapsible({created: function(event) {
                                if (focusData.eventSourceId && $(this).find('#' + focusData.eventSourceId).length) {
                                    self.setFocus(focusData);
                                }
                            }});
                        }

                        window.history.replaceState({}, '', historyUrl);
                    } else {
                        location.reload();
                    }
                },
                error: function() {
                    location.reload();
                }
            });
        },
        setFocus: function(focusData) {
            if (focusData.focusTarget == 'product') {
                $('body').addClass('_keyfocus');
                $('#' + focusData.eventSourceAreaId + ' .product-item-info:first').addClass('active')
                    .find('.product-item-link:first').focus();
            } else {
                var $item = $('#' + focusData.eventSourceAreaId + ' #' + focusData.eventSourceId);
                var $filter = $item.parents('.filter-options-item');
                if ($filter.length == 1) {
                    $filter.collapsible('activate');
                }
                $item.focus();
            }
        }
    };
    return ajaxList;
});
