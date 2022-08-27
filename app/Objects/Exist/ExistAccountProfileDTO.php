<?php

namespace App\Objects\Exist;

use Spatie\DataTransferObject\DataTransferObject;

class ExistAccountProfileDTO extends DataTransferObject
{
	public string $username;
	public string $first_name;
	public string $last_name;
	public string $avatar;
	public string $timezone;
	public string $local_time;
	public bool $imperial_distance;
	public bool $imperial_weight;
	public bool $imperial_energy;
	public bool $imperial_liquid;
	public bool $imperial_temperature;
	public bool $trial;
	public bool $delinquent;
}