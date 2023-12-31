<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public const PAGINATOR_PER_PAGE = 5;
	public function getArticlePaginator(?Category $category, int $offset): Paginator
	{
		$query = $this->createQueryBuilder('c');
        if ($category !=='') {
            $query->andWhere('c.category = :category')
                ->setParameter('category', $category);
        }
        $query->orderBy('c.createdAt', 'DESC')
                ->setMaxResults(self::PAGINATOR_PER_PAGE)
                ->setFirstResult($offset)
                ->getQuery();
        return new Paginator($query);
	}

	public function findByTitle($value): Paginator
    {
        $query = $this->createQueryBuilder('c');
        $query->where($query->expr()->like('c.title', ':value'))
            ->setParameter('value', "%$value%")
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery();
            return new Paginator($query);
    }

    public function sortByRecent(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isPremium = 0')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    
//    /**
//     * @return Article[] Returns an array of Article objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
