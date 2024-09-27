<?php
namespace App\Services;

use NumberToWords\NumberToWords;

class NumberToWord
{
    public function convert(float $number): string
    {
        $words = new NumberToWords();
        $transformers = $words->getNumberTransformer('fr');

        return $transformers->toWords($number);
    }
}