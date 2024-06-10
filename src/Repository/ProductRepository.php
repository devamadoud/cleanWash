<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    private $paginator;
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Product::class);
        $this->paginator = $paginator;
    }

    /**
    * Récuperer les produits en lien avec une recherche
    * @return PaginationInrface
    */
    public function findBySearch($search): PaginationInterface
    {
        $query = $this->createQueryBuilder('p')
            ->select('c', 'p')
            ->join('p.category', 'c')
        ;
        
        if(!empty($search->q)) {
            $query = $this->createQueryBuilder('p')
                ->andWhere('p.name LIKE :search')
                ->setParameter('search', '%' . $search->q . '%')
            ;
        }

        if(!empty($search->category)) {
            $query = $query
                ->andWhere('c.id IN (:category)')
                ->setParameter('category', $search->category)
            ;
        }

        $query = $query->getQuery();

        $pagination = $this->paginator->paginate($query, $search->page, 16);

        return $pagination;
    }

    // Trouver les produits en lien avec une catégorie
    public function findByCategory($category, $page): PaginationInterface
    {
        $query = $this->createQueryBuilder('p')
            ->select('c', 'p')
            ->join('p.category', 'c')
            ->andWhere('c.id IN (:category)')
            ->setParameter('category', $category)
            ->getQuery()
        ;

        return $this->paginator->paginate($query, $page, 4);
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
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

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
