<?php

namespace SomethingDigital\AjaxLayeredNav\Model;

class Config implements ConfigInterface
{
    const XML_PATH_ENABLED = 'catalog/layered_navigation/enable_multiselect_layer';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check whether entire module functionality is enabled
     *
     * @return bool
     */
    public function enabled()
    {
        return $this->scopeConfig->isSetFlag(static::XML_PATH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
