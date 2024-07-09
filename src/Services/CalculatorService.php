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
}