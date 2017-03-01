<?php
namespace SomethingDigital\AjaxLayeredNav\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\Item as ItemBase;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class Item extends ItemBase
{
    /**
     * @var \SomethingDigital\AjaxLayeredNav\Model\ConfigInterface
     */
    protected $ajaxConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Construct
     *
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Theme\Block\Html\Pager $htmlPagerBlock
     * @param ConfigInterface $ajaxConfig
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        ConfigInterface $ajaxConfig,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        $this->ajaxConfig = $ajaxConfig;
        $this->request = $request;
        parent::__construct($url, $htmlPagerBlock, $data);
    }

    /**
     * Get filter item url
     *
     * Override to get proper URL with multiselect layered navigation enabled
     *
     * @return string
     */
    public function getUrl()
    {
        $newFilterValue = null;
        $value = $this->request->getParam($this->getFilter()->getRequestVar());
        if ($value && $this->ajaxConfig->enabled()) {
            $filterValueArray = explode(',', $value);
            if (in_array($this->getValue(), $filterValueArray)) {
                foreach ($filterValueArray as $key => $filterValue) {
                    if ($filterValue == $this->getValue()) {
                        unset($filterValueArray[$key]);
                    }
                }
            } else {
                $filterValueArray[] = $this->getValue();
            }
            if (count($filterValueArray)) {
                $newFilterValue = implode(',', $filterValueArray);
            }
        } else {
            $newFilterValue = $this->getValue();
        }
        $query = [
            $this->getFilter()->getRequestVar() => $newFilterValue,
            // exclude current page from urls
            $this->_htmlPagerBlock->getPageVarName() => null,
        ];
        return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }

    /**
     * Get url for remove item from filter
     *
     * Override to get proper URL with multiselect layered navigation enabled
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        $resetValue = $this->getFilter()->getResetValue();
        if ($this->ajaxConfig->enabled()) {
            $filterValueArray = array();
            $value = $this->request->getParam($this->getFilter()->getRequestVar());
            if ($value) {
                $filterValueArray = explode(',', $value);
                foreach ($filterValueArray as $key => $filterValue) {
                    if ($filterValue == $this->getValue()) {
                        unset($filterValueArray[$key]);
                    }
                }
            }
            if (count($filterValueArray)) {
                $resetValue = implode(',', $filterValueArray);
            }
        }

        $query = [$this->getFilter()->getRequestVar() => $resetValue];
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $query;
        $params['_escape'] = true;
        return $this->_url->getUrl('*/*/*', $params);
    }
}
