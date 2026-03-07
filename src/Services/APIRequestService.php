<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class APIRequestService{

    private HttpClientInterface $client;
    private string $baseUrl;
    private ?string $apiKey = null;

    public function __construct(HttpClientInterface $client, string $baseUrl, string $apiKey){
        $this->client = $client;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey ;
    }

    public function request(string $method, string $endpoint, array $options = []){
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        
        if($this->apiKey){
            $options['query']['apikey'] = $this->apiKey;
        }

        $options['timeout'] ??= 10;

        $response = $this->client->request($method, $url, $options);

        return $response->toArray();
    }

    public function get(string $endpoint, array $query = []): array {
        return $this->request(
            'GET',
            $endpoint,
            [
                'query' => $query,
            ]
        );
    }

}