<?php

namespace App\Services;

use App\Objects\ApiRequestDTO;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class AbstractApiService
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Call the external API endpoint.
     * 
     * All calls are logged using the default application logger. On return it will return an object that
     * can be processed.
     * 
     * @param string $method (GET, POST, PUT, PATCH, DELETE)
     * @param string $uri
     * @param array $params
     * @return ApiRequestDTO
     * 
     */
    public function request(string $method, string $uri, array $params = []): ApiRequestDTO
    {
        $correlationId = (string) Str::uuid();
        Log::info(sprintf("%s %s %s ", $correlationId, $method, $uri), $params);
        
        $success = true;
        $statusCode = 0;
        $responseBody = null;

        try {
            $request = $this->client->request($method, $uri, $params);

            $statusCode = $request->getStatusCode();
            $responseBody = json_decode($request->getBody(), true);

            Log::debug($correlationId, $responseBody);
        } catch (ClientException $exception) {
            $statusCode = $exception->getResponse()->getStatusCode();
            Log::error(sprintf("%s ClientException %s", $correlationId, $exception->getMessage()));
        } catch (ServerException $exception) {
            $statusCode = $exception->getResponse()->getStatusCode();
            Log::error(sprintf("%s ServerException %s", $correlationId, $exception->getMessage()));
        }

        return new ApiRequestDTO(
            success: $success,
            statusCode: $statusCode,
            responseBody: $responseBody
        );

    }

}