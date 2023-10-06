<?php

namespace App\Repository;

use App\Entity\Admin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Admin>
* @implements PasswordUpgraderInterface<Admin>
 *
 * @method Admin|null find($id, $lockMode = null, $lockVersion = null)
 * @method Admin|null findOneBy(array $criteria, array $orderBy = null)
 * @method Admin[]    findAll()
 * @method Admin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdminRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Admin::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Admin) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function getListRole()
	{
		$roles = ['ROLE_USER', 'ROLE_AUTHOR', 'ROLE_PREMIUM', 'ROLE_MODO', 'ROLE_ADMIN'];
		return $roles;
	}

    public function getListName()
    {
        $names = [];
        foreach ($this->createQueryBuilder('c')
            ->select('c.username')
            ->distinct(true)
            ->orderBy('c.username', 'ASC')
            ->getQuery()
            ->getResult() as $cols) {
            $names[] = $cols['username'];
        }
        return $names;
    }

    public const PAGINATOR_PER_PAGE = 5;
    public function getAdminPaginator(?string $role=null, ?string $name=null, int $offset): Paginator
    {
        $query = $this->createQueryBuilder('c');
        if ($role !=='') {
            $query->andWhere('c.roles = :role')
                ->setParameter('role', $role);
        }
        if ($name!== '') {
            $query->andWhere('c.username = :name')
                ->setParameter('name', $name);
        }
        $query->orderBy('c.username', 'ASC')
                ->setMaxResults(self::PAGINATOR_PER_PAGE)
                ->setFirstResult($offset)
                ->getQuery();
        return new Paginator($query);
    }

//    /**
//     * @return Admin[] Returns an array of Admin objects
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

//    public function findOneBySomeField($value): ?Admin
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
