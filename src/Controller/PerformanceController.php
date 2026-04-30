<?php

namespace App\Controller;

use App\Entity\CashEvent;
use App\Entity\UserTradingPlace;
use App\Entity\User;
use App\Form\CashEventFormType;
use App\Form\UserTradingPlaceFormType;
use App\Repository\CashEventRepository;
use App\Repository\DailyPerformanceRepository;
use App\Repository\TradingPlacePerformanceRepository;
use App\Repository\UserAssetTradingPlaceRepository;
use App\Repository\UserTradingPlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PerformanceController extends AbstractController
{
    private const UTP_METRICS = [
        'cash'                        => 'Cash',
        'tax'                         => 'Tax (lifetime)',
        'paidTaxCurrentYear'          => 'Tax (this year)',
        'taxableEarnings'             => 'Taxable earnings',
        'realizedEarningsCurrentYear' => 'Realized YTD',
        'realizedEarnings'            => 'Realized (lifetime)',
        'overallEarnings'             => 'Overall earnings',
    ];

    private const UATP_METRICS = [
        'leftover'                       => 'Quantity held',
        'bought_value'                   => 'Bought value',
        'sold_value'                     => 'Sold value',
        'currently_invested'             => 'Currently invested',
        'current_value'                  => 'Current value',
        'potential_earnings'             => 'Potential earnings',
        'realized_earnings'              => 'Realized earnings',
        'realized_earnings_after_fees'   => 'Realized after fees',
        'realized_earnings_current_year' => 'Realized YTD',
        'tax'                            => 'Tax allocated',
        'fee'                            => 'Fee (cumulative)',
        'overall_earnings'               => 'Overall earnings',
    ];

    #[Route('/performance', name: 'app_performance')]
    public function index(
        DailyPerformanceRepository $dpRepo,
        TradingPlacePerformanceRepository $tppRepo,
        UserAssetTradingPlaceRepository $uatpRepo,
        UserTradingPlaceRepository $utpRepo
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $latestTpps = $tppRepo->findLatestPerUtp($user);
        $latestDps = $dpRepo->findLatestPerUatp($user);

        $utps = $utpRepo->findBy(['tradingplaceUser' => $user]);
        $uatps = $uatpRepo->findBy(['tradingplace_user' => $user]);

        return $this->render('performance/index.html.twig', [
            'latestTpps'  => $latestTpps,
            'latestDps'   => $latestDps,
            'utps'        => $utps,
            'uatps'       => $uatps,
            'utpMetrics'  => self::UTP_METRICS,
            'uatpMetrics' => self::UATP_METRICS,
        ]);
    }

    #[Route('/performance/series', name: 'app_performance_series')]
    public function series(
        Request $request,
        DailyPerformanceRepository $dpRepo,
        TradingPlacePerformanceRepository $tppRepo,
        UserAssetTradingPlaceRepository $uatpRepo,
        UserTradingPlaceRepository $utpRepo
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $entityType = $request->query->get('entityType');
        $entityId = $request->query->getInt('entityId');
        $metric = $request->query->get('metric');

        if($entityType === 'utp'){
            if(!isset(self::UTP_METRICS[$metric])){
                return new JsonResponse(['error' => 'Invalid metric'], 400);
            }
            $utp = $utpRepo->find($entityId);
            if (!$utp || $utp->getTradingplaceUser() !== $user) {
                return new JsonResponse(['error' => 'Not found'], 404);
            }
            $rows  = $tppRepo->findHistoryForUtp($utp);
            $label = self::UTP_METRICS[$metric];
        } elseif ($entityType === 'uatp'){
            if (!isset(self::UATP_METRICS[$metric])) {
                return new JsonResponse(['error' => 'Invalid metric'], 400);
            }
            $uatp = $uatpRepo->find($entityId);
            if (!$uatp || $uatp->getTradingplaceUser() !== $user) {
                return new JsonResponse(['error' => 'Not found'], 404);
            }
            $rows  = $dpRepo->findHistoryForUatp($uatp);
            $label = self::UATP_METRICS[$metric];
        } else{
            return new JsonResponse(['error' => 'Invalid entityType'], 400);
        }

        $dates = [];
        $values = [];
        foreach ($rows as $row){
            $dates[] = $row['date']->format('Y-m-d');
            $values[] = isset($row[$metric]) ? (float) $row[$metric] : null;
        }

        return new JsonResponse([
            'label' => $label,
            'dates' => $dates,
            'values' => $values
        ]);
    }

    #[Route('/performance/utp/{id}/settings', name: 'app_performance_utp_settings')]
    public function editUtp(
        int $id,
        Request $request,
        UserTradingPlaceRepository $utpRepo,
        EntityManagerInterface $manager,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $utp = $utpRepo->find($id);
        if(!$utp || $utp->getTradingplaceUser() !== $user){
            throw $this->createNotFoundException();
        }

        $settingsForm = $this->createForm(UserTradingPlaceFormType::class, $utp);
        $settingsForm->handleRequest($request);
        if($settingsForm->isSubmitted() && $settingsForm->isValid()){
            $manager->flush();
            $this->addFlash('success', 'Settings updated. Run "Calculate Performance" to apply.');
            return $this->redirectToRoute('app_performance_utp_settings', ['id' => $id]);
        }

        $newEvent = new CashEvent();
        $cashEventForm = $this->createForm(CashEventFormType::class, $newEvent);
        $cashEventForm->handleRequest($request);
        if($cashEventForm->isSubmitted() && $cashEventForm->isValid()){
            $newEvent->setUserTradingPlace($utp);
            $manager->persist($newEvent);
            $manager->flush();
            $this->addFlash('success', 'Cash event added. Run "Calculate Performance" to apply.');
            return $this->redirectToRoute('app_performance_utp_settings', ['id' => $id]);
        }


        return $this->render('performance/utp_settings.html.twig', [
            'form' => $settingsForm->createView(),
            'cashEventForm' => $cashEventForm->createView(),
            'utp' => $utp,
        ]);
    }

    #[Route ('/performance/cash-event/{id}/delete', name: 'app_performance_cash_event_delete', methods: ['POST'])]
    public function deleteCashEvent(
        int $id,
        Request $request,
        CashEventRepository $cashEventRepo,
        EntityManagerInterface $manager
    ): Response {
        $event = $cashEventRepo->find($id);
        if(!$event){
            throw $this->createNotFoundException();
        }

        $utp = $event->getUserTradingPlace();
        if($utp->getTradingplaceUser() !== $this->getUser()){
            throw $this->createAccessDeniedException();
        }

        if(!$this->isCsrfTokenValid('delete-cash-event-'. $event->getId(), $request->request->get('token'))){
            throw $this->createAccessDeniedException();
        }

        $manager->remove($event);
        $manager->flush();

        $this->addFlash('success', 'Cash event removed. Run "Calculate Performance" to apply.');

        return $this->redirectToRoute('app_performance_utp_settings', ['id' => $utp->getId()]);
    }

}
