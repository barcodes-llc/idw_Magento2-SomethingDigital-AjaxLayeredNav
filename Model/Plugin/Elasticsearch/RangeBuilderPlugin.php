<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Plugin\Elasticsearch;

use Magento\Elasticsearch\SearchAdapter\Filter\Builder;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range;
use Magento\Framework\Search\Request\Filter\Range as RangeFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class RangeBuilderPlugin
{
    protected $ajaxConfig = null;

    public function __construct(ConfigInterface $ajaxConfig)
    {
        $this->ajaxConfig = $ajaxConfig;
    }

    /**
     * Add multi-select support to the range filter builder.
     *
     * @param Range $subject Range filter builder.
     * @param callable $proceed Actual method or next plugin.
     * @param RequestFilterInterface $filter Filter information.
     * @return array
     */
    public function aroundBuildFilter(Range $subject, $proceed, RequestFilterInterface $filter)
    {
        // Note that this may call $proceed multiple times.
        if ($filter instanceof RangeFilterRequest && $this->ajaxConfig->enabled()) {
            $froms = explode(',', $filter->getFrom());
            $tos = explode(',', $filter->getTo());

            // As long as they match, we can pair them up - let's go.
            if (count($froms) == count($tos) && count($froms) > 1) {
                return $this->buildFilterByParts($proceed, $filter, $froms, $tos);
            }
        }

        return $proceed($filter);
    }

    protected function buildFilterByParts($proceed, RangeFilterRequest $filter, array $froms, array $tos)
    {
        $parts = [];
        foreach ($froms as $k => $from) {
            $to = $tos[$k];

            $partFilter = new RangeFilterRequest($filter->getName(), $filter->getField(), $from, $to);
            $parts[] = $proceed($partFilter);
        }

        // Okay, now we have each part of the multiselect, time to combine to an OR.
        return [[
            'bool' => [
                Builder::QUERY_CONDITION_SHOULD => $parts,
                'minimum_should_match' => 1,
            ]],
        ];
    }
}
