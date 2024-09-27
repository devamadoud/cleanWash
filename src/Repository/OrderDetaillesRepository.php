<?php

namespace App\Repository;

use App\Entity\OrderDetailles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderDetailles>
 *
 * @method OrderDetailles|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderDetailles|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderDetailles[]    findAll()
 * @method OrderDetailles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderDetaillesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderDetailles::class);
    }

    //    /**
    //     * @return OrderDetailles[] Returns an array of OrderDetailles objects
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

    //    public function findOneBySomeField($value): ?OrderDetailles
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
