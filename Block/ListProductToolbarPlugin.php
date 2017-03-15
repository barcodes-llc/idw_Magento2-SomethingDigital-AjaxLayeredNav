<?php

namespace SomethingDigital\AjaxLayeredNav\Block;

use Magento\Catalog\Block\Product\ProductList\Toolbar;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class ListProductToolbarPlugin
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
     * Initialize custom toolbar JS
     *
     * @param Toolbar $subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetWidgetOptionsJson(Toolbar $subject, $result)
    {
        if (!$this->ajaxConfig->enabled()) {
            return $result;
        }
        $data = json_decode($result, true);
        if (isset($data['productListToolbarForm'])) {
            return json_encode(['productListAjaxToolbarForm' => $data['productListToolbarForm']]);
        }
        return $result;
    }
}
