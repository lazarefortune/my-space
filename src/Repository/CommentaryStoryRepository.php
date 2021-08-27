<?php

namespace App\Repository;

use App\Entity\CommentaryStory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommentaryStory|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentaryStory|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentaryStory[]    findAll()
 * @method CommentaryStory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentaryStoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentaryStory::class);
    }

    // /**
    //  * @return CommentaryStory[] Returns an array of CommentaryStory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CommentaryStory
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
