<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Search\Request;

use Magento\Framework\Search\Request\Builder as BuilderBase;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Search\RequestInterface;

class Builder extends BuilderBase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Binder
     */
    private $binder;

    /**
     * @var array
     */
    private $data = [
        'dimensions' => [],
        'placeholder' => [],
    ];

    /**
     * @var Cleaner
     */
    private $cleaner;

    /**
     * Request Builder constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     * @param Binder $binder
     * @param Cleaner $cleaner
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config, Binder $binder, Cleaner $cleaner)
    {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->binder = $binder;
        $this->cleaner = $cleaner;
    }

    /**
     * Create request object
     *
     * Creates general full request.
     * In case $currentFilterField is not null, method will create short request object for retrieving facet options
     * for current filter only
     *
     * @param string $currentFilterField
     * @return RequestInterface
     */
    public function create($currentFilterField = null)
    {
        if (!isset($this->data['requestName'])) {
            throw new \InvalidArgumentException("Request name not defined.");
        }
        $requestName = $this->data['requestName'];
        /** @var array $data */
        $data = $this->config->get($requestName);
        if ($data === null) {
            throw new NonExistingRequestNameException(new Phrase("Request name '%1' doesn't exist.", [$requestName]));
        }

        if ($currentFilterField) {
            // Update list of requested aggregations (buckets with facet options) to remove all except bucket for
            // current field
            $aggregations = $data['aggregations'];
            $refinedAggregations = [];
            foreach ($aggregations as $aggregationName => $aggregation) {
                if ($aggregation['field'] != $currentFilterField) {
                    continue;
                }
                $refinedAggregations[$aggregationName] = $aggregation;
            }
            $data['aggregations'] = $refinedAggregations;
        }

        $data = $this->binder->bind($data, $this->data);
        $data = $this->cleaner->clean($data);

        $this->clear();

        return $this->convert($data);
    }

    /**
     * Clear data
     *
     * @return void
     */
    private function clear()
    {
        $this->data = [
            'dimensions' => [],
            'placeholder' => [],
        ];
    }

    /**
     * Convert array to Request instance
     *
     * @param array $data
     * @return RequestInterface
     */
    private function convert($data)
    {
        /** @var Mapper $mapper */
        $mapper = $this->objectManager->create(
            \Magento\Framework\Search\Request\Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'rootQueryName' => $data['query'],
                'queries' => $data['queries'],
                'aggregations' => $data['aggregations'],
                'filters' => $data['filters']
            ]
        );
        return $this->objectManager->create(
            \Magento\Framework\Search\Request::class,
            [
                'name' => $data['query'],
                'indexName' => $data['index'],
                'from' => $data['from'],
                'size' => $data['size'],
                'query' => $mapper->getRootQuery(),
                'dimensions' => $this->buildDimensions(isset($data['dimensions']) ? $data['dimensions'] : []),
                'buckets' => $mapper->getBuckets()
            ]
        );
    }

    /**
     * @param array $dimensionsData
     * @return array
     */
    private function buildDimensions(array $dimensionsData)
    {
        $dimensions = [];
        foreach ($dimensionsData as $dimensionData) {
            $dimensions[$dimensionData['name']] = $this->objectManager->create(
                \Magento\Framework\Search\Request\Dimension::class,
                $dimensionData
            );
        }
        return $dimensions;
    }
}
