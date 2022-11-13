<?php

namespace App\Objects\WhatPulse;

use Illuminate\Support\Optional;
use Spatie\DataTransferObject\DataTransferObject;

class WhatPulsePulseDataDTO extends DataTransferObject
{
	public ?string $PulseID;
	public ?string $Timedate;
	public ?int $Keys;
	public ?int $Clicks;
	public ?int $DownloadMB;
	public ?int $UploadMB;
	public ?int $UptimeSeconds;

	/**
	 * Build the WhatPulse Data Object from the Returned Object since it's formatted oddly
	 */
	public static function fromRequest($key, $pulseObject): self
	{
		$pulse = new WhatPulsePulseDataDTO();

		$pulse->PulseID = $key;
		$pulse->Timedate = $pulseObject['Timedate'];
		$pulse->Keys = (int) $pulseObject['Keys'];
		$pulse->Clicks = (int) $pulseObject['Clicks'];
		$pulse->DownloadMB = (int) $pulseObject['DownloadMB'];
		$pulse->UploadMB = (int) $pulseObject['UploadMB'];
		$pulse->UptimeSeconds = (int) $pulseObject['UptimeSeconds'];

		return $pulse;
	}
}