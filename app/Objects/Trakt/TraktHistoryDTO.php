<?php

namespace App\Objects\Trakt;

use Spatie\DataTransferObject\DataTransferObject;

class TraktHistoryDTO extends DataTransferObject
{
	/** @var \App\Objects\Trakt\TraktHistoryDataDTO[] */
	public $data;

	/**
	 * The Trakt History object doesn't contain a name for the array. To get around
	 * this, a static method is used that accepts the json_decoded response from Trakt.
	 */
	public static function fromRequest(array $historyResponse): self
	{
		$data = array();

		foreach ($historyResponse as $historyObject) {
			array_push($data, new TraktHistoryDataDTO($historyObject));
		}

		$history = new TraktHistoryDTO();
		$history->data = $data;
		return $history;
	}
}