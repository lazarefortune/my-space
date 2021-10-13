<?php

namespace App\Repository;

use App\Entity\AgreeRgpd;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgreeRgpd|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgreeRgpd|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgreeRgpd[]    findAll()
 * @method AgreeRgpd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgreeRgpdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgreeRgpd::class);
    }

    // /**
    //  * @return AgreeRgpd[] Returns an array of AgreeRgpd objects
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
    public function findOneBySomeField($value): ?AgreeRgpd
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
