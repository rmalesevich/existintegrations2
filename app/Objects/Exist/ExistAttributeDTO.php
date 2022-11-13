<?php

namespace App\Objects\Exist;

use Spatie\DataTransferObject\DataTransferObject;

class ExistAttributeDTO extends DataTransferObject
{
	public array $success;
	public array $failed;
}