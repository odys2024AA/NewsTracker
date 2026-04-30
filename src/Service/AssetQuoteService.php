<?php

namespace App\Service;


use App\Repository\AssetRepository;
use App\Repository\AssetQuoteRepository;
use App\Entity\AssetQuote;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FMPRequestService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AssetQuoteService{
    public function __construct(
        private AssetRepository $assetRepo,
        private AssetQuoteRepository $quoteRepo, 
        private EntityManagerInterface $manager,
        #[Autowire(service: 'finance.api')] private FMPRequestService $fmpApi, 
    ){}

    public function updateAllQuotesForAllAssets(): void
    {
        $assets = $this->assetRepo->findAll();
        foreach($assets as $asset){
            $this->updateQuoteForAsset($asset);
        }
    }

    private function updateQuoteForAsset($asset): void
    {
        $symbol = $asset->getSymbol();

        $lastQuote = $this->quoteRepo->findLastForAsset($asset);
        if ($lastQuote) {
            $lastDate = \DateTime::createFromInterface($lastQuote->getDate());
            $from = $lastDate->modify('+1 day')->format('Y-m-d');
        } else {
            $from = '2024-06-30';
        }
        
        $response  = $this->fmpApi->get(
            '/stable/historical-price-eod/light',
            [
                "symbol" => $symbol,
                "from" => $from
             ]
        );


        if(!is_array($response) || empty($response)){
            return;
        }
        
        foreach($response as $quote){
            $date = new \DateTime($quote['date']);
            $price = $quote['price'];

            $existing = $this->quoteRepo->findOneBy([
                'asset' => $asset,
                'date' => $date
            ]);

            if($existing){
                continue;
            }

            $quoteEntry = new AssetQuote();
            $quoteEntry->setAsset($asset);
            $quoteEntry->setDate($date);
            $quoteEntry->setQuote($price);

            $this->manager->persist($quoteEntry);
        }

        $this->manager->flush();

        //forward-filling of gaps in the timeline
        $quotes = $this->quoteRepo->findBy(
            ['asset' => $asset],
            ['date' => 'ASC']
        );

        if(!$quotes){
            return;
        }

        $priceByDate = [];
        foreach($quotes as $quote){
            $priceByDate[$quote->getDate()->format('Y-m-d')] = $quote->getQuote();
        }

        

        $start = $quotes[0]->getDate();
        $end = new \DateTime(); //now
        $period = new \DatePeriod(
            $start,
            new \DateInterval('P1D'),
            $end,
            \DatePeriod::INCLUDE_END_DATE
        );

        $lastPrice = null;

        foreach($period as $day){
            $key = $day->format('Y-m-d');
            if(isset($priceByDate[$key])){
                $lastPrice = $priceByDate[$key];
            }
            else {
                if($lastPrice !== null){
                    $existing = $this->quoteRepo->findOneBy([
                        'asset' => $asset,
                        'date' => $day
                    ]);

                    if($existing){
                        continue;
                    }

                    $fill = new AssetQuote();
                    $fill->setAsset($asset);
                    $fill->setDate(clone $day);
                    $fill->setQuote($lastPrice);

                    $this->manager->persist($fill);
                }
            }
        }

        $this->manager->flush();
    }
}