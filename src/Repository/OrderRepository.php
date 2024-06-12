<?php

namespace App\Repository;

use App\Entity\Order;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    private $paginator;
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Order::class);
        $this->paginator = $paginator;
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findOrderByShop($searche): PaginationInterface
    {
        $query = $this->createQueryBuilder('o')
            ->select('s', 'o')
            ->join('o.shop', 's')
            ->andWhere('o.shop IN (:shop)')
            ->orderBy('o.createdAt', 'DESC')
            ->setParameter('shop', $searche->shop);

        if (!empty($searche->ref)) {
            $query = $query->andWhere('o.reference = :ref')
                ->setParameter('ref', $searche->ref);
        }

        if (!empty($searche->tel)) {
            $query = $query->join('o.customer', 'oc')
                ->andWhere('oc.phoneNumber = :tel')
                ->setParameter('tel', $searche->tel);
        }

        if (!empty($searche->dateFrom) && !empty($searche->dateTo)) {
            // Créer un objet DateTime à partir de la chaîne de date
            $dateFrom = new DateTimeImmutable($searche->dateFrom);
            $dateFrom->setTime(0, 0, 0);
            $dateTo = new DateTimeImmutable($searche->dateTo);
            $dateTo->setTime(0, 0, 0);

            $query = $query->andWhere('o.createdAt BETWEEN :dateFrom AND :dateTo')
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo);
        }

        if (!empty($searche->status)) {
            $query = $query->andWhere('o.status = :status')
                ->setParameter('status', $searche->status);
        }

        $query = $query->getQuery();
        return $this->paginator->paginate($query, $searche->page, 10);
    }

    public function findByWeek($shop)
    {

        $query = $this->createQueryBuilder('o')
            ->select('COUNT(o) as orderCount, DAYNAME(o.createdAt) AS dayName, DAY(o.createdAt) AS dayNumber, MONTHNAME(o.createdAt) AS monthName, YEAR(o.createdAt) AS yearNumber, WEEK(o.createdAt) AS weekNumber')
            ->join('o.shop', 's')
            ->andWhere('s = :shop')
            ->setParameter('shop', $shop)
            ->andWhere('o.createdAt >= :startDate')
            ->setParameter('startDate', (new \DateTime())->modify('-2 weeks')->setTime(0, 0, 0))
            ->groupBy('yearNumber, weekNumber, monthName, dayNumber, dayName')
            ->orderBy('yearNumber', 'DESC')
            ->addOrderBy('weekNumber', 'DESC')
            ->addOrderBy('dayNumber', 'DESC');

        return $query->getQuery()->getResult();
    }

    public function getTransactionsCountByDay($shop)
    {
        $query = $this->createQueryBuilder('o')
            ->select('DATE(o.createdAt) as dayDate, COUNT(o) as orderCount, SUM(o.totale) as totalAmount')
            ->join('o.shop', 's')
            ->andWhere('s = :shop')
            ->setParameter('shop', $shop)
            ->andWhere('o.createdAt >= :startDate')
            ->andWhere('o.createdAt < :endDate')
            ->andWhere('o.status = :status')
            ->setParameter('status', 'Terminé')
            ->setParameter('startDate', (new \DateTimeImmutable('now'))->modify('-7 days')->setTime(23, 59, 59))
            ->setParameter('endDate', (new \DateTimeImmutable('now'))->setTime(23, 59, 59))
            ->groupBy('dayDate')
            ->orderBy('dayDate', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    public function getTotalTransactionAmountLast7Days($shop)
    {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.totale) as totalAmount')
            ->join('o.shop', 's')
            ->andWhere('s = :shop')
            ->setParameter('shop', $shop)
            ->andWhere('o.createdAt >= :startDate')
            ->andWhere('o.createdAt < :endDate')
            ->andWhere('o.status = :status')
            ->setParameter('status', 'Terminé')
            ->setParameter('startDate', (new \DateTimeImmutable('now'))->modify('-7 days')->setTime(0, 0, 0))
            ->setParameter('endDate', (new \DateTimeImmutable('now'))->setTime(23, 59, 59))
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getTotalTransactionAmountPrevious7Days($shop)
    {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.totale) as totalAmount')
            ->join('o.shop', 's')
            ->andWhere('s = :shop')
            ->setParameter('shop', $shop)
            ->andWhere('o.createdAt >= :startDate')
            ->andWhere('o.createdAt < :endDate')
            ->andWhere('o.status = :status')
            ->setParameter('status', 'Terminé')
            ->setParameter('startDate', (new \DateTimeImmutable('now'))->modify('-14 days')->setTime(0, 0, 0))
            ->setParameter('endDate', (new \DateTimeImmutable('now'))->modify('-7 days')->setTime(23, 59, 59))
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getNewOrders($shop)
    {
        $query = $this->createQueryBuilder('o')
            ->select('o')
            ->join('o.shop', 's')
            ->andWhere('o.shop = :shop')
            ->andWhere('o.status = :status')
            ->setParameter('shop', $shop)
            ->setParameter('status', 'En attente')
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
        ;

        return $query->getResult();
    }
}
