<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Repository;

use DoctrineEncryptor\DoctrineEncryptorBundle\Entity\NeoxEncryptor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NeoxEncryptor>
 *
 * @method NeoxEncryptor|null find($id, $lockMode = null, $lockVersion = null)
 * @method NeoxEncryptor|null findOneBy(array $criteria, array $orderBy = null)
 * @method NeoxEncryptor[]    findAll()
 * @method NeoxEncryptor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NeoxEncryptorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NeoxEncryptor::class);
    }

//    /**
//     * @return NeoxEncryptor[] Returns an array of NeoxEncryptor objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?NeoxEncryptor
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
