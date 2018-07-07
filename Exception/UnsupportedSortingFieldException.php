<?php

namespace Btc\PaginationBundle\Exception;

class UnsupportedSortingFieldException extends \UnexpectedValueException
{
    public function __construct($field)
    {
        parent::__construct("Sorting is not allowed on field: '$field'");
    }
}
