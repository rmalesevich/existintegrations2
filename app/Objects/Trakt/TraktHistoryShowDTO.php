<?php

namespace App\Objects\Trakt;

use Spatie\DataTransferObject\DataTransferObject;

class TraktHistoryShowDTO extends DataTransferObject
{
	public ?string $title;
	public ?int $year;
	
	/** @var \App\Objects\Trakt\TraktIdsDTO|null */
	public $ids;
}