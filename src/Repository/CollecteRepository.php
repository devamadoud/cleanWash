<?php

namespace App\Repository;

use App\Entity\Collecte;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Collecte>
 *
 * @method Collecte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Collecte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Collecte[]    findAll()
 * @method Collecte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollecteRepository extends ServiceEntityRepository
{
    private $paginator;
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Collecte::class);
        $this->paginator = $paginator;
    }

    //    /**
    //     * @return Collecte[] Returns an array of Collecte objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Collecte
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByShop($searche): PaginationInterface
    {
        $query = $this->createQueryBuilder('c')
            ->select('s', 'c')
            ->join('c.shop', 's')
            ->andWhere('c.shop IN (:shop)')
            ->orderBy('c.collectedAt', 'DESC')
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

            // Formater la date dans le format souhaité
            $formattedDateFrom = $dateFrom->format('Y-m-d');
            $formattedDateTo = $dateTo->format('Y-m-d');

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

    public function getTransactionsCountByDay($shop)
    {
        $query = $this->createQueryBuilder('c')
            ->select('DATE(c.collectedAt) as dayDate, COUNT(c) as orderCount, SUM(c.totale) as totalAmount')
            ->join('c.shop', 's')
            ->andWhere('s = :shop')
            ->setParameter('shop', $shop)
            ->andWhere('c.collectedAt >= :startDate')
            ->andWhere('c.collectedAt < :endDate')
            ->andWhere('c.status = :status')
            ->setParameter('status', 'Terminé')
            ->setParameter('startDate', (new \DateTimeImmutable('now'))->modify('-7 days')->setTime(23, 59, 59))
            ->setParameter('endDate', (new \DateTimeImmutable('now')))
            ->groupBy('dayDate')
            ->orderBy('dayDate', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    public function getTotalTransactionAmountLast7Days($shop)
    {
        $query = $this->createQueryBuilder('c')
            ->select('SUM(c.totale) as totalAmount')
            ->join('c.shop', 's')
            ->andWhere('s = :shop')
            ->setParameter('shop', $shop)
            ->andWhere('c.collectedAt >= :startDate')
            ->andWhere('c.collectedAt < :endDate')
            ->andWhere('c.status = :status')
            ->setParameter('status', 'Terminé')
            ->setParameter('startDate', (new \DateTimeImmutable('now'))->modify('-7 days')->setTime(23, 59, 59))
            ->setParameter('endDate', (new \DateTimeImmutable('now'))->setTime(23, 59, 59))
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getTotalTransactionAmountPrevious7Days($shop)
    {
        $query = $this->createQueryBuilder('c')
            ->select('SUM(c.totale) as totalAmount')
            ->join('c.shop', 's')
            ->andWhere('s = :shop')
            ->setParameter('shop', $shop)
            ->andWhere('c.collectedAt >= :startDate')
            ->andWhere('c.collectedAt < :endDate')
            ->andWhere('c.status = :status')
            ->setParameter('status', 'Terminé')
            ->setParameter('startDate', (new \DateTimeImmutable('now'))->modify('-14 days')->setTime(23, 59, 59))
            ->setParameter('endDate', (new \DateTimeImmutable('now'))->modify('-7 days')->setTime(23, 59, 59))
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getNewCollectes($shop)
    {
        $query = $this->createQueryBuilder('c')
            ->select('c')
            ->join('c.shop', 's')
            ->andWhere('c.shop = :shop')
            ->andWhere('c.status = :status')
            ->setParameter('shop', $shop)
            ->setParameter('status', 'En attente')
            ->orderBy('c.collectedAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
        ;

        return $query->getResult();
    }
}
