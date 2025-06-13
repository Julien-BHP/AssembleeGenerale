<?php

namespace App\Repository;

use App\Entity\AGPoint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AGPoint>
 */
class AGPointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AGPoint::class);
    }
    
    public function findOpenedVote(){
        $now = new \DateTime();
        return $this->createQueryBuilder('p')
            ->andWhere('p.hasVote = :val')
            ->setParameter('val', 1)
            ->andWhere('p.voteOpen is not NULL')
            ->andWhere('p.voteClose is NULL')
            ->andWhere('p.parent is NULL')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    public function findClosedVotes(){
        $now = new \DateTime();
        return $this->createQueryBuilder('p')
            ->andWhere('p.hasVote = :val')
            ->setParameter('val', 1)
            ->andWhere('p.voteOpen is not NULL')
            ->andWhere('p.voteClose is not NULL')
            ->andWhere('p.parent is NULL')
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findOrdreSup($ordre){        
        return $this->createQueryBuilder('p')
            ->andWhere('p.ordre > :val')
            ->setParameter('val', $ordre)
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findMaxOrdre() {
        $query = $this->createQueryBuilder('p');
        $query->select('MAX(p.ordre)');
        return $query->getQuery()->getOneOrNullResult();
    }

    //    /**
    //     * @return AGPoint[] Returns an array of AGPoint objects
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

    //    public function findOneBySomeField($value): ?AGPoint
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
