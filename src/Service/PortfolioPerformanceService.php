<?php

namespace App\Service;

use App\Entity\UserAssetTradingPlace;
use App\Entity\DailyPerformance;
use App\Repository\TransactionRepository;
use App\Repository\AssetQuoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\UserTradingPlace;
use App\Entity\TradingPlacePerformance;
use App\Repository\UserAssetTradingPlaceRepository;

class PerformanceSnapshot {
    public float $fee;
    public float $boughtValue;
    public float $soldValue;
    public float $leftover;
    public float $currentlyInvested;
    public float $currentValue;
    public float $potentialEarnings;
    public float $realizedEarnings;
    public float $realizedDay;
    public float $realizedEarningsAfterFees;
    public float $realizedDayAfterFees;
}

class PortfolioPerformanceService
{
    public function __construct(
        private TransactionRepository $transactionRepo,
        private AssetQuoteRepository $quoteRepo,
        private EntityManagerInterface $manager,
        private UserAssetTradingPlaceRepository $uatpRepo
    ) {}

    
    public function calculatePerformanceHistory(UserAssetTradingPlace $uatp): void
    {
        //Deleting the old snapshot to prevent issues with doubles and recomputations
        $this->manager->createQuery(
            'DELETE FROM App\Entity\DailyPerformance dp
            WHERE dp.userAssetTradingPlace = :uatp'
        )
        ->setParameter('uatp', $uatp)
        ->execute();

        //Load all transactions in question
        $assetId = $uatp->getAsset()->getId();
        $userId = $uatp->getTradingplaceUser()->getId();
        $tpId = $uatp->getTradingPlace()->getId();

        $transactionsAllTime = $this->transactionRepo->findByUatpId($userId, $assetId, $tpId);

        //get quotes
        $quotesRaw = $this->quoteRepo->findAllForAssetAsArray($assetId);
        $quotesByDate = [];
        foreach ($quotesRaw as $qr){
             $quotesByDate[$qr['date']->format('Y-m-d')] = (float) $qr['quote'];
        }
           

        // Create the day range 2years back from today
        $start = (new \DateTimeImmutable('today'))->modify('-2 years');
        $end = new \DateTimeImmutable('today');
        $period = new \DatePeriod(
            $start,
            new \DateInterval('P1D'),
            $end,
            \DatePeriod::INCLUDE_END_DATE
        );

        //Compute daily snapshot
        $realizedEarningsCurrentYear = 0;
        $lastCumulativeRealized = 0;
        $lastCumulativeRealizedAfterFees = 0;
        foreach ($period as $day) {
            if($day->format('m-d') === '01-01'){
                $realizedEarningsCurrentYear = 0;
            }

            $snapshot = $this->calculateSnapshotForDay ($transactionsAllTime, $quotesByDate, $day, $lastCumulativeRealized, $lastCumulativeRealizedAfterFees);

            $realizedEarningsCurrentYear += $snapshot->realizedDay;

            $lastCumulativeRealized = $snapshot->realizedEarnings;
            $lastCumulativeRealizedAfterFees = $snapshot->realizedEarningsAfterFees;
            $pastEarnings = $snapshot->realizedEarnings;
            $potentialEarnings = $snapshot->potentialEarnings;
            $fees = $snapshot->fee;
            
            $overallEarnings = $pastEarnings + $potentialEarnings - $fees;
            
            $row = new DailyPerformance();
            $row->setUserAssetTradingPlace($uatp);
            $row->setDate($day);
            $row->setFee((string) $snapshot->fee);
            $row->setBoughtValue((string) $snapshot->boughtValue);
            $row->setSoldValue((string) $snapshot->soldValue);
            $row->setLeftover((string) $snapshot->leftover);
            $row->setCurrentlyInvested((string) $snapshot->currentlyInvested);
            $row->setCurrentValue((string) $snapshot->currentValue);
            $row->setPotentialEarnings((string) $snapshot->potentialEarnings);
            $row->setRealizedEarnings((string) $snapshot->realizedEarnings);
            $row->setRealizedEarningsCurrentYear((string) $realizedEarningsCurrentYear);
            $row->setRealizedEarningsAfterFees((string) $snapshot->realizedEarningsAfterFees);
            $row->setOverallEarnings((string) $overallEarnings);

            $this->manager->persist($row);
        }

        $this->manager->flush();

    }

