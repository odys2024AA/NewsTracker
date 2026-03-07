<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WatchlistAnalysisController extends AbstractController
{
    #[Route('/watchlist', name: 'app_watchlist')]
    public function index(): Response
    {
        return $this->render('watchlist_analysis/index.html.twig', [
            'controller_name' => 'WatchlistAnalysisController',
        ]);
    }
}
