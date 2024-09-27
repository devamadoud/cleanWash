<?php

namespace App\Repository;

use App\Entity\Shipe;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Shipe>
 *
 * @method Shipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Shipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Shipe[]    findAll()
 * @method Shipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShipeRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Shipe::class);
        $this->paginator = $paginator;
    }

    //    /**
    //     * @return Shipe[] Returns an array of Shipe objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Shipe
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findBySearch(object $searche): PaginationInterface
    {
        $query = $this->createQueryBuilder('s')
            ->select('sh', 's')
            ->join('s.shop', 'sh')
            ->andWhere('s.shop IN (:shop)')
            ->orderBy('s.shippedAt', 'DESC')
            ->setParameter('shop', $searche->shop)
        ;

        if(!empty($searche->tel)){
            $query = $query->join('s.customer', 'cu')
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

            $query = $query->andWhere('s.shippedAt BETWEEN :dateFrom AND :dateTo')
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo)
            ;
        }

        if(!empty($searche->status)){
            $query = $query->andWhere('s.status = :status')
                ->setParameter('status', $searche->status)
            ;
        }

        $query = $query->getQuery();

        return $this->paginator->paginate($query, $searche->page, 10);
    }
}