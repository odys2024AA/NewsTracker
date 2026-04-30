<?php

namespace App\Controller;

use App\Repository\AssetRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Asset;
use App\Entity\Transaction;
use App\Form\TransactionFormType;
use App\Entity\User;
use App\Repository\UserAssetTradingPlaceRepository;
use App\Entity\UserAssetTradingPlace;
use App\Repository\TradingPlaceRepository;
use App\Entity\TradingPlace;
use App\Service\PortfolioPerformanceService;
use App\Service\AssetQuoteService;
use App\Entity\UserTradingPlace;
use App\Repository\UserTradingPlaceRepository;


final class TransactionController extends AbstractController
{

    #[Route('/transactions', name: 'app_transactions')]
    public function index(
        Request $request,
        EntityManagerInterface $manager,
        AssetRepository $assetRepo,
        TransactionRepository $transactionRepo,
        TradingPlaceRepository $tpRepo,
        UserAssetTradingPlaceRepository $uatpRepo,
        UserTradingPlaceRepository $utpRepo
    ): Response
    {
        $transaction = new Transaction();

        $form = $this->createForm(TransactionFormType::class, $transaction);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $symbol = strtoupper($form->get('symbol')->getData());
            $assetType = $form->get('asset_type')->getData();

            $asset = $assetRepo->findOneBy(['symbol' => $symbol]);
            /* if asset does not exist yet in Assets a new entry will be created */
            if(!$asset){
                $asset = new Asset();
                $asset->setSymbol($symbol);
                $asset->setAssetType(($assetType));
                $manager->persist($asset);
            }

            $transaction->setAsset($asset);

            /** @var \App\Entity\User $user */
            $user = $this->getUser();

            if (!$user instanceof User) {
                return $this->redirectToRoute('app_login');
            }

            $transaction->setUser($user);
            $manager->persist($transaction);


            /* Check for and if necessary create UserAssetTradingPlace pivot table */
            $tradingPlaceName = $form->get('trading_place')->getData();

            if(!$tradingPlaceName){
                $tradingPlace = $tpRepo->findOneBy([
                    'name' => 'Default'
                ]);
            }
            else{
                $tradingPlace = $tpRepo->findOneBy(['name' => $tradingPlaceName]);

                if(!$tradingPlace){
                $tradingPlace = new TradingPlace();
                $tradingPlace->setName($tradingPlaceName);
                $manager->persist($tradingPlace);
                }
            }
            $transaction->setTradingPlace($tradingPlace);

            $uatp = $uatpRepo->findOneBy([
                'tradingplace_user' => $user,
                'asset' => $asset,
                'tradingPlace' => $tradingPlace
            ]);

            if(!$uatp){
                $uatp = new UserAssetTradingPlace();
                $uatp->setTradingplaceUser($user);
                $uatp->setAsset($asset);
                $uatp->setTradingPlace($tradingPlace);

                $manager->persist($uatp);
            }

            $utp = $utpRepo->findOneBy([
                'tradingplaceUser' => $user,
                'tradingPlace' => $tradingPlace
            ]);

            if(!$utp){
                $utp = new UserTradingPlace();
                $utp->setTradingplaceUser($user);
                $utp->setTradingPlace($tradingPlace);
                $utp->setStartingCash('5000');
                $utp->setTaxFreePot('1000');
                $utp->setTaxRate('0.25');
                $manager->persist($utp);
            }

            

            
            $manager->flush();

            $this->addFlash('success', 'Transacion added successfully');

            return $this->redirectToRoute('app_transactions');
        }

        $transactions = $transactionRepo->findBy(
            [
                'user'=>$this->getUser()
            ], 
            [
                'date'=>'DESC'
            ]);

        return $this->render('transactions/index.html.twig', [
            'form' => $form->createView(),
            'transactions' => $transactions,
        ]);
    }

    #[Route('/getQuotes', name: 'get_quotes')]
    public function getQuotes(
        AssetQuoteService $quoteService,
    ){
        $quoteService->updateAllQuotesForAllAssets();
        $this->addFlash('success', 'Quotes updated');
        return $this->redirectToRoute('app_transactions');
    }

    #[Route('/calcPerformance', name: 'calc_performance')]
    public function calcPerformance(
        UserAssetTradingPlaceRepository $uatpRepo,
        PortfolioPerformanceService $perfService,
        UserTradingPlaceRepository $utpRepo,
        EntityManagerInterface $manager
    ){
        //check if UserTradingPlace already exists and if not then create it
        //for legacy reasons if there are elements in DB before the entity has been set
        $user = $this->getUser();
        $uatps = $uatpRepo->findBy([
            'tradingplace_user' => $user
        ]);
        foreach($uatps as $uatp){
            $tradingPlace = $uatp->getTradingPlace();
            $existing = $utpRepo->findOneBy([
                'tradingplaceUser' => $user,
                'tradingPlace' => $tradingPlace
            ]);
            if($existing){
                continue;
            }
            $utp = new UserTradingPlace();
            $utp->setTradingplaceUser($user);
            $utp->setTradingPlace($tradingPlace);
            $utp->setStartingCash('5000');
            $utp->setTaxRate('0.25');
            $utp->setTaxFreePot('1000');
            $manager->persist($utp);
        }
        $manager->flush();

        //per asset calculation
        foreach($uatps as $uatp){
            $perfService->calculatePerformanceHistory($uatp);
        }
        //per trading place calculation
        $utps = $utpRepo->findBy(['tradingplaceUser'=>$user]);
        foreach($utps as $utp){
            $perfService->calculateTradingPlacePerformance($utp);
        }

        $this->addFlash('success', 'Calculation performed');

        return $this->redirectToRoute('app_transactions');
    }



}
