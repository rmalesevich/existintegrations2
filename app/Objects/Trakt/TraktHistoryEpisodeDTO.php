<?php

namespace App\Objects\Trakt;

use Spatie\DataTransferObject\DataTransferObject;

class TraktHistoryEpisodeDTO extends DataTransferObject
{
	public ?int $season;
	public ?int $number;
	public ?string $title;
	
	/** @var \App\Objects\Trakt\TraktIdsDTO|null */
	public $ids;
}