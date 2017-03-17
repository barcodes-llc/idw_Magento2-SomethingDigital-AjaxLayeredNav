<?php

namespace SomethingDigital\AjaxLayeredNav\Block\Swatches;

use Magento\Swatches\Block\LayeredNavigation\RenderLayered as RenderLayeredBase;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Eav\Model\Entity\Attribute\Option;

class RenderLayered extends RenderLayeredBase
{
    /**
     * Path to template file.
     *
     * @var string
     */
    protected $_template = 'SomethingDigital_AjaxLayeredNav::layer/swatches/renderer.phtml';

    /**
     * Prepare filter option data
     *
     * Override to mark selected filter options with class 'selected' and use correct URL which support multiselecting.
     * This class does not overrides original class directly via di.xml.
     * Check SomethingDigital\AjaxLayeredNav\Model\Plugin\Swatches\FilterRendererPlugin for more details.
     *
     * @param FilterItem $filterItem
     * @param Option $swatchOption
     * @return array
     */
    protected function getOptionViewData(FilterItem $filterItem, Option $swatchOption)
    {
        $customStyle = '';
        $linkToOption = $filterItem->getUrl();
        if ($filterItem->getIsActive()) {
            $customStyle = 'selected';
        }
        if ($this->isOptionDisabled($filterItem)) {
            $customStyle = 'disabled';
            $linkToOption = 'javascript:void();';
        }

        return [
            'label' => $swatchOption->getLabel(),
            'link' => $linkToOption,
            'custom_style' => $customStyle
        ];
    }
}
