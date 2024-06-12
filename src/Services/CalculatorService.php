<?php

namespace App\Services;

class CalculatorService
{
    public function percentage($amount1, $amount2)
    {
        if($amount1 == 0 && $amount2 == 0){
            return 0;
        }

        $difference = abs($amount1 - $amount2);
        $somme = ($amount1 + $amount2) / 2;

        $pourcentage = ($difference / $somme) * 100;
        
        return $pourcentage;
    }
}