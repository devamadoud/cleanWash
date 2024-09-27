<?php

namespace App\Repository;

use App\Entity\ClothingPea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClothingPea>
 *
 * @method ClothingPea|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClothingPea|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClothingPea[]    findAll()
 * @method ClothingPea[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClothingPeaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClothingPea::class);
    }

    //    /**
    //     * @return ClothingPea[] Returns an array of ClothingPea objects
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

    //    public function findOneBySomeField($value): ?ClothingPea
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
