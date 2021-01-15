<?php

namespace App\Repository;

use App\Entity\Brand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Brand|null find($id, $lockMode = null, $lockVersion = null)
 * @method Brand|null findOneBy(array $criteria, array $orderBy = null)
 * @method Brand[]    findAll()
 * @method Brand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    /**
     * @return Brand[] Returns an array of Brand objects following the searching parameters
     */
    public function searchBrands($search_category, $search_text)
    {
        $query_builder =  $this->createQueryBuilder('b');
        dump($search_category);
        dump($search_text);
        switch ($search_category) {
            case 0:
                $query_builder->andWhere('b.name LIKE :name')
                ->setParameter('name', '%'.$search_text.'%')
                ;
                break;
            case 1:
                $query_builder->andWhere('b.nationality LIKE :nationality')
                ->setParameter('nationality', '%'.$search_text.'%')
                ;
                break;
            case 2:
                $query_builder->andWhere('b.slogan LIKE :slogan')
                ->setParameter('slogan', '%'.$search_text.'%')
                ;
                break;
        }

        return $query_builder->orderBy('b.name', 'ASC')
        ->getQuery()
        ->getResult();
    }

    // /**
    //  * @return Brand[] Returns an array of Brand objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Brand
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
