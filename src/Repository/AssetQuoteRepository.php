<?php

namespace App\Repository;

use App\Entity\AssetQuote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AssetQuote>
 */
class AssetQuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssetQuote::class);
    }

        public function findAllForAssetAsArray(int $assetId): array
    {
        return $this->createQueryBuilder('q')
            ->select('q.date, q.quote')
            ->andWhere('q.asset = :asset')
            ->setParameter('asset', $assetId)
            ->orderBy('q.date', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }
    
    public function findForAssetOnDate($asset, \DateTimeInterface $day)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.asset = :asset')
            ->andWhere('q.date = :day')
            ->setParameter('asset', $asset)
            ->setParameter('day', $day->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLastBeforeDate($asset, \DateTimeInterface $day)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.asset = :asset')
            ->andWhere('q.date <= :day')
            ->setParameter('asset', $asset)
            ->setParameter('day', $day->format('Y-m-d'))
            ->orderBy('q.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLastForAsset($asset): ?AssetQuote
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.asset = :asset')
            ->setParameter('asset', $asset)
            ->orderBy('q.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return AssetQuote[] Returns an array of AssetQuote objects
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

    //    public function findOneBySomeField($value): ?AssetQuote
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
