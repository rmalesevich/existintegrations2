<?php

namespace App\Objects\WhatPulse;

use Illuminate\Support\Optional;
use Spatie\DataTransferObject\DataTransferObject;

class WhatPulseUserDTO extends DataTransferObject
{
	public ?string $AccountName;
	public ?string $error;
}