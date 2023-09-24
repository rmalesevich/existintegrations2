<?php

namespace App\Objects\Exist;

use Spatie\DataTransferObject\DataTransferObject;

class ExistAttributesOwnedResultsDTO extends DataTransferObject
{
	public string $name;
	public bool $active;
}