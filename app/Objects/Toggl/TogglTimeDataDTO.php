<?php

namespace App\Objects\Toggl;

use Spatie\DataTransferObject\DataTransferObject;

class TogglTimeDataDTO extends DataTransferObject
{
    public ?int $id;
    public ?int $pid;
    public ?string $project;
    public ?string $description;
    public ?string $start;
    public ?string $end;
    public ?int $dur;
}