<?php

namespace App\Objects\Ynab;

use Spatie\DataTransferObject\DataTransferObject;

class YnabCategoryDTO extends DataTransferObject
{
	/** @var \App\Objects\Ynab\YnabCategoryDataDTO|null */
    public $data;

    /** @var \App\Objects\Ynab\YnabErrorDTO|null */
    public $error;
}