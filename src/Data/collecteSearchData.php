<?php

namespace App\Data;

class collecteSearchData
{
    /**
    * @var Shop|int
    */
    public $shop;

    /**
    * @var int
    */
    public int $page = 1;
    
    /**
    * @var null|string
    */
    public string $ref;

    /**
    * @var null|string
    */
    public string $tel;

    /**
    * @var null|string
    */
    public string $dateFrom;

    /**
    * @var null|string
    */
    public string $dateTo;

    /**
    * @var null|string
    */
    public string $status;
}