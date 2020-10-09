<?php

namespace SomethingDigital\AjaxLayeredNav\Model;

class Config implements ConfigInterface
{
    const XML_PATH_ENABLED = 'catalog/layered_navigation/enable_multiselect_layer';
    const XML_PATH_ENABLED_SEO_FRIENDLY_URLS = 'catalog/layered_navigation/enable_seo_urls';

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

    /**
     * Check whether SEO friendly URLs are enabled
     *
     * @return bool
     */
    public function enabledSeoUrls()
    {
        return $this->scopeConfig->isSetFlag(static::XML_PATH_ENABLED_SEO_FRIENDLY_URLS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
