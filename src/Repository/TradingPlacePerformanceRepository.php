<?php

namespace App\Repository;

use App\Entity\TradingPlacePerformance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\UserTradingPlace;

/**
 * @extends ServiceEntityRepository<TradingPlacePerformance>
 */
class TradingPlacePerformanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TradingPlacePerformance::class);
    }

    /**
     * 
     *  @return TradingPlacePerformance[]
     */
    public function findLatestPerUtp(User $user): array
    {
        return $this->createQueryBuilder('tpp')
            ->innerJoin('tpp.userTradingPlace', 'utp')
            ->where('utp.tradingplaceUser = :user')
            ->andWhere('tpp.date = (
                SELECT MAX(tpp2.date)
                FROM App\Entity\TradingPlacePerformance tpp2
                WHERE tpp2.userTradingPlace = tpp.userTradingPlace
            )')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Full daily history for a single UTP, oldest first.
     *
     * @return array<int, array{date: \DateTimeInterface, ...}>
     */
    public function findHistoryForUtp(UserTradingPlace $utp): array
    {
        return $this->createQueryBuilder('tpp')
            ->where('tpp.userTradingPlace = :utp')
            ->setParameter('utp', $utp)
            ->orderBy('tpp.date', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    //    /**
    //     * @return TradingPlacePerformance[] Returns an array of TradingPlacePerformance objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TradingPlacePerformance
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
