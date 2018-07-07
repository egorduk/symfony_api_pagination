<?php

namespace Btc\PaginationBundle;

use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\ORM\QueryBuilder;

interface SorterInterface
{
    /**
     * Get sorter name, used as query parameter name
     *
     * @return string
     */
    function name();

    /**
     * Get sorter direction query parameter name
     *
     * @return string
     */
    function direction();

    /**
     * Field which should be sorted by default
     *
     * @return string - empty string means no default sorting
     */
    function defaultField();

    /**
     * Get default sort field direction.
     * Ignored if default sort field is not specified
     *
     * @return string - asc or desc
     */
    function defaultDirection();

    /**
     * Get all allowed sorting fields.
     * If empty, sorting cannot be applied, including default
     *
     * @return array
     */
    function allowed();

    /**
     * Apply sorting on query builer $qb
     * based on request $params
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param \Symfony\Component\HttpFoundation\ParameterBag $params
     */
    function apply(QueryBuilder $qb, ParameterBag $params);
}
