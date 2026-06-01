<?php

namespace App\Services\Hiscores;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;

class OsrsHiscoresClient
{
    public string $url;

    protected HttpClient $client;

    public function __construct(?HttpClient $client = null)
    {
        $this->url = Config::get('services.osrs_hiscores.url')
            ?: 'https://secure.runescape.com/m=hiscore_oldschool';

        $this->client = $client ?? new HttpClient;
    }

    /**
     * @throws GuzzleException
     */
    public function fetch(string $username): ResponseInterface
    {
        return $this->client->request('GET', $this->url.'/index_lite.json', [
            'query' => ['player' => $username],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }
}
