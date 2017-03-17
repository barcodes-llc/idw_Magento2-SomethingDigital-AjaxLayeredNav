<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Plugin;

use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class UrlPlugin
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
     * Remove unnecessary param "is_ajax=1"
     *
     * This need to be done for "return URL". When there is an AJAX request to category page magento generates links with
     * "return URL" like AddToCart, AddToCompare. Click on them returns customer to wrong URL with AJAX response.
     *
     * @param \Magento\Framework\Url $subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetCurrentUrl(\Magento\Framework\Url $subject, $result)
    {
        if (!$this->ajaxConfig->enabled()) {
            return $result;
        }
        return str_replace(array('&amp;is_ajax=1', '&is_ajax=1', 'is_ajax=1&amp;', 'is_ajax=1&', '?is_ajax=1'), '', $result);
    }
}
