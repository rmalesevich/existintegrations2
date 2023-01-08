<?php

namespace App\Objects\Ynab;

use Spatie\DataTransferObject\DataTransferObject;

class YnabErrorDTO extends DataTransferObject
{
	public string $id;
	public string $name;
	public string $detail;
}