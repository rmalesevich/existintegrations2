<?php

namespace App\Objects\Ynab;

use Spatie\DataTransferObject\DataTransferObject;

class YnabCategoriesDTO extends DataTransferObject
{
	public string $id;
	public string $category_group_id;
	public string $name;
	public bool $hidden;
	public $original_category_group_id;
	public $note;
	public int $budgeted;
	public int $activity;
	public int $balance;
	public $goal_type;
	public $goal_creation_month;
	public int $goal_target;
	public $goal_target_month;
	public $goal_percentage_complete;
	public $goal_months_to_budget;
	public $goal_under_funded;
	public $goal_overall_funded;
	public $goal_overall_left;
	public bool $deleted;
}