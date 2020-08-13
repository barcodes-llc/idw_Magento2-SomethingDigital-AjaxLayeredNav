<?php
namespace SomethingDigital\AjaxLayeredNav\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\Item as ItemBase;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Theme\Block\Html\Pager $htmlPagerBlock
     * @param ConfigInterface $ajaxConfig
     * @param \Magento\Framework\App\RequestInterface $request
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        ConfigInterface $ajaxConfig,
        \Magento\Framework\App\RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->ajaxConfig = $ajaxConfig;
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
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

        $categoryUrl = false;
        if (strtolower($this->getFilter()->getName()) == 'category' && $this->ajaxConfig->enabledSeoUrls()) {
            $categoryId = $this->getValue();
            $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
            $categoryUrl = $category->getUrl();

            $url = parent::getUrl();
            $catRequestVar  = $this->getFilter()->getRequestVar();
            parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);
            unset($queryParams[$catRequestVar]);

            $categoryUrl .= '?' . http_build_query($queryParams);
        }

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

        if ($categoryUrl) {
            return $categoryUrl;
        }

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
                    $thisValue = $this->getValue();
                    if (is_array($thisValue)) {
                        // Prices and decimals use ranges that need to be removed by range.
                        $hyphenValue = implode("-", $thisValue);
                        if ($filterValue == $hyphenValue) {
                            unset($filterValueArray[$key]);
                        }
                    }
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

    /**
     * Check whether current item is active
     *
     * @return boolean
     */
    public function getIsActive()
    {
        $active = false;
        $value = $this->request->getParam($this->getFilter()->getRequestVar());
        if ($value) {
            $filterValueArray = explode(',', $value);
            foreach ($filterValueArray as $key => $filterValue) {
                if ($filterValue == $this->getValue()) {
                    $active = true;
                    break;
                }
            }
        }
        return $active;
    }

    /**
     * Get unique item id
     *
     * @return string
     */
    public function getId()
    {
        return $this->getFilter()->getRequestVar() . $this->getValue();
    }
}
