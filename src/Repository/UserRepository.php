<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @return User[] Returns an array of User objects by role
     */
    public function findByRole(String $role)
    {
        $role = mb_strtoupper($role);
        
        return $this->createQueryBuilder('u')
            ->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', '"ROLE_' . $role . '"')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * @return User[] Returns an array of User objects by role
     */
    public function findByRoleEncadrant()
    {
        // $role = mb_strtoupper($role);
        
        // $this->createQueryBuilder('u')
        //     ->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
        //     ->andWhere('u.exampleField = :val')
        //     ->setParameter('role', '"ROLE_' . $role . '"')
        //     ->getQuery()
        //     ->getResult();
        
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT u, e
            FROM App\Entity\User u
            INNER JOIN encadre e
            WHERE u.idUser = e.id_eval'
        );

        return $query->getOneOrNullResult();


        $entityManager = $this->getEntityManager();

        // return $entityManager->createQuery(
        //     'SELECT u
        //     FROM App\Entity\User u, encadre
        //     WHERE (u.id_user = encadre.id_eval)
        // )
        // ->getOneOrNullResult();

        
        // returns an array of Product objects
        // return $query->getResult();
        
        $query = $entityManager->createQuery(
            'SELECT u
            FROM App\Entity\User u
            WHERE u.idUser = 
            ORDER BY u.nom ASC'
        );
        
        // return $query->getResult();

        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT * 
            FROM user u, encadre
            WHERE u.id_user = encadre.id_eval
            ORDER BY u.id_user ASC
            ';
        
        $stmt = $conn->executeQuery($sql);
        // returns an array of arrays (i.e. a raw data set)
        // return $stmt->fetchAllAssociative();
    }
    
    // /**
    //  * @return User[] Returns an array of Evaluateur objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findByUsername(string $usernameOrEmail): ?User
    {
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
                'SELECT u
                FROM App\Entity\User u
                WHERE u.login = :query
                OR u.email = :query'
            )
            ->setParameter('query', $usernameOrEmail)
            ->getOneOrNullResult();
    }

    /*
    public function findOneBySomeField($value): ?Evaluateur
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
