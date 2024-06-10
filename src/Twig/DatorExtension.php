<?php 

namespace App\Twig;

use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DatorExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction("days_between", [$this, "daysBetween"]),
        ];
    }

    public function daysBetween(DateTimeInterface $date1, DateTimeInterface $date2): int
    {
        $interval = $date1->diff($date2);

        return $interval->days;
    }
}