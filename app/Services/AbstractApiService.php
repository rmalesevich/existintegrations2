<?php

namespace App\Services;

use App\Objects\ApiRequestDTO;
use App\Objects\ApiResponseDTO;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class AbstractApiService
{
    /**
     * Call the external API endpoint.
     * 
     * All calls are logged using the default application logger. On return it will return an object that
     * can be processed.
     * 
     * @param ApiRequestDTO $apiRequest
     * @return ApiResponseDTO
     * 
     */
    public function request(ApiRequestDTO $apiRequest): ApiResponseDTO
    {
        $correlationId = (string) Str::uuid();
        Log::info(sprintf("%s %s %s ", $correlationId, $apiRequest->method, $apiRequest->uri), $apiRequest->params ?? array());

        $client = new Client();
        
        $success = false;
        $statusCode = 0;
        $responseBody = null;

        try {
            $request = $client->request($apiRequest->method, $apiRequest->uri, $apiRequest->params ?? array());

            $success = true;
            $statusCode = $request->getStatusCode();
            $responseBody = json_decode($request->getBody(), true);

            Log::debug($correlationId, $responseBody ?? array());
        } catch (ClientException $exception) {
            $statusCode = $exception->getResponse()->getStatusCode();
            Log::error(sprintf("%s ClientException %s", $correlationId, $exception->getMessage()));
        } catch (ServerException $exception) {
            $statusCode = $exception->getResponse()->getStatusCode();
            Log::error(sprintf("%s ServerException %s", $correlationId, $exception->getMessage()));
        }

        return new ApiResponseDTO(
            success: $success,
            statusCode: $statusCode,
            responseBody: $responseBody
        );

    }

}