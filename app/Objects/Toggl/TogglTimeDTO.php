<?php

namespace App\Objects\Toggl;

use Spatie\DataTransferObject\DataTransferObject;

class TogglTimeDTO extends DataTransferObject
{
    public ?int $total_count;
    public ?int $per_page;
    
    /** @var \App\Objects\Toggl\TogglTimeDataDTO[]|null */
    public $data;
}