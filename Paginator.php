<?php

namespace Btc\PaginationBundle;

use Btc\CoreBundle\Entity\Currency;
use Btc\CoreBundle\Entity\Deal;
use Btc\CoreBundle\Entity\Market;
use Btc\CoreBundle\Entity\Order;
use Btc\CoreBundle\Entity\Transaction;
use Btc\CoreBundle\Entity\User;
use Btc\CoreBundle\Entity\Wallet;
use Btc\PaginationBundle\Pagination\SlidingPagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class Paginator
{
    private $parameters = [
        'min' => 1,
        'max' => 100,
    ];

    public function __construct($params = array())
    {
        $this->parameters = array_merge($this->parameters, $params);
    }

    /**
     * Paginates $target based on request parameters
     *
     * @param Request $request
     * @param TargetInterface $target
     * @param bool $countDistinct
     *
     * @return SlidingPagination
     */
    public function paginate(Request $request, TargetInterface $target, $countDistinct = false)
    {
        $params = $this->getParams($request);
        $limit = $this->getLimit($params);
        $request->query->set('limit', $limit);
        $page = $this->getPage($params);
        $offset = abs($page - 1) * $limit;

        $pagination = new SlidingPagination($params, $target);

        $qb = $target->getQueryBuilder();
        // to count total items, do not sort it
        $counter = $target->getCounterQueryBuilder();
        // determine filters which needs to be applied based on request
        $applyable = array_filter(
            $target->getFilters(),
            function ($filter) use ($params) {
                return $params->has($filter->name()) || $filter->applyDefault();
            }
        );

        foreach ($applyable as $filter) {
            $filter->apply($qb, $params);
            $filter->apply($counter, $params);
        }

        // apply sorting if available
        if ($target->getSorter()) {
            $target->getSorter()->apply($qb, $params);
        }

        // offset and limit
        $qb->setFirstResult($offset);
        $qb->setMaxResults($limit);

        // pagination details
        if ($countDistinct) {
            $counter->select('COUNT(DISTINCT ' . $counter->getRootAlias() . ')');
        } else {
            $counter->select('COUNT(' . $counter->getRootAlias() . ')');
        }

        $pagination->setTotalItemCount(intval($counter->getQuery()->getSingleScalarResult()));
        $pagination->setItems($qb->getQuery()->getResult());
        $pagination->setItemNumberPerPage($limit);
        $pagination->setCurrentPageNumber($page);
        $pagination->setRoute($request->attributes->get('_route'));

        $totalPageCount = ceil($pagination->getTotalItemCount() / $limit);
        $pagination->setTotalPageCount($totalPageCount);

        return $pagination;
    }

    /**
     * @param Request $request
     * @param TargetInterface $target
     * @param [] $data
     *
     * @return SlidingPagination
     */
    public function paginateTradesFilter(Request $request, TargetInterface $target, array $data = [])
    {
        $params = $this->getParams($request);
        $limit = $this->getLimit($params);
        $request->query->set('limit', $limit);
        $page = $this->getPage($params);
        $offset = abs($page - 1) * $limit;

        $query = null;
        $parameters = [];

        $em = $target->getQueryBuilder()->getEntityManager();

        $sql = 'SELECT d.id, d.createdAt, cr1.code code1, cr2.code code2, o.id orderId,
              SUM(ABS(t.amount*t.price))/2 value, 
              SUM((o.feePercent/100)*ABS(t.amount*t.price)) feesCollected, 
              SUM(case when t.amount > 0 then t.amount else 0 end) amnt 
              FROM '.Deal::class.' d 
              INNER JOIN '.Transaction::class.' t WITH d.id = t.deal
              INNER JOIN '.Market::class.' m WITH m.id = t.market
              INNER JOIN '.Currency::class.' cr1 WITH cr1.id = m.currency
              INNER JOIN '.Currency::class.' cr2 WITH cr2.id = m.withCurrency
              LEFT JOIN '.Order::class.' o WITH t.order = o.id
              %s
              GROUP BY d.id';

        if (isset($data['amountFrom']) || isset($data['amountTo'])) {
            $to = $data['amountTo'];

            $parameters['from'] = !$data['amountFrom'] ? 0 : $data['amountFrom'];

            $sql .= ' HAVING amnt >= :from';

            if ($to !== null) {
                $sql .= ' AND amnt <= :to';
                $parameters['to'] = $to;
            }
        } elseif (isset($data['orderValueTo'])) {
            $to = $data['orderValueTo'];

            $parameters['from'] = !$data['orderValueFrom'] ? 0 : $data['orderValueFrom'];

            $sql .= ' HAVING value >= :from';

            if ($to !== null) {
                $sql .= ' AND value <= :to';
                $parameters['to'] = $to;
            }
        } elseif (isset($data['orderId'])) {
            $parameters['orderId'] = $data['orderId'];

            $sql = sprintf($sql, ' WHERE o.id = :orderId');
        } elseif (isset($data['buyerEmail'])) {
            $parameters = [
                'email' => $data['buyerEmail'],
                'side' => Order::SIDE_BUY,
            ];

            $sql = sprintf($sql, ' 
            INNER JOIN '.Wallet::class.' w WITH o.outWallet = w 
            INNER JOIN '.User::class.' u WITH w.user = u 
            WHERE u.email = :email AND o.side = :side');
        } elseif (isset($data['sellerEmail'])) {
            $parameters = [
                'email' => $data['sellerEmail'],
                'side' => Order::SIDE_SELL,
            ];

            $sql = sprintf($sql, ' INNER JOIN '.Wallet::class.' w WITH o.inWallet = w 
            INNER JOIN '.User::class.' u WITH w.user = u 
            WHERE u.email = :email AND o.side = :side');
        } elseif (isset($data['dateFrom']) && isset($data['dateTo'])) {
            $parameters = [
                'dateFrom' => date('Y-m-d 00:00:00', ($data['dateFrom']->getTimestamp())),
                'dateTo' => date('Y-m-d 23:59:59', ($data['dateTo']->getTimestamp())),
            ];

            $sql = sprintf($sql, ' WHERE d.createdAt >= :dateFrom AND d.createdAt <= :dateTo');
        } elseif (isset($data['currency'])) {
            $parameters['currency'] = $data['currency'];

            $sql = sprintf($sql, ' WHERE cr1.code = :currency OR cr2.code = :currency');
        }

        $sql = sprintf($sql, '').' ORDER BY d.id DESC';

        $query = $em
            ->createQuery($sql)
            ->setParameters($parameters);

        $deals = $query->getResult();
        $cnt = count($deals);

        $deals = array_slice($deals, $offset, $limit);

        $pagination = new SlidingPagination($params, $target);
        $pagination->setTotalItemCount($cnt);
        $pagination->setItems($deals);
        $pagination->setItemNumberPerPage($limit);
        $pagination->setCurrentPageNumber($page);
        $pagination->setRoute($request->attributes->get('_route'));

        return $pagination;
    }

    /**
     * Extracts all relevant parameters for pagination
     * from $request. Merges query and attributes, leaving
     * internal parameters outside.
     *
     * @param Request $request
     * @return ParameterBag
     */
    private function getParams(Request $request)
    {
        $params = array_merge($request->query->all(), $request->attributes->all());

        foreach ($params as $key => $param) {
            if (substr($key, 0, 1) === '_') {
                unset($params[$key]);
            }
        }

        return new ParameterBag($params);
    }

    /**
     * Normalizes limit
     * Fallbacks to max 100 or min 10 as default
     *
     * @param ParameterBag $params
     * @return integer
     */
    private function getLimit(ParameterBag $params)
    {
        $min = $this->parameters['min'];
        $max = $this->parameters['max'];

        $limit = intval(abs($params->get('limit', $min)));
        if ($limit > $max) {
            $limit = $max;
        } elseif ($limit < $min) {
            $limit = $min;
        }

        return $limit;
    }

    /**
     * Normalizes page
     * Fallbacks to max 1 if not valid
     *
     * @param ParameterBag $params
     * @return integer
     */
    private function getPage(ParameterBag $params)
    {
        $page = intval(abs($params->get('page', 1)));

        if ($page < 1) {
            $page = 1;
        }

        return $page;
    }
}
