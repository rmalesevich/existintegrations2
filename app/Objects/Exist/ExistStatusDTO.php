<?php

namespace App\Objects\Exist;

use Spatie\DataTransferObject\DataTransferObject;

class ExistStatusDTO extends DataTransferObject
{
	public array $success;
	public array $failed;
}