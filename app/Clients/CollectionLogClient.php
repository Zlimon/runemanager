<?php

namespace App\Clients;

use \GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;

class CollectionLogClient
{
    /**
     * @var string
     */
    public string $url;

    /**
     *
     */
    public function __construct()
    {
        $this->url = Config::get('services.collectionlog.url', 'https://api.collectionlog.net');
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $client = new HttpClient();

        $headers = [
            'headers' => [
                'Accept' => 'application/json',
            ]
        ];
        $options = array_merge($headers, $options);

        return $client->request($method, $this->url . $uri, $options);
    }
}
