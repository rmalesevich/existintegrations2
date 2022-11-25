<?php

namespace App\Objects\Trakt;

use Spatie\DataTransferObject\DataTransferObject;

class TraktMovieDTO extends DataTransferObject
{
	public ?string $title;
	public ?int $year;
	
	/** @var \App\Objects\Trakt\TraktIdsDTO|null */
	public $ids;

	public ?int $runtime;
}