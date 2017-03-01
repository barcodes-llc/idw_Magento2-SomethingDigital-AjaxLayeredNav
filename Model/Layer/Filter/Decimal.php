<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Layer\Filter;

use Magento\CatalogSearch\Model\Layer\Filter\Decimal as DecimalBase;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class Decimal extends DecimalBase
{
    const RANGE_DELTA = 0.001;

    protected $ajaxConfig = null;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory $filterDecimalFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        ConfigInterface $ajaxConfig,
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
        $this->ajaxConfig = $ajaxConfig;
    }

    /**
     * Apply price range filter
     *
     * This override allows multiple ranges to be selected.
     * The upper range is also corrected to match the displayed range.
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

        // The format will be $fromPrice-$toPrice,$fromPrice-$toPrice etc.
        $filters = $request->getParam($this->getRequestVar());
        if (empty($filters) || is_array($filters)) {
            return $this;
        }

        $filters = array_filter(explode(',', $filters));
        if (empty($filters)) {
            return $this;
        }

        $state = $this->getLayer()->getState();

        $condition = [
            'from' => [],
            'to' => [],
        ];
        foreach ($filters as $filter) {
            list ($from, $to) = explode('-', $filter);

            $condition['from'][] = $from;
            // This isn't included in the base code, but it matches the display.  OOB bug.
            $condition['to'][] = empty($to) || $from == $to ? $to : $to - self::RANGE_DELTA;

            $state->addFilter(
                $this->_createItem($this->renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
            );
        }

        $condition['from'] = implode(',', $condition['from']);
        $condition['to'] = implode(',', $condition['to']);

        $attributeCode = $this->getAttributeModel()->getAttributeCode();
        $this->getLayer()->getProductCollection()->addFieldToFilter($attributeCode, $condition);

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
