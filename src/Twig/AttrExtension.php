<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AttrExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('attr_without_value', [$this, 'attrWithoutValue'], ['is_safe' => ['html']]),
        ];
    }

    public function attrWithoutValue(object $attributes) :string
    {
        $output = '';

        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $output .= ' ' . $key;
            } else {
                $output .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
            }
        }

        return trim($output);
    }
}
