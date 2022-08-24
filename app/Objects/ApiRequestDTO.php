<?php

namespace App\Objects;

use Spatie\DataTransferObject\DataTransferObject;

class ApiRequestDTO extends DataTransferObject
{
    public bool $success;

    public string $statusCode;

    public ?array $responseBody;
}