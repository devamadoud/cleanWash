<?php

namespace App\Services;

class CalculatorService
{
    public function percentage(int|null $amount1, int|null $amount2): int
    {
        // Si les deux montants sont nuls, la différence est de 0%
        if ($amount1 == 0 && $amount2 == 0) {
            return 0;
        }

        // Calculer la différence absolue entre les deux montants
        $difference = abs($amount1 - $amount2);

        // Calculer la moyenne des deux montants
        $average = ($amount1 + $amount2) / 2;

        // Calculer le pourcentage de différence
        $percentage = ($difference / $average) * 100;

        return $percentage;
    }

    // Retourne le prix de la promotion
    public function getPriceOfPromo(int|null $amount, int|null $promo): int
    {
        return $amount - $this->getPromotPrice($amount, $promo);
    }

    // Retourne le montant a soustraire du prix de base
    public function getPromotPrice(int|null $amount, int|null $promo): int
    {
        if($promo == 0){
            return $amount;
        }

        return $amount * $promo / 100;
    }
}