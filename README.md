# SomethingDigital_AjaxLayeredNav

This module adjusts the layered navigation in two ways:

1. Enables the selection of multiple filter options.
2. Uses AJAX to update the result set.

## Compatibility

 * Magento 2.1.x, 2.2.x, 2.3.x
 * PHP 5.6, 7.0, 7.1, 7.2, 7.3
 * Elasticsearch, MySQL backends

## Installation

For projects whose theme does not inherit from our Bryant Park theme, we will need to add the required templates to our active theme.

Steps:
1. Copy the following template from the Magento core
- [Magento_LayeredNavigation/templates/layer/view.phtml](https://github.com/magento/magento2/blob/2.4-develop/app/code/Magento/LayeredNavigation/view/frontend/templates/layer/view.phtml)
- [Magento_LayeredNavigation/templates/layer/state.phtml](https://github.com/magento/magento2/blob/2.4-develop/app/code/Magento/LayeredNavigation/view/frontend/templates/layer/state.phtml)
2. Commit the templates with a message simmilar to
> Copy Magento templates
3. Copy the following Bryant Park templates:
- [Magento_LayeredNavigation/templates/layer/view.phtml](https://github.com/sdinteractive/SomethingDigital-Magento2-Theme-BryantPark/blob/develop/Magento_LayeredNavigation/templates/layer/view.phtml)
- [Magento_LayeredNavigation/templates/layer/state.phtml](https://github.com/sdinteractive/SomethingDigital-Magento2-Theme-BryantPark/blob/develop/Magento_LayeredNavigation/templates/layer/state.phtml)
4. Commit the changes made to the templates with a message similar to
> Pull in Byrant Park changes
