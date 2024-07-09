<?php

namespace App\Repository;

use App\Entity\Payment;
use App\Entity\Shop;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Payment>
 *
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Payment::class);
        $this->paginator = $paginator;
    }

    //    /**
    //     * @return Payment[] Returns an array of Payment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Payment
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByCollecteShop(Shop $shop) : array
    {
        $query = $this->createQueryBuilder('p')
            ->select('c', 'p')
            ->join('p.collecte', 'c')
            ->andWhere('c.shop IN (:shop)')
            ->orderBy('c.payedAt', 'DESC')
            ->setParameter('shop', $shop)
            ->getQuery()
            ->setMaxResults(5)
        ;

        return $query->getResult();
    }

    public function findByOrderShop(Shop $shop): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('o', 'p')
            ->join('p.productOrder', 'o')
            ->andWhere('o.shop IN (:shop)')
            ->orderBy('o.paidAt', 'DESC')
            ->setParameter('shop', $shop)
            ->getQuery()
            ->setMaxResults(5)
        ;

        return $query->getResult();
    }

    public function findCollecteByShop(object $searche): PaginationInterface
    {
        $query = $this->createQueryBuilder('p')
            ->select('c', 'p')
            ->join('p.collecte', 'c')
            ->andWhere('c.shop IN (:shop)')
            ->orderBy('c.payedAt', 'DESC')
            ->setParameter('shop', $searche->shop)
        ;

        if(!empty($searche->ref)){
            $query = $query->andWhere('c.reference = :ref')
                ->setParameter('ref', $searche->ref)
            ;
        }

        if(!empty($searche->tel)){
            $query = $query->join('c.customer', 'cu')
                ->andWhere('cu.phoneNumber = :tel')
                ->setParameter('tel', $searche->tel)
            ;
        }

        if(!empty($searche->dateFrom) && !empty($searche->dateTo)){
            // Créer un objet DateTime à partir de la chaîne de date
            $dateFrom = new DateTimeImmutable($searche->dateFrom);
            $dateFrom->setTime(0, 0, 0);
            $dateTo = new DateTimeImmutable($searche->dateTo);
            $dateTo->setTime(0, 0, 0);

            $query = $query->andWhere('c.collectedAt BETWEEN :dateFrom AND :dateTo')
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo)
            ;
        }

        if(!empty($searche->status)){
            $query = $query->andWhere('c.status = :status')
                ->setParameter('status', $searche->status)
            ;
        }

        $query = $query->getQuery();

        return $this->paginator->paginate($query, $searche->page, 10);
    }

    public function findOrderByShop(object $searche): PaginationInterface
    {
        $query = $this->createQueryBuilder('p')
            ->select('o', 'p')
            ->join('p.productOrder', 'o')
            ->andWhere('o.shop IN (:shop)')
            ->orderBy('o.paidAt', 'DESC')
            ->setParameter('shop', $searche->shop)
        ;

        if(!empty($searche->ref)){
            $query = $query->andWhere('o.reference = :ref')
                ->setParameter('ref', $searche->ref)
            ;
        }

        if(!empty($searche->tel)){
            $query = $query->join('o.customer', 'cu')
                ->andWhere('cu.phoneNumber = :tel')
                ->setParameter('tel', $searche->tel)
            ;
        }

        if(!empty($searche->dateFrom) && !empty($searche->dateTo)){
            // Créer un objet DateTime à partir de la chaîne de date
            $dateFrom = new DateTimeImmutable($searche->dateFrom);
            $dateFrom->setTime(23, 59, 59);
            $dateTo = new DateTimeImmutable($searche->dateTo);
            $dateTo->setTime(23, 59, 59);

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
