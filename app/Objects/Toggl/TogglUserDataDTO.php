<?php

namespace App\Objects\Toggl;

use Spatie\DataTransferObject\DataTransferObject;

class TogglUserDataDTO extends DataTransferObject
{
    public ?int $id;
    public ?string $default_wid;
    public ?string $fullname;
}