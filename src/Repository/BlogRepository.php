<?php

namespace App\Repository;

use App\Entity\Blog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Blog>
 */
class BlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blog::class);
    }

    public function findByCategoryPaginated(int $categoryId, int $limit, int $offset): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.category = :category')
            ->setParameter('category', $categoryId)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByCategory(int $categoryId): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('count(b.id)')
            ->andWhere('b.category = :category')
            ->setParameter('category', $categoryId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
