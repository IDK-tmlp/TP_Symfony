<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Category::class);
	}

//    /**
//     * @return Category[] Returns an array of Category objects
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

	public function getListTitle()
	{
		$titles = [];
		foreach ($this->createQueryBuilder('c')
			->select('c.title')
			->distinct(true)
			->orderBy('c.title', 'ASC')
			->getQuery()
			->getResult() as $cols) {
			$titles[] = $cols['title'];
		}
		return $titles;
	}

	public const PAGINATOR_PER_PAGE = 5;
    public function getCategoryPaginator(?string $title=null, int $offset): Paginator
    {
        $query = $this->createQueryBuilder('c');
        if ($title !=='') {
            $query->andWhere('c.title = :title')
                ->setParameter('title', $title);
        }
        $query->orderBy('c.title', 'ASC')
                ->setMaxResults(self::PAGINATOR_PER_PAGE)
                ->setFirstResult($offset)
                ->getQuery();
        return new Paginator($query);
    }
	
//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
