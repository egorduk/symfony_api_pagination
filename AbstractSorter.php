<?php

namespace Btc\PaginationBundle;

use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\ORM\QueryBuilder;
use Btc\PaginationBundle\Exception\UnsupportedSortingFieldException;

abstract class AbstractSorter implements SorterInterface
{
    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return 'sort';
    }

    /**
     * {@inheritDoc}
     */
    public function direction()
    {
        return 'direction';
    }

    /**
     * {@inheritDoc}
     */
    function apply(QueryBuilder $qb, ParameterBag $params)
    {
        // set default sorting if not sorted
        if (!$params->has($this->name()) && $this->defaultField()) {
            $params->set($this->name(), $this->defaultField());
            $params->set($this->direction(), $this->defaultDirection());
        }
        // apply sorting
        if ($field = $params->get($this->name())) {
            // whitelist
            if (!in_array($field, $this->allowed(), true)) {
                throw new UnsupportedSortingFieldException($field);
            }
            $direction = strtolower($params->get($this->direction()));
            $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';
            $qb->orderBy($field, $direction);
        }
    }
}
