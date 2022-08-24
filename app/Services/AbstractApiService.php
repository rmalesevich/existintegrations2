<?php

namespace App\Services;

use GuzzleHttp\Client;

abstract class AbstractApiService
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function request(string $method, string $uri, array $params = [])
    {
        $request = $this->client->request($method, $uri, $params);

        return $request->getBody();
    }
}