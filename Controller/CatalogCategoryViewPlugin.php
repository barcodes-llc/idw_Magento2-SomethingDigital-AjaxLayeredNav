<?php

namespace SomethingDigital\AjaxLayeredNav\Controller;

use Magento\Framework\Controller\ResultFactory;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class CatalogCategoryViewPlugin
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var ConfigInterface
     */
    protected $ajaxConfig;

    /**
     * @param ResultFactory $resultFactory
     * @param ConfigInterface $ajaxConfig
     */
    public function __construct(
        ResultFactory $resultFactory,
        ConfigInterface $ajaxConfig
    ) {
        $this->resultFactory = $resultFactory;
        $this->ajaxConfig = $ajaxConfig;
    }

    /**
     * Build JSON with products and filters
     *
     * @param \Magento\Catalog\Controller\Category\View $subject
     * @param callable|\Closure $proceed
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function aroundExecute(
        \Magento\Catalog\Controller\Category\View $subject,
        \Closure $proceed
    ) {
        $page = $proceed();
        $request = $subject->getRequest();
        if (!$this->ajaxConfig->enabled() || !$request->isAjax() || !($page instanceof \Magento\Framework\View\Result\Page)) {
            return $page;
        }
        $productList = $page->getLayout()->getBlock('category.products.list')->toHtml();
        $filters = $page->getLayout()->getBlock('catalog.leftnav')->toHtml();

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(array('product_list' => $productList, 'filters' => $filters));
        return $result;
    }
}
