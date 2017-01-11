<?php

namespace SomethingDigital\AjaxLayeredNav\Model\Plugin\Mysql;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Range;
use Magento\Framework\Search\Request\Filter\Range as RangeFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use SomethingDigital\AjaxLayeredNav\Model\ConfigInterface;

class RangeBuilderPlugin
{
    protected $conditionManager = null;
    protected $ajaxConfig = null;

    public function __construct(ConditionManager $conditionManager, ConfigInterface $ajaxConfig)
    {
        $this->conditionManager = $conditionManager;
        $this->ajaxConfig = $ajaxConfig;
    }

    /**
     * Add multi-select support to the range filter builder.
     *
     * @param Range $subject Range filter builder.
     * @param callable $proceed Actual method or next plugin.
     * @param RequestFilterInterface $filter Filter information.
     * @param bool $isNegation Whether filter is being negated.
     * @return string
     */
    public function aroundBuildFilter(Range $subject, $proceed, RequestFilterInterface $filter, $isNegation)
    {
        // Note that this may call $proceed multiple times.
        if ($filter instanceof RangeFilterRequest && $this->ajaxConfig->enabled()) {
            $froms = explode(',', $filter->getFrom());
            $tos = explode(',', $filter->getTo());

            // As long as they match, we can pair them up - let's go.
            if (count($froms) == count($tos) && count($froms) > 1) {
                return $this->buildFilterByParts($proceed, $filter, $froms, $tos, $isNegation);
            }
        }

        return $proceed($filter, $isNegation);
    }

    protected function buildFilterByParts($proceed, RangeFilterRequest $filter, array $froms, array $tos, $isNegation)
    {
        $parts = [];
        foreach ($froms as $k => $from) {
            $to = $tos[$k];

            $partFilter = new RangeFilterRequest($filter->getName(), $filter->getField(), $from, $to);
            $parts[] = '(' . $proceed($partFilter, $isNegation) . ')';
        }

        // Okay, now we have each part of the multiselect, time to combine.
        $operator = $this->getMultiselectUnionOperator($isNegation);
        return $this->conditionManager->combineQueries($parts, $operator);
    }

    protected function getMultiselectUnionOperator($isNegation)
    {
        return $isNegation ? Select::SQL_AND : Select::SQL_OR;
    }
}
