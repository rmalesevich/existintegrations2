<?php

namespace App\Objects;

use Spatie\DataTransferObject\DataTransferObject;

class StandardDTO extends DataTransferObject
{
    public bool $success;

    public ?string $message;
}