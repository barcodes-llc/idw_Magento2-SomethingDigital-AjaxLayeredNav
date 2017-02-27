<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Plugin\Mysql\Adapter\Filter;

use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor;
use Magento\Framework\Search\Request\FilterInterface;

class PreprocessorPlugin
{
    /**
     * @var \SomethingDigital\AjaxLayeredNav\Model\ConfigInterface
     */
    protected $ajaxConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @param ConfigInterface $ajaxConfig
     * @param ResourceConnection $resource
     */
    public function __construct(ConfigInterface $ajaxConfig, ResourceConnection $resource)
    {
        $this->ajaxConfig = $ajaxConfig;
        $this->resource = $resource;
    }

    /**
     * Apply filter by multiple categories
     *
     * Native implementation can filter by single category only. We need this to filter properly data in tempStorage.
     * Valid for MySQL FullText search engine only.
     *
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $subject
     * @param array $categoriesFilter
     */
    public function aroundProcess(Preprocessor $subject, \Closure $proceed, FilterInterface $filter, $isNegation, $query)
    {
        if ($this->ajaxConfig->enabled() && $filter->getField() === 'category_ids' && is_array($filter->getValue())) {
            return $this->resource->getConnection()->quoteInto('category_ids_index.category_id in (?)', $filter->getValue());
        }
        return $proceed($filter, $isNegation, $query);
    }
}
