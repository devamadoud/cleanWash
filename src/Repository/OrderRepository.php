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
            ->setParameter('shop', $searche->shop)
        ;

        if(!empty($searche->ref)){
            $query = $query->andWhere('o.reference = :ref')
                ->setParameter('ref', $searche->ref)
            ;
        }

        if(!empty($searche->tel)){
            $query = $query->join('o.customer', 'oc')
                ->andWhere('oc.phoneNumber = :tel')
                ->setParameter('tel', $searche->tel)
            ;
        }

        if(!empty($searche->dateFrom) && !empty($searche->dateTo)){
            // Créer un objet DateTime à partir de la chaîne de date
            $dateFrom = new DateTimeImmutable($searche->dateFrom);
            $dateFrom->setTime(0, 0, 0);
            $dateTo = new DateTimeImmutable($searche->dateTo);
            $dateTo->setTime(0, 0, 0);

            $query = $query->andWhere('o.createdAt BETWEEN :dateFrom AND :dateTo')
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo)
            ;
        }

        if(!empty($searche->status)){
            $query = $query->andWhere('o.status = :status')
                ->setParameter('status', $searche->status)
            ;
        }

        $query = $query->getQuery();
        return $this->paginator->paginate($query, $searche->page, 10);
    }
}
