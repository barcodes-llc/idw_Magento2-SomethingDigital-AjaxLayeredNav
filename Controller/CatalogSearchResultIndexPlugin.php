<?php

namespace SomethingDigital\AjaxLayeredNav\Controller;

use Magento\Framework\Controller\ResultFactory;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class CatalogSearchResultIndexPlugin
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
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * @var Resolver
     */
    protected $layerResolver;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param ResultFactory $resultFactory
     * @param ConfigInterface $ajaxConfig
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     * @param StoreManagerInterface $storeManager
     * @param PageFactory $resultPageFactory
     * @param \Magento\CatalogSearch\Helper\Data $helper
     */
    public function __construct(
        ResultFactory $resultFactory,
        ConfigInterface $ajaxConfig,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        StoreManagerInterface $storeManager,
        PageFactory $resultPageFactory,
        \Magento\CatalogSearch\Helper\Data $helper
    ) {
        $this->resultFactory = $resultFactory;
        $this->ajaxConfig = $ajaxConfig;
        $this->queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
        $this->storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
    }

    /**
     * Build JSON with products and filters
     *
     * Initialization of query, layer and layout are necessary because in fact current method replaces original
     * \Magento\CatalogSearch\Controller\Result\Index::execute. It is because original method renders layout itself
     * instead of returning page result object. It is not acceptable and non-reusable.
     *
     * @param \Magento\CatalogSearch\Controller\Result\Index $subject
     * @param callable|\Closure $proceed
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function aroundExecute(
        \Magento\CatalogSearch\Controller\Result\Index $subject,
        \Closure $proceed
    ) {
        $request = $subject->getRequest();
        if (!$this->ajaxConfig->enabled() || !$request->getParam('is_ajax')) {
            $proceed();
            return;
        }

        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
        /* @var $query \Magento\Search\Model\Query */
        $query = $this->queryFactory->get();
        $query->setStoreId($this->storeManager->getStore()->getId());
        if ($query->getQueryText() != '') {
            if ($this->helper->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();
            }
            $this->helper->checkNotes();
        }
        $page = $this->resultPageFactory->create();

        if ($page->getLayout()->getBlock('search_result_list')) {
            $productList = $page->getLayout()->getBlock('search_result_list')->toHtml();
        } else {
            $productList = '<div id="product-listing"></div>';
        }

        $filters = $page->getLayout()->getBlock('catalogsearch.leftnav')->toHtml();

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(array('product_list' => $productList, 'filters' => $filters));
        return $result;
    }
}
