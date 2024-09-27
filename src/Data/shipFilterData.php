<?php

namespace App\Data;

class shipFilterData
{
    /**
    * @var null|int
    */
    public $shop;

    /**
    * @var int
    */
    public int $page = 1;
    
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
}