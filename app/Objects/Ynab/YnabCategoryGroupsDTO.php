<?php

namespace App\Objects\Ynab;

use Spatie\DataTransferObject\DataTransferObject;

class YnabCategoryGroupsDTO extends DataTransferObject
{
	public string $id;
	public string $name;
	public bool $hidden;
	public bool $deleted;

	/** @var \App\Objects\Ynab\YnabCategoriesDTO[] $categories */
	public array $categories;
}