    public function calculateTradingPlacePerformance(UserTradingPlace $utp): void 
    {
        //Remove previouse entries from the table
        $this->manager->createQuery(
            'DELETE FROM App\Entity\TradingPlacePerformance tpp
            WHERE tpp.userTradingPlace = :utp'
        )
        ->setParameter('utp', $utp)
        ->execute();

        $startingCash  = (float) $utp->getStartingCash();
        $taxRate = (float) $utp->getTaxRate();
        $taxFreePot = (float) $utp->getTaxFreePot();
        $user = $utp->getTradingplaceUser();
        $tradingPlace = $utp->getTradingPlace();

        $uatps = $this->uatpRepo->findBy([
            'tradingplace_user' => $user,
            'tradingPlace' => $tradingPlace
        ]);

        if (empty($uatps)){
            return;
        }

        //indexing the daily performance rows by combination ID and date
        //place them in a structure that can be easily accessed by teh upcming calcualtions
        $dayPerfByUatpAndDate = [];
        foreach($uatps as $u){
            foreach($u->getDailyPerformances() as $dayPerf){
                $dateStr = $dayPerf->getDate()->format('Y-m-d');
                $dayPerfByUatpAndDate[$u->getId()][$dateStr] = $dayPerf;
            }
        }

        //grouping the transactions by combiation and date
        $allUtpTransactions = $this->transactionRepo->findByUserAndPlace(
            $user->getId(),
            $tradingPlace->getId()
        );
        $utpTransactionsByDate = [];
        foreach($allUtpTransactions as $transaction){
            $dateStr = $transaction['date']->format('Y-m-d');
            $utpTransactionsByDate[$dateStr][] = $transaction;
        }

        $cashEventsByDate = [];
        foreach($utp->getCashEvents() as $event){
            $dateStr = $event->getDate()->format('Y-m-d');
            $cashEventsByDate[$dateStr][] = $event;
        }

        //define the time frame
        $start  = (new \DateTimeImmutable('today'))->modify('-2 years');
        $end    = new \DateTimeImmutable('today');
        $period = new \DatePeriod(
            $start,
            new \DateInterval('P1D'),
            $end,
            \DatePeriod::INCLUDE_END_DATE,
        );

        //initializing the calculation variables
        $cash = $startingCash;
        $overallTax = 0;
        $paidTaxCurrentYear = 0;
        $realizedEarningsCurrentYear = 0;
        $realizedEarningsAfterFeesCurrentYear = 0;
        $highWaterTaxable = 0;
        $taxableEarnings = 0;


        $lastRealizedPerUatp = [];
        foreach ($uatps as $u){
            $lastRealizedPerUatp[$u->getId()] = 0;
        }

        $lastRealizedAfterFeesPerUatp = [];
        foreach($uatps as $u){
            $lastRealizedAfterFeesPerUatp[$u->getId()] = 0;
        }

        //loop through all days
        foreach($period as $day){
            $dateStr = $day->format('Y-m-d');

            if($day->format('m-d') === '01-01'){
                $paidTaxCurrentYear = 0;
                $realizedEarningsCurrentYear = 0;
                $realizedEarningsAfterFeesCurrentYear = 0;
                $taxableEarnings = 0;
                $highWaterTaxable = 0;
            }
            //realized earnings update
            $realizedDayPerUatp = [];
            $realizedDayAfterFeesPerUatp = [];
            $totalRealizedDay = 0;
            $totalRealizedDayAfterFees = 0;
            foreach($uatps as $u){
                $uatpId = $u->getId();
                $dayPerf = $dayPerfByUatpAndDate[$uatpId][$dateStr] ?? null;
                $todayCumulativeRealized = $dayPerf
                    ? (float) $dayPerf->getRealizedEarnings()
                    : $lastRealizedPerUatp[$uatpId];
                $todayCumulativeRealizedAfterFees = $dayPerf
                    ? (float) $dayPerf->getRealizedEarningsAfterFees()
                    : $lastRealizedAfterFeesPerUatp[$uatpId];

                $realizedDayDelta = $todayCumulativeRealized - $lastRealizedPerUatp[$uatpId];
                $realizedDayDeltaAfterFees = $todayCumulativeRealizedAfterFees - $lastRealizedAfterFeesPerUatp[$uatpId];

                $realizedDayPerUatp[$uatpId] = $realizedDayDelta;
                $realizedDayAfterFeesPerUatp[$uatpId] = $realizedDayDeltaAfterFees;
                $totalRealizedDay += $realizedDayDelta;
                $totalRealizedDayAfterFees += $realizedDayDeltaAfterFees;

                $lastRealizedPerUatp[$uatpId] = $todayCumulativeRealized;
                $lastRealizedAfterFeesPerUatp[$uatpId] = $todayCumulativeRealizedAfterFees;
            }

            //cash
            foreach($utpTransactionsByDate[$dateStr] ?? [] as $utpTransaction){
                $quantity = (float) $utpTransaction['quantity'];
                $price = (float) $utpTransaction['price'];
                $fee = (float) $utpTransaction['fee'];
                
                if($utpTransaction['transaction_type'] === 'BUY'){
                    $cash -= ($quantity * $price + $fee);
                } else {
                    $cash += ($quantity * $price - $fee);
                }
            }

            foreach($cashEventsByDate[$dateStr] ?? [] as $event){
                $cash += (float) $event->getAmount();
            }

            //taxes
            $realizedEarningsCurrentYear += $totalRealizedDay;
            $realizedEarningsAfterFeesCurrentYear += $totalRealizedDayAfterFees;

            $currentTaxable = max(0, $realizedEarningsAfterFeesCurrentYear - $taxFreePot);
            $newHigh = max($highWaterTaxable, $currentTaxable);
            $dailyTax = ($newHigh - $highWaterTaxable) * $taxRate;
            $highWaterTaxable = $newHigh;
            $taxableEarnings = $highWaterTaxable;

            $paidTaxCurrentYear += $dailyTax;
            $overallTax += $dailyTax;
            $cash -= $dailyTax; 


            //divide shares taxes back to individual assets 
            // Sign-matching: winners pay tax (dailyTax > 0), losers get refunds (dailyTax < 0).
            // Proportional to |realizedDay|
            if($dailyTax !== 0.0){
                $sameSignSum = 0;
                foreach($realizedDayAfterFeesPerUatp as $realizedDay){
                    if(($dailyTax > 0 && $realizedDay > 0) || ($dailyTax < 0 && $realizedDay <0)){
                        $sameSignSum += abs($realizedDay);
                    }
                }

                if($sameSignSum > 0){
                    foreach($realizedDayAfterFeesPerUatp as $uatpId => $realizedDay){
                        $sameSign = ($dailyTax > 0 && $realizedDay > 0) || ($dailyTax < 0 && $realizedDay < 0);
                        if (!$sameSign) {
                            continue;
                        }

                        $share = (abs($realizedDay)/$sameSignSum)*$dailyTax;
                        $dayPerf = $dayPerfByUatpAndDate[$uatpId][$dateStr] ?? null;
                        if(!$dayPerf){
                            continue;
                        }

                        $existingTax = (float) ($dayPerf->getTax() ?? 0);
                        $newTax = $existingTax + $share;
                        $dayPerf->setTax((string) $newTax);

                        //recalculate overall earnings including taxes
                        $realizedEarnings = (float) $dayPerf->getRealizedEarnings();
                        $potentialEarnings = (float) $dayPerf->getPotentialEarnings();
                        $fees = (float) $dayPerf->getFee();
                        $dayPerf->setOverallEarnings((string) ($realizedEarnings + $potentialEarnings -$fees - $newTax));
                    }
                }
            }

            //aggregate the daily parameters on trading place level
            $totalLifeTimeRealized = 0;
            $totalOverallEarnings = 0;
            foreach($uatps as $u){
                $dayPerf = $dayPerfByUatpAndDate[$u->getId()][$dateStr] ?? null;
                if($dayPerf){
                    $totalLifeTimeRealized += (float) $dayPerf->getRealizedEarnings();
                    $totalOverallEarnings += (float) ($dayPerf->getOverallEarnings() ?? 0);
                }
            }

            //persist to row for TradingPlacePerformance
            $tppRow = new TradingPlacePerformance();
            $tppRow->setUserTradingPlace($utp);
            $tppRow->setDate($day);
            $tppRow->setCash((string) $cash);
            $tppRow->setTax((string) $overallTax);
            $tppRow->setPaidTaxCurrentYear((string) $paidTaxCurrentYear);
            $tppRow->setTaxableEarnings((string) $taxableEarnings);
            $tppRow->setRealizedEarningsCurrentYear((string) $realizedEarningsCurrentYear);
            $tppRow->setRealizedEarnings((string) $totalLifeTimeRealized);
            $tppRow->setOverallEarnings((string) $totalOverallEarnings);

            $this->manager->persist($tppRow);
        }

        $this->manager->flush();
    }

