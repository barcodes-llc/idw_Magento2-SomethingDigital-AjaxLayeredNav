<?php

namespace SomethingDigital\AjaxLayeredNav\Block\Swatches;

use Magento\Swatches\Block\LayeredNavigation\RenderLayered as RenderLayeredBase;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Eav\Model\Entity\Attribute\Option;

class RenderLayered extends RenderLayeredBase
{
    /**
     * @param FilterItem $filterItem
     * @param Option $swatchOption
     * @return array
     */
    protected function getOptionViewData(FilterItem $filterItem, Option $swatchOption)
    {
        $customStyle = '';
        $linkToOption = $this->buildUrl($this->eavAttribute->getAttributeCode(), $filterItem->getValue());
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
