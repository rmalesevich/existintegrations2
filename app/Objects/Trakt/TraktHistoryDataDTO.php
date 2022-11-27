<?php

namespace App\Objects\Trakt;

use Spatie\DataTransferObject\DataTransferObject;

class TraktHistoryDataDTO extends DataTransferObject
{
	public ?int $id;
	public ?string $watched_at;
	public ?string $action;
	public ?string $type;
	
	/** @var \App\Objects\Trakt\TraktHistoryEpisodeDTO|null */
    public $episode;

    /** @var \App\Objects\Trakt\TraktHistoryShowDTO|null */
    public $show;

    /** @var \App\Objects\Trakt\TraktHistoryMovieDTO|null */
    public $movie;

}