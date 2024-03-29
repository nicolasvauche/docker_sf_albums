<?php

namespace App\Repository;

use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Album|null find($id, $lockMode = null, $lockVersion = null)
 * @method Album|null findOneBy(array $criteria, array $orderBy = null)
 * @method Album[]    findAll()
 * @method Album[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Album $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Album $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByMostPlayed(?int $limit = null)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nbPlays > 1')
            ->orderBy('a.nbPlays', 'DESC')
            ->addOrderBy('a.lastListened', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByLastListened(?int $limit = null)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.lastListened IS NOT NULL')
            ->orderBy('a.lastListened', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByNeverListened()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.lastListened IS NULL')
            ->orderBy('a.artist', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByEra(string $era, ?int $limit = null)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.year LIKE :year')
            ->orderBy('a.year', 'ASC')
            ->setMaxResults($limit)
            ->setParameter('year', $era . '%')
            ->getQuery()
            ->getResult();
    }

    public function findByActual(?int $limit = null)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.year > :year')
            ->orderBy('a.year', 'ASC')
            ->setMaxResults($limit)
            ->setParameter('year', '1999')
            ->getQuery()
            ->getResult();
    }

    public function findByLastArrived(?int $limit = null)
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Album[] Returns an array of Album objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Album
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
