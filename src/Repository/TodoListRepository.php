<?php

namespace App\Repository;

use App\Entity\TodoList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TodoList|null find($id, $lockMode = null, $lockVersion = null)
 * @method TodoList|null findOneBy(array $criteria, array $orderBy = null)
 * @method TodoList[]    findAll()
 * @method TodoList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TodoListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TodoList::class);
    }

    public function findAllQueryBuilder($name = '')
    {
        $qb = $this->createQueryBuilder('tl')
            ->andWhere('tl.deleted = :deleted')
            ->setParameter('deleted', 0);

        if ($name) {
            $qb->andWhere('tl.name LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }
        return $qb;
    }

    /**
     * @param string|null $code
     * @return TodoList|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByCode(?string $code): ?TodoList
    {
        return $this->createQueryBuilder('tl')
            ->andWhere('tl.code = :code')
            ->setParameter('code', $code)
            ->andWhere('tl.deleted = :deleted')
            ->setParameter('deleted', 0)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return TodoList[] Returns an array of TodoList objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TodoList
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
