<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Layer\Filter;

use Magento\CatalogSearch\Model\Layer\Filter\Category as CategoryBase;
use Magento\Framework\Registry;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class Category extends CategoryBase
{
    protected $ajaxConfig = null;
    protected $coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
     */
    protected $dataProvider = null;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        Registry $coreRegistry,
        ConfigInterface $ajaxConfig,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $escaper,
            $categoryDataProviderFactory,
            $data
        );
        $this->ajaxConfig = $ajaxConfig;
        $this->coreRegistry = $coreRegistry;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * Apply attribute option filter to product collection
     *
     * This override allows multiple subcategories to be filtered.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->ajaxConfig->enabled()) {
            return parent::apply($request);
        }

        $categoryIds = $request->getParam($this->_requestVar) ?: $request->getParam('id');
        if (empty($categoryIds) || is_array($categoryIds)) {
            return $this;
        }
        $categoryIds = array_filter(explode(',', $categoryIds));
        if (empty($categoryIds)) {
            return $this;
        }
        $categories = [];
        // TODO: This ultimately results in a looped category load, improve.
        foreach ($categoryIds as $categoryId) {
            $this->dataProvider->setCategoryId($categoryId);
            $category = $this->dataProvider->getCategory();
            if (!$this->dataProvider->isValid()) {
                continue;
            }
            $categories[$categoryId] = $category;
            if ($request->getParam('id') != $category->getId()) {
                $this->getLayer()->getState()->addFilter($this->_createItem($category->getName(), $categoryId));
            }
        }

        if (count($categories) > 1) {
            // Set the current category filter to the current category.
            // This gives the most predictable price filtering.
            $this->coreRegistry->unregister('current_category_filter');
            $category = $this->getLayer()->getCurrentCategory();
            $this->coreRegistry->register('current_category_filter', $category);
        }

        // register the subcategory filters. if no subcategories are selected this will just be
        // the current category id.
        $this->coreRegistry->register('filter_category_ids', array_keys($categories));

        if (count($categories) == 1) {
            $this->getLayer()->getProductCollection()->addCategoryFilter(current($categories));
        } elseif (count($categories) > 1) {
            $this->getLayer()->getProductCollection()->addCategoriesFilter(['in' => array_keys($categories)]);
        }

        return $this;
    }

    /**
     * Checks whether the option reduces the number of results
     *
     * Override to display all options. Customer must be able to select one more option(s) in addition to currently
     * selected, so all options must be there.
     *
     * @param type $optionCount
     * @param type $totalSize
     * @return boolean
     */
    protected function isOptionReducesResults($optionCount, $totalSize)
    {
        if (!$this->ajaxConfig->enabled()) {
            return parent::isOptionReducesResults($optionCount, $totalSize);
        }
        return true;
    }
}
