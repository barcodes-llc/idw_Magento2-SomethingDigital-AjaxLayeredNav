<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Search;

use Magento\Framework\Api\Search\SearchInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\App\ScopeResolverInterface;
use SomethingDigital\AjaxLayeredNav\Model\Search\Request\BuilderFactory;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Framework\Search\SearchResponseBuilder;
use Magento\Framework\Search\ResponseInterface;

class Search implements SearchInterface
{
    /**
     * @var BuilderFactory
     */
    protected $requestBuilderFactory;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @var SearchResponseBuilder
     */
    private $searchResponseBuilder;

    /**
     * @param BuilderFactory $requestBuilderFactory
     * @param ScopeResolverInterface $scopeResolver
     * @param SearchEngineInterface $searchEngine
     * @param SearchResponseBuilder $searchResponseBuilder
     */
    public function __construct(
        BuilderFactory $requestBuilderFactory,
        ScopeResolverInterface $scopeResolver,
        SearchEngineInterface $searchEngine,
        SearchResponseBuilder $searchResponseBuilder
    ) {
        $this->requestBuilderFactory = $requestBuilderFactory;
        $this->scopeResolver = $scopeResolver;
        $this->searchEngine = $searchEngine;
        $this->searchResponseBuilder = $searchResponseBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        $generalSearchResponse = $this->getGeneralSearchResponse($searchCriteria);
        return $this->searchResponseBuilder->build($searchResponse)
            ->setSearchCriteria($searchCriteria);
    }

    /**
     * Prepare search response for current request
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return ResponseInterface
     */
    protected function getGeneralSearchResponse(SearchCriteriaInterface $searchCriteria)
    {
        $requestBuilder = $this->requestBuilderFactory->create();
        $requestBuilder->setRequestName($searchCriteria->getRequestName());

        $scope = $this->scopeResolver->getScope()->getId();
        $requestBuilder->bindDimension('scope', $scope);

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $this->addFieldToFilter($filter->getField(), $filter->getValue());
            }
        }

        $requestBuilder->setFrom($searchCriteria->getCurrentPage() * $searchCriteria->getPageSize());
        $requestBuilder->setSize($searchCriteria->getPageSize());
        $generalRequest = $requestBuilder->create();
        return $this->searchEngine->search($generalRequest);
    }

    /**
     * Apply attribute filter to facet collection
     *
     * @param string $field
     * @param string|array|null $condition
     * @return $this
     */
    private function addFieldToFilter($field, $condition = null)
    {
        if (!is_array($condition) || !in_array(key($condition), ['from', 'to'], true)) {
            $this->requestBuilder->bind($field, $condition);
        } else {
            if (!empty($condition['from'])) {
                $this->requestBuilder->bind("{$field}.from", $condition['from']);
            }
            if (!empty($condition['to'])) {
                $this->requestBuilder->bind("{$field}.to", $condition['to']);
            }
        }

        return $this;
    }
}
