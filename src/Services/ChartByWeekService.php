<?php 

namespace App\Services;

use App\Entity\Collecte;
use App\Entity\Order;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;

class ChartByWeekService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, private $shop)
    {
        $this->entityManager = $entityManager;
        $this->shop = $shop;
    }

    public function getOrderByWeek()
    {
        $results = $this->entityManager->getRepository(Order::class)->getTransactionsCountByDay($this->shop);
        $totalAmountLast7Days = $this->entityManager->getRepository(Order::class)->getTotalTransactionAmountLast7Days($this->shop);
        $totalAmountPrevious7Days = $this->entityManager->getRepository(Order::class)->getTotalTransactionAmountPrevious7Days($this->shop);
        //$orderByDay = $this->getDashboardData($this->shop);
        
        // Initialize an array to hold transactions by day
        $transactionsByDay = [];

        // Create an array of all dates in the last 7 days
        $currentDate = Carbon::now();
        $dates = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $currentDate->copy()->subDays($i);
            $dates[$date->format('Y-m-d')] = [
                'dayName' => $date->locale('fr_FR')->isoFormat('dddd'), // Full name of the day in French
                'orderCount' => 0,
                'totalAmount' => 0
            ];
        }

        // Merge the actual results with the initialized array
        foreach ($results as $result) {
            $dates[$result['dayDate']]['orderCount'] = $result['orderCount'];
            $dates[$result['dayDate']]['totalAmount'] = $result['totalAmount'];
        }

        // Convert the associative array to an indexed array
        foreach ($dates as $date => $data) {
            $transactionsByDay[] = array_merge(['dayDate' => $date,], $data);
        }

         return [
            'transactionsByDay' => $transactionsByDay,
            'totalAmountLast7Days' => $totalAmountLast7Days,
            'totalAmountPrevious7Days' => $totalAmountPrevious7Days
        ];
        
    }

    public function getCollecteByWeek()
    {
        $results = $this->entityManager->getRepository(Collecte::class)->getTransactionsCountByDay($this->shop);
        $totalAmountLast7Days = $this->entityManager->getRepository(Collecte::class)->getTotalTransactionAmountLast7Days($this->shop);
        $totalAmountPrevious7Days = $this->entityManager->getRepository(Collecte::class)->getTotalTransactionAmountPrevious7Days($this->shop);
        //$orderByDay = $this->getDashboardData($this->shop);
        
        // Initialize an array to hold transactions by day
        $transactionsByDay = [];

        // Create an array of all dates in the last 7 days
        $currentDate = Carbon::now();
        $dates = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $currentDate->copy()->subDays($i);
            $dates[$date->format('Y-m-d')] = [
                'dayName' => $date->locale('fr_FR')->isoFormat('dddd'), // Full name of the day in French
                'orderCount' => 0,
                'totalAmount' => 0
            ];
        }

        // Merge the actual results with the initialized array
        foreach ($results as $result) {
            $dates[$result['dayDate']]['orderCount'] = $result['orderCount'];
            $dates[$result['dayDate']]['totalAmount'] = $result['totalAmount'];
        }

        // Convert the associative array to an indexed array
        foreach ($dates as $date => $data) {
            $transactionsByDay[] = array_merge(['dayDate' => $date,], $data);
        }

         return [
            'transactionsByDay' => $transactionsByDay,
            'totalAmountLast7Days' => $totalAmountLast7Days,
            'totalAmountPrevious7Days' => $totalAmountPrevious7Days
        ];
        
    }

    public function getDashboardData()
    {
        $query = $this->entityManager->createQuery(
            'SELECT 
                COUNT(o) as orderCount, 
                DAYNAME(o.createdAt) as dayName, 
                DATE(o.createdAt) as dayDate,
                (SELECT SUM(o1.totale) 
                FROM App\Entity\Order o1 
                WHERE o1.shop = :shop 
                AND o1.createdAt >= :startDate 
                AND o1.createdAt < :endDate) as totalAmountLast7Days,
                (SELECT SUM(o2.totale) 
                FROM App\Entity\Order o2 
                WHERE o2.shop = :shop 
                AND o2.createdAt >= :startDatePrevious 
                AND o2.createdAt < :startDate) as totalAmountPrevious7Days
            FROM App\Entity\Order o
            WHERE o.shop = :shop
            AND o.createdAt >= :startDate
            AND o.createdAt < :endDate
            GROUP BY dayDate
            ORDER BY dayDate ASC'
        )
        ->setParameter('shop', $this->shop)
        ->setParameter('startDate', (new \DateTime())->modify('-7 days')->setTime(0, 0, 0))
        ->setParameter('endDate', (new \DateTime())->setTime(0, 0, 0))
        ->setParameter('startDatePrevious', (new \DateTime())->modify('-14 days')->setTime(0, 0, 0));
        
        return $query->getResult();
    }
}