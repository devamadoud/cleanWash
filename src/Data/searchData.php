<?php
namespace App\Data;

use Symfony\Component\Validator\Constraints\Collection;

class searchData
{   
    /**
    * @var int
    */
    public int $page = 1;

    /**
    * @var null|string
    */
    public string $q;

    /**
    * @var null|int
    */
    public $category;

    /**
    * @var null|float $min
    */
    // public float $min;

    /**
    * @var null|float $max
    */
    // public float $max;

    /**
    * @var null|bool $promo
    */
    // public bool $promo = false;
}