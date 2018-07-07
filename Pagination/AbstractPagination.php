<?php

namespace Btc\PaginationBundle\Pagination;

use Btc\CoreBundle\Entity\User;
use Countable, Iterator, ArrayAccess;
use Btc\PaginationBundle\TargetInterface;
use Btc\PaginationBundle\FilterInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractPagination implements Countable, Iterator, ArrayAccess
{
    protected $currentPageNumber;
    protected $numItemsPerPage;
    protected $items = [];
    protected $totalCount;
    protected $totalPageCount;
    protected $route;
    protected $routeParams;

    private $target;

    public function __construct(ParameterBag $routeParams, TargetInterface $target)
    {
        $this->routeParams = $routeParams;
        $this->target = $target;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        reset($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return current($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return key($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        next($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return key($this->items) !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrentPageNumber($pageNumber)
    {
        $this->currentPageNumber = $pageNumber;
    }

    /**
     * Get currently used page number
     *
     * @return integer
     */
    public function getCurrentPageNumber()
    {
        return $this->currentPageNumber;
    }

    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * {@inheritDoc}
     */
    public function setItemNumberPerPage($numItemsPerPage)
    {
        $this->numItemsPerPage = $numItemsPerPage;
    }

    /**
     * Get number of items per page
     *
     * @return integer
     */
    public function getItemNumberPerPage()
    {
        return $this->numItemsPerPage;
    }

    /**
     * {@inheritDoc}
     */
    public function setTotalItemCount($numTotal)
    {
        $this->totalCount = $numTotal;
    }

    /**
     * Get total item number available
     *
     * @return integer
     */
    public function getTotalItemCount()
    {
        return $this->totalCount;
    }

    /**
     * {@inheritDoc}
     */
    public function setItems($items)
    {
        if (!is_array($items) && !$items instanceof \Traversable) {
            throw new \UnexpectedValueException("Items must be of an array type");
        }

        $this->items = $items;
    }

    /**
     * Get current items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public function getQuery()
    {
        return $this->routeParams->all();
    }

    public function setQueryParam($name, $value)
    {
        $this->routeParams->set($name, $value);
    }

    public function navigation()
    {
        $data = $this->paginationData();
        $data['route'] = $this->route;

        $routeParams = $this->routeParams->all();

        if (array_key_exists('user', $routeParams)) {
            $user = $routeParams['user'];

            if ($user instanceof User) {
                $data['query'] = ['user' => $user->getId()];
            } else {
                $data['query'] = $routeParams;
            }
        } else {
            $data['query'] = $routeParams;
        }

        return $data;
    }

    public function sortable($field, $title)
    {
        $sorter = $this->target->getSorter();
        $sorted = $this->routeParams->get($sorter->name()) === $field;

        // default direction to sort
        $direction = $sorter->defaultField() === $field && $sorted ?
            $sorter->defaultDirection() : 'asc';
        // was sorted already
        if ($sorted) {
            $direction = $this->routeParams->get($sorter->direction(), $direction);
            $direction = (strtolower($direction) == 'asc') ? 'desc' : 'asc';
        }

        $query = array_merge(
            $this->routeParams->all(), [
                $sorter->name() => $field,
                $sorter->direction() => $direction,
                'page' => 1, // reset to 1 on sort
            ]
        );

        $data = [
            'query' => $query,
            'sorted' => $sorted,
            'route' => $this->route,
            'direction' => $direction,
            'title' => $title,
            'field' => $field
        ];

        return $data;
    }

    public function filter($name)
    {
        $filter = current(
            array_filter(
                $this->target->getFilters(),
                function (FilterInterface $filter) use ($name) {
                    return $filter->name() === $name;
                }
            )
        );

        if ($filter === false) {
            throw new \UnexpectedValueException("There is no filter: '{$name}' registered");
        }

        $data = [
            'route' => $this->route,
            'filter' => $filter,
        ];

        $routeParams = $this->routeParams->all();

        if (array_key_exists('user', $routeParams)) {
            $user = $routeParams['user'];

            if ($user instanceof User) {
                $data['query'] = array_merge($routeParams, ['user' => $user->getId()]);
            } else {
                $data['query'] = $routeParams;
            }
        } else {
            $data['query'] = $routeParams;
        }

        return $data;
    }

    abstract protected function paginationData();

    public function setTotalPageCount($numTotal)
    {
        $this->totalPageCount = $numTotal;
    }

    public function getTotalPageCount() {
        return $this->totalPageCount;
    }
}
