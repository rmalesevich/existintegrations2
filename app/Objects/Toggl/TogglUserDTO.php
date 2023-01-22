<?php

namespace App\Objects\Toggl;

use Spatie\DataTransferObject\DataTransferObject;

class TogglUserDTO extends DataTransferObject
{
    public ?int $since;

    /** @var \App\Objects\Toggl\TogglUserDataDTO|null */
    public $data;
}