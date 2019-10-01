<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

abstract class GroupFilter implements FilterInterface
{
    /**
     * @var FilterInterface[]
     */
    private $filters;

    public function __construct(FilterInterface...$filters)
    {
        $this->filters = $filters;
    }

    public function toArray(): array
    {
        $filtersArray = [];
        foreach ($this->filters as $filter) {
            if ($filter instanceof FilterInterface) {
                $filter = $filter->toArray();
            }
            $filtersArray[] = $filter;
        }
        return [static::getOperator(), $filtersArray];
    }

    /**
     * Building criteria with array
     *
     * ~~~
     * $dataReader->withFilters((new All())->withArray(
     *   [
     *     ['>', 'id', 88],
     *     ['or', [
     *        ['=', 'state', 2],
     *        ['like', 'name', 'eva'],
     *     ],
     *   ]
     * ))
     * ~~~
     *
     * @param array $filtersArray
     * @return static
     */
    public function withFiltersArray(array $filtersArray)
    {
        foreach ($filtersArray as $key => $filter) {
            if ($filter instanceof FilterInterface) {
                continue;
            } elseif (!is_array($filter)) {
                throw new \RuntimeException(sprintf('Invalid filter at "%s" key', $key));
            }
            $first = array_shift($filter);
            if (!is_string($first) || !strlen($first)) {
                throw new \RuntimeException(sprintf('Invalid filter operator on "%s" key', $key));
            }
        }
        $new = clone $this;
        $new->filters = $filtersArray;
        return $new;
    }
}
