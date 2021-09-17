<?php

namespace App\Repository;

use App\Entity\MailSend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MailSend|null find($id, $lockMode = null, $lockVersion = null)
 * @method MailSend|null findOneBy(array $criteria, array $orderBy = null)
 * @method MailSend[]    findAll()
 * @method MailSend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailSendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailSend::class);
    }

    // /**
    //  * @return MailSend[] Returns an array of MailSend objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MailSend
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
