<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Layer\Filter;

use Magento\CatalogSearch\Model\Layer\Filter\Price as PriceBase;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class Price extends PriceBase
{
    protected $dataProvider = null;
    protected $ajaxConfig = null;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory,
        ConfigInterface $ajaxConfig,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $resource,
            $customerSession,
            $priceAlgorithm,
            $priceCurrency,
            $algorithmFactory,
            $dataProviderFactory,
            $data
        );
        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->ajaxConfig = $ajaxConfig;
    }

    /**
     * Apply price range filter
     *
     * This override allows multiple ranges to be selected.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
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

        // Note: with multiselect, we ignore the "prior filters".
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
            $filter = $this->dataProvider->validateFilter($filter);
            if (!$filter) {
                continue;
            }

            $this->dataProvider->setInterval($filter);
            list ($from, $to) = $filter;

            // Add each part to the array so we can add it at once (an OR.)
            $condition['from'][] = $from;
            $condition['to'][] = empty($to) || $from == $to ? $to : $to - self::PRICE_DELTA;

            $state->addFilter(
                $this->_createItem($this->_renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
            );
        }

        $condition['from'] = implode(',', $condition['from']);
        $condition['to'] = implode(',', $condition['to']);

        $this->getLayer()->getProductCollection()->addFieldToFilter('price', $condition);

        return $this;
    }
}
