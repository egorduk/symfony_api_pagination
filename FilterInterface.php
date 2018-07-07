<?php

namespace Btc\PaginationBundle;

use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\ORM\QueryBuilder;

interface FilterInterface
{
    const OPTION_ANY = 'any';

    /**
     * Get filter name, used as query parameter name
     *
     * @return string
     */
    function name();

    /**
     * Get translation domain name
     *
     * @return string
     */
    function domain();

    /**
     * Apply filtration on query builer $qb
     * based on request $params
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param \Symfony\Component\HttpFoundation\ParameterBag $params
     */
    function apply(QueryBuilder $qb, ParameterBag $params);

    /**
     * Get filtration options available
     *
     * @return array
     */
    function options();

    /**
     * Apply filter even if it does not have a parameter in request
     * useful when some defaults needs to be applied
     *
     * @return boolean
     */
    function applyDefault();

    /**
     * Get a translation key based on $option
     *
     * @param string $option
     * @param boolean $main - whether the item is main in head
     * @return string
     */
    function trans($option, $main = false);

    /**
     * Get active option based on request $params
     *
     * @param array $params
     * @return string
     */
    function active(array $params);
}
