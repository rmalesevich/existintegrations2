<?php

namespace App\Objects;

use Ekut\SpatieDtoValidators\Choice;
use Ekut\SpatieDtoValidators\Url;
use Spatie\DataTransferObject\DataTransferObject;

class ApiRequestDTO extends DataTransferObject
{
    #[Choice(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])]
    public string $method;

    #[URL()]
    public string $uri;

    public ?array $params;
}