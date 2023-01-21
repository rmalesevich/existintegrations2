<?php

namespace App\Objects\Toggl;

use Spatie\DataTransferObject\DataTransferObject;

class TogglProjectDataDTO extends DataTransferObject
{
    public ?int $id;
    public ?string $name;
    public ?bool $active;
}