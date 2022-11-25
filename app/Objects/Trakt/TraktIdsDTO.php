<?php

namespace App\Objects\Trakt;

use Spatie\DataTransferObject\DataTransferObject;

class TraktIdsDTO extends DataTransferObject
{
	public int $trakt;
	public ?string $slug;
	public ?string $imdb;
	public ?string $tmdb;
	public ?string $tvdb;
}