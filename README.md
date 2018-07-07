PaginationBundle
===============================
[![Build Status](http://drone.datajob.lt/deathstar.datajob.lt/php/pagination-bundle/status.svg?branch=master)](http://drone.datajob.lt/deathstar.datajob.lt/php/pagination-bundle)

Simple pagination

## Install

composer.json:

``` json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "ssh://git@pm.datajob.lt:btc-x/btcpaginationbundle.git"
    }
  ],
  "require": [
    {
      "btc-x/pagination-bundle": "~0.1"
    }
  ]
}
```


## Configuration template (defaults)

Template configuration config.yml:

``` yaml
btc_pagination:
  template:
    navigation: "BtcPaginationBundle:Pagination:navigation.html.twig"
    filter: "BtcPaginationBundle:Pagination:filter.html.twig"
    sorting: "BtcPaginationBundle:Pagination:sorting.html.twig"

```

Defaults:

 *  pagination slider shows 5 page range
 *  minimum limit items per page is 10
 *  maximum limit items per page is 100

If you want to override default page limits, add these lines in *app/config/config.yml*

```
services:
  paginator:
    class: Btc\PaginationBundle\Paginator
    arguments: [ {min: 50, max:200} ]
```

## Basic example
``` php
<?php

$repository = new SomeRepository();
$qb = $repository->getFindAllQueryBuilder();

$filters = [
    /* any of your filters */
    new PageLimitFilter();
];

$sorting = null; /* or construct ur sorting stuff */

$target = new Target($qb, $sorting, $filters);

$paginator = new Paginator();

try {
    $items = $paginator->paginate(/*Symfony HTTP Request obj.*/$request, $target);
} catch (\Exception $e) {
    // handle exceptions
}


```

``` twig
# File: sample.html.twig
# assuming you have passed $items from previous example into twig template

{{ items|page_filter('limit') }} # render items per page widget
{{ items|page_navigation() }} # renders the navigation buttons between pages
{{ items|page_sorting('name') }} # renders your sorting widget

```
