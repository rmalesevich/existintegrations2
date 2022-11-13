<?php

namespace App\Objects\WhatPulse;

use Illuminate\Support\Optional;
use Spatie\DataTransferObject\DataTransferObject;

class WhatPulsePulseDTO extends DataTransferObject
{
	/** @var \App\Objects\WhatPulse\WhatPulsePulseDataDTO[]|null */
	public $data;
	
	/**
	 * Build the array of WhatPulsePulseDTO from the raw JSON request because the JSON is formatted oddly
	 */
	public static function fromRequest($pulsesResponse): self
	{
		$data = array();

		$keys = array_keys($pulsesResponse);
		foreach ($keys as $key) {
			array_push($data, WhatPulsePulseDataDTO::fromRequest($key, $pulsesResponse[$key]));
		}

		$pulse = new WhatPulsePulseDTO();
		$pulse->data = $data;
		return $pulse;
	}
}