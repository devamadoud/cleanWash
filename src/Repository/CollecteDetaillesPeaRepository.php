<?php

namespace App\Repository;

use App\Entity\CollecteDetaillesPea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CollecteDetaillesPea>
 *
 * @method CollecteDetaillesPea|null find($id, $lockMode = null, $lockVersion = null)
 * @method CollecteDetaillesPea|null findOneBy(array $criteria, array $orderBy = null)
 * @method CollecteDetaillesPea[]    findAll()
 * @method CollecteDetaillesPea[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollecteDetaillesPeaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CollecteDetaillesPea::class);
    }

    //    /**
    //     * @return CollecteDetaillesPea[] Returns an array of CollecteDetaillesPea objects
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

    //    public function findOneBySomeField($value): ?CollecteDetaillesPea
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
