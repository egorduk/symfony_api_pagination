<?php

namespace Btc\PaginationBundle;

use Doctrine\ORM\QueryBuilder;

/**
 * Default pagination target class
 */
class Target implements TargetInterface
{
    protected $filters = [];
    protected $sorter;
    protected $qb;
    protected $counter;

    /**
     * Construct pagination target
     *
     * @param \Doctrine\ORM\QueryBuilder $qb - query builder to paginate
     * @param SorterInterface - sorting specification
     * @param array $filters - filters which can be applied
     */
    public function __construct(QueryBuilder $qb, SorterInterface $sorter = null, array $filters = array())
    {
        $this->qb = $qb;
        $this->filters = $filters;
        $this->sorter = $sorter;
        $this->counter = clone $qb;
    }

    /**
     * {@inheritDoc}
     */
    public function setCounterQueryBuilder(QueryBuilder $qb)
    {
        $this->counter = $qb;
    }

    /**
     * {@inheritDoc}
     */
    public function getCounterQueryBuilder()
    {
        return clone $this->counter;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryBuilder()
    {
        return clone $this->qb;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * {@inheritDoc}
     */
    public function getSorter()
    {
        return $this->sorter;
    }
}
