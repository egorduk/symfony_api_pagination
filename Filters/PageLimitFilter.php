<?php

namespace Btc\PaginationBundle\Filters;

use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\ORM\QueryBuilder;
use Btc\PaginationBundle\FilterInterface;

class PageLimitFilter implements FilterInterface
{
    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return 'limit';
    }

    /**
     * {@inheritDoc}
     */
    public function apply(QueryBuilder $qb, ParameterBag $params)
    {
        // applied by paginator
    }

    /**
     * {@inheritDoc}
     */
    public function domain()
    {
        return 'Pagination';
    }

    /**
     * {@inheritDoc}
     */
    public function applyDefault()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function options()
    {
        return [10, 25, 50, 100];
    }

    /**
     * {@inheritDoc}
     */
    public function trans($option, $main = false)
    {
        return $main ? 'pagination.filter.limit.' . $option : $option;
    }

    /**
     * {@inheritDoc}
     */
    public function active(array $params)
    {
        return array_key_exists($this->name(), $params) ? $this->find(intval($params[$this->name()])) : 10;
    }

    /**
     * {@inheritDoc}
     */
    private function find($option)
    {
        $key = array_search($option, $this->options(), true);

        return $key !== false ? $this->options()[$key] : 10;
    }
}
