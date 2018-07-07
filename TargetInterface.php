<?php

namespace Btc\PaginationBundle;

interface TargetInterface
{
    /**
     * Get query builder to paginate
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    function getQueryBuilder();

    /**
     * Get counter query builder to paginate
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    function getCounterQueryBuilder();

    /**
     * Get filters which can be applied
     *
     * @return array
     */
    function getFilters();

    /**
     * Get sorter which manages sorting
     *
     * @return SorterInterface
     */
    function getSorter();
}
