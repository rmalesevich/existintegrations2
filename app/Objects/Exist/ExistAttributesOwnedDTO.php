<?php

namespace App\Objects\Exist;

use Spatie\DataTransferObject\DataTransferObject;

class ExistAttributesOwnedDTO extends DataTransferObject
{
	public int $count;

	/** @var \App\Objects\Exist\ExistAttributesOwnedResultsDTO[] $results */
	public array $results;
}