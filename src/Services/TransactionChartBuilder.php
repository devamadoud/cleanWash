<?php

namespace App\Services;

use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionChartBuilder
{

    public function build($chartBuilder, $transactions, $label)
    {
        $labels = [];
        $orderCount = [];
        $totalAmount = [];

        foreach ($transactions['transactionsByDay'] as $transaction) {
            $labels[] = $transaction['dayName'];
            $orderCount[] = $transaction['orderCount'];
            $totalAmount[] = $transaction['totalAmount'];
        }

        $chartBuilder->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => 'rgb(93, 105, 179)',
                    'borderColor' => 'rgb(93, 105, 179)',
                    'data' => $orderCount,
                    'yAxisID' => 'y-transaction-count'
                ],
                [
                    'label' => 'Montant total des transaction',
                    'backgroundColor' => 'rgb(5, 150, 115)',
                    'borderColor' => 'rgb(5, 150, 115)',
                    'data' => $totalAmount,
                    'yAxisID' => 'y-transaction-amount'
                ],
            ],
        ]);

        $chartBuilder->setOptions([
            'scales' => [
                'y-transaction-count' => [
                    'suggestedMin' => 5,
                    'suggestedMax' => 25,
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 5,
                        'min' => 5,
                        'max' => 50,
                        'autoSkip' => false,
                        'ticks' => [
                            'callback' => 'function(value) { return Number(value).toLocaleString(); }'
                        ]
                    ]
                ],
                'y-transaction-amount' => [
                    'suggestedMin' => 10000,
                    'suggestedMax' => 1000000,
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 100000,
                        'maxTicksLimit' => 6,
                        'autoSkip' => true,
                        'includeBounds' => true,
                    ]
                ],
            ],
        ]);

        return $chartBuilder;
    }
}
