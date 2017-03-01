<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Layer\Filter;

use Magento\CatalogSearch\Model\Layer\Filter\Attribute as AttributeBase;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class Attribute extends AttributeBase
{
    protected $ajaxConfig = null;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        ConfigInterface $ajaxConfig,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $data
        );
        $this->ajaxConfig = $ajaxConfig;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * This override allows for multiple values to be added to the filter.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->ajaxConfig->enabled()) {
            return parent::apply($request);
        }

        $attributeValues = $request->getParam($this->_requestVar);
        if (empty($attributeValues) || is_array($attributeValues)) {
            return $this;
        }
        $attributeValues = array_filter(explode(',', $attributeValues));
        if (empty($attributeValues)) {
            return $this;
        }

        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();
        $productCollection->addFieldToFilter($attribute->getAttributeCode(), $attributeValues);

        $state = $this->getLayer()->getState();
        foreach ($attributeValues as $attributeValue) {
            $label = $this->getOptionText($attributeValue);
            $state->addFilter($this->_createItem($label, $attributeValue));
        }

        return $this;
    }

    /**
     * Checks whether the option reduces the number of results
     *
     * Override to display all options. Customer must be able to select one more option(s) in addition to currently
     * selected, so all options must be there.
     *
     * @param type $optionCount
     * @param type $totalSize
     * @return boolean
     */
    protected function isOptionReducesResults($optionCount, $totalSize)
    {
        if (!$this->ajaxConfig->enabled()) {
            return parent::isOptionReducesResults($optionCount, $totalSize);
        }
        return true;
    }
}
