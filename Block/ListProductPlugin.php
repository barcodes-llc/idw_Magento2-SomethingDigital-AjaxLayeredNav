<?php

namespace SomethingDigital\AjaxLayeredNav\Block;

use Magento\Catalog\Block\Product\ListProduct;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class ListProductPlugin
{
    /**
     * @var ConfigInterface
     */
    protected $ajaxConfig;

    /**
     * @param ConfigInterface $ajaxConfig
     */
    public function __construct(ConfigInterface $ajaxConfig)
    {
        $this->ajaxConfig = $ajaxConfig;
    }

    /**
     * Wrap product list with additional div
     *
     * @param ListProduct $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(ListProduct $subject, $result)
    {
        if (!$this->ajaxConfig->enabled()) {
            return $result;
        }
        return '<div id="product-listing" data-mage-init=\'{"ajaxListBinder":{}}\'>' . $result . '</div>';
    }
}
