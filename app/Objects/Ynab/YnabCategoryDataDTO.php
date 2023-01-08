<?php

namespace App\Objects\Ynab;

use Spatie\DataTransferObject\DataTransferObject;

class YnabCategoryDataDTO extends DataTransferObject
{
	/** @var \App\Objects\Ynab\YnabCategoryGroupsDTO[] $category_groups */
	public $category_groups;

	public int $server_knowledge;
}