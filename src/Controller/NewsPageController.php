<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use App\Service\FMPRequestService;
//use App\Service\LLMRequestService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class NewsPageController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function newsdisplay(): Response
    {
        return $this->render('news_page/index.html.twig');
    }

    #[Route('/news/frame', name: 'app_news_frame', methods: ['GET', 'POST'])]
    public function newsframe(HttpClientInterface $client, #[Autowire(service: 'finance.api')] FMPRequestService $fmpApi, Request $request): Response
    {
        $news = [];

        // run in terminal: docker run -d -p 5000:5000 -v libretranslate-data:/app/data -e LT_LOAD_ONLY=en,de libretranslate/libretranslate with docker open to launch the translation container

       if ($request->isMethod('POST')) {
            try {
                $originalNewsData = $request->toArray();
                $batch = $originalNewsData; //array_slice($originalNewsData, 0, 5);

                foreach ($batch as &$article) {
                    $article['title'] = $this->translate($client, $article['title']);
                    $article['text']  = $this->translate($client, $article['text']);
                }

                $news = $batch;

            } catch (\Exception $e) {
                dump("Connection Error: " . $e->getMessage());
                $news = $batch;
            }
        }
        else {
            $news = $fmpApi->get('/stable/news/general-latest');
        }

        return $this->render('news_page/news_frame.html.twig', [
            'news' => $news
        ]);
    }

    private function translate(HttpClientInterface $client, string $text): string
    {
        $response = $client->request('POST', 'http://localhost:5000/translate', [
            'json' => [
                'q' => $text,
                'source' => 'en',
                'target' => 'de',
                'format' => 'text'
            ]
        ]);

        return $response->toArray()['translatedText'];
    }
}
