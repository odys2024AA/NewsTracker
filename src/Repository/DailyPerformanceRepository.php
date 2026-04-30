<?php

namespace App\Repository;

use App\Entity\DailyPerformance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\UserAssetTradingPlace;

/**
 * @extends ServiceEntityRepository<DailyPerformance>
 */
class DailyPerformanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyPerformance::class);
    }

    /**
     * 
     * @return DailyPerformance[]
     */
    public function findLatestPerUatp(User $user): array
    {
        return $this->createQueryBuilder('dp')
            ->innerJoin('dp.userAssetTradingPlace', 'uatp')
            ->where('uatp.tradingplace_user = :user')
            ->andWhere('dp.date = (
                SELECT MAX(dp2.date)
                FROM App\Entity\DailyPerformance dp2
                WHERE dp2.userAssetTradingPlace = dp.userAssetTradingPlace
            )')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Full daily history for a single UATP, oldest first.
     *
     * @return array<int, array{date: \DateTimeInterface, ...}>
     */
    public function findHistoryForUatp(UserAssetTradingPlace $uatp): array
    {
        return $this->createQueryBuilder('dp')
            ->where('dp.userAssetTradingPlace = :uatp')
            ->setParameter('uatp', $uatp)
            ->orderBy('dp.date', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    //    /**
    //     * @return DailyPerformance[] Returns an array of DailyPerformance objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DailyPerformance
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
