<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Customer>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    private $paginator;
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Customer::class);
        $this->paginator = $paginator;
    }

    //    /**
    //     * @return Customer[] Returns an array of Customer objects
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

    //    public function findOneBySomeField($value): ?Customer
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findAllAndPaginate($shop, $page): PaginationInterface
    {
        $query = $this->createQueryBuilder('c')
            ->select('s', 'c')
            ->join('c.shop', 's')
            ->andWhere('s.id IN (:shop)')
            ->setParameter('shop', $shop)
            ->getQuery()
        ;

        return $this->paginator->paginate($query, $page, 10);
    }

    public function findByFilter($search)
    {
        $query = $this->createQueryBuilder('c')
            ->select('s', 'c')
            ->join('c.shop', 's')
            ->andWhere('s.id IN (:shop)')
            ->setParameter('shop', $search->shop)
            ->orderBy('c.id', 'DESC')
        ;

        if(!empty($search->tel)){
            $query->andWhere('c.phoneNumber = :tel')
                ->setParameter('tel', $search->tel)
            ;
        }

        if(!empty($search->fullName)){
            $query->andWhere('c.fullName LIKE :name')
                ->setParameter('name', '%'.$search->fullName.'%')
            ;
        }

        return $this->paginator->paginate($query, $search->page, 10);
    }
}