    public function calculateSnapshotForDay (
        array $transactionsAllTime, 
        array $quotesByDate, 
        \DateTimeInterface  $day, 
        float $lastRealizedEarnings,
        float $lastRealizedEarningsAfterFees): PerformanceSnapshot
    {
        $snapshot = new PerformanceSnapshot();
        $dayStr = $day->format('Y-m-d');
        
        $transactions = array_filter(
            $transactionsAllTime,
            fn($t) => $t['date']->format('Y-m-d') <= $dayStr
        );

        $totalFees = 0;
        $boughtValue = 0;
        $soldValue = 0;
        $leftover = 0;
        $currentValue = 0;
        $currentlyInvested = 0;
        $potentialEarnings = 0;
        $pastEarnings = 0;
        $pastEarningsAfterFees = 0;

        $realizedDay = 0;   

        $buyTransactions = [];
        foreach($transactions as $t){
            if($t['transaction_type'] === 'BUY'){
                $qty = (float) $t['quantity'];
                $fee = (float) $t['fee'];
                $buyTransactions[] = [
                    'quantity' => $qty,
                    'price' => (float) $t['price'],
                    'feePerUnit' => $qty > 0 ? $fee/$qty : 0,
                ];
            }
        }

        $sellTransactions = array_values(
            array_filter(
                $transactions, 
                fn($t) => $t['transaction_type'] === "SELL"
        ));
        
        foreach($sellTransactions as $st){
            $sellQty = (float) $st['quantity'];
            $sellPrice = (float) $st['price'];
            $sellFee = (float) $st['fee'];
            $sellValue = $sellPrice*$sellQty;
            $buyValue = 0;
            $allocatedBuyFee = 0;

            while($sellQty > 0 && count($buyTransactions) > 0){
                $bt = array_shift($buyTransactions);
                $buyQty = (float) $bt['quantity'];
                $buyPrice = (float) $bt['price'];
                $feePerUnit = $bt['feePerUnit'];

                if($buyQty >= $sellQty){
                    $buyValue += $sellQty*$buyPrice;
                    $allocatedBuyFee += $sellQty*$feePerUnit;
                    $remaining = $buyQty - $sellQty;
                    $bt['quantity'] = $remaining;
                    array_unshift($buyTransactions, $bt);
                    $sellQty = 0;
                }
                else{
                    $buyValue += $buyQty*$buyPrice;
                    $allocatedBuyFee += $buyQty*$feePerUnit;
                    $sellQty -= $buyQty;
                }
            }

            $pastEarnings += $sellValue - $buyValue;
            $pastEarningsAfterFees += ($sellValue - $buyValue) - $sellFee - $allocatedBuyFee;
        }

        foreach ($transactions as $t) {
            $fee = (float) $t['fee'];
            $qty = (float) $t['quantity'];
            $price = (float) $t['price'];

            $totalFees += $fee;

            if ($t['transaction_type'] === 'BUY') {
                $boughtValue += $qty * $price;
                $leftover += $qty;
            } else {
                $soldValue += $qty * $price;
                $leftover -= $qty;
            }
        }

        foreach($buyTransactions as $bt){
            $price = $bt['price'];
            $buyQty = $bt['quantity'];
            $currentlyInvested += $price*$buyQty;
        }

        $price = 0;
        $d = new \DateTime($day->format('Y-m-d'));
        while ($d->format('Y-m-d') >= '2000-01-01') {
            $key = $d->format('Y-m-d');
            if (isset($quotesByDate[$key])) {
                $price = $quotesByDate[$key];
                break;
            }
            $d->modify('-1 day');
        }


        $currentValue = $price * $leftover;
        $potentialEarnings = $currentValue - $currentlyInvested;

        $realizedDay = $pastEarnings - $lastRealizedEarnings;

        $snapshot->fee = $totalFees;
        $snapshot->boughtValue = $boughtValue;
        $snapshot->soldValue = $soldValue;
        $snapshot->leftover = $leftover;
        $snapshot->currentlyInvested = $currentlyInvested;
        $snapshot->currentValue = $currentValue;
        $snapshot->potentialEarnings = $potentialEarnings;
        $snapshot->realizedEarnings = $pastEarnings;
        $snapshot->realizedDay = $realizedDay;
        $snapshot->realizedEarningsAfterFees = $pastEarningsAfterFees;
        $snapshot->realizedDayAfterFees = $pastEarningsAfterFees - $lastRealizedEarningsAfterFees;

        return $snapshot;
    }

    
}