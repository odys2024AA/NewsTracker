<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use App\Service\APIRequestService;

final class NewsPageController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function newsdisplay(): Response
    {
        return $this->render('news_page/index.html.twig');
    }

    #[Route('/news/frame', name: 'app_news_frame')]
    public function newsframe(#[Autowire(service: 'finance.api')] APIRequestService $fmpApi): Response
    {
        $news = $fmpApi->get('/stable/news/general-latest');
        //dd($news);
        return $this->render('news_page/news_frame.html.twig', [
            'news' => $news
        ]);
    }
}
