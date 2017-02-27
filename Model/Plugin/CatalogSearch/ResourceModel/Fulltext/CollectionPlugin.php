<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Plugin\CatalogSearch\ResourceModel\Fulltext;

use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

class CollectionPlugin
{
    /**
     * @var \SomethingDigital\AjaxLayeredNav\Model\ConfigInterface
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
     * Apply 'category_ids' filter properly to let it be added to internal SearchCriteria object
     *
     * This is important for search engine - to have info about applied filters.
     * See method \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::addCategoryFilter
     *
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $subject
     * @param array $categoriesFilter
     */
    public function beforeAddCategoriesFilter(Collection $subject, array $categoriesFilter)
    {
        if ($this->ajaxConfig->enabled()) {
            $subject->addFieldToFilter('category_ids', current($categoriesFilter));
        }
    }
}
