<?php

namespace App\Repository;

use App\Entity\TodoItem;
use App\Entity\TodoList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TodoItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method TodoItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method TodoItem[]    findAll()
 * @method TodoItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TodoItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TodoItem::class);
    }

    public function findAllQueryBuilder(TodoList $list, $name = '')
    {
        $qb = $this->createQueryBuilder('ti')
            ->andWhere('ti.list = :list')
            ->setParameter('list', $list)
            ->andWhere('ti.deleted = :deleted')
            ->setParameter('deleted', 0);

        if ($name) {
            $qb->andWhere('ti.name LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }
        return $qb;
    }

    /**
     * @param string|null $code
     * @return TodoList|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByCode(TodoList $list, ?string $code): ?TodoItem
    {
        return $this->createQueryBuilder('ti')
            ->andWhere('ti.list = :list')
            ->setParameter('list', $list)
            ->andWhere('ti.code = :code')
            ->setParameter('code', $code)
            ->andWhere('ti.deleted = :deleted')
            ->setParameter('deleted', 0)
            ->getQuery()
            ->getOneOrNullResult();
    }
    // /**
    //  * @return ToDoItem[] Returns an array of ToDoItem objects
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
    public function findOneBySomeField($value): ?ToDoItem
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
