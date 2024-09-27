<?php

namespace App\Repository;

use App\Entity\ClothingType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClothingType>
 *
 * @method ClothingType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClothingType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClothingType[]    findAll()
 * @method ClothingType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClothingTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClothingType::class);
    }

    //    /**
    //     * @return ClothingType[] Returns an array of ClothingType objects
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

    //    public function findOneBySomeField($value): ?ClothingType
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
