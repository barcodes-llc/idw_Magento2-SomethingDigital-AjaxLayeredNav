<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Search;

use Magento\Framework\Api\Search\SearchInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\App\ScopeResolverInterface;
use SomethingDigital\AjaxLayeredNav\Model\Search\Request\BuilderFactory;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Framework\Search\SearchResponseBuilder;
use Magento\Framework\Search\ResponseInterface;
use Magento\Framework\Search\Response\AggregationFactory;
use Magento\Framework\Search\Response\QueryResponseFactory;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class Search implements SearchInterface
{
    /**
     * @var BuilderFactory
     */
    protected $requestBuilderFactory;

    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var SearchEngineInterface
     */
    protected $searchEngine;

    /**
     * @var SearchResponseBuilder
     */
    protected $searchResponseBuilder;

    /**
     * @var AggregationFactory
     */
    protected $aggregationFactory;

    /**
     * @var QueryResponse
     */
    protected $queryResponseFactory;

    /**
     * @var ConfigInterface
     */
    protected $ajaxConfig;

    /**
     * @param BuilderFactory $requestBuilderFactory
     * @param ScopeResolverInterface $scopeResolver
     * @param SearchEngineInterface $searchEngine
     * @param SearchResponseBuilder $searchResponseBuilder
     * @param AggregationFactory $aggregationFactory
     * @param QueryResponseFactory $queryResponseFactory
     */
    public function __construct(
        BuilderFactory $requestBuilderFactory,
        ScopeResolverInterface $scopeResolver,
        SearchEngineInterface $searchEngine,
        SearchResponseBuilder $searchResponseBuilder,
        AggregationFactory $aggregationFactory,
        QueryResponseFactory $queryResponseFactory,
        ConfigInterface $ajaxConfig
    ) {
        $this->requestBuilderFactory = $requestBuilderFactory;
        $this->scopeResolver = $scopeResolver;
        $this->searchEngine = $searchEngine;
        $this->searchResponseBuilder = $searchResponseBuilder;
        $this->aggregationFactory = $aggregationFactory;
        $this->queryResponseFactory = $queryResponseFactory;
        $this->ajaxConfig = $ajaxConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        $searchResponse = $this->getGeneralSearchResponse($searchCriteria);
        if ($this->ajaxConfig->enabled()) {
            $searchResponse = $this->combineResponse(
                $searchResponse,
                $this->getPartialSearchResponses($searchCriteria)
            );
        }
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
                $this->addFieldToRequestBuilder($requestBuilder, $filter->getField(), $filter->getValue());
            }
        }

        $requestBuilder->setFrom($searchCriteria->getCurrentPage() * $searchCriteria->getPageSize());
        $requestBuilder->setSize($searchCriteria->getPageSize());
        $generalRequest = $requestBuilder->create();
        return $this->searchEngine->search($generalRequest);
    }

    /**
     * Prepare partial search responses for each active applied filter
     *
     * Send search request for each active applied filter to get proper facet data for that filter. Each request for
     * active applied filter includes all other filters except current. 
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     */
    protected function getPartialSearchResponses(SearchCriteriaInterface $searchCriteria)
    {
        $scope = $this->scopeResolver->getScope()->getId();
        $permanentFilterFields = ['search_term', 'visibility', 'price_dynamic_algorithm'];
        $permanentFilters = [];
        $filters = [];
        $filterFileldNames = [];
        $defaultCategoryFilterValue = null;
        // prepare filters
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                // TODO: check on search result page, maybe it would be better to use layer->getCurrentCategory()
                // There could be 2 filters by 'category_ids':
                // - filter for current category
                // - filter by subcategories from layered navigation
                // We need to store default filter by current category to use it for filtering partial search request
                // for 'category_ids' filter from layered navigation
                if (!$defaultCategoryFilterValue && $filter->getField() === 'category_ids') {
                    $defaultCategoryFilterValue = $filter->getValue();
                    continue;
                }
                // We need only real field name because price (and decimal fields) can have 2 filter objects with 
                // fields like 'price.from' and 'price.to'. In fact it is single filter by price.
                $realFieldName = str_replace(['.from', '.to'], '', $filter->getField());
                $filterFileldNames[$realFieldName] = $realFieldName;
                // permanent filters are list of filters which must be applied to each partial search request
                if (in_array($filter->getField(), $permanentFilterFields)) {
                    $permanentFilters[] = $filter;
                } else {
                    $filters[] = $filter;
                }
            }
        }

        $partialSearchResponses = [];
        // prepare partial search responses for each active applied filter
        foreach ($filterFileldNames as $currentFilterField) {
            if (in_array($currentFilterField, $permanentFilterFields)) {
                continue;
            }

            $requestBuilder = $this->requestBuilderFactory->create();
            $requestBuilder->setRequestName($searchCriteria->getRequestName());
            $requestBuilder->bindDimension('scope', $scope);
            // Add permanent filters. They don't depends on filter state in layered navigation
            foreach ($permanentFilters as $permanentFilter) {
                $this->addFieldToRequestBuilder($requestBuilder, $permanentFilter->getField(), $permanentFilter->getValue());
            }
            foreach ($filters as $filter) {
                // don't apply current filter
                // in case current filter is 'category_ids' apply default filter by current category
                if (str_replace(['.from', '.to'], '', $filter->getField()) == $currentFilterField) {
                    if ($filter->getField() === 'category_ids') {
                        $this->addFieldToRequestBuilder($requestBuilder, 'category_ids', $defaultCategoryFilterValue);
                    }
                    continue;
                }
                $this->addFieldToRequestBuilder($requestBuilder, $filter->getField(), $filter->getValue());
            }
            $requestBuilder->setFrom($searchCriteria->getCurrentPage() * $searchCriteria->getPageSize());
            $requestBuilder->setSize($searchCriteria->getPageSize());
            $partialRequest = $requestBuilder->create($currentFilterField);

            $partialSearchResponse = $this->searchEngine->search($partialRequest);
            $partialSearchResponses[] = $partialSearchResponse;
        }
        return $partialSearchResponses;
    }

    /**
     * Apply attribute filter to facet collection
     *
     * @param Builder $requestBuilder
     * @param string $field
     * @param string|array|null $condition
     * @return $this
     */
    protected function addFieldToRequestBuilder($requestBuilder, $field, $condition = null)
    {
        if (!is_array($condition) || !in_array(key($condition), ['from', 'to'], true)) {
            $requestBuilder->bind($field, $condition);
        } else {
            if (!empty($condition['from'])) {
                $requestBuilder->bind("{$field}.from", $condition['from']);
            }
            if (!empty($condition['to'])) {
                $requestBuilder->bind("{$field}.to", $condition['to']);
            }
        }

        return $this;
    }

    /**
     * Update given $generalSearchResponse with correct facet options from $partialSearchResponses
     *
     * "Merge" filter buckets from partial search response into general search response
     *
     * @param \Magento\Framework\Search\Response\QueryResponse $generalSearchResponse
     * @param array $partialSearchResponses
     * @return \Magento\Framework\Search\Response\QueryResponse
     */
    protected function combineResponse($generalSearchResponse, $partialSearchResponses)
    {
        $documents = iterator_to_array($generalSearchResponse);
        $buckets = $generalSearchResponse->getAggregations()->getBuckets();
        if (!is_array($buckets)) {
            $buckets = [];
        }

        foreach ($partialSearchResponses as $partialSearchResponse) {
            foreach ($partialSearchResponse->getAggregations()->getBuckets() as $bucketName => $partialResponseBucket) {
                $buckets[$bucketName] = $partialResponseBucket;
            }
        }

        $aggregations = $this->aggregationFactory->create(['buckets' => $buckets]);
        return $this->queryResponseFactory->create([
            'documents' => $documents,
            'aggregations' => $aggregations
        ]);
    }
}
