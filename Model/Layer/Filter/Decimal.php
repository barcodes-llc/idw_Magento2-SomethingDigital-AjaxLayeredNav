<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Layer\Filter;

use Magento\CatalogSearch\Model\Layer\Filter\Decimal as DecimalBase;

class Decimal extends DecimalBase
{
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory $filterDecimalFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $filterDecimalFactory,
            $priceCurrency,
            $data
        );
    }

    /**
     * Apply price range filter
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        /**
         * Filter must be string: $fromPrice-$toPrice
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }

        list($from, $to) = explode('-', $filter);

        $this->getLayer()
            ->getProductCollection()
            ->addFieldToFilter(
                $this->getAttributeModel()->getAttributeCode(),
                ['from' => $from, 'to' => $to]
            );

        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
        );

        return $this;
    }
}
