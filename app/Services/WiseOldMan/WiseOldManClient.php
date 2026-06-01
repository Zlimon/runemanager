<?php

namespace App\Services\WiseOldMan;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;

class WiseOldManClient
{
    public string $url;

    public string $userAgent;

    protected HttpClient $client;

    public function __construct(?HttpClient $client = null)
    {
        $this->url = Config::get('services.wise_old_man.url') ?: 'https://api.wiseoldman.net/v2';
        $this->userAgent = Config::get('services.wise_old_man.user_agent') ?: 'RuneManager/1.0';
        $this->client = $client ?? new HttpClient;
    }

    /**
     * Fetch a page of recent name changes.
     *
     * @param  string  $status  approved | pending | denied | skipped
     * @param  int  $limit  WOM caps at 50 per page
     *
     * @throws GuzzleException
     */
    public function recentNameChanges(string $status = 'approved', int $limit = 50, int $offset = 0): ResponseInterface
    {
        return $this->client->request('GET', $this->url.'/names', [
            'query' => [
                'status' => $status,
                'limit' => $limit,
                'offset' => $offset,
            ],
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => $this->userAgent,
            ],
        ]);
    }
}
