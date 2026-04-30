<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\UserAssetTradingPlace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findByUatpId(int $userId, int $assetId, int $tradingPlaceId): array
    {
    return $this->createQueryBuilder('t')
        ->select('t.date, t.quantity, t.price, t.fee, t.transaction_type')
        ->andWhere('t.user = :user')
        ->andWhere('t.asset = :asset')
        ->andWhere('t.tradingPlace = :tp')
        ->setParameter('user', $userId)
        ->setParameter('asset', $assetId)
        ->setParameter('tp', $tradingPlaceId)
        ->orderBy('t.date', 'ASC')
        ->getQuery()
        ->getArrayResult();
    }

    public function findByUserAndPlace(int $userId, int $tradingPlaceId): array {
        return $this->createQueryBuilder('t')
            ->select('t.date, t.quantity, t.price, t.fee, t.transaction_type')
            ->andWhere('t.user = :user')
            ->andWhere('t.tradingPlace = :tp')
            ->setParameter('user', $userId)
            ->setParameter('tp', $tradingPlaceId)
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /* public function findByUserAssetTradingPlace(UserAssetTradingPlace $uatp): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.date, t.quantity, t.price, t.fee, t.transactionType AS type')
            ->andWhere('t.user = :user')
            ->andWhere('t.asset = :asset')
            ->andWhere('t.tradingPlace = :tp')
            ->setParameter('user', $uatp->getTradingplaceUser())
            ->setParameter('asset', $uatp->getAsset())
            ->setParameter('tp', $uatp->getTradingPlace())
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getArrayResult(); // ✅ plain arrays, no identity map
    } */
}
