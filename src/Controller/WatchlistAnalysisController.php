<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\WatchlistAsset;
use App\Repository\WatchlistAssetRepository;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Service\FMPRequestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WatchlistAnalysisController extends AbstractController
{
    #[Route('/watchlist', name: 'app_watchlist')]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('watchlist_analysis/index.html.twig', [
            'watchlist' => $user->getWatchlistAssets(),
        ]);
    }

    #[Route('/watchlist/add', name: 'app_watchlist_add')]
    public function add_watchlistitem(
        Request $request, 
        EntityManagerInterface $manager, 
        #[Autowire(service: 'finance.api')] FMPRequestService $fmpApi, 
        WatchlistAssetRepository $assetRepo
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $symbol = $request->request->get('symbol');
        

        if (!$symbol) {
            return new Response("No symbol provided", Response::HTTP_BAD_REQUEST);
        }
            try {

                $asset = $assetRepo->findOneBy(['symbol' => $symbol]);  

                if(!$asset){
                    $response = $fmpApi->get(
                        '/stable/quote',
                        [
                            "symbol" => $symbol,
                        ]
                    );

                    if (empty($response) || !isset($response[0])) {
                        throw new \Exception("Symbol '$symbol' not found.");
                    }
                    $data = $response[0];

                    $asset = new WatchlistAsset();

                    $asset->setSymbol($symbol);
                    $asset->setName($data['name']);
                    $asset->setQuote($data['price']);
                    $asset->setChange($data['changePercentage']);

                    $manager->persist($asset);
                }

                if ($user->getWatchlistAssets()->contains($asset)) {
                    $response = $this->render('watchlist_analysis/add_asset_error.stream.html.twig', [
                        'message' => "Symbol '$symbol' is already in your watchlist."
                    ]);
                    $response->headers->set('Content-Type', 'text/vnd.turbo-stream.html');
                    return $response;
                }

                $user->addWatchlistAsset($asset);
                $manager->flush();

                $response = $this->render('watchlist_analysis/add_asset.stream.html.twig', [
                    'asset' => $asset
                ]);
                $response->headers->set('Content-Type', 'text/vnd.turbo-stream.html');
                return $response;

            } catch (\Exception $error) {
                $response = $this->render('watchlist_analysis/add_asset_error.stream.html.twig', [
                    'message' => $error->getMessage()
                ]);

                $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
                $response->headers->set('Content-Type', 'text/vnd.turbo-stream.html');
                return $response;
            }
    }

}